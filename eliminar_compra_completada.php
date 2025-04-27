<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compra_id'])) {
    $compra_id = intval($_POST['compra_id']);
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // 1. Restaurar stock y estado de los autos
        $sql_restaurar = "UPDATE autos a
                         JOIN detalles_compra d ON a.id = d.auto_id
                         SET 
                             a.stock = a.stock + d.cantidad,
                             a.estado = IF(a.stock + d.cantidad > 0, 'disponible', a.estado)
                         WHERE d.compra_id = ?";
        
        $stmt_restaurar = $conn->prepare($sql_restaurar);
        $stmt_restaurar->bind_param("i", $compra_id);
        $stmt_restaurar->execute();
        
        // 2. Eliminar detalles de la compra
        $sql_eliminar_detalles = "DELETE FROM detalles_compra WHERE compra_id = ?";
        $stmt_detalles = $conn->prepare($sql_eliminar_detalles);
        $stmt_detalles->bind_param("i", $compra_id);
        $stmt_detalles->execute();
        
        // 3. Eliminar la compra principal
        $sql_eliminar_compra = "DELETE FROM compras WHERE id = ?";
        $stmt_compra = $conn->prepare($sql_eliminar_compra);
        $stmt_compra->bind_param("i", $compra_id);
        $stmt_compra->execute();
        
        $conn->commit();
        $_SESSION['mensaje'] = "✅ Compra eliminada y stock restaurado correctamente";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "❌ Error al eliminar: " . $e->getMessage();
    }
}

header('Location: gestionar_compras_completadas.php');
exit();
