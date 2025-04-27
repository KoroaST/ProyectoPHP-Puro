<?php
session_start();
include 'conexion.php';

// Validación reforzada
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID no proporcionado";
    header('Location: gestionar_autos.php');
    exit();
}

$auto_id = intval($_GET['id']);

// Obtener imagen
$query = "SELECT imagen FROM autos WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $auto_id);
$stmt->execute();
$result = $stmt->get_result();
$auto = $result->fetch_assoc();

if (!$auto) {
    $_SESSION['error'] = "Auto no encontrado";
    header('Location: gestionar_autos.php');
    exit();
}

// Eliminar registro
$query = "DELETE FROM autos WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $auto_id);

if ($stmt->execute()) {
    // Eliminar imagen (ruta absoluta)
    $ruta_imagen = __DIR__ . "/img/autos/" . $auto['imagen'];
    if (file_exists($ruta_imagen)) {
        unlink($ruta_imagen);
    }
    $_SESSION['mensaje'] = "Auto eliminado exitosamente";
} else {
    $_SESSION['error'] = "Error al eliminar: " . $stmt->error;
}

header('Location: gestionar_autos.php');
exit();
?>
