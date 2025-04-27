<?php
session_start();
if (!isset($_SESSION['compra_exitosa'])) {
    header('Location: autos-disponibles.php');
    exit();
}
unset($_SESSION['compra_exitosa']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compra Exitosa</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            text-align: center;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .check-icon {
            color: #28a745;
            font-size: 4rem;
            margin: 1rem;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="check-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>¡Compra realizada con éxito!</h2>
        <p>Recibirás un correo de confirmación con los detalles de tu compra.</p>
        <a href="autos-disponibles.php" class="btn-primary">
            <i class="fas fa-car"></i> Ver más autos
        </a>
    </div>
</body>
</html>
