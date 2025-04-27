<?php
session_start();
include 'conexion.php';

// Validación de sesión
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "Acceso denegado";
    header('Location: dashboard.php');
    exit();
}

// Verificar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID inválido";
    header('Location: gestionar-autos.php');
    exit();
}

$id = intval($_GET['id']);

// Consulta SQL para obtener los datos actuales del auto
$sql = "SELECT * FROM autos WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$auto = $stmt->get_result()->fetch_assoc();

// Verificar si existe el auto
if (!$auto) {
    $_SESSION['error'] = "Auto no encontrado";
    header('Location: gestionar-autos.php');
    exit();
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $estado = $_POST['estado'];
    $descripcion = $_POST['descripcion'];
    $imagen_actual = $_POST['imagen_actual'];
    $imagen_nombre = $imagen_actual;

    // Validar si se subió una nueva imagen
    if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = $_FILES['imagen'];
        $imagen_nombre = uniqid() . "_" . basename($imagen['name']);
        $imagen_ruta = "img/autos/" . $imagen_nombre;
        
        // Subir la imagen al servidor
        if (move_uploaded_file($imagen['tmp_name'], $imagen_ruta)) {
            // Eliminar la imagen anterior si se ha subido una nueva
            if (!empty($imagen_actual) && file_exists("img/autos/$imagen_actual")) {
                unlink("img/autos/$imagen_actual");
            }
        } else {
            $_SESSION['error'] = "Error al subir la imagen";
            header('Location: editar_auto.php?id=' . $id);
            exit();
        }
    }

    // Actualizar los datos del auto en la base de datos
    $sql_update = "UPDATE autos SET marca = ?, modelo = ?, precio = ?, stock = ?, estado = ?, descripcion = ?, imagen = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    
    // CORRECCIÓN CLAVE: Orden de parámetros y tipos de datos
    $stmt_update->bind_param("ssdisssi", 
        $marca,
        $modelo,
        $precio,
        $stock,
        $estado,
        $descripcion,
        $imagen_nombre,
        $id
    );

    if ($stmt_update->execute()) {
        $_SESSION['success'] = "Auto actualizado correctamente";
        header('Location: gestionar-autos.php');
        exit();
    } else {
        $_SESSION['error'] = "Error al actualizar: " . $stmt_update->error;
        header('Location: editar_auto.php?id=' . $id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Auto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Auto - Concesionaria</title>
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

        .form-container {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-label {
            color: var(--secondary);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.1);
            color: white !important;
        }

        .input-group-text {
            background-color: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: var(--secondary) !important;
        }

        .file-upload {
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .file-upload:hover {
            border-color: rgba(255, 255, 255, 0.5);
            background-color: rgba(255, 255, 255, 0.08);
        }

        .preview-img {
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: rgba(0, 123, 255, 0.7);
            border: 1px solid rgba(0, 123, 255, 0.8);
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-weight: 500;
            color: white;
        }

        .btn-secondary {
            background: rgba(108, 117, 125, 0.7);
            border: 1px solid rgba(108, 117, 125, 0.8);
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-weight: 500;
            color: white;
        }

        .btn-primary:hover, .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            background: rgba(0, 123, 255, 0.8);
        }

        .btn-secondary:hover {
            background: rgba(108, 117, 125, 0.8);
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

        .alert-danger {
            border-left-color: #dc3545;
            color: #f8d7da;
        }

        /* Estructura vertical mejorada */
        .form-row {
            margin-bottom: 1.5rem;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            header h1 {
                font-size: 2rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }

            .row {
                flex-direction: column;
            }

            .col-md-6 {
                width: 100%;
            }
        }
    </style>
</head>
<body class="dashboard">
    <div class="main-container">
        <header>
            <h1>Editar Auto</h1>
            <p class="welcome-text">Modificando: <strong><?= htmlspecialchars($auto['marca'] ?? 'Nuevo') ?> <?= htmlspecialchars($auto['modelo'] ?? 'Auto') ?></strong></p>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
        </header>

        <div class="form-container">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($auto['imagen'] ?? '') ?>">
                
                <div class="form-row">
                    <label class="form-label">Marca*</label>
                    <input type="text" name="marca" class="form-control" 
                           value="<?= htmlspecialchars($auto['marca'] ?? '') ?>" required>
                </div>
                
                <div class="form-row">
                    <label class="form-label">Modelo*</label>
                    <input type="text" name="modelo" class="form-control" 
                           value="<?= htmlspecialchars($auto['modelo'] ?? '') ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 form-row">
                        <label class="form-label">Precio*</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="precio" class="form-control" 
                                   step="0.01" value="<?= htmlspecialchars($auto['precio'] ?? 0) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-row">
                        <label class="form-label">Stock*</label>
                        <input type="number" name="stock" class="form-control" 
                               min="0" value="<?= htmlspecialchars($auto['stock'] ?? 0) ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <label class="form-label">Estado*</label>
                    <select name="estado" class="form-select" required>
                        <option value="disponible" <?= ($auto['estado'] ?? '') === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="vendido" <?= ($auto['estado'] ?? '') === 'vendido' ? 'selected' : '' ?>>Vendido</option>
                    </select>
                </div>

                <div class="form-row">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($auto['descripcion'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    <label class="form-label">Imagen Actual</label>
                    <div class="text-center mb-3">
                        <img src="img/autos/<?= htmlspecialchars($auto['imagen'] ?? '') ?>" 
                             class="preview-img" onerror="this.style.display='none'">
                    </div>
                    
                    <label class="form-label                <!-- Continuación desde la imagen actual -->
                <div class="form-row">
                    <label class="form-label">Nueva Imagen (Opcional)</label>
                    <div class="file-upload mb-3">
                        <div class="upload-content">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: var(--secondary);"></i>
                            <p>Arrastra y suelta o haz clic para seleccionar</p>
                            <input type="file" name="imagen" class="d-none">
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="form-row mt-4">
                    <div class="d-flex flex-wrap justify-content-between gap-3">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                        <a href="gestionar-autos.php" class="btn btn-secondary flex-grow-1">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Script mejorado con manejo de errores
        document.addEventListener('DOMContentLoaded', function() {
            // File upload interactivo
            const uploadArea = document.querySelector('.file-upload');
            if(uploadArea) {
                uploadArea.addEventListener('click', function(e) {
                    if(e.target.tagName !== 'INPUT') {
                        this.querySelector('input[type="file"]').click();
                    }
                });

                // Drag and drop básico
                uploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadArea.style.borderColor = 'rgba(255, 255, 255, 0.7)';
                });

                uploadArea.addEventListener('dragleave', () => {
                    uploadArea.style.borderColor = 'rgba(255, 255, 255, 0.3)';
                });
            }

            // Vista previa de imagen mejorada
            const imgInput = document.querySelector('input[name="imagen"]');
            if(imgInput) {
                imgInput.addEventListener('change', function(e) {
                    const preview = document.querySelector('.preview-img');
                    if(e.target.files.length > 0) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            if(preview) {
                                preview.src = event.target.result;
                                preview.style.display = 'block';
                            } else {
                                // Crear imagen si no existe
                                const newPreview = document.createElement('img');
                                newPreview.src = event.target.result;
                                newPreview.className = 'preview-img';
                                document.querySelector('.text-center').appendChild(newPreview);
                            }
                        };
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            }

            // Validación en tiempo real
            document.querySelector('form')?.addEventListener('submit', function(e) {
                const precio = document.querySelector('input[name="precio"]');
                if(parseFloat(precio.value) <= 0) {
                    e.preventDefault();
                    alert('El precio debe ser mayor a cero');
                    precio.focus();
                }
            });
        });
    </script>
</body>
</html>

