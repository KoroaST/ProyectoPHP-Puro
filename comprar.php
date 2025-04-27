<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Validar que exista el parámetro ID
if (!isset($_GET['id'])) {
    echo "No se proporcionó ID del auto.";
    exit();
}

$auto_id = intval($_GET['id']);

// Obtener los datos del auto
$query = "SELECT * FROM autos WHERE id = ? AND estado = 'disponible'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $auto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "El auto no está disponible o ya fue vendido.";
    exit();
}

$auto = $result->fetch_assoc();

// Verificar si hay stock disponible
$stock = $auto['stock']; // Asegúrate de tener un campo 'stock' en tu base de datos
if ($stock <= 0) {
    echo "No hay vehículos disponibles en este modelo. Lo sentimos.";
    exit();
}

// Si se envió el formulario de compra
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Aquí podrías hacer más validaciones (saldo, usuario, etc.)
    $update = "UPDATE autos SET estado = 'vendido', stock = stock - 1 WHERE id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("i", $auto_id);
    
    if ($stmt->execute()) {
        $mensaje = "¡Compra realizada con éxito! El vehículo ahora está marcado como vendido.";
        // Redirigir a autos disponibles después de la compra
        header("Location: autos-disponibles.php");
        exit();
    } else {
        $mensaje = "Error al registrar la compra.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Compra</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .compra-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            text-align: center;
        }

        .auto-imagen {
            max-width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
            max-height: 250px;
            object-fit: cover;
        }

        .confirmar-btn {
            background-color: #28a745;
            padding: 12px 25px;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .confirmar-btn:hover {
            background-color: #218838;
        }

        .regresar-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .regresar-link:hover {
            background-color: #0056b3;
        }

        .mensaje {
            font-size: 18px;
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="compra-container">
        <h2>Confirmar compra</h2>

        <?php if (isset($mensaje)): ?>
            <p class="mensaje"><?php echo $mensaje; ?></p>
            <a href="autos-disponibles.php" class="regresar-link">Volver a Autos Disponibles</a>
        <?php else: ?>
            <img src="img/<?php echo $auto['imagen']; ?>" alt="Imagen del auto" class="auto-imagen">
            <h3><?php echo $auto['marca'] . ' ' . $auto['modelo']; ?></h3>
            <p><strong>Año:</strong> <?php echo $auto['anio']; ?></p>
            <p><strong>Precio:</strong> $<?php echo number_format($auto['precio'], 2); ?></p>
            <p><strong>Descripción:</strong> <?php echo $auto['descripcion']; ?></p>

            <form method="POST">
                <button type="submit" class="confirmar-btn">Confirmar Compra</button>
            </form>
            <a href="autos-disponibles.php" class="regresar-link">Cancelar</a>
        <?php endif; ?>
    </div>
</body>
</html>
