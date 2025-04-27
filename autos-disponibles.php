<?php
session_start();

if (isset($_SESSION['error_compra'])) {
    echo "<div class='alert alert-error'>" . $_SESSION['error_compra'] . "</div>";
    unset($_SESSION['error_compra']);
}

include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$filter = "";
if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];
}

$query = "SELECT * FROM autos WHERE (estado = 'disponible' OR estado = 'vendido') AND (marca LIKE ? OR modelo LIKE ?)";
$stmt = $conn->prepare($query);
$searchTerm = "%" . $filter . "%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autos Disponibles - Concesionaria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2575fc;
            --secondary: #6a11cb;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
        }

        body {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%) fixed;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: white;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .dashboard-container {
            background-color: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .title-container {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .title-container h1 {
            font-size: 2.2rem;
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .filter-container {
            margin-bottom: 2rem;
        }

        .filter-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 1rem;
            align-items: center;
        }

        .filter-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .filter-input:focus {
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
            outline: none;
        }

        .filter-button {
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .filter-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px) scale(1.02);
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .auto-card {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .auto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .auto-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .auto-content {
            padding: 1.5rem;
        }

        .auto-content h2 {
            margin: 0 0 1rem 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .ver-detalles {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--light);
            margin: 1rem 0;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .ver-detalles:hover {
            color: var(--primary);
        }

        .detalles {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease;
            background-color: rgba(0, 0, 0, 0.2);
            border-left: 3px solid transparent;
            margin: 0.5rem 0;
        }

        .detalles.active {
            max-height: 500px;
            border-left-color: var(--primary);
        }

        .detalles-content {
            padding: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease 0.2s;
        }

        .detalles.active .detalles-content {
            opacity: 1;
        }

        .detalles p {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detalles i {
            width: 20px;
            text-align: center;
        }

        .comprar-btn {
            display: inline-block;
            width: 100%;
            padding: 0.75rem;
            text-align: center;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            margin-top: 1rem;
            background-color: var(--success);
            .comprar-btn:hover {
    background-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.comprar-btn.disabled {
    background-color: var(--danger);
    opacity: 0.7;
    cursor: not-allowed;
}

.stock-alert {
    background-color: rgba(255, 193, 7, 0.2);
    color: var(--warning);
    padding: 0.5rem;
    border-radius: 5px;
    margin-top: 0.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
}

.regresar-container {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
}

.regresar-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: var(--warning);
    color: var(--dark);
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.regresar-btn:hover {
    background-color: #e0a800;
    transform: translateY(-2px) scale(1.05);
}

.alert-error {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    background-color: rgba(220, 53, 69, 0.15);
    border-left: 5px solid var(--danger);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 1.5rem;
        margin: 1rem auto;
    }

    .filter-form {
        grid-template-columns: 1fr;
    }

    .dashboard-content {
        grid-template-columns: 1fr;
    }

    .title-container h1 {
        font-size: 1.8rem;
    }
}

@media (max-width: 480px) {
    .title-container h1 {
        flex-direction: column;
        font-size: 1.6rem;
    }

    .auto-content h2 {
        font-size: 1.3rem;
    }

    .ver-detalles {
        font-size: 0.9rem;
    }

    .detalles-content p {
        font-size: 0.85rem;
    }

    .filter-button {
        padding: 0.6rem;
        font-size: 0.9rem;
    }

    .auto-image {
        height: 180px;
    }
}
        }
   
</style>

</head>



<body class="dashboard">
    <div class="dashboard-container">

        <div class="title-container">
            <h1 style="color: white;"><i class="fas fa-car"></i> Autos Disponibles</h1>
        </div>

        <?php if(isset($_SESSION['error_compra'])): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_compra']) ?>
            </div>
            <?php unset($_SESSION['error_compra']); ?>
        <?php endif; ?>

        <div class="filter-container">
            <form method="POST" action="" class="filter-form">
                <input type="text" name="filter" class="filter-input" placeholder="Buscar por marca o modelo..." value="<?= htmlspecialchars($filter ?? '') ?>">
                <button type="submit" class="filter-button">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="autos-disponibles.php" class="filter-button">
                    <i class="fas fa-sync-alt"></i> Limpiar
                </a>
            </form>
        </div>

        <div class="dashboard-content">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="auto-card" style="color: white;">
                    <img src="img/<?= htmlspecialchars($row['imagen']) ?>" class="auto-image" alt="Auto">

                    <div class="auto-content">
                        <h2 style="color: white;"><?= htmlspecialchars($row['marca']) ?> <?= htmlspecialchars($row['modelo']) ?></h2>

                        <a href="#" class="ver-detalles" onclick="toggleDetalles(event, 'detalles-<?= $row['id'] ?>')" style="color: white;">
                            <i class="fas fa-chevron-down"></i> <span>Ver detalles</span>
                        </a>

                        <div class="detalles" id="detalles-<?= $row['id'] ?>">
                            <div class="detalles-content" style="color: white;">
                                <p><i class="fas fa-dollar-sign"></i> Precio: $<?= number_format($row['precio'], 2) ?></p>
                                <p><i class="fas fa-box"></i> Stock: <?= $row['stock'] ?></p>
                                <p><i class="fas fa-info-circle"></i> Estado: <?= ucfirst($row['estado']) ?></p>
                                <p><i class="fas fa-align-left"></i> Descripción: <?= htmlspecialchars($row['descripcion']) ?></p>
                            </div>
                        </div>

                        <?php if ($row['stock'] > 0 && $row['estado'] === 'disponible'): ?>
                            <?php if ($row['stock'] < 4): ?>
                                <div class="stock-warning" style="color: orange; font-weight: bold;">
                                    <i class="fas fa-exclamation-circle"></i> ¡Últimas unidades!
                                </div>
                            <?php endif; ?>

                            <a href="ver_carrito.php?add=<?= $row['id'] ?>" class="comprar-btn" style="color: white; background-color: green; padding: 8px 12px; display: inline-block; margin-top: 10px; text-decoration: none; border-radius: 4px;">
                                <i class="fas fa-shopping-cart"></i> Agregar al carrito
                            </a>
                        <?php else: ?>
                            <div class="stock-alert" style="color: red; font-weight: bold;">
                                <i class="fas fa-exclamation-triangle"></i> No hay vehículo disponible en este modelo.
                            </div>
                            <a class="comprar-btn disabled" style="color: white; background-color: gray; padding: 8px 12px; display: inline-block; margin-top: 10px; text-decoration: none; border-radius: 4px; opacity: 0.6; pointer-events: none;">
                                <i class="fas fa-ban"></i> Agregar al carrito
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- BOTÓN VOLVER AL DASHBOARD ABAJO CENTRADO -->
        <div style="text-align: center; margin-top: 40px;">
            <a href="dashboard.php" style="color: white; background-color: #007bff; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 16px;">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

    </div>

    <script>
        function toggleDetalles(event, id) {
            event.preventDefault();
            const detalles = document.getElementById(id);
            detalles.classList.toggle('active');
        }
    </script>
</body>
