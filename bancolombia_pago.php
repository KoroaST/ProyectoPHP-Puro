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

// Lógica para procesar el pago con Bancolombia
// Esto es un ejemplo, en un entorno real debes integrar la API de Bancolombia.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Aquí harías la validación y conexión con Bancolombia.
    // Supongamos que la validación es exitosa:
    $pago_exitoso = true; // Simulación de pago exitoso.

    if ($pago_exitoso) {
        // Redirigir a confirmación de pago
        header('Location: confirmacion_pago.php?metodo=bancolombia');
        exit();
    } else {
        // En caso de error, muestra un mensaje de fallo
        $error_message = "Hubo un problema al procesar tu pago con Bancolombia. Intenta de nuevo.";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar con Bancolombia</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="compra-container">
        <h2>Pagar con Bancolombia</h2>

        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <p>Procesando tu pago a través de Bancolombia...</p>
        <form method="POST">
            <!-- Simulación de pago: en un entorno real agregarías la integración con la API de Bancolombia aquí -->
            <button type="submit">Confirmar Pago</button>
        </form>

        <a href="metodos_pago.php" class="regresar-btn">Volver a elegir método de pago</a>
    </div>
</body>
</html>
