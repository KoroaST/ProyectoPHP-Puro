<?php
include 'conexion.php';

// Consulta para obtener todos los usuarios con contraseñas en texto plano
$query = "SELECT id, clave FROM usuarios";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $clave_sin_hash = $row['clave'];

        // Hashear la contraseña
        $clave_hasheada = password_hash($clave_sin_hash, PASSWORD_ARGON2ID);

        // Actualizar la base de datos con la contraseña hasheada
        $update_query = "UPDATE usuarios SET clave = '$clave_hasheada' WHERE id = $id";
        if ($conn->query($update_query) === TRUE) {
            echo "Contraseña hasheada para el usuario con ID: " . $id . "<br>";
        } else {
            echo "Error al hashear la contraseña para el usuario con ID: " . $id . ": " . $conn->error . "<br>";
        }
    }
} else {
    echo "No se encontraron usuarios con contraseñas en texto plano.";
}

$conn->close();
?>
