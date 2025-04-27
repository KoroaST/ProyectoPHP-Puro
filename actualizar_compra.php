<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'conexion.php';

$compra_id = intval($_POST['compra_id'] ?? 0);

// Validaciones
$errores = [];

if (!preg_match('/^[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]+$/', $_POST['primer_nombre'])) {
    $errores[] = 'nombre_invalido';
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'email_invalido';
}

if (!preg_match('/^\d{7,15}$/', $_POST['telefono'])) {
    $errores[] = 'telefono_invalido';
}

if (!empty($errores)) {
    header("Location: editar_compra.php?id=$compra_id&error=" . implode(',', $errores));
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
    header("Location: gestionar_compra_detalles.php?id=$compra_id&exito=1");
} else {
    header("Location: editar_compra.php?id=$compra_id&error=bd");
}
