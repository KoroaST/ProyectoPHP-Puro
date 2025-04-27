<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$rol = $_SESSION['usuario']['rol'] ?? 'usuario';

$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error']);

include 'conexion.php';
$total_autos = 0;
if ($rol === 'admin') {
    $query = "SELECT COUNT(*) as total FROM autos";
    $result = $conn->query($query);
    if ($result) {
        $total_autos = $result->fetch_assoc()['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Concesionaria</title>
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
            max-width: 800px;
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
            max-width: 600px;
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
        /* Compras en proceso */
        .compras-tabla {
            width: 100%;
            color: white;
            background: transparent;
        }
        .compras-tabla th, .compras-tabla td {
            color: white;
            background: transparent;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 0.5em 0.3em;
            text-align: center;
        }
        .compras-tabla th {
            font-weight: bold;
            background: rgba(106,17,203,0.2);
        }
        .compras-tabla tr:last-child td {
            border-bottom: none;
        }
        .badge-pendiente {
            background: #ffc107;
            color: #212529;
            border-radius: 12px;
            padding: 0.2em 0.8em;
            font-size: 0.95em;
        }
        .btn-aceptar {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.3em 1em;
            font-size: 0.95em;
            margin-right: 0.3em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-aceptar:hover {
            background: #218838;
        }
        
        .btn-ver {
            background: #17a2b8;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.3em 1em;
            font-size: 0.95em;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        
        .btn-ver:hover {
            background: #138496;
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
        }
    </style>
</head>


<body class="dashboard">
    <div class="main-container">
        <header>
            <h1>Dashboard de la Concesionaria</h1>
            <p class="welcome-text">Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></strong></p>
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
        <?php if ($rol === 'admin'): ?>
                <!-- Tarjeta para Compras en Proceso -->
                <div class="card">
                    <h2><i class="fas fa-clock"></i> Compras en Proceso</h2>
                    <p>Revisa y acepta las compras pendientes de aprobación.</p>
                    <a href="gestionar_compras_pendientes.php" class="card-link">
                        <i class="fas fa-arrow-right"></i> Ir al Panel
                    </a>
                </div>

                <!-- Nueva Tarjeta para Compras Completadas -->
                <div class="card">
                    <h2><i class="fas fa-check-circle"></i> Compras Completadas</h2>
                    <p>Gestiona el historial de compras finalizadas.</p>
                    <a href="gestionar_compras_completadas.php" class="card-link">
                        <i class="fas fa-arrow-right"></i> Ver Historial
                    </a>
                </div>

                <!-- Tarjeta para Autos -->
                <div class="card">
                    <h2><i class="fas fa-car"></i> Gestionar Autos</h2>
                    <p>Administra el inventario de vehículos disponibles.</p>
                    <a href="gestionar-autos.php" class="card-link">
                        <i class="fas fa-arrow-right"></i> Ir al Panel
                    </a>
                </div>

                <!-- Tarjeta para Usuarios -->
                <div class="card">
                    <h2><i class="fas fa-users-cog"></i> Gestionar Usuarios</h2>
                    <p>Administra cuentas de usuarios y permisos.</p>
                    <a href="usuarios.php" class="card-link">
                        <i class="fas fa-arrow-right"></i> Administrar
                    </a>
                </div>
            <?php else: ?>
                <!-- Módulos para usuarios normales -->
                <div class="card">
                    <h2><i class="fas fa-car-side"></i> Autos Disponibles</h2>
                    <p>Explora nuestro catálogo de vehículos.</p>
                    <a href="autos-disponibles.php" class="card-link">
                        <i class="fas fa-arrow-right"></i> Ver Catálogo
                    </a>
                </div>
                <div class="card">
                    <h2><i class="fas fa-shopping-bag"></i> Mis Compras</h2>
                    <p>Revisa el estado de tus compras.</p>
                    <a href="gestionar_compra.php" class="card-link">
                        <i class="fas fa-arrow-right"></i> Ver Compras
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Tarjeta de Logout -->
            <div class="card logout-card">
                <h2><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</h2>
                <p>Finaliza tu sesión de forma segura.</p>
                <a href="logout.php" class="card-link">
                    <i class="fas fa-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </div>
</body>

</html>
