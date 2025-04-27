<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'conexion.php';

$compra_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT direccion_envio FROM compras WHERE id = ?");
$stmt->bind_param("i", $compra_id);
$stmt->execute();
$result = $stmt->get_result();
$compra = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Dirección</title>
</head>
<body>
    <form method="post" action="actualizar_compra.php">
        <input type="hidden" name="compra_id" value="<?=$compra_id?>">
        
        <label>Dirección de Envío:
            <input type="text" name="direccion_envio" 
                   value="<?=htmlspecialchars($compra['direccion_envio'])?>" 
                   required>
        </label><br>
        
        <button type="submit">Actualizar</button>
    </form>
</body>
</html>
