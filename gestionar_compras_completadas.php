<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    header('Location: login.php');
    exit();
}

$sql = "SELECT c.id, c.fecha_compra, c.total, u.nombre as cliente 
        FROM compras c
        JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.estado = 'completado'
        ORDER BY c.fecha_compra DESC";

$compras = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras Completadas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Usa el mismo estilo del dashboard morado */
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
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
        
        .main-container {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin: 0 auto;
            max-width: 1200px;
            padding: 2rem;
            border: 1px solid var(--glass-border);
        }
        
        h1 {
            color: white;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background: var(--glass-bg);
            border-radius: 12px;
            overflow: hidden;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
        }
        
        th {
            background: rgba(106, 17, 203, 0.5);
            font-weight: 600;
        }
        
        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
                .btn-eliminar {
            background: #dc3545 !important;
            color: white !important;
            border: none !important;
            border-radius: 50px !important;
            padding: 0.3em 1em !important;
            font-size: 0.95em !important;
            margin-right: 0.3em !important;
            cursor: pointer !important;
            transition: background 0.2s !important;
            display: inline-block !important;
            text-decoration: none !important;
        }

        .btn-eliminar:hover {
            background: #c82333 !important;
        }

        .btn-detalles {
            background: #17a2b8 !important;
            color: white !important;
            border: none !important;
            border-radius: 50px !important;
            padding: 0.3em 1em !important;
            font-size: 0.95em !important;
            cursor: pointer !important;
            transition: background 0.2s !important;
            text-decoration: none !important;
            display: inline-block !important;
        }

        .btn-detalles:hover {
            background: #138496 !important;
        }
        .btn-detalles {
            background: rgba(23, 162, 184, 0.2);
            color: #17a2b8;
            border: 1px solid #17a2b8;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            margin-left: 10px;
            transition: all 0.3s;
        }
        
        .btn-detalles:hover {
            background: rgba(23, 162, 184, 0.3);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="main-container">
        <header>
            <h1><i class="fas fa-check-circle"></i> Compras Completadas</h1>
            <a href="dashboard.php" class="btn-volver" style="background: var(--secondary); padding: 10px 20px; border-radius: 50px; color: white; text-decoration: none; display: inline-block; margin-bottom: 1.5rem;">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </header>

        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success" style="background: rgba(40, 167, 69, 0.2); color: #d4edda; padding: 15px; border-radius: 8px; margin-bottom: 1.5rem; border-left: 5px solid #28a745;">
                <?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($compra = $compras->fetch_assoc()): ?>
                <tr>
                    <td><?= $compra['id'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($compra['fecha_compra'])) ?></td>
                    <td><?= htmlspecialchars($compra['cliente']) ?></td>
                    <td>$<?= number_format($compra['total'], 2) ?></td>
                    <td>
                        <form method="post" action="eliminar_compra_completada.php" style="display: inline;">
                            <input type="hidden" name="compra_id" value="<?= $compra['id'] ?>">
                            <button type="submit" class="btn-eliminar" onclick="return confirm('¿Eliminar esta compra? Se restaurará el stock de los vehículos')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                        <a href="detalles_compra.php?id=<?= $compra['id'] ?>" class="btn-detalles">
                            <i class="fas fa-eye"></i> Detalles
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
