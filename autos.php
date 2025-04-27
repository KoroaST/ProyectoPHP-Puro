<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestionar Autos - Concesionaria</title>
</head>
<body>
    <h2>Gestionar Autos</h2>
    <!-- Aquí irán los formularios y tablas para el CRUD de autos -->
</body>
</html>
