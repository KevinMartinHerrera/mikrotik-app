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
    
            $req_data = [
                'ip_address' => $request->ip_address,
                'login' => $request->login,
                'password' => $request->password
            ];
    
            $routeros_db = RouterOs::where('ip_address', $req_data['ip_address'])->first();
    
            if ($routeros_db) {
                $API = new RouterosAPI;
                $connection = $API->connect($req_data['ip_address'], $req_data['login'], $req_data['password']);
    
                if ($connection) {
                    $routeros_db->login = $req_data['login'];
                    $routeros_db->password = $req_data['password'];
                    $routeros_db->connect = $connection;
                    $routeros_db->save();
    
                    // Redirige a la vista dashboard con los datos de RouterOS
                    return redirect()->route('dashboard')->with('routeros_data', $routeros_db);
                } else {
                    return back()->withErrors(['message' => 'RouterOS no conectado, login o contraseña incorrectos'])->withInput();
                }
    
            } else {
                // Si no existe en la base de datos, creamos una nueva entrada y redirigimos al dashboard
                $new_data = $this->store_routeros($req_data);
                return redirect()->route('dashboard')->with('routeros_data', $new_data->original['routeros_data']);
            }
    
        } catch (Exception $e) {
            return back()->withErrors(['message' => 'Error en la conexión: ' . $e->getMessage()])->withInput();
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
            // Validar la solicitud
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'address' => 'required',
                'interface' => 'required'
            ]);
    
            if ($validator->fails()) return response()->json($validator->errors(), 404);
    
            // Verificar la conexión a RouterOS
            if ($this->check_routeros_connection($request->all())) {
                // Verificar que $this->API sea un objeto
                if (is_object($this->API)) {
                    // Obtener la lista de direcciones IP
                    $address_lists = $this->API->comm('/ip/address/print');
    
                    // Buscar si la interfaz ya tiene la dirección IP
                    $find_interface = array_search($request->interface, array_column($address_lists, 'interface'));
    
                    if ($find_interface !== false) {
                        return response()->json([
                            'error' => true,
                            'message' => "Interface $request->interface already has an IP address on RouterOS.",
                            'suggestion' => "Did you want to edit the IP address from interface: $request->interface",
                            'address_lists' => $address_lists
                        ]);
                    }
    
                    // Agregar la nueva dirección IP
                    $add_address = $this->API->comm('/ip/address/add', [
                        'address' => $request->address,
                        'interface' => $request->interface
                    ]);
    
                    // Obtener la lista actualizada de direcciones IP
                    $list_address = $this->API->comm('/ip/address/print');
    
                    // Verificar si se agregó correctamente
                    $find_address_id = array_search($add_address, array_column($list_address, '.id'));
    
                    if ($find_address_id !== false) {
                        return response()->json([
                            'success' => true,
                            'message' => "Successfully added new address to interface: $request->interface",
                            'address_lists' => $list_address
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => isset($add_address['!trap']) ? $add_address['!trap'][0]['message'] : 'Failed to add address.',
                            'address_lists' => $list_address,
                            'routeros_data' => $this->routeros_data
                        ]);
                    }
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
                'message' => 'Error fetching data from RouterOS API: ' . $e->getMessage()
            ]);
        }
    }

    public function add_ip_route(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'gateway' => 'required'
            ]);

            if ($validator->fails()) return response()->json($validator->errors(), 404);

            if ($this->check_routeros_connection($request->all())) {
                if (is_object($this->API)) { // Verificar si $this->API es un objeto
                    $route_lists = $this->API->comm('/ip/route/print');
                    $find_route_lists = array_search($request->gateway, array_column($route_lists, 'gateway'));

                    if ($find_route_lists === 0) {
                        return response()->json([
                            'success' => false,
                            'message' => "Gateway address : $request->gateway has already been taken",
                            'route_lists' => $this->API->comm('/ip/route/print')
                        ]);
                    } else {
                        $add_route_lists = $this->API->comm('/ip/route/add', [
                            'gateway' => $request->gateway
                        ]);
                        return response()->json([
                            'success' => true,
                            'message' => "Successfully added new routes with gateway : $request->gateway",
                            'route_lists' => $this->API->comm('/ip/route/print'),
                            'routeros_data' => $this->routeros_data
                        ]);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'API is not initialized correctly.'
                    ]);
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data from RouterOS API: ' . $e->getMessage()
            ]);
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
        try{
            $schema = [
                'ip_address' => 'required'
            ];

            $validator = Validator::make($request->all(), $schema);

            if($validator->fails()) return response()->json($validator->errors(), 404);

            if($this->check_routeros_connection($request->all())):
                if (is_object($this->API)) {
                    $reboot = $this->API->comm('/system/reboot');

                    return response()->json([
                        'reboot' => true,
                        'message' => 'RouterOS has rebooted the system',
                        'connection' => $this->connection
                    ]);
                }
            endif;
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data from RouterOS API, '.$e->getMessage()
            ]);
        }
    }

    public function routeros_shutdown(Request $request)
    {
        try{
            $schema = [
                'ip_address' => 'required'
            ];

            $validator = Validator::make($request->all(), $schema);

            if($validator->fails()) return response()->json($validator->errors(), 404);

            if($this->check_routeros_connection($request->all())):
                if (is_object($this->API)) {
                    $update_connection = RouterOs::where('ip_address', $request->ip_address)->update(['connect' => 0]);

                    $new_routeros_data = RouterOs::where('ip_address', $request->ip_address)->get();

                    $shutdown = $this->API->comm('/system/shutdown');

                    return response()->json([
                        'shutdown' => true,
                        'message' => 'RouterOS has shut down the system',
                        'connection' => $new_routeros_data[0]['connect']
                    ]);
                }
            endif;
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data from RouterOS API, '.$e->getMessage()
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
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'username' => 'required',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 404);
            }

            if ($this->check_routeros_connection($request->all())) {
                if (is_object($this->API)) {
                    // Agregar usuario en Mikrotik
                    $add_user = $this->API->comm('/ip/hotspot/user/add', [
                        'name' => $request->username,
                        'password' => $request->password,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => "User {$request->username} added successfully.",
                        'user_data' => $add_user
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

    public function set_bandwidth_limit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'target' => 'required', // IP o nombre de usuario
                'download_limit' => 'required', // En bps, como '2M' para 2 Mbps
                'upload_limit' => 'required',   // En bps, como '1M' para 1 Mbps
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 404);
            }

            if ($this->check_routeros_connection($request->all())) {
                if (is_object($this->API)) {
                    // Configurar la cola simple para el usuario
                    $set_queue = $this->API->comm('/queue/simple/add', [
                        'name' => $request->target,
                        'target' => $request->target,
                        'max-limit' => "{$request->upload_limit}/{$request->download_limit}"
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => "Bandwidth limit set for {$request->target}.",
                        'queue_data' => $set_queue
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
                'message' => 'Error setting bandwidth limit: ' . $e->getMessage()
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
