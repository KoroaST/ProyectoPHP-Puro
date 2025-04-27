<?php
session_start();
include 'conexion.php';

// Validación de sesión y parámetros
if (!isset($_SESSION['usuario'])) {
    $_SESSION['error'] = "Debes iniciar sesión";
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID inválido";
    header('Location: gestionar_compras_pendientes.php');
    exit();
}

// Consulta principal
$sql = "SELECT 
            c.*,
            u.nombre as cliente_nombre,
            u.usuario as cliente_usuario,
            u.email as cliente_email
        FROM compras c
        JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$compra = $stmt->get_result()->fetch_assoc();

// Consulta de items
$sql_items = "SELECT 
                a.marca,
                a.modelo,
                a.precio,
                d.cantidad,
                (a.precio * d.cantidad) as subtotal
            FROM detalles_compra d
            JOIN autos a ON d.auto_id = a.id
            WHERE d.compra_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $_GET['id']);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Compra</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --light: #f8f9fa;
            --dark: #212529;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%) fixed;
            min-height: 100vh;
            padding: 2rem;
            color: white;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 2rem;
            border: 1px solid var(--glass-border);
        }
        
        h1 {
            color: white;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 1rem;
            text-align: center;
            font-size: 2.2rem;
            margin-bottom: 2rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .info-card {
            background: var(--glass-bg);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .info-card h3 {
            margin-top: 0;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3rem;
        }
        
        .info-card p {
            margin: 0.8rem 0;
            color: rgba(255, 255, 255, 0.9);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            background: var(--glass-bg);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
            color: white;
        }
        
        th {
            background: rgba(106, 17, 203, 0.5);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        
        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .total-card {
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.7) 0%, rgba(37, 117, 252, 0.7) 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: right;
            margin-top: 2rem;
            border: 1px solid var(--glass-border);
        }
        
        .total-card h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .btn-volver {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            margin-top: 2rem;
            transition: all 0.3s;
            border: none;
            font-weight: 500;
            font-size: 1rem;
        }
        
        .btn-volver:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            background: linear-gradient(135deg, #5a0db0 0%, #1a6beb 100%);
        }
        
        .badge-estado {
            display: inline-block;
            padding: 0.35rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalles de Compra #<?= $compra['id'] ?></h1>
        
        <div class="info-grid">
            <div class="info-card">
                <h3><i class="fas fa-user"></i> Cliente</h3>
                <p><?= htmlspecialchars($compra['cliente_nombre']) ?></p>
                <p><?= htmlspecialchars($compra['cliente_email']) ?></p>
                <p>Usuario: <?= htmlspecialchars($compra['cliente_usuario']) ?></p>
            </div>
            
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> Información</h3>
                <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($compra['fecha_compra'])) ?></p>
                <p><strong>Estado:</strong> 
                    <span class="badge-estado" style="background: <?= $compra['estado'] == 'pendiente' ? '#ffc107' : '#28a745' ?>; 
                        color: <?= $compra['estado'] == 'pendiente' ? '#212529' : 'white' ?>;">
                        <?= ucfirst($compra['estado']) ?>
                    </span>
                </p>
            </div>
        </div>

        <h2 style="color: white; margin-bottom: 1rem;"><i class="fas fa-car"></i> Vehículos Comprados</h2>
        <table>
            <thead>
                <tr>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['marca']) ?></td>
                    <td><?= htmlspecialchars($item['modelo']) ?></td>
                    <td>$<?= number_format($item['precio'], 2) ?></td>
                    <td><?= $item['cantidad'] ?></td>
                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="total-card">
            <h3>Total de la Compra: $<?= number_format($compra['total'], 2) ?></h3>
        </div>

        <a href="gestionar_compras_pendientes.php" class="btn-volver">
            <i class="fas fa-arrow-left"></i> Volver al Panel
        </a>
    </div>
</body>
</html>
