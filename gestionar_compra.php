<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario']['id'];

$sql = "SELECT 
    c.id, 
    c.primer_nombre, 
    c.primer_apellido, 
    c.direccion_envio,
    c.email,
    c.telefono,
    c.total, 
    c.ultimos_digitos, 
    c.fecha_compra,
    c.estado,
    GROUP_CONCAT(CONCAT(a.marca, ' ', a.modelo) SEPARATOR ', ') AS autos
FROM compras c
LEFT JOIN detalles_compra dc ON c.id = dc.compra_id
LEFT JOIN autos a ON dc.auto_id = a.id
WHERE c.usuario_id = ?
GROUP BY c.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Compras | MotorShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --morado-profundo: #4F0341;
            --morado-principal: #6a0dad;
            --morado-secundario: #9370db;
            --lavanda: #e6e6fa;
            --blanco: #ffffff;
        }
        
        body {
            background: linear-gradient(145deg, #f5f0ff 0%, #d9c7ff 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
        }
        
        .compra-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            margin-bottom: 25px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(106, 13, 173, 0.1);
            border: none;
        }
        
        .compra-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(106, 13, 173, 0.2);
        }
        
        .compra-header {
            background: linear-gradient(135deg, var(--morado-principal) 0%, var(--morado-profundo) 100%);
            color: var(--blanco);
            padding: 20px;
            position: relative;
            border-bottom: 3px solid rgba(255,255,255,0.1);
        }
        
        .compra-body {
            padding: 25px;
            background: var(--blanco);
            position: relative;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(106, 13, 173, 0.1);
        }
        
        .info-icon {
            font-size: 1.2rem;
            min-width: 35px;
            color: var(--morado-principal);
            padding-top: 3px;
        }
        
        .estado-badge {
            padding: 7px 14px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .estado-pendiente {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .estado-completado {
            background-color: rgba(25, 135, 84, 0.15);
            color: var(--success);
        }
        
        .btn-action {
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s;
            min-width: 130px;
            text-align: center;
            border: 2px solid transparent;
        }
        
        .btn-editar {
            background-color: var(--morado-principal);
            color: var(--blanco);
        }
        
        .btn-editar:hover {
            background-color: var(--morado-profundo);
            transform: translateY(-3px);
            border-color: rgba(106, 13, 173, 0.3);
        }
        
        .btn-cancelar {
            background-color: #dc3545;
            color: var(--blanco);
        }
        
        .btn-cancelar:hover {
            background-color: #bb2d3b;
            transform: translateY(-3px);
            border-color: rgba(220, 53, 69, 0.3);
        }
        
        .empty-state {
            background: var(--blanco);
            border-radius: 15px;
            padding: 4rem;
            box-shadow: 0 10px 30px rgba(106, 13, 173, 0.08);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--morado-principal) 0%, var(--morado-profundo) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
            margin: 25px 0;
        }
        
        .timeline::before {
            content: "";
            position: absolute;
            left: 11px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--morado-secundario);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .timeline-item::before {
            content: "";
            position: absolute;
            left: -30px;
            top: 7px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--morado-secundario);
            border: 3px solid var(--blanco);
            box-shadow: 0 0 0 2px var(--morado-secundario);
        }
        
        .alert-success {
            background: rgba(25, 135, 84, 0.9);
            color: white;
            border: none;
            backdrop-filter: blur(5px);
        }
        
        .btn-outline-secondary {
            border-color: var(--morado-secundario);
            color: var(--morado-principal);
        }
        
        .btn-outline-secondary:hover {
            background: var(--morado-secundario);
            color: white;
        }
        
        .text-success {
            color: var(--morado-principal)!important;
        }
        
        .btn-primary {
            background: var(--morado-principal);
            border: none;
            padding: 12px 28px;
        }
        
        .btn-primary:hover {
            background: var(--morado-profundo);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="display-5 fw-bold mb-0">
                <i class="fas fa-shopping-bag me-2 gradient-text"></i>
                <span class="gradient-text">Mis Compras</span>
            </h1>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> Volver al Panel
            </a>
        </div>

        <?php if(isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-lg">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3 fs-4"></i>
                <div>
                    <h5 class="mb-1">¡Operación Exitosa!</h5>
                    <p class="mb-0"><?= $_SESSION['mensaje'] ?></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if($result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-lg-2 g-4">
                <?php while($compra = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="compra-card h-100">
                        <div class="compra-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i> Orden #<?= $compra['id'] ?>
                                </h5>
                                <span class="estado-badge estado-<?= $compra['estado'] ?>">
                                    <?= ucfirst($compra['estado']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="compra-body">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="mb-1">Cliente</h6>
                                    <p class="mb-0"><?= htmlspecialchars($compra['primer_nombre']) ?> <?= htmlspecialchars($compra['primer_apellido']) ?></p>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="mb-1">Vehículos</h6>
                                    <p class="mb-0"><?= $compra['autos'] ?? 'No especificado' ?></p>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="mb-1">Dirección de Envío</h6
                                    <p class="mb-0"><?= htmlspecialchars($compra['direccion_envio']) ?></p>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="mb-1">Pago</h6>
                                            <p class="mb-0">**** **** **** <?= $compra['ultimos_digitos'] ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="info-content">
                                            <h6 class="mb-1">Fecha</h6>
                                            <p class="mb-0"><?= date('d M Y H:i', strtotime($compra['fecha_compra'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="timeline mt-4">
                                <div class="timeline-item">
                                    <h6 class="mb-1">Estado actual</h6>
                                    <p class="mb-0 text-muted"><?= 
                                        ($compra['estado'] == 'completado') 
                                        ? 'Entrega finalizada el ' . date('d M Y', strtotime($compra['fecha_compra']))
                                        : 'Procesando tu pedido'
                                    ?></p>
                                </div>
                                <div class="timeline-item">
                                    <h6 class="mb-1">Total pagado</h6>
                                    <p class="mb-0 h5 text-success">$<?= number_format($compra['total'], 2) ?></p>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="editar_compra_completa.php?id=<?= $compra['id'] ?>" 
                                   class="btn btn-editar btn-action">
                                    <i class="fas fa-edit me-2"></i> Editar
                                </a>
                                
                                <form action="cancelar_compra.php" method="POST" onsubmit="return confirm('¿Confirmas la cancelación de esta compra?')">
                                    <input type="hidden" name="compra_id" value="<?= $compra['id'] ?>">
                                    <button type="submit" class="btn btn-cancelar btn-action" 
                                        <?= $compra['estado'] == 'completado' ? 'disabled' : '' ?>>
                                        <i class="fas fa-times-circle me-2"></i> Cancelar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-shopping-cart fa-4x text-muted opacity-25"></i>
                </div>
                <h3 class="text-muted mb-3">¡Aún no tienes compras!</h3>
                <p class="text-muted mb-4">Descubre nuestros vehículos disponibles y realiza tu primera compra</p>
                <a href="autos-disponibles.php" class="btn btn-primary btn-lg rounded-pill px-4">
                    <i class="fas fa-car me-2"></i> Explorar Catálogo
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animaciones mejoradas
        document.querySelectorAll('.compra-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px)';
                card.querySelector('.compra-header').style.background = 'linear-gradient(135deg, #4F0341 0%, #6a0dad 100%)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.querySelector('.compra-header').style.background = 'linear-gradient(135deg, var(--morado-principal) 0%, var(--morado-profundo) 100%)';
            });
        });

        // Tooltips dinámicos
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                boundary: 'window',
                animation: true
            });
        });

        // Efecto de carga suave
        window.addEventListener('DOMContentLoaded', () => {
            document.body.style.opacity = '1';
            document.body.style.transition = 'opacity 0.5s ease';
        });
    </script>
</body>
</html>

