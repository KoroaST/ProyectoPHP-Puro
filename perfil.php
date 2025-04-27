<?php
session_start();
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener los datos del usuario desde la base de datos
$usuario_id = $_SESSION['usuario']['id']; // Suponiendo que 'usuario' está en la sesión con su id
$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No se encontraron datos para este usuario.";
    exit();
}

$usuario = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Usuario</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .perfil-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .perfil-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .perfil-header h2 {
            margin: 0;
        }

        .perfil-info {
            margin: 20px 0;
            font-size: 18px;
        }

        .perfil-info p {
            margin: 5px 0;
        }

        .perfil-btn {
            background-color: #007bff;
            padding: 12px 25px;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .perfil-btn:hover {
            background-color: #0056b3;
        }

        .volver-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .volver-link:hover {
            background-color: #0056b3;
        }

        .payment-methods {
            margin-top: 30px;
        }

        .payment-methods label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .payment-methods select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="perfil-container">
        <div class="perfil-header">
            <h2>Perfil de Usuario</h2>
        </div>

        <div class="perfil-info">
            <p><strong>Nombre:</strong> <?php echo $usuario['nombre']; ?></p>
            <p><strong>Correo Electrónico:</strong> <?php echo $usuario['correo']; ?></p>
            <p><strong>Fecha de Registro:</strong> <?php echo date('d-m-Y', strtotime($usuario['fecha_registro'])); ?></p>
            <p><strong>Dirección de Envío:</strong> <?php echo $usuario['direccion_envio']; ?></p>
            <p><strong>Teléfono:</strong> <?php echo $usuario['telefono']; ?></p>
        </div>

        <!-- Métodos de pago -->
        <div class
