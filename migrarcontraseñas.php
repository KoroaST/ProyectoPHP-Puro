<?php
// Conexión a la base de datos
include 'conexion.php';

// Consulta para obtener todos los usuarios con contraseñas en formato bcrypt
$query = "SELECT id, clave FROM usuarios";
$result = $conn->query($query);

// Verificar si hay usuarios en la base de datos
if ($result->num_rows > 0) {
    // Recorremos todos los usuarios
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $clave_sin_hash = $row['clave'];

        // Verificar si la contraseña está en formato bcrypt
        if (strlen($clave_sin_hash) > 60 && strpos($clave_sin_hash, '$2y$') === 0) {
            // Verificar la contraseña usando bcrypt
            if (password_verify($clave_sin_hash, $clave_sin_hash)) {
                // Hashear la contraseña a Argon2
                $clave_hasheada = password_hash($clave_sin_hash, PASSWORD_ARGON2ID);

                // Actualizar la base de datos con la nueva contraseña hasheada en Argon2
                $update_query = "UPDATE usuarios SET clave = '$clave_hasheada' WHERE id = $id";
                if ($conn->query($update_query) === TRUE) {
                    echo "Contraseña hasheada para el usuario con ID: " . $id . "<br>";
                } else {
                    echo "Error al actualizar la contraseña para el usuario con ID: " . $id . ": " . $conn->error . "<br>";
                }
            }
        }
    }
} else {
    echo "No se encontraron usuarios con contraseñas en texto plano.";
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
