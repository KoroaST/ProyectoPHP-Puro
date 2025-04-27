<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Comprobamos si el carrito está vacío
if (empty($_SESSION['carrito'])) {
    echo "Tu carrito está vacío. No puedes realizar una compra sin autos en el carrito.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Método de Pago</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .metodo-pago-container {
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
            max-width: 600px;
        }

        .metodo-pago-container h1 {
            margin-bottom: 30px;
        }

        .metodo-pago-btn {
            background-color: #007bff;
            padding: 12px 25px;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
            margin: 15px;
            width: 200px;
            display: inline-block;
        }

        .metodo-pago-btn:hover {
            background-color: #0056b3;
        }

        .regresar-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .regresar-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="metodo-pago-container">
        <h1>Selecciona tu Método de Pago</h1>

        <form action="" method="POST">
            <!-- Botón para Bancolombia -->
            <button type="submit" name="pago" value="bancolombia" class="metodo-pago-btn">Pagar con Bancolombia</button>
            <br><br>
            <!-- Botón para PayPal -->
            <button type="submit" name="pago" value="paypal" class="metodo-pago-btn">Pagar con PayPal</button>
        </form>

        <a href="carrito.php" class="regresar-btn">Volver al Carrito</a>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $pago = $_POST['pago'];

        // Si elige Bancolombia
        if ($pago == 'bancolombia') {
            // Redirigir a Bancolombia (en un entorno real se integraría la API aquí)
            header('Location: bancolombia_pago.php');
            exit();
        }

        // Si elige PayPal
        if ($pago == 'paypal') {
            // Redirigir a PayPal (en un entorno real se integraría la API aquí)
            header('Location: paypal_pago.php');
            exit();
        }
    }
    ?>
</body>
</html>
