<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario']['id'];

// Obtener las compras del usuario, uniendo la tabla de autos para obtener la marca y modelo
$query = "SELECT c.*, a.marca, a.modelo FROM compras c 
          JOIN autos a ON c.id_auto = a.id 
          WHERE c.id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Definir los métodos de pago posibles
$metodos_pago = [
    'PayPal' => 'PayPal',
    'Bancolombia' => 'Bancolombia',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Compras</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<div class="dashboard-container">
    <h1>Historial de Compras</h1>
    <?php if ($result->num_rows > 0): ?>
        <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <strong>Compra #<?php echo $row['id']; ?></strong><br>
                Auto: <?php echo htmlspecialchars($row['marca'] . ' ' . $row['modelo']); ?><br>
                Precio: $<?php echo number_format($row['precio'], 2); ?><br>

                <!-- Mostrar el método de pago según el valor almacenado en la base de datos -->
                <?php
                // Verificar que el método de pago exista en la lista definida
                $metodo_pago = $row['metodo_pago'];
                if (array_key_exists($metodo_pago, $metodos_pago)) {
                    $metodo_pago_texto = $metodos_pago[$metodo_pago];
                } else {
                    $metodo_pago_texto = 'No especificado';
                }
                ?>
                Método de Pago: <?php echo htmlspecialchars($metodo_pago_texto); ?><br>

                Dirección: <?php echo htmlspecialchars($row['direccion']); ?><br>

                <!-- Agregar opción para gestionar la compra si está permitido -->
                <a href="gestionar_compra.php?id=<?php echo $row['id']; ?>" class="gestionar-btn">Gestionar Compra</a>
            </li>
        <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No tienes compras registradas.</p>
    <?php endif; ?>
    <a href="dashboard.php" class="regresar-btn">Volver al Dashboard</a>
</div>
</body>
</html>
