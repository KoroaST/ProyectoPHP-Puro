<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Si se recibe un parámetro add, se intenta agregar el auto al carrito
if (isset($_GET['add'])) {
    $id_auto = intval($_GET['add']);

    // Buscar el auto en la base de datos
    $stmt = $conn->prepare("SELECT * FROM autos WHERE id = ?");
    $stmt->bind_param("i", $id_auto);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $auto = $resultado->fetch_assoc();

        // Verificar si el auto ya está en el carrito
        $ya_en_carrito = false;
        foreach ($_SESSION['carrito'] as $item) {
            if ($item['id'] == $id_auto) {
                $ya_en_carrito = true;
                break;
            }
        }

        // Agregar solo si no está ya en el carrito
        if (!$ya_en_carrito) {
            $_SESSION['carrito'][] = [
                'id' => $auto['id'],
                'marca' => $auto['marca'],
                'modelo' => $auto['modelo'],
                'precio' => $auto['precio'],
                'cantidad' => 1
            ];
        }
    }

    $stmt->close();

    // Redirigir para evitar recarga múltiple
    header("Location: ver_carrito.php");
    exit();
}

// Eliminar item del carrito
if (isset($_GET['delete'])) {
    $index = intval($_GET['delete']);
    if (isset($_SESSION['carrito'][$index])) {
        unset($_SESSION['carrito'][$index]);
        // Reindexar el array después de eliminar un elemento
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }

    // Redirigir para actualizar el carrito
    header("Location: ver_carrito.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos base del sistema (copia de tus estilos previos) */
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%) fixed;
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
            max-width: 900px;
            margin: 2rem auto;
            padding: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 600;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 2rem;
            background-color: rgba(255, 255, 255, 0.05);
        }

        th, td {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            font-weight: 500;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }

        .eliminar-btn {
            color: #ff6b6b;
            background: none;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 5px;
        }

        .eliminar-btn:hover {
            color: #ff3838;
            background-color: rgba(255, 107, 107, 0.1);
            transform: scale(1.1);
        }

        .botones-container {
            text-align: center;
            margin-top: 1.5rem;
        }

        .comprar-btn, .regresar-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            margin: 0 0.5rem;
        }

        .comprar-btn {
            background-color: #28a745;
            color: white;
        }

        .comprar-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .regresar-btn {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .regresar-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .total-row td {
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .empty-cart {
            text-align: center;
            padding: 2rem;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1.5rem;
                margin: 1rem auto;
            }

            table {
                font-size: 0.9rem;
            }

            .comprar-btn, .regresar-btn {
                padding: 0.7rem 1.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            table {
                display: block;
                overflow-x: auto;
            }

            .botones-container {
                display: flex;
                flex-direction: column;
                gap: 0.8rem;
            }

            .comprar-btn, .regresar-btn {
                margin: 0;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <h1><i class="fas fa-shopping-cart"></i> Carrito de Compras</h1>
    
    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart fa-3x" style="opacity: 0.3; margin-bottom: 1rem;"></i>
            <p>No hay artículos en el carrito</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Auto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach ($_SESSION['carrito'] as $index => $auto): 
                    $cantidad = $auto['cantidad'] ?? 1;
                    $subtotal = $auto['precio'] * $cantidad;
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($auto['marca'] . ' ' . $auto['modelo']) ?></td>
                        <td>$<?= number_format($auto['precio'], 2) ?></td>
                        <td><?= htmlspecialchars($cantidad) ?></td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                        <td>
                            <a href="ver_carrito.php?delete=<?= htmlspecialchars($index) ?>" class="eliminar-btn" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Total:</td>
                    <td colspan="2" style="text-align: left;">$<?= number_format($total, 2) ?></td>
                </tr>
            </tbody>
        </table>

    <div class="botones-container">
        <?php if (!empty($_SESSION['carrito'])): ?>
            <a href="formulario_compra.php" class="comprar-btn">
                <i class="fas fa-credit-card"></i> Completar compra
            </a>
        <?php endif; ?>
        <a href="autos-disponibles.php" class="regresar-btn">
            <i class="fas fa-arrow-left"></i> Volver a la tienda
        </a>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
