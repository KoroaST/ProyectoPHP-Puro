<?php
require_once('conexion.php');
require_once('vendor/econea/nusoap/src/nusoap.php');

// Crear un servidor SOAP
$server = new soap_server();

// Configurar el servidor SOAP
$server->configureWSDL('LoginService', 'urn:login');

// Registrar el método del servicio SOAP
$server->register('validarUsuario',
    array('usuario' => 'xsd:string', 'clave' => 'xsd:string'),
    array('return' => 'xsd:string'));

function validarUsuario($usuario, $clave) {
    global $conn;

    // Preparar la consulta SQL
    $query = "SELECT id, nombre, rol, clave FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($query);
    
    // Verificar si la preparación de la consulta falló
    if (!$stmt) {
        return json_encode(array("error" => "Error al preparar la consulta."));
    }

    // Vincular los parámetros
    if (!$stmt->bind_param("s", $usuario)) {
        return json_encode(array("error" => "Error al vincular parámetros."));
    }

    // Ejecutar la consulta
    if (!$stmt->execute()) {
        return json_encode(array("error" => "Error al ejecutar la consulta."));
    }

    // Obtener el resultado
    $result = $stmt->get_result();

    // Verificar si el resultado es válido
    if (!$result) {
        return json_encode(array("error" => "Error al obtener el resultado."));
    }

    // Verificar si se encontró al menos un usuario
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $clave_hash = $row['clave'];

        // Verificar si la clave coincide con el hash
        if (password_verify($clave, $clave_hash)) {
            return json_encode(array(
                "id" => $row['id'],
                "nombre" => $row['nombre'],
                "rol" => $row['rol'] 
            ));
        } else {
            return 'false'; // Clave incorrecta
        }
    } else {
        return 'false'; // Usuario no encontrado
    }
}



$HTTP_RAW_POST_DATA = file_get_contents("php://input");

// Servir el servicio SOAP
$server->service($HTTP_RAW_POST_DATA);
?>


