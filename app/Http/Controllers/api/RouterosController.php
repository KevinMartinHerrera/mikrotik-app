<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\models\RouterOs;
use App\Myhelper\RouterosAPI;
use Illuminate\Support\Facades\Hash;

class RouterosController extends Controller
{
    public $API=[], $routeros_data=[], $connection;

    public function test_api()
    {
        try{
            return response()->json([
                'success' => true,
                'message' => 'Welcome in Routeros API'
            ]);
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetch data Routeros API'
            ]);
        }
    }

    public function store_routeros($data)
    {
        $API = new RouterosAPI;
        $connection = $API->connect($data['ip_address'], $data['login'], $data['password']);
    
        if (!$connection) {
            return redirect('/')->with('error', 'Error: No se pudo conectar a RouterOS');
        }
    
        $store_routeros = new RouterOs();
        $store_routeros->identity = $API->comm('/system/identity/print')[0]['name'];
        $store_routeros->ip_address = $data['ip_address'];
        $store_routeros->login = $data['login'];
        $store_routeros->password = $data['password'];
        $store_routeros->connect = $connection;
        $store_routeros->save();
    
        return redirect('/')->with('success', 'Datos de RouterOS guardados correctamente');
    }
    
    public function routeros_connection(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'login' => 'required',
                'password' => 'required'
            ]);
    
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
    
            $req_data = $request->only(['ip_address', 'login', 'password']);
            $routeros_db = RouterOs::where('ip_address', $req_data['ip_address'])->first();
    
            if ($routeros_db) {
                $API = new RouterosAPI;
                $connection = $API->connect($req_data['ip_address'], $req_data['login'], $req_data['password']);
    
                if ($connection) {
                    $routeros_db->login = $req_data['login'];
                    $routeros_db->password = $req_data['password'];
                    $routeros_db->connect = $connection;
                    $routeros_db->save();
    
                    // Guardar autenticación y datos en la sesión
                    session(['authenticated' => true]);
                    session(['ip_address' => $req_data['ip_address']]);
    
            
                    $groups = $API->comm('/user/group/print');
                    session(['groups' => $groups]);
    
                    // Obtener y almacenar las interfaces de red
                    $interfaces = $API->comm('/interface/print');
                    session(['interfaces' => $interfaces]);

                    $users = $API->comm('/ip/hotspot/user/print');
                    session(['users' => $users]);
    
                    // Obtener y almacenar las direcciones IP
                    $ips = $API->comm('/ip/address/print');
                    session(['ips' => $ips]);
    
                    return redirect()->route('show.Dashboard');
                } else {
                    return back()->with('error', 'Login o contraseña incorrectos');
                }
            } else {
                return $this->store_routeros($req_data);
            }
        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    
    public function check_routeros_connection($data)
    {
        $routeros_db = RouterOs::where('ip_address', $data['ip_address'])->get();
    
        if (count($routeros_db) > 0) {
            $API = new RouterosAPI;
            $connection = $API->connect($routeros_db[0]['ip_address'], $routeros_db[0]['login'], $routeros_db[0]['password']);
    
            if ($routeros_db[0]['connect'] !== $connection) {
                RouterOs::where('id', $routeros_db[0]['id'])->update(['connect' => $connection]);
            }
    
            if (!$connection) {
                return false;
            }
    
            // Verificación de la respuesta de la API antes de acceder a los datos
            $identity = $API->comm('/system/identity/print');
    
            if (is_array($identity) && isset($identity[0]['name'])) {
                $this->routeros_data = [
                    'identity' => $identity[0]['name'],
                    'ip_address' => $routeros_db[0]['ip_address'],
                    'login' => $routeros_db[0]['login'],
                    'password' => Hash::make($routeros_db[0]['password']),
                    'connect' => $connection
                ];
    
                $this->API = $API;
                $this->connection = $connection;
    
                return true;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch identity from RouterOS'
                ]);
            }
        } else {
            echo "Routeros data not available in database, please create connection again!";
        }
    }

    public function set_interface(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'id' => 'required',
                'interface' => 'required',
                'name' => 'required'
            ]);
    
            if($validator->fails()) {
                return response()->json($validator->errors(), 404);
            }
    
            // Verificamos la conexión al RouterOS
            if($this->check_routeros_connection($request->all())):
                // Aseguramos que $this->API sea un objeto de RouterosAPI
                if (is_object($this->API)) {
                    $interface_lists = $this->API->comm('/interface/print');
                    
                    if (is_array($interface_lists)) {
                        // Buscamos la interfaz por nombre
                        $find_interface = array_search($request->name, array_column($interface_lists, 'name'));
    
                        if($find_interface === false):
                            $set_interface = $this->API->comm('/interface/set', [
                                '.id' => "*$request->id",
                                'name' => "$request->name"
                            ]);
    
                            return response()->json([
                                'success' => true,
                                'message' => "Successfully set interface from: $request->interface, to: $request->name",
                                'interface_lists' => $this->API->comm('/interface/print')
                            ]);
                        else:
                            return response()->json([
                                'success' => false,
                                'message' => "Interface name: $request->name, with .id: *$request->id has already been taken from RouterOS",
                                'interface_lists' => $this->API->comm('/interface/print')
                            ]);
                        endif;
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unexpected response from RouterOS API, expected an array.'
                        ]);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'RouterOS API instance is not an object.'
                    ]);
                }
            else:
                return response()->json([
                    'error' => true,
                    'message' => 'RouterOS not connected, check the login details!'
                ]);
            endif;
    
        } catch(Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data from RouterOS API: ' . $e->getMessage()
            ]);
        }
    }
    public function add_new_address(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'address' => 'required',
                'interface' => 'required'
            ]);
    
            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Error en la validación de datos.');
            }
    
            if ($this->check_routeros_connection($request->all())) {
                if (is_object($this->API)) {
                    $address_lists = $this->API->comm('/ip/address/print');
                    $find_interface = array_search($request->interface, array_column($address_lists, 'interface'));
    
                    if ($find_interface !== false) {
                        return redirect()->back()->with('error', "La interfaz {$request->interface} ya tiene una dirección IP.");
                    }
    
                    $this->API->comm('/ip/address/add', [
                        'address' => $request->address,
                        'interface' => $request->interface
                    ]);
    
                    return redirect()->back()->with('success', 'Dirección IP añadida correctamente.');
                }
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error en la conexión: ' . $e->getMessage());
        }
    }
    


    public function add_ip_route(Request $request)
    {
        try {
            // Validar la solicitud
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required|ip',  // Asegúrate de que la IP sea válida
                'gateway' => 'required|ip'  // Validar que el gateway sea una dirección IP
            ]);
    
            if ($validator->fails()) {
                dd($validator->errors()->all());  // Muestra los errores de validación

                return redirect()->back()->with('error', 'Error en la validación de datos.');
            }
    
            // Verificar la conexión a RouterOS
            if ($this->check_routeros_connection($request->all())) {
                if (is_object($this->API)) {  // Verificar si $this->API es un objeto
                    // Obtener las rutas existentes
                    $route_lists = $this->API->comm('/ip/route/print');
                    
                    // Buscar si el gateway ya está presente en las rutas
                    $find_route_lists = array_search($request->gateway, array_column($route_lists, 'gateway'));
    
                    if ($find_route_lists !== false) {
                        // Si la ruta ya existe, mostrar error
                        return redirect()->back()->with('error', "La dirección de gateway {$request->gateway} ya está en uso.");
                    } else {
                        // Si la ruta no existe, agregarla
                        $this->API->comm('/ip/route/add', [
                            'gateway' => $request->gateway,
                            'dst-address' => $request->ip_address  // Puedes agregar la IP de destino si es necesario
                        ]);
    
                        return redirect()->back()->with('success', "Ruta añadida con éxito con el gateway: {$request->gateway}.");
                    }
                } else {
                    return redirect()->back()->with('error', 'La API no está inicializada correctamente.');
                }
            } else {
                return redirect()->back()->with('error', 'No se pudo conectar a RouterOS.');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al obtener datos de la API de RouterOS: ' . $e->getMessage());
        }
    }
    
    

    public function add_dns_servers(Request $request)
    {
        try{
            $schema = [
                'ip_address' => 'required',
                'servers' => 'required',
                'remote_requests' => 'required'
            ];

            $validator = Validator::make($request->all(), $schema);

            if($validator->fails()) return response()->json($validator->errors(), 404);

            if($this->check_routeros_connection($request->all())):
                if (is_object($this->API)) {
                    $add_dns = $this->API->comm('/ip/dns/set', [
                        'servers' => $request->servers,
                        'allow-remote-requests' => $request->remote_requests
                    ]);

                    $dns_lists = $this->API->comm('/ip/dns/print');

                    if(count($add_dns) == 0):
                        return response()->json([
                            'success' => true,
                            'message' => 'Successfully added new DNS servers',
                            'dns_lists' => $dns_lists
                        ]);
                    else:
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to add DNS servers',
                            'routeros_data' => $this->routeros_data
                        ]);
                    endif;
                }
            endif;

        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data from RouterOS API, '.$e->getMessage()
            ]);
        }
    }

    public function masquerade_srcnat(Request $request)
    {
        try{
            $schema = [
                'ip_address' => 'required',
                'chain' => 'required',
                'protocol' => 'required',
                'out_interface' => 'required',
                'action' => 'required'
            ];
            $validator = Validator::make($request->all(), $schema);
            if($validator->fails()) return response()->json($validator->errors(), 404);

            if($this->check_routeros_connection($request->all())):
                if (is_object($this->API)) {
                    $check_src_nat = $this->API->comm('/ip/firewall/nat/print');

                    if(count($check_src_nat) == 0):
                        $add_firewall_nat = $this->API->comm('/ip/firewall/nat/add', [
                            'chain' => $request->chain,
                            'action' => $request->action,
                            'protocol' => $request->protocol,
                            'out-interface' => $request->out_interface
                        ]);

                        $firewall_nat_lists = $this->API->comm('/ip/firewall/nat/print');

                        return response()->json([
                            'success' => true,
                            'message' => "Successfully added firewall NAT for $request->chain",
                            'nat_lists' => $firewall_nat_lists
                        ]);
                    else:
                        return response()->json([
                            'error' => true,
                            'message' => "srcnat for out-interface $request->out_interface already exists",
                            'srcnat_lists' => $check_src_nat
                        ]);
                    endif;
                }
            endif;
        }catch(Exception $e){
            return response()->json(['error' => true, 'message' => 'Error fetching RouterOS API '.$e->getMessage()]);
        }
    }

    public function routeros_reboot(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required'
            ]);
    
            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Error en la validación de datos.');
            }
    
            if ($this->check_routeros_connection($request->all())) {
                if (is_object($this->API)) {
                    $this->API->comm('/system/reboot');
    
                    return redirect()->route('show.login')->with('success', 'El sistema de RouterOS se ha reiniciado.');
                }
            }
    
            return redirect()->back()->with('error', 'No se pudo establecer la conexión con RouterOS.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al intentar reiniciar RouterOS: ' . $e->getMessage());
        }
    }
    
    public function routeros_shutdown(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required'
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 404);
            }
    
            if ($this->check_routeros_connection($request->all())) {
                if (is_object($this->API)) {
                    // Actualizar el estado de la conexión en la base de datos
                    RouterOs::where('ip_address', $request->ip_address)->update(['connect' => 0]);
    
                    // Apagar el sistema RouterOS
                    $shutdown = $this->API->comm('/system/shutdown');
    
                    return redirect()->route('show.login')->with('success', 'El sistema de RouterOS se ha Apagado Correctamente');
                }
            }
    
            return response()->json([
                'success' => false,
                'message' => 'No se pudo establecer la conexión con RouterOS.'
            ]);
    
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al intentar apagar RouterOS: ' . $e->getMessage()
            ]);
        }
    }
    



    
    
    public function showDashboard()
    {
        $routeros_data = session('routeros_data'); // Recuperar datos de sesión

        if (!$routeros_data) {
            return redirect()->route('login')->withErrors(['No hay conexión activa con RouterOS.']);
        }

        return view('dashboard', compact('routeros_data'));
    }
    public function add_user(Request $request)
    {
        try {
            // Validación de los campos
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'username' => 'required',
                'password' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 404);
            }
    
            // Agregar el usuario en RouterOS
            if ($this->check_routeros_connection($request->all())) {
                if (is_object($this->API)) {
                    $add_user = $this->API->comm('/ip/hotspot/user/add', [
                        'name' => $request->username,
                        'password' => $request->password,
                        'address' => $request->ip_address, // Asignar la IP seleccionada
                    ]);
    
                    return response()->json([
                        'success' => true,
                        'message' => "User {$request->username} added successfully.",
                        'user_data' => $add_user,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'API client is not initialized properly.'
                    ]);
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding user: ' . $e->getMessage()
            ]);
        }
    }
    

    public function create_user_group(Request $request)
    {
        try {
            // Validación de datos de entrada
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'group_name' => 'required',
                'policies' => 'required'  // ejemplo de formato: "ssh,ftp,winbox,read"
            ]);

            if ($validator->fails()) return response()->json($validator->errors(), 404);

            // Verificar la conexión a RouterOS
            if ($this->check_routeros_connection($request->all())) {
                if (is_object($this->API)) {
                    // Crear el grupo de usuario
                    $create_group = $this->API->comm('/user/group/add', [
                        'name' => $request->group_name,
                        'policy' => $request->policies
                    ]);

                    // Obtener y devolver la lista actualizada de grupos
                    $group_lists = $this->API->comm('/user/group/print');

                    return response()->json([
                        'success' => true,
                        'message' => "Grupo de usuario '$request->group_name' creado con éxito",
                        'group_lists' => $group_lists
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'API no inicializada correctamente.'
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo conectar a RouterOS. Verifica los datos de conexión.'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear grupo en RouterOS: ' . $e->getMessage()
            ]);
        }
    }



    
    

    

    
}
