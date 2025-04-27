<?php
session_start();
include 'conexion.php'; // Incluye tu archivo de conexión a la base de datos

// Verifica si 'usuario' está definida en $_SESSION
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verificar si el usuario tiene el rol de administrador
if ($_SESSION['rol'] !== 'admin') {
    header('Location: dashboard.php'); // Redirige a usuarios no administradores
    exit();
}

// Inicializar variable para el filtro
$filter = "";

// Verifica si se ha enviado un filtro
if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];
}

// Consulta para obtener autos, aplicando el filtro si existe
$query = "SELECT * FROM autos WHERE marca LIKE ? OR modelo LIKE ?";
$stmt = $conn->prepare($query);
$searchTerm = "%" . $filter . "%"; // Agregar comodines para LIKE
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Autos</title>
    <link rel="stylesheet" href="css/dashboard.css"> <!-- Usa el mismo CSS del dashboard -->
    <style>
        /* Estilos adicionales para admin.php */
        .dashboard-container {
            max-width: 960px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .filter-container {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #007bff;
            border-radius: 8px;
            background-color: #f0f8ff;
            text-align: center;
        }

        .filter-input {
            padding: 10px;
            width: 80%;
            max-width: 400px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-style: italic;
            color: #aaa;
        }

        /* Placeholder para el filtro */
        .filter-input::placeholder {
            font-style: italic;
            color: #aaa;
        }

        .filter-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .filter-button:hover {
            background-color: #0056b3;
        }

        .dashboard-content {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .auto-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            text-align: center;
            width: 300px;
            transition: transform 0.3s ease;
        }

        .auto-card:hover {
            transform: translateY(-5px);
        }

        .auto-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
            max-height: 200px;
            object-fit: cover;
        }

        .detalles {
            display: none;
            margin-top: 10px;
        }

        .admin-actions {
            margin-top: 10px;
        }

        .admin-actions a {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            background-color: #28a745; /* Verde para acciones */
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .admin-actions a:hover {
            background-color: #218838;
        }

        .help-text {
            font-style: italic;
            color: #555;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        .add-auto-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            max-width: 200px;
        }

        .add-auto-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Administración de Autos</h1>
        </header>

        <!-- Zona de filtración -->
        <div class="filter-container">
            <form method="POST" action="">
                <input type="text" name="filter" class="filter-input" placeholder="Para buscar el modelo, ingresa 'GTR-34','GTR-35', etc" value="<?php echo htmlspecialchars($filter); ?>">
                <button type="submit" class="filter-button">Filtrar</button>
                <a href="admin.php" class="filter-button">Limpiar Filtro</a>
            </form>
        </div>

        <!-- Botón para agregar nuevo auto -->
        <a href="agregar_auto.php" class="add-auto-btn">Agregar Nuevo Auto</a>

        <div class="dashboard-content">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="auto-card">
                    <img src="img/<?php echo $row['imagen']; ?>" alt="<?php echo $row['marca'] . ' ' . $row['modelo']; ?>" class="auto-image">
                    <h2><?php echo $row['marca'] . ' ' . $row['modelo']; ?></h2>

                    <!-- Acciones de administración -->
                    <div class="admin-actions">
                        <a href="editar_auto.php?id=<?php echo $row['id']; ?>">Editar</a>
                        <a href="eliminar_auto.php?id=<?php echo $row['id']; ?>">Eliminar</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Botón para regresar al Dashboard como enlace -->
        <a href="dashboard.php" class="add-auto-btn">Regresar al Dashboard</a>
    </div>
</body>
</html>
