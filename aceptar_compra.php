<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compra_id'])) {
    $compra_id = intval($_POST['compra_id']);
    
    // Iniciar transacción para operaciones atómicas
    $conn->begin_transaction();
    
    try {
        // 1. Verificar stock y calcular total
        $sql_verificar = "SELECT 
                            a.id, 
                            a.stock, 
                            d.cantidad, 
                            a.precio * d.cantidad AS subtotal
                          FROM detalles_compra d
                          JOIN autos a ON d.auto_id = a.id
                          WHERE d.compra_id = ?";
        
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("i", $compra_id);
        $stmt_verificar->execute();
        $resultados = $stmt_verificar->get_result();
        
        $total = 0;
        $error_stock = false;
        
        while ($item = $resultados->fetch_assoc()) {
            $total += $item['subtotal'];
            
            if ($item['stock'] < $item['cantidad']) {
                $error_stock = true;
                $_SESSION['error'] = "Stock insuficiente para el auto ID {$item['id']}";
            }
        }
        
        if ($error_stock) {
            throw new Exception("Error de stock");
        }
        
        // 2. Actualizar compra
        $sql_actualizar_compra = "UPDATE compras 
                                 SET estado='completado', total=?
                                 WHERE id=?";
        
        $stmt_compra = $conn->prepare($sql_actualizar_compra);
        $stmt_compra->bind_param("di", $total, $compra_id);
        $stmt_compra->execute();
        
        // 3. Actualizar stock y estado de autos
        $sql_actualizar_autos = "UPDATE autos a
                                JOIN detalles_compra d ON a.id = d.auto_id
                                SET 
                                    a.stock = a.stock - d.cantidad,
                                    a.estado = IF(a.stock - d.cantidad <= 0, 'vendido', a.estado)
                                WHERE d.compra_id = ?";
        
        $stmt_autos = $conn->prepare($sql_actualizar_autos);
        $stmt_autos->bind_param("i", $compra_id);
        $stmt_autos->execute();
        
        // Confirmar transacción
        $conn->commit();
        $_SESSION['mensaje'] = "Compra aceptada y stock actualizado";
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage() ?: "Error al procesar la compra";
    }
}
header('Location: dashboard.php');
exit();
