<?php
session_start();
include 'conexion.php';

// Validación de sesión y permisos
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    header('Location: dashboard.php');
    exit();
}

// Eliminar auto
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);

    // Eliminar la imagen del auto antes de eliminar el registro
    $sql_select = "SELECT imagen FROM autos WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows > 0) {
        $auto = $result->fetch_assoc();
        $imagen = $auto['imagen'];

        // Eliminar la imagen si existe
        if (!empty($imagen) && file_exists("img/autos/" . $imagen)) {
            unlink("img/autos/" . $imagen);
        }
    }

    // Eliminar el registro de la base de datos
    $sql_delete = "DELETE FROM autos WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);

    if ($stmt_delete->execute()) {
        $_SESSION['success'] = "Auto eliminado correctamente";
    } else {
        $_SESSION['error'] = "Error al eliminar el auto";
    }

    header("Location: gestionar-autos.php");
    exit();
}

// Obtener la lista de autos
$sql = "SELECT * FROM autos";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Autos - Concesionaria</title>
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

        .title-container h2 {
            font-size: 2rem;
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-secondary {
            background-color: var(--warning);
            color: var(--dark);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 5px solid;
        }

        .alert-danger {
            border-left-color: var(--danger);
            color: #f8d7da;
        }

        .alert-success {
            border-left-color: var(--success);
            color: #d4edda;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background-color: rgba(0, 0, 0, 0.3);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .acciones {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem;
            min-width: 36px;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .btn-warning {
            background-color: var(--warning);
            color: var(--dark);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .text-center {
            text-align: center;
        }

        .py-4 {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .text-muted {
            color: rgba(255, 255, 255, 0.6);
        }

        .fa-2x {
            font-size: 2em;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            th, td {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            .btn {
                padding: 0.65rem 1rem;
            }
        }

        @media (max-width: 576px) {
            .acciones {
                flex-direction: column;
            }

            .btn-sm {
                width: 100%;
            }
        }
    </style>
</head>
<body class="dashboard">
    <div class="dashboard-container">
        <div class="title-container">
            <h2><i class="fas fa-car"></i>Gestionar Autos</h2>
        </div>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i><?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>Volver
            </a>
            <a href="agregar_auto.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>Agregar Auto
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($auto = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $auto['id'] ?></td>
                                <td><?= htmlspecialchars($auto['marca']) ?></td>
                                <td><?= htmlspecialchars($auto['modelo']) ?></td>
                                <td>$<?= number_format($auto['precio'], 2) ?></td>
                                <td><?= $auto['stock'] ?></td>
                                <td><?= htmlspecialchars($auto['estado']) ?></td>
                                <td>
                                    <div class="acciones">
                                        <a href="editar_auto.php?id=<?= $auto['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?eliminar=<?= $auto['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este auto?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-car fa-2x mb-3"></i>
                                No hay autos registrados
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function confirmarEliminacion() {
            return confirm("¿Estás seguro de eliminar este auto?");
        }
    </script>
</body>
</html>

