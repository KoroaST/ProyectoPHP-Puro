<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificamos si hay un índice en la URL
if (isset($_GET['index'])) {
    $index = $_GET['index'];

    // Verificamos si el carrito existe y el índice es válido
    if (isset($_SESSION['carrito'][$index])) {
        unset($_SESSION['carrito'][$index]); // Elimina el auto del carrito
        $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexa el arreglo
    }
}

// Redirige de vuelta al carrito
header("Location: ver_carrito.php");
exit();
