<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'conexion.php';

$compra_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT 
    primer_nombre, primer_apellido, segundo_apellido,
    direccion_envio, email, telefono, ultimos_digitos
FROM compras WHERE id = ?");
$stmt->bind_param("i", $compra_id);
$stmt->execute();
$result = $stmt->get_result();
$compra = $result->fetch_assoc();

if (!$compra) {
    header("Location: gestionar_compra.php?error=compra_no_existe");
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --morado-profundo: #4F0341;
            --morado-principal: #6a0dad;
            --morado-secundario: #9370db;
            --lavanda: #f5f0ff;
            --blanco: #ffffff;
        }
        
        body {
            background: linear-gradient(145deg, 
                rgba(106, 13, 173, 0.05) 0%, 
                rgba(255, 255, 255, 1) 100%
            );
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
        }
        
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: var(--blanco);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(106, 13, 173, 0.1);
            border: 1px solid rgba(106, 13, 173, 0.1);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .form-header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 25%;
            width: 50%;
            height: 3px;
            background: linear-gradient(90deg, 
                var(--morado-principal) 0%, 
                var(--morado-profundo) 100%
            );
            border-radius: 3px;
        }
        
        .form-icon {
            font-size: 2.5rem;
            color: var(--morado-principal);
            margin-bottom: 1rem;
            background: rgba(106, 13, 173, 0.1);
            width: 80px;
            height: 80px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .form-title {
            font-weight: 700;
            background: linear-gradient(135deg, 
                var(--morado-principal) 0%, 
                var(--morado-profundo) 100%
            );
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .form-group {
            margin-bottom: 1.8rem;
        }
        
        .form-control-lg {
            padding: 12px 16px;
            border-radius: 8px;
            border: 2px solid rgba(106, 13, 173, 0.1);
            transition: all 0.3s;
        }
        
        .form-control-lg:focus {
            border-color: var(--morado-secundario);
            box-shadow: 0 0 0 0.25rem rgba(106, 13, 173, 0.15);
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            font-size: 1.1rem;
            border-radius: 50px;
            background: linear-gradient(135deg, 
                var(--morado-principal) 0%, 
                var(--morado-profundo) 100%
            );
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(106, 13, 173, 0.2);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }
        
        .bg-light {
            background-color: rgba(106, 13, 173, 0.05)!important;
            border: 2px dashed rgba(106, 13, 173, 0.3)!important;
            padding: 1rem;
            border-radius: 8px;
        }
        
        .text-muted {
            color: rgba(106, 13, 173, 0.7)!important;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--morado-profundo);
            margin-bottom: 0.5rem;
        }
        
        .fa-credit-card {
            color: var(--morado-principal);
            margin-right: 8px;
        }
        
        h2 {
            background: linear-gradient(135deg, 
                var(--morado-principal) 0%, 
                var(--morado-profundo) 100%
            );
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="form-container">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <h2>Editar Compra #<?= $compra_id ?></h2>
            </div>
            
            <?php if(isset($_SESSION['errores'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                    <div>
                        <h5 class="mb-1">¡Corrige estos errores!</h5>
                        <?php foreach($_SESSION['errores'] as $error): ?>
                            <div class="mb-1"><?= $error ?></div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['errores']); ?>
            </div>
            <?php endif; ?>

            <form method="post" action="actualizar_compra_completa.php">
                <input type="hidden" name="compra_id" value="<?= $compra_id ?>">
                
                <!-- Método de Pago (no editable) -->
                <div class="form-group">
                    <label class="form-label">Método de Pago</label>
                    <div class="form-control bg-light">
                        <i class="fas fa-credit-card"></i> Tarjeta terminada en <?= $compra['ultimos_digitos'] ?>
                    </div>
                    <small class="text-muted">No modificable por seguridad</small>
                </div>

                <!-- Campos editables -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Primer Nombre</label>
                            <input type="text" name="primer_nombre" 
                                   class="form-control form-control-lg"
                                   value="<?= htmlspecialchars($compra['primer_nombre']) ?>"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Primer Apellido</label>
                            <input type="text" name="primer_apellido" 
                                   class="form-control form-control-lg"
                                   value="<?= htmlspecialchars($compra['primer_apellido']) ?>"
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Segundo Apellido</label>
                    <input type="text" name="segundo_apellido" 
                           class="form-control form-control-lg"
                           value="<?= htmlspecialchars($compra['segundo_apellido']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Dirección de Envío</label>
                    <input type="text" name="direccion_envio" 
                           class="form-control form-control-lg"
                           value="<?= htmlspecialchars($compra['direccion_envio']) ?>"
                           required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" 
                                   class="form-control form-control-lg"
                                   value="<?= htmlspecialchars($compra['email']) ?>"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" 
                                   class="form-control form-control-lg"
                                   value="<?= htmlspecialchars($compra['telefono']) ?>"
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary btn-submit">
                        <i class="fas fa-save me-2"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animación de carga suave
        document.addEventListener('DOMContentLoaded', () => {
            document.body.style.opacity = '1';
            document.body.style.transition = 'opacity 0.5s ease';
            
            // Efecto hover en inputs
            document.querySelectorAll('.form-control-lg').forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.querySelector('.form-label').style.color = 'var(--morado-principal)';
                });
                
                input.addEventListener('blur', () => {
                    input.parentElement.querySelector('.form-label').style.color = 'var(--morado-profundo)';
                });
            });
        });
    </script>
</body>
</html>

