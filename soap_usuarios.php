<?php
require_once('conexion.php');
require_once('vendor/econea/nusoap/src/nusoap.php');

$server = new soap_server();
$server->configureWSDL('UsuariosService', 'urn:usuarios');
$server->wsdl->schemaTargetNamespace = 'urn:usuarios';

// FUNCIONES SOAP

// Crear usuario
$server->register(
    'agregarUsuario',
    array('nombre' => 'xsd:string', 'usuario' => 'xsd:string', 'clave' => 'xsd:string', 'rol' => 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:usuarios',
    'urn:usuarios#agregarUsuario',
    'rpc',
    'encoded',
    'Registra un nuevo usuario en la base de datos'
);

// Consultar todos los usuarios
$server->register(
    'obtenerUsuarios',
    array(),
    array('return' => 'xsd:string'),
    'urn:usuarios',
    'urn:usuarios#obtenerUsuarios',
    'rpc',
    'encoded',
    'Devuelve todos los usuarios en formato JSON'
);

// Buscar usuario por nombre o usuario
$server->register(
    'buscarUsuario',
    array('criterio' => 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:usuarios',
    'urn:usuarios#buscarUsuario',
    'rpc',
    'encoded',
    'Busca un usuario en la base de datos por nombre o usuario'
);

// Modificar usuario
$server->register(
    'modificarUsuario',
    array('id' => 'xsd:int', 'nombre' => 'xsd:string', 'usuario' => 'xsd:string', 'rol' => 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:usuarios',
    'urn:usuarios#modificarUsuario',
    'rpc',
    'encoded',
    'Modifica los datos de un usuario en la base de datos'
);

// Eliminar usuario
$server->register(
    'eliminarUsuario',
    array('id' => 'xsd:int'),
    array('return' => 'xsd:string'),
    'urn:usuarios',
    'urn:usuarios#eliminarUsuario',
    'rpc',
    'encoded',
    'Elimina un usuario de la base de datos'
);

// FUNCIONES

// Agregar usuario
function agregarUsuario($nombre, $usuario, $clave, $rol) {
    global $conn;

    // Asegurar que los nuevos usuarios tengan rol "usuario" por defecto
    if ($rol !== 'admin') {
        $rol = 'usuario';
    }

    // Verificar si el usuario ya existe
    $check_query = "SELECT id FROM usuarios WHERE usuario = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $usuario);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        return json_encode(array("error" => "El usuario ya existe."));
    }

    // Insertar usuario
    $query = "INSERT INTO usuarios (nombre, usuario, clave, rol) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        return json_encode(array("error" => "Error al preparar la consulta."));
    }

    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
    $stmt->bind_param("ssss", $nombre, $usuario, $clave_hash, $rol);

    if ($stmt->execute()) {
        return json_encode(array("success" => "Usuario creado con exito."));
    } else {
        return json_encode(array("error" => "Error al insertar usuario."));
    }
}

// Obtener todos los usuarios
function obtenerUsuarios() {
    global $conn;
    $query = "SELECT id, nombre, usuario, rol FROM usuarios";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $usuarios = array();
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        return json_encode($usuarios);
    } else {
        return json_encode(array("error" => "No hay usuarios disponibles."));
    }
}

// Buscar usuario
function buscarUsuario($criterio) {
    global $conn;
    $criterio = "%" . $criterio . "%";
    $query = "SELECT id, nombre, usuario, rol FROM usuarios WHERE nombre LIKE ? OR usuario LIKE ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $criterio, $criterio);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuarios = array();
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        return json_encode($usuarios);
    } else {
        return json_encode(array("error" => "No se encontraron usuarios."));
    }
}

// Modificar usuario
function modificarUsuario($id, $nombre, $usuario, $rol) {
    global $conn;
    $query = "UPDATE usuarios SET nombre = ?, usuario = ?, rol = ? WHERE id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        return json_encode(array("error" => "Error al preparar la consulta."));
    }

    $stmt->bind_param("sssi", $nombre, $usuario, $rol, $id);

    if ($stmt->execute()) {
        return json_encode(array("success" => "Usuario actualizado correctamente."));
    } else {
        return json_encode(array("error" => "Error al actualizar usuario."));
    }
}

// Eliminar usuario
function eliminarUsuario($id) {
    global $conn;
    $query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        return json_encode(array("error" => "Error al preparar la consulta."));
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        return json_encode(array("success" => "Usuario eliminado correctamente."));
    } else {
        return json_encode(array("error" => "Error al eliminar usuario."));
    }
}

// Servir el servicio SOAP
$server->service(file_get_contents("php://input"));
?>
