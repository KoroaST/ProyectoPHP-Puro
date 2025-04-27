<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Incluir la librería NuSOAP
require_once 'vendor/autoload.php';
require_once('vendor/econea/nusoap/src/nusoap.php');

$message = '';
$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $clave = trim($_POST['clave'] ?? '');
    $rol = 'usuario'; // Asignar rol usuario por defecto

    // Validaciones básicas
    if ($nombre === '') $errores[] = "El nombre es obligatorio.";
    if ($usuario === '') $errores[] = "El usuario es obligatorio.";
    if ($clave === '') $errores[] = "La contraseña es obligatoria.";

    if (empty($errores)) {
        // Crear un cliente SOAP
        $client = new nusoap_client('http://localhost/webservices/Proyecto/soap_usuarios.php?wsdl', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        // Llamar al método del servicio SOAP para crear el usuario
        $params = array('nombre' => $nombre, 'usuario' => $usuario, 'clave' => $clave, 'rol' => $rol);
        $resultado = $client->call('agregarUsuario', $params);

        // Verificar si hubo errores en la llamada al servicio SOAP
        if ($client->fault) {
            $message = 'Error en la respuesta del servicio SOAP.';
        } else {
            $err = $client->getError();
            if ($err) {
                $message = 'Error en la llamada al servicio SOAP: ' . htmlspecialchars($err);
            } else {
                // Usuario creado con exito, redirigir a usuarios.php con mensaje
                header('Location: usuarios.php?msg=agregado');
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario - Concesionaria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2575fc;
            --secondary: #6a11cb;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #198754;
            --warning:rgb(7, 156, 255);
        }

        body {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%) fixed;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: white;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .dashboard-container {
            background-color: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .title-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .title-container h2 {
            font-size: 1.8rem;
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .form-container {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        .form-label {
            color: var(--light);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.1);
        }

        .action-buttons {
            display: grid;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--success);
            color: white;
        }

        .btn-secondary {
            background-color: var(--warning);
            color: var(--dark);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .mensaje {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid;
        }

        .mensaje.error {
            border-left-color: #dc3545;
            color: #f8d7da;
        }

        .error-list {
            list-style: none;
            padding: 1rem;
            border-radius: 8px;
            background-color: rgba(220, 53, 69, 0.15);
            border-left: 4px solid #dc3545;
            margin-bottom: 1.5rem;
            display: grid;
            gap: 0.5rem;
        }

        .error-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .error-list li::before {
            content: "⚠️";
            font-size: 0.8rem;
        }

        ::placeholder {
            color: rgba(255, 255, 255, 0.6);
            opacity: 1;
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .title-container h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="title-container">
            <h2><i class="fas fa-user-plus"></i>Agregar Usuario</h2>
        </div>

        <?php if (!empty($errores)): ?>
            <ul class="error-list">
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="mensaje error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="form-container">
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" 
                       placeholder="Ej: Juan Pérez" required>
            </div>

            <div class="form-group">
                <label for="usuario" class="form-label">Nombre de usuario</label>
                <input type="text" id="usuario" name="usuario" class="form-control" 
                       value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>" 
                       placeholder="Mínimo 4 caracteres" required>
            </div>

            <div class="form-group">
                <label for="clave" class="form-label">Contraseña</label>
                <input type="password" id="clave" name="clave" class="form-control" 
                       placeholder="Mínimo 8 caracteres" required>
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Registrar
                </button>
                <a href="usuarios.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </form>
    </div>
</body>
</html>

