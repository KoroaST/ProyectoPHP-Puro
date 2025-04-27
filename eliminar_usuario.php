<?php
session_start();
include 'conexion.php';

// Validación corregida (según tu estructura de sesión)
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Validar ID existente y numérico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: usuarios.php?msg=error_id');
    exit();
}

$id = (int)$_GET['id'];

// Protección contra auto-eliminación (opcional)
if ($_SESSION['usuario']['id'] === $id) {
    header('Location: usuarios.php?msg=nodisponible');
    exit();
}

// Consulta preparada con manejo de errores
try {
    $query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error en preparación: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        header('Location: usuarios.php?msg=eliminado');
    } else {
        header('Location: usuarios.php?msg=no_encontrado');
    }

} catch (Exception $e) {
    error_log("Error al eliminar usuario: " . $e->getMessage());
    header('Location: usuarios.php?msg=error');
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
