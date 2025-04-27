<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario']) || !isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header('Location: autos-disponibles.php');
    exit();
}

$usuario_id = $_SESSION['usuario']['id'];
$metodo_pago = $_POST['metodo_pago'] ?? '';
$direccion = $_POST['direccion'] ?? '';

if ($metodo_pago && $direccion) {
    foreach ($_SESSION['carrito'] as $auto) {
        // Verificar stock
        $stmt = $conn->prepare("SELECT stock FROM autos WHERE id = ?");
        $stmt->bind_param("i", $auto['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $stock_row = $result->fetch_assoc();

        if ($stock_row && $stock_row['stock'] > 0) {
            // Registrar la compra
            $stmt = $conn->prepare("INSERT INTO compras (id_usuario, id_auto, precio, metodo_pago, direccion) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iidds", $usuario_id, $auto['id'], $auto['precio'], $metodo_pago, $direccion);
            if ($stmt->execute()) {
                // Disminuir stock
                $stmt = $conn->prepare("UPDATE autos SET stock = stock - 1 WHERE id = ?");
                $stmt->bind_param("i", $auto['id']);
                $stmt->execute();
            } else {
                echo "Error al registrar la compra: " . $stmt->error;
            }
        } else {
            echo "No hay stock disponible para el auto con ID " . $auto['id'] . ".<br>";
        }
    }

    // Vaciar carrito
    unset($_SESSION['carrito']);

    // Redirigir a historial
    header("Location: historial_compras.php");
    exit();
} else {
    echo "Faltan datos del formulario.";
}
?>
