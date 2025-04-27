<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtenemos el carrito
$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

// Calcular el total de la compra
$total = 0;
foreach ($carrito as $auto) {
    $total += $auto['precio'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Carrito de Compras</h1>

        <?php if (empty($carrito)): ?>
            <p>No hay autos en tu carrito.</p>
        <?php else: ?>
            <div class="dashboard-content">
                <?php foreach ($carrito as $auto): ?>
                    <div class="auto-card">
                        <img src="img/<?php echo $auto['imagen']; ?>" class="auto-image">
                        <h2><?php echo $auto['marca'] . ' ' . $auto['modelo']; ?></h2>
                        <p><strong>Precio:</strong> $<?php echo number_format($auto['precio'], 2); ?></p>
                        <form action="eliminar_carrito.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $auto['id']; ?>">
                            <button type="submit" class="ver-detalles">Eliminar</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total-compra">
                <h3>Total: $<?php echo number_format($total, 2); ?></h3>
                <a href="procesar_compra.php" class="confirmar-compra-btn">Confirmar Compra</a>
            </div>
        <?php endif; ?>

        <a href="autos-disponibles.php" class="regresar-btn">Seguir comprando</a>
    </div>
</body>
</html>
