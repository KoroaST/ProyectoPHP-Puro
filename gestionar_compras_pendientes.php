<?php
session_start();
include 'conexion.php';

// Validación de sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "Acceso denegado";
    header('Location: dashboard.php');
    exit();
}

// Inicializar variables
$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error']);

// Consulta para obtener las compras pendientes
$sql = "SELECT c.id, c.fecha_compra, u.nombre, u.usuario, c.total, c.estado
        FROM compras c
        JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.estado = 'pendiente'
        ORDER BY c.fecha_compra DESC";
$compras = $conn->query($sql);
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Compras Pendientes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
          :root {
            --primary: #ffffff;
            --secondary: #e0e0e0;
            --success: #d4edda;
            --danger: #f8d7da;
            --light: #f8f9fa;
            --dark: #212529;
        }
        body.dashboard {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%) fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: white;
        }
        .main-container {
            background-color: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin: 2rem auto;
            max-width: 90%; /* Aumentar ancho máximo */
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        header h1 {
            font-size: 2.3rem;
            margin-bottom: 0.5rem;
            font-weight: bold;
            letter-spacing: 1px;
            color: white;
        }
        .welcome-text {
            font-size: 1.2rem;
            margin-top: 0;
            color: rgba(255, 255, 255, 0.9);
        }
        .dashboard-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            text-align: center;
            color: white;
            width: 100%;
            max-width: 900px; /* Aumentar ancho máximo */
        }
        .card:hover {
            transform: translateY(-5px);
            background-color: rgba(255, 255, 255, 0.15) !important;
        }
        .card h2 {
            margin-top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.5rem;
            color: white;
        }
        .card p {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
            margin: 1rem 0;
        }
        .card-link {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-weight: 500;
            margin-top: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .card-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.1);
        }
        .logout-card {
            border: 1px solid rgba(255, 255, 255, 0.3);
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        .logout-card .card-link {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid rgba(255, 0, 0, 0.3);
        }
        .logout-card .card-link:hover {
            background: rgba(255, 0, 0, 0.3);
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 5px solid;
        }
        .alert-success {
            border-left-color: #28a745;
            color: #d4edda;
        }
        .alert-error {
            border-left-color: #dc3545;
            color: #f8d7da;
        }
        .compras-tabla {
            width: 100%;
            color: white;
            background: transparent;
            border-collapse: collapse; /* Eliminar bordes dobles */
        }
        .compras-tabla th, .compras-tabla td {
            color: white;
            background: transparent;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 0.7em 0.5em; /* Aumentar padding */
            text-align: center;
            vertical-align: middle; /* Centrar verticalmente */
        }
        .compras-tabla th {
            font-weight: bold;
            background: rgba(106,17,203,0.2);
            text-transform: uppercase; /* Mayúsculas */
            letter-spacing: 0.5px;
        }
        .compras-tabla tr:last-child td {
            border-bottom: none;
        }
        .badge-pendiente {
            background: #ffc107;
            color: #212529;
            border-radius: 12px;
            padding: 0.4em 0.9em; /* Aumentar padding */
            font-size: 0.95em;
            font-weight: bold; /* Negrita */
        }
        .btn-aceptar {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.5em 1.2em; /* Aumentar padding */
            font-size: 0.95em;
            margin-right: 0.3em;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap; /* Evitar saltos de línea */
        }
        .btn-aceptar:hover {
            background: #218838;
        }
        .btn-ver {
            background: #17a2b8;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.5em 1.2em; /* Aumentar padding */
            font-size: 0.95em;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
            white-space: nowrap; /* Evitar saltos de línea */
        }
        .btn-ver:hover {
            background: #138496;
        }
        /* Espaciado entre botones */
        .acciones {
            display: flex;
            justify-content: center;
            gap: 0.5em;
        }
        @media (max-width: 768px) {
            .main-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            header h1 {
                font-size: 2rem;
            }
            .card { max-width: 100%; }
            .compras-tabla th, .compras-tabla td { font-size: 0.95em; }
            .acciones {
                flex-direction: column;
                width: 100%;
            }
            .btn-aceptar, .btn-ver {
                width: 100%;
                margin-bottom: 0.5em;
            }
        }
    </style>
</head>
<body class="dashboard">
    <div class="main-container">
        <header>
            <h1>Dashboard de la Concesionaria</h1>
            <p class="welcome-text">Gestionar Compras Pendientes</p>
            <?php if($mensaje): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </header>
        <div class="dashboard-content">
            <!-- Módulo: Compras en Proceso -->
            <div class="card">
                <h2><i class="fas fa-clock"></i> Compras en Proceso</h2>
                
                <?php if ($compras && $compras->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="compras-tabla">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($row = $compras->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['fecha_compra'])) ?></td>
                                    <td>
                                        <?= $row['total'] > 0 ? '$'.number_format($row['total'],2) : '<span class="text-warning">Por calcular</span>' ?>
                                    </td>
                                    <td>
                                        <span class="badge-pendiente"><?= ucfirst($row['estado']) ?></span>
                                    </td>
                                    <td>
                                        <div class="acciones">
                                            <form action="aceptar_compra.php" method="POST">
                                                <input type="hidden" name="compra_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn-aceptar">
                                                    <i class="fas fa-check"></i> Aceptar
                                                </button>
                                            </form>
                                            <a href="detalles_compra.php?id=<?= $row['id'] ?>" class="btn-ver">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p>No hay compras pendientes.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
            
        <a href="dashboard.php" class="card-link">Volver al Dashboard</a>
    </div>
</body>
</html>

