<?php
require_once 'vendor/autoload.php';
require_once 'vendor/econea/nusoap/src/nusoap.php';

// Crear un cliente SOAP
$client = new nusoap_client('http://localhost/webservices/Proyecto/soap_usuarios.php?wsdl', false);
$client->soap_defencoding = 'UTF-8';
$client->decode_utf8 = false;


// Parámetros de prueba
$params = array(
    'nombre' => 'Prueba',
    'usuario' => 'prueba123',
    'clave' => '123456',
    'rol' => 'usuario'
);

// Llamar al método agregarUsuario
$resultado = $client->call('agregarUsuario', $params);

// Mostrar respuesta
echo "<pre>";
print_r($resultado);
echo "</pre>";

// Mostrar errores si existen
if ($client->fault) {
    echo "Error en la respuesta SOAP.";
} else {
    $err = $client->getError();
    if ($err) {
        echo "Error en la llamada SOAP: " . $err;
    }
}
?>
