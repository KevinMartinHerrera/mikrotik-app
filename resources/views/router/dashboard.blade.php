<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RouterOS Dashboard</title>
    <style>
        /* Reset */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        /* Body */
        body {
            background-color: #1d1f27;
            color: #e4e6eb;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        /* Container */
        .container {
            width: 80%;
            max-width: 1000px;
            background-color: #2b2f3b;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        /* Title */
        h1 {
            text-align: center;
            color: #5edfff;
            margin-bottom: 20px;
        }

        /* Router Info */
        .router-info {
            background-color: #393e4a;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        .router-info h2 {
            color: #5edfff;
            margin-bottom: 10px;
            font-size: 1.3rem;
            text-align: center;
        }

        .router-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .router-info th, .router-info td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #4a4e57;
        }

        .router-info th {
            color: #99aabb;
            font-weight: 500;
        }

        .router-info td {
            color: #e4e6eb;
        }

        /* Actions Section */
        .actions {
            margin-top: 25px;
        }

        .actions h2 {
            text-align: center;
            color: #5edfff;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .actions .action-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .actions button {
            background-color: #5edfff;
            color: #1d1f27;
            padding: 12px 20px;
            font-size: 1rem;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .actions button:hover {
            background-color: #45c8e0;
        }

        .actions button:active {
            transform: scale(0.98);
        }

        /* Footer */
        footer {
            text-align: center;
            margin-top: 40px;
            font-size: 0.9rem;
            color: #99aabb;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>RouterOS Dashboard</h1>

    @if(session('routeros_data'))
        <!-- Router Information Section -->
        <div class="router-info">
            <h2>Información de Conexión</h2>
            <table>
                <tr>
                    <th>Identidad</th>
                    <td>{{ session('routeros_data')->identity }}</td>
                </tr>
                <tr>
                    <th>Dirección IP</th>
                    <td>{{ session('routeros_data')->ip_address }}</td>
                </tr>
                <tr>
                    <th>Estado de Conexión</th>
                    <td>{{ session('routeros_data')->connect ? 'Conectado' : 'Desconectado' }}</td>
                </tr>
            </table>
        </div>

        <!-- Actions Section -->
        <div class="actions">
            <h2>Acciones Disponibles</h2>
            <div class="action-buttons">
                <button class="btn btn-info" data-toggle="modal" data-target="#setInterfaceModal">Configurar Interfaz</button>
                <button class="btn btn-info" data-toggle="modal" data-target="#addNewAddressModal">Agregar Nueva Dirección</button>
                <button class="btn btn-info" data-toggle="modal" data-target="#addIpRouteModal">Agregar Ruta IP</button>

            </div>
        </div>

        <!-- Modal para `set_interface` -->
        <div class="modal fade" id="setInterfaceModal" tabindex="-1" role="dialog" aria-labelledby="setInterfaceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="setInterfaceModalLabel">Configurar Interfaz</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="setInterfaceForm">
                @csrf
                <div class="form-group">
                    <label for="interface-ip-address">IP Address</label>
                    <input type="text" class="form-control" id="interface-ip-address" name="ip_address" required>
                </div>
                <div class="form-group">
                    <label for="interface-id">ID</label>
                    <input type="text" class="form-control" id="interface-id" name="id" required>
                </div>
                <div class="form-group">
                    <label for="interface-name">Interface</label>
                    <input type="text" class="form-control" id="interface-name" name="interface" required>
                </div>
                <div class="form-group">
                    <label for="new-name">New Name</label>
                    <input type="text" class="form-control" id="new-name" name="name" required>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
            </div>
        </div>
        </div>
    @else
        <p style="text-align: center; color: #e4e6eb;">No se encontró información de RouterOS. Por favor, intenta conectarte de nuevo.</p>
    @endif

    <footer>&copy; {{ date('Y') }} RouterOS Management</footer>
</div>

<script>
    function confirmReboot() {
        if (confirm('¿Estás seguro de que deseas reiniciar el RouterOS?')) {
            window.location.href = '/routeros-reboot';
        }
    }

    function confirmShutdown() {
        if (confirm('¿Estás seguro de que deseas apagar el RouterOS?')) {
            window.location.href = '/routeros-shutdown';
        }
    }
    $(document).ready(function() {
  // AJAX para `set_interface`
  $('#setInterfaceForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: "{{ route('set_interface') }}",
      type: "POST",
      data: $(this).serialize(),
      success: function(response) {
        alert(response.message);
        $('#setInterfaceModal').modal('hide');
      },
      error: function(xhr) {
        alert('Error: ' + xhr.responseJSON.message);
      }
    });
  });

  // Similar setup for `add_new_address` and `add_ip_route` modals
});
</script>

</body>
</html>
