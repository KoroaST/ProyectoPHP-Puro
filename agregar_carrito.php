<?php
session_start();
include 'conexion.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug 1: Mostrar parámetros GET
echo "<pre>GET: ";
print_r($_GET);
echo "</pre>";

if (!isset($_SESSION['usuario'])) {
    die("Error: Usuario no autenticado");
}

if (!isset($_GET['id'])) {
    die("Error: Falta parámetro ID");
}

$auto_id = intval($_GET['id']);
echo "ID procesado: $auto_id<br>";

// Debug 2: Forzar un auto de prueba (¡COMENTAR LUEGO!)
$_SESSION['carrito'][] = [
    'id' => 999,
    'marca' => 'DEBUG',
    'modelo' => 'TEST',
    'precio' => 1000,
    'cantidad' => 1
];

// Debug 3: Mostrar sesión modificada
echo "<pre>SESSION MODIFICADA: ";
print_r($_SESSION);
echo "</pre>";

// Comenta temporalmente el header para ver el debug
// header('Location: ver_carrito.php'); 
exit();
?>
