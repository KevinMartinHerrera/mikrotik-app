<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #0d1117;
            color: #c9d1d9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            width: 320px;
            padding: 30px;
            background-color: #161b22;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #58a6ff;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-size: 0.9em;
            color: #8b949e;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            background-color: #0d1117;
            border: 1px solid #30363d;
            border-radius: 5px;
            color: #c9d1d9;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #58a6ff;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #58a6ff;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            font-size: 1em;
            cursor: pointer;
        }

        button:hover {
            background-color: #1f6feb;
        }
    </style>

</head>
<body>
    <div class="login-container">
        <h2>Acceso Seguro</h2>
        <form action="{{ route('routeros.connect') }}" method="POST">
            @csrf
            <div class="input-group">
                <label for="ip_address">IP Address</label>
                <input type="text" id="ip_address" name="ip_address" required>
            </div>
            <div class="input-group">
                <label for="login">Usuario</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
        
    </div>
</body>
</html>
