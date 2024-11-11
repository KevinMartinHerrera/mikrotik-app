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
        {{-- añadir dirrceion ip  --}}
        <div class="card" id="add-new-address">
            <div class="card-header">Añadir Dirección IP</div>
            <div class="card-body">
                <form method="POST" action="{{ route('add.address') }}">
                    @csrf
                    <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
        
                    <div class="mb-3">
                        <label for="address" class="form-label">Dirección IP</label>
                        <input type="text" class="form-control" id="address" name="address" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="interface" class="form-label">Interfaz</label>
                        <select class="form-control" id="interface" name="interface" required>
                            <option value="">Selecciona una interfaz</option>
                            @foreach(session('interfaces', []) as $interface)
                                <option value="{{ $interface['name'] }}">{{ $interface['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
        
                    <button type="submit" class="btn btn-primary">Añadir Dirección</button>
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
        

        <!-- Agregar Usuario -->
        <div class="card" id="add-user">
            <div class="card-header">Agregar Usuario</div>
            <div class="card-body">
                <form method="POST" action="{{ route('add.user') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="ip_address" class="form-label">Seleccionar IP</label>
                        <select class="form-control" id="ip_address" name="ip_address" required>
                            <option value="">Selecciona una IP</option>
                            @foreach($ips as $ip)
                                <option value="{{ $ip['address'] }}">{{ $ip['address'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="user_group" class="form-label">Grupo de Usuario</label>
                        <select class="form-control" id="user_group" name="user_group" required>
                            <option value="">Selecciona un grupo</option>
                            @foreach($groups as $group)
                                <option value="{{ $group['name'] }}">{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar Usuario</button>
                </form>
            </div>
        </div>

        <!-- Crear Grupo de Usuario -->
        <div class="card">
            <div class="card-header">Crear Grupo de Usuario</div>
            <div class="card-body">
                <form method="POST" action="{{ route('create.user.group') }}">
                    @csrf
                    <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
                    <div class="mb-3">
                        <label for="group_name" class="form-label">Nombre del Grupo</label>
                        <input type="text" class="form-control" id="group_name" name="group_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="policies" class="form-label">Políticas del Grupo</label>
                        <input type="text" class="form-control" id="policies" name="policies" placeholder="Ejemplo: ssh,ftp,winbox,read" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Crear Grupo</button>
                </form>
            </div>
        </div>

        <!-- Limitar Ancho de Banda -->
        <div class="card" id="set-bandwidth-limit">
            <div class="card-header">Establecer Límite de Ancho de Banda</div>
            <div class="card-body">
                <form method="POST" action="{{ route('set.bandwidth') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="ip_address" class="form-label">Seleccionar IP</label>
                        <select class="form-control" id="ip_address" name="ip_address" required>
                            <option value="">Selecciona una IP</option>
                            @foreach($ips as $ip)
                                <option value="{{ $ip['address'] }}">{{ $ip['address'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="target" class="form-label">Seleccionar Usuario</label>
                        <select class="form-control" id="target" name="target" required>
                            <option value="">Selecciona un usuario</option>
                            @foreach($users as $user)
                                <option value="{{ $user['name'] }}">{{ $user['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="download_limit" class="form-label">Límite de Descarga</label>
                        <input type="text" class="form-control" id="download_limit" name="download_limit" placeholder="Ejemplo: 2M" required>
                    </div>
                    <div class="mb-3">
                        <label for="upload_limit" class="form-label">Límite de Subida</label>
                        <input type="text" class="form-control" id="upload_limit" name="upload_limit" placeholder="Ejemplo: 1M" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Establecer Límite</button>
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
