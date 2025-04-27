<?php
session_start();

include 'conexion.php';

// Redirigir si no es admin O Koroa
if (!isset($_SESSION['usuario']) || (strtolower($_SESSION['usuario']['usuario']) !== 'koroa' && $_SESSION['usuario']['rol'] !== 'admin')) {
    header('Location: dashboard.php');
    exit();
}

// Conectar con el servicio SOAP
require_once('vendor/econea/nusoap/src/nusoap.php');
$client = new nusoap_client('http://localhost/webservices/Proyecto/soap_usuarios.php?wsdl', true);

// Buscar usuarios si hay criterio
$criterio = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
if ($criterio !== '') {
    $params = ['criterio' => $criterio];
    $resultado = $client->call('buscarUsuario', $params);
} else {
    $resultado = $client->call('obtenerUsuarios');
}

// Verificar errores
$usuarios = [];
if ($client->fault || $client->getError()) {
    $msg = "Error al conectar con el servicio de usuarios.";
} else {
    $usuarios = json_decode($resultado, true);
    if (!is_array($usuarios)) {
        $usuarios = [];
    }
}

// Mensajes de éxito o error
$mensaje = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'agregado':
            $mensaje = "✅ Usuario agregado exitosamente.";
            break;
        case 'editado':
            $mensaje = "✅ Usuario editado correctamente.";
            break;
        case 'eliminado':
            $mensaje = "✅ Usuario eliminado correctamente.";
            break;
        case 'error':
            $mensaje = "⚠️ Ocurrió un error al procesar la solicitud.";
            break;
        case 'nodisponible':
            $mensaje = "⚠️ No se puede eliminar tu propio usuario.";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2575fc;
            --secondary: #6a11cb;
            --light: #f8f9fa;
            --dark: #212529;
        }

        body {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%) fixed;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: white;
            margin: 0;
            padding: 20px;
        }

        .dashboard-container {
            background-color: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dashboard-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dashboard-header h1 {
            font-size: 2rem;
            margin: 0;
            font-weight: 600;
        }

        .search-form {
            display: flex;
            gap: 10px;
            width: 100%;
            max-width: 400px;
        }

        .search-form input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            flex-grow: 1;
        }

        .search-form button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-form button:hover {
            background-color: rgba(37, 117, 252, 0.9);
            transform: translateY(-2px);
        }

        .mensaje {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid;
        }

        .mensaje.success {
            border-left-color: #28a745;
        }

        .mensaje.error {
            border-left-color: #dc3545;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background-color: rgba(0, 0, 0, 0.2);
            font-weight: 600;
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .edit-btn {
            background-color: rgba(13, 110, 253, 0.7);
            color: white;
        }

        .delete-btn {
            background-color: rgba(220, 53, 69, 0.7);
            color: white;
        }

        .dashboard-btn {
            background-color: rgba(108, 117, 125, 0.7);
            color: white;
        }

        .add-user-btn {
            background-color: rgba(25, 135, 84, 0.7);
            color: white;
        }

        .edit-btn:hover, .delete-btn:hover, .dashboard-btn:hover, .add-user-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-admin {
            background-color: rgba(13, 110, 253, 0.2);
            color: #0d6efd;
        }

        .badge-vendedor {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .button-container {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .search-form {
                width: 100%;
            }

            th, td {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-users"></i> Gestionar Usuarios Existentes</h1>
            <form class="search-form" method="GET" action="usuarios.php" role="search" aria-label="Buscar usuarios">
                <input type="search" name="buscar" placeholder="Buscar usuario..." value="<?= htmlspecialchars($criterio ?? '') ?>" aria-label="Buscar usuario">
                <button type="submit" aria-label="Buscar"><i class="fas fa-search"></i> Buscar</button>
            </form>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?= strpos($mensaje, '⚠️') !== false ? 'error' : 'success' ?>">
                <i class="fas <?= strpos($mensaje, '⚠️') !== false ? 'fa-exclamation-triangle' : 'fa-check-circle' ?>"></i>
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <table aria-describedby="tabla-usuarios">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Rol</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= htmlspecialchars($row['usuario']) ?></td>
                            <td>
                                <span class="badge <?= $row['rol'] === 'admin' ? 'badge-admin' : 'badge-vendedor' ?>">
                                    <?= ucfirst(htmlspecialchars($row['rol'])) ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <?php if (strtolower($_SESSION['usuario']['usuario'] ?? '') === 'koroa'): ?>
                                    <a href="editar_usuario.php?id=<?= $row['id'] ?>" class="btn edit-btn" aria-label="Editar usuario <?= htmlspecialchars($row['usuario']) ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="eliminar_usuario.php?id=<?= $row['id'] ?>" class="btn delete-btn" onclick="return confirmarEliminar()" aria-label="Eliminar usuario <?= htmlspecialchars($row['usuario']) ?>">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                <?php else: ?>
                                    <?php if ($row['rol'] !== 'admin'): ?>
                                        <a href="editar_usuario.php?id=<?= $row['id'] ?>" class="btn edit-btn" aria-label="Editar usuario <?= htmlspecialchars($row['usuario']) ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="eliminar_usuario.php?id=<?= $row['id'] ?>" class="btn delete-btn" onclick="return confirmarEliminar()" aria-label="Eliminar usuario <?= htmlspecialchars($row['usuario']) ?>">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No permitido</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No hay usuarios encontrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="button-container">
    <a href="dashboard.php" class="btn dashboard-btn" aria-label="Volver al dashboard">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>
    
    
    <a href="agregar_usuario.php" class="btn add-user-btn" aria-label="Agregar nuevo usuario">
        <i class="fas fa-user-plus"></i> Agregar Nuevo Usuario
    </a>
</div>

    <script>
        function confirmarEliminar() {
            return confirm('¿Estás seguro de eliminar este usuario?');
        }
    </script>
</body>
</html>

