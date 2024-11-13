<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RouterOS Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            height: 100vh;
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: white;
            padding-top: 30px;
        }
        .sidebar h2 {
            text-align: center;
            font-size: 1.5rem;
            color: #f8f9fa;
        }
        .sidebar a {
            color: #adb5bd;
            padding: 15px 20px;
            display: block;
            text-decoration: none;
            font-size: 1rem;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: #f8f9fa;
        }
        .main-content {
            margin-left: 280px;
            padding: 30px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: none;
            border-radius: 10px;
            margin-bottom: 20px;
            background-color: #fff;
        }
        .card-header {
            font-size: 1.25rem;
            font-weight: bold;
            background-color: #495057;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .card-body {
            padding: 20px;
        }
        .footer {
            text-align: center;
            background-color: #343a40;
            color: white;
            padding: 15px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            margin-left: -280px;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .btn-action {
            width: 100%;
            text-align: center;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>RouterOS Dashboard</h2>
        <a href="#add-new-address">Añadir Dirección IP</a>
        <a href="#add-ip-route">Añadir Ruta IP</a>
        <a href="#add-dns-servers">Configurar Servidores DNS</a>
        <a href="#masquerade-srcnat">Configurar NAT</a>
        <a href="#add-user">Agregar Usuario</a>
        <a href="#set-bandwidth-limit">Limitar Ancho de Banda</a>
        <a href="#create-user-group">Crear Grupo de Usuario</a>
        <a href="{{ route('logout') }}">Cerrar Sesión</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Sección de acciones de RouterOS -->
        <div class="card">
            <div class="card-header">Acciones de RouterOS</div>
            <div class="card-body">
                <form action="{{ route('routeros.reboot') }}" method="POST" class="mb-3">
                    @csrf
                    <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
                    <button type="submit" class="btn btn-primary btn-action">Reiniciar RouterOS</button>
                </form>
                <form action="{{ route('routeros.shutdown') }}" method="POST" class="mb-3">
                    @csrf
                    <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
                    <button type="submit" class="btn btn-danger btn-action">Apagar RouterOS</button>
                </form>
            </div>
        </div>

    <!-- Formulario para establecer la interfaz -->
    <div class="card" id="set-interface">
        <div class="card-header">Establecer Interfaz de Red</div>
        <div class="card-body">
            <form action="{{ route('set.interface') }}" method="POST">
                @csrf
                <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">

                <div class="mb-3">
                    <select class="form-control" id="id" name="id" required>
                        @foreach(session('interfaces', []) as $interface)
                        <option value="{{ $interface['name'] }}">{{ $interface['name'] }}</option>
                        @endforeach   
                        
                    </select>
                </div>

                <div class="mb-3">
                    <label for="interface" class="form-label">Interfaz Actual</label>
                    <input type="text" class="form-control" id="interface" name="interface" placeholder="Nombre de la interfaz actual" required>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nuevo Nombre de la Interfaz</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Nuevo nombre para la interfaz" required>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Interfaz</button>
            </form>
        </div>
    </div>


        <!-- Formulario para añadir una ruta IP -->
        <div class="card" id="add-ip-route">
            <div class="card-header">Añadir Ruta IP</div>
            <div class="card-body">
                <form method="POST" action="{{ route('add.route') }}">
                    @csrf
                    <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
                    <div class="mb-3">
                        <label for="gateway" class="form-label">Gateway</label>
                        <input type="text" class="form-control" id="gateway" name="gateway" required>
                    </div>
        
                    <button type="submit" class="btn btn-primary">Añadir Ruta</button>
                </form>
            </div>
        </div>
        




        <!-- Limitar Ancho de Banda -->
        <div class="card" id="set-bandwidth-limit">
            <div class="card-header">Establecer Límite de Ancho de Banda</div>
            <div class="card-body">
                <form action="{{ route('set.bandwidth') }}" method="POST">
                    @csrf
                    <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
                    <div class="mb-3">
                        <label for="target" class="form-label">Selecciona el Usuario DHCP</label>
                        <select class="form-control" id="target" name="target" required>
                            <option value="" disabled selected>Selecciona un usuario...</option>
                            @foreach(session('users', []) as $user)
                                <option value="{{ $user['.id'] }}"> - {{ $user['address'] ?? 'Sin IP' }}</option>
                            @endforeach
                        </select>
                    </div>
            
                    <div class="mb-3">
                        <label for="download_limit" class="form-label">Límite de Descarga (kbps)</label>
                        <input class="form-control" id="download_limit" name="download_limit" placeholder="3M" required>
                    </div>
            
                    <div class="mb-3">
                        <label for="upload_limit" class="form-label">Límite de Subida (kbps)</label>
                        <input class="form-control" id="upload_limit" name="upload_limit" placeholder="2M" required>
                    </div>
            
                    <button type="submit" class="btn btn-primary">Aplicar Límite de Ancho de Banda</button>
                </form>
            </div>
        </div>

        <!-- Nat-->
        <div class="card">
            <div class="card-header">Configurar NAT</div>
            <div class="card-body">
                <form method="POST" action="{{ route('masquerade.srcnat') }}">
                    @csrf
                    <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
        
                    <div class="mb-3">
                        <label for="chain" class="form-label">Chain</label>
                        <select class="form-control" id="chain" name="chain" required>
                            <option value="srcnat" selected>srcnat</option>
                            <!-- Otras opciones pueden agregarse aquí -->
                        </select>
                    </div>
        
                    <div class="mb-3">
                        <label for="protocol" class="form-label">Protocolo</label>
                        <select class="form-control" id="protocol" name="protocol" required>
                            <option value="tcp" selected>TCP</option>
                            <option value="udp">UDP</option>
                            <option value="icmp">ICMP</option>
                            <option value="all">All</option>
                        </select>
                    </div>
        
                    <div class="mb-3">
                        <label for="out_interface" class="form-label">Interfaz de salida</label>
                        <select class="form-control" id="out_interface" name="out_interface" required>
                            @foreach(session('interfaces', []) as $interface)
                            <option value="{{ $interface['name'] }}">{{ $interface['name'] }}</option>
                            @endforeach   
                            
                        </select>
                    </div>
        
                    <div class="mb-3">
                        <label for="action" class="form-label">Acción</label>
                        <select class="form-control" id="action" name="action" required>
                            <option value="masquerade" selected>Masquerade</option>
                            <option value="accept">Accept</option>
                            <option value="drop">Drop</option>
                        </select>
                    </div>
        
                    <button type="submit" class="btn btn-primary">Configurar NAT</button>
                </form>
            </div>
        </div>
        
        {{-- dhcp --}}
        <div class="card">
            <div class="card-header">
                Configurar Servidor DHCP
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('configure.dhcp.server') }}">
                    @csrf
                    <div class="mb-3">
                        <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
                        <label for="interface" class="form-label">Interfaz</label>
                        <select class="form-control" id="interface" name="interface" required>
                            <option value="">Selecciona una interfaz</option>
                            @foreach(session('interfaces', []) as $interface)
                                <option value="{{ $interface['name'] }}">{{ $interface['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ip_range" class="form-label">Rango de IP</label>
                        <input type="text" class="form-control" id="ip_range" name="ip_range" placeholder="Ej. 192.168.88.10-192.168.88.100" required>
                    </div>
                    <div class="mb-3">
                        <label for="gateway" class="form-label">Puerta de Enlace</label>
                        <input type="text" class="form-control" id="gateway" name="gateway" placeholder="Ej. 192.168.88.1" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Configurar DHCP</button>
                </form>
            </div>
        </div>
        <div class="card">
            <h2>Crear Usuario DHCP</h2>
            <div class="card-body">
                

                    <!-- Mostrar mensajes de éxito o error -->
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('dhcp.create.user') }}" method="POST">
                        @csrf
                        <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
                        <div class="mb-3">
                            <label for="ip_new" class="form-label">Dirección IP</label>
                            <input type="text" class="form-control" id="ip_new" name="ip_new" placeholder="Ej. 192.168.1.10" required>
                        </div>

                        <div class="mb-3">
                            <label for="mac_address" class="form-label">Dirección MAC</label>
                            <input type="text" class="form-control" id="mac_address" name="mac_address" placeholder="Ej. AA:BB:CC:DD:EE:FF" required>
                        </div>

                        <div class="mb-3">
                            <label for="comment" class="form-label">Nombre del Host</label>
                            <input type="text" class="form-control" id="comment" name="comment" placeholder="Nombre del dispositivo" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Crear Usuario DHCP</button>
                    </form>
            </div>
        </div>
        
        

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: "{{ session('success') }}"
                });
            @endif
    
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "{{ session('error') }}"
                });
            @endif
        });


    </script>
    
</body>
</html>
