<?php
// Habilitar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Depuración
file_put_contents('debug.log', "\n--- INICIO COMPRA ---\n", FILE_APPEND);
file_put_contents('debug.log', "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents('debug.log', "SESSION: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

include 'conexion.php';

// Captura y saneamiento de datos
$datos_compra = [
    'primer_nombre' => trim($_POST['primer_nombre'] ?? ''),
    'segundo_nombre' => trim($_POST['segundo_nombre'] ?? ''), // Aunque no se use en la tabla
    'primer_apellido' => trim($_POST['primer_apellido'] ?? ''),
    'segundo_apellido' => trim($_POST['segundo_apellido'] ?? ''),
    'direccion_envio' => trim($_POST['direccion_envio'] ?? ''),
    'email' => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
    'telefono' => preg_replace('/\D/', '', $_POST['telefono'] ?? ''),
    'numero_tarjeta' => substr(preg_replace('/\D/', '', $_POST['numero_tarjeta'] ?? ''), -4),
    'metodo_pago' => 'Tarjeta', // Definido estáticamente (ajusta según tu formulario)
    'total' => $_SESSION['total'] ?? 0
];

// Validaciones
$errores = [];

// Validar nombres
if (!preg_match('/^[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]+$/', $datos_compra['primer_nombre'])) {
    $errores[] = 'nombre_invalido';
}

// Validar email
if (!filter_var($datos_compra['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'email_invalido';
}

// Validar teléfono
if (!preg_match('/^\d{7,15}$/', $datos_compra['telefono'])) {
    $errores[] = 'telefono_invalido';
}

// Validar tarjeta (16 dígitos)
if (strlen(preg_replace('/\D/', '', $_POST['numero_tarjeta'] ?? '')) !== 16) {
    $errores[] = 'tarjeta_invalida';
}

// Redirigir si hay errores
if (!empty($errores)) {
    header("Location: formulario_compra.php?error=" . implode(',', $errores));
    exit();
}

// Transacción
$conn->begin_transaction();

try {
    // Insertar compra (CONSULTA CORRECTA)
    $stmt = $conn->prepare("INSERT INTO compras (
        usuario_id,
        primer_nombre,
        primer_apellido,
        segundo_apellido,
        direccion_envio,
        email,
        telefono,
        ultimos_digitos,
        metodo_pago,
        total
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "issssssssd",
        $_SESSION['usuario']['id'],
        $datos_compra['primer_nombre'],
        $datos_compra['primer_apellido'],
        $datos_compra['segundo_apellido'],
        $datos_compra['direccion_envio'],
        $datos_compra['email'],
        $datos_compra['telefono'],
        $datos_compra['numero_tarjeta'],
        $datos_compra['metodo_pago'],
        $datos_compra['total']
    );

    if (!$stmt->execute()) {
        throw new Exception("Error compra: " . $stmt->error);
    }

    $compra_id = $conn->insert_id;

    // Insertar detalles (CONSULTA CORRECTA)
    $stmt_detalle = $conn->prepare("INSERT INTO detalles_compra (
        compra_id,
        auto_id,
        cantidad,
        precio_unitario
    ) VALUES (?, ?, 1, ?)");

    foreach ($_SESSION['carrito'] as $item) {
        $stmt_detalle->bind_param("iid", 
            $compra_id,
            $item['id'], // Asegurar que existe en tabla autos
            $item['precio']
        );
        
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error detalle: " . $stmt_detalle->error);
        }
    }

    $conn->commit();
    
    // Limpiar carrito y redirigir
    unset($_SESSION['carrito']);
    header("Location: gestionar_compra.php?exito=1&compra_id=$compra_id");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    file_put_contents('debug.log', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    header("Location: formulario_compra.php?error=bd");
    exit();
}
?>
