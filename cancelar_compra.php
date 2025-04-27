<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$compra_id = intval($_POST['compra_id']);
$usuario_id = $_SESSION['usuario']['id'];

// Iniciar transacción para operaciones atómicas
$conn->begin_transaction();

try {
    // 1. Eliminar detalles de compra primero
    $query = "DELETE FROM detalles_compra WHERE compra_id = ? AND compra_id IN (
                SELECT id FROM compras WHERE id = ? AND usuario_id = ?
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $compra_id, $compra_id, $usuario_id);
    $stmt->execute();
    
    // 2. Eliminar la compra principal
    $query = "DELETE FROM compras WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $compra_id, $usuario_id);
    $stmt->execute();
    
    // Confirmar operaciones
    $conn->commit();
    $_SESSION['mensaje'] = "Compra cancelada exitosamente";
    
} catch (Exception $e) {
    // Revertir en caso de error
    $conn->rollback();
    $_SESSION['error'] = "Error al cancelar: " . $e->getMessage();
}

header("Location: gestionar_compra.php");
exit();
?>
