<!-- resources/views/routeros/login.blade.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conexión RouterOS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header text-center">
                    <h3>Conectar a RouterOS</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('routeros.connect') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="ip_address">Dirección IP:</label>
                            <input type="text" name="ip_address" id="ip_address" class="form-control" placeholder="Ej: 192.168.88.1" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="login">Usuario:</label>
                            <input type="text" name="login" id="login" class="form-control" placeholder="Ingrese su usuario" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Contraseña:</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Ingrese su contraseña" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Conectar</button>
                    </form>
                    @if(session('success'))
                        <div class="alert alert-success mt-3">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger mt-3">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
