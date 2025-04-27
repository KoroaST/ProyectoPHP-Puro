<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'conexion.php';

$compra_id = intval($_POST['compra_id'] ?? 0);

// Validaciones
$errores = [];
if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/', $_POST['primer_nombre'])) {
    $errores[] = "Nombre inválido (solo letras y espacios)";
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = "Email inválido";
}
if (!preg_match('/^\d{7,15}$/', $_POST['telefono'])) {
    $errores[] = "Teléfono debe tener 7-15 dígitos";
}
if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    header("Location: editar_compra_completa.php?id=$compra_id");
    exit();
}

// Actualización
$stmt = $conn->prepare("UPDATE compras SET
    primer_nombre = ?,
    primer_apellido = ?,
    segundo_apellido = ?,
    direccion_envio = ?,
    email = ?,
    telefono = ?
WHERE id = ?");

$stmt->bind_param(
    "ssssssi",
    $_POST['primer_nombre'],
    $_POST['primer_apellido'],
    $_POST['segundo_apellido'],
    $_POST['direccion_envio'],
    $_POST['email'],
    $_POST['telefono'],
    $compra_id
);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "¡Datos actualizados correctamente!";
    // Redirige a gestionar_compra.php (listado general)
    header("Location: gestionar_compra.php");
} else {
    $_SESSION['error'] = "Error al actualizar: " . $stmt->error;
    header("Location: editar_compra_completa.php?id=$compra_id");
}
exit();
?>
