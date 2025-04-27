<?php
session_start();
include 'conexion.php';

// Validación mejorada con manejo de errores
if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['rol'] !== 'admin' && strtolower($_SESSION['usuario']['usuario']) !== 'koroa')) {
    header('Location: dashboard.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: usuarios.php');
    exit();
}

$id = $_GET['id'];

$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: usuarios.php');
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $clave = !empty($_POST['clave']) ? password_hash($_POST['clave'], PASSWORD_DEFAULT) : $user['clave'];
    
    // Lógica de roles mejorada
    if (strtolower($_SESSION['usuario']['usuario']) === 'koroa') {
        $rol = $_POST['rol'] ?? $user['rol']; // Koroa puede modificar cualquier rol
    } else {
        $rol = $user['rol']; // Otros admins mantienen el rol original
    }

    try {
        $query = "UPDATE usuarios SET nombre = ?, usuario = ?, clave = ?, rol = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $nombre, $usuario, $clave, $rol, $id);

        if ($stmt->execute()) {
            $message = "✅ Usuario actualizado correctamente ✅";
        } else {
            $message = "⚠️ Error al actualizar: " . $stmt->error;
        }
    } catch (Exception $e) {
        $message = "⚠️ Error crítico: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .title-container {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .title-container h1 {
            font-size: 2.3rem;
            margin: 0;
            font-weight: 600;
        }

        .mensaje {
            padding: 1rem;
            border-radius: 8px;
            margin: 0 auto 2rem;
            max-width: 600px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid;
        }

        .mensaje.success {
            border-left-color: #28a745;
            color: #d4edda;
        }

        .mensaje.error {
            border-left-color: #dc3545;
            color: #f8d7da;
        }

        .control-propietario {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin: 0 auto 2rem;
            max-width: 600px;
            text-align: center;
        }

        .control-propietario h4 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .badge-success {
            background-color: rgba(25, 135, 84, 0.2);
            color: #28a745;
        }

        .badge-warning {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-label {
            color: var(--light);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            width: 100%;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.1);
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-align: center;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            grid-column: 1 / -1;
        }

        .btn-secondary {
            background-color: rgba(108, 117, 125, 0.7);
            color: white;
        }

        .btn-info {
            background-color: rgba(29, 13, 253, 0.7);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .title-container h1 {
                font-size: 2rem;
            }

            .control-propietario {
                padding: 1rem;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
                option {
            background-color: rgba(0,0,0,0.9) !important;
            color: white !important;
            padding: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* Mejora el dropdown del select */
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='white' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

/* Estilo para el hover de las opciones */
option:hover {
    background-color: var(--primary) !important;
}
    </style>
    <script>
        function confirmarActualizar() {
            return confirm("¿Confirmas la actualización de este usuario?");
        }
    </script>
</head>
<body>
    <div class="dashboard-container">
        <div class="title-container">
            <h1><i class="fas fa-user-edit"></i> Editar Usuario</h1>
        </div>

        <?php if ($message): ?>
            <div class="mensaje <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
                <i class="fas <?= strpos($message, '✅') !== false ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="control-propietario">
            <h4><i class="fas fa-user-shield"></i> Control de Propietario</h4>
            <?php if(strtolower($_SESSION['usuario']['usuario'] ?? '') === 'koroa'): ?>
            <span class="badge badge-success">
                <i class="fas fa-unlock-alt"></i> Acceso Total
            </span>
            <p>
                Tienes control completo sobre todos los usuarios y roles del sistema.<br>
                <small class="text-muted">Privilegios de: <strong>Propietario del Sistema</strong></small>
            </p>
            <?php else: ?>
            <span class="badge badge-warning">
                <i class="fas fa-lock"></i> Acceso Administrativo
            </span>
            <p>
                Permisos estándar de administrador. No puedes modificar roles de otros administradores.<br>
                <small class="text-muted">Privilegios de: <strong>Administrador</strong></small>
            </p>
            <?php endif; ?>
        </div>

        <div class="form-container">
            <form method="post" onsubmit="return confirmarActualizar()">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($user['nombre'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="usuario" class="form-label">Usuario:</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" 
                           value="<?= htmlspecialchars($user['usuario'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="clave" class="form-label">Contraseña:</label>
                    <input type="password" class="form-control" id="clave" name="clave"
                           placeholder="Dejar vacío para mantener la actual">
                </div>

                <?php if (strtolower($_SESSION['usuario']['usuario'] ?? '') === 'koroa'): ?>
                                    <div class="form-group">
                    <label for="rol" class="form-label">Rol:</label>
                    <select class="form-select" id="rol" name="rol" required>
                        <option value="usuario" <?= ($user['rol'] ?? '') === 'usuario' ? 'selected' : '' ?> style="background-color: rgba(0,0,0,0.7); color: white;">Usuario</option>
                        <option value="admin" <?= ($user['rol'] ?? '') === 'admin' ? 'selected' : '' ?> style="background-color: rgba(0,0,0,0.7); color: white;">Administrador</option>
                    </select>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label class="form-label">Rol actual:</label>
                    <input type="text" class="form-control" 
                           value="<?= strtoupper($user['rol'] ?? '') ?>" 
                           style="font-weight: bold; color: <?= ($user['rol'] ?? '') === 'admin' ? '#d63384' : '#0d6efd' ?>" 
                           readonly>
                    <input type="hidden" name="rol" value="<?= $user['rol'] ?? '' ?>">
                </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Atrás
                    </a>
                    <a href="dashboard.php" class="btn btn-info">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html
