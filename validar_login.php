<?php
session_start();
include 'conexion.php'; // Incluye tu archivo de conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    // Consulta para obtener el hash almacenado
    
    $stmt = $conn->prepare("SELECT id, nombre, rol, clave FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Obtener el hash almacenado

        $stmt->bind_result($id, $nombre, $rol, $hashedPassword);
        $stmt->fetch();

        // Verificar la contraseña usando password_verify

        if (password_verify($clave, $hashedPassword)) {
            // Contraseña correcta, guardar información en la sesión
            $_SESSION['id'] = $id;
            $_SESSION['usuario'] = $nombre;
            $_SESSION['rol'] = $rol; 

            header('Location: dashboard.php'); // Redirigir al dashboard
            exit();
        } else {
            // Contraseña incorrecta
            header('Location: login.php?error=1');
            exit();
        }
    } else {
        // Usuario no encontrado
        header('Location: login.php?error=1');
        exit();
    }
}
?>

