<?php
session_start();
include 'conexion.php';

// Validación de sesión y permisos reforzada
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    header('Location: dashboard.php');
    exit();
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errores[] = "Token de seguridad inválido";
    }

    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $estado = $_POST['estado'];
    $descripcion = trim($_POST['descripcion']);

    // Validaciones mejoradas
    if (empty($marca)) $errores[] = "Marca es obligatoria";
    if (empty($modelo)) $errores[] = "Modelo es obligatoria";
    if (!is_numeric($_POST['precio']) || $precio <= 0) $errores[] = "Precio debe ser un número positivo";
    if (!is_numeric($_POST['stock']) || $stock < 0) $errores[] = "Stock debe ser un número entero positivo";

    // Procesar imagen con validaciones reforzadas
    $imagen = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Validar tamaño (máximo 2MB)
        $max_size = 2 * 1024 * 1024;
        if ($_FILES['imagen']['size'] > $max_size) {
            $errores[] = "La imagen debe pesar menos de 2MB";
        }

        // Validar tipo de archivo
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $extensiones_validas = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!in_array($extension, $extensiones_validas)) {
            $errores[] = "Formato de imagen no válido (Use JPG, PNG o WEBP)";
        } else {
            // Crear directorio si no existe
            $directorio = __DIR__ . "/img/autos/";
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            // Generar nombre único
            $imagen = uniqid() . '.' . $extension;
            $ruta = $directorio . $imagen;
            
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
                $errores[] = "Error al subir imagen";
                error_log("Error subiendo archivo: " . $_FILES['imagen']['error']);
            }
        }
    } else {
        $errores[] = "Imagen es requerida";
    }

    if (empty($errores)) {
        $sql = "INSERT INTO autos (marca, modelo, precio, stock, estado, descripcion, imagen) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssss", $marca, $modelo, $precio, $stock, $estado, $descripcion, $imagen);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Auto agregado correctamente";
            header("Location: gestionar-autos.php");
            exit();
        } else {
            // Eliminar imagen si hubo error en BD
            if (!empty($imagen) && file_exists($directorio . $imagen)) {
                unlink($directorio . $imagen);
            }
            $errores[] = "Error al guardar: " . $stmt->error;
            error_log("Error BD al agregar auto: " . $stmt->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Auto - Concesionaria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos del dashboard */
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
            max-height: 150px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 1rem;
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

        .error-input {
            border-color: #dc3545 !important;
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
        }
    </style>
</head>
<body class="dashboard">
    <div class="main-container">
        <header>
            <h1>Agregar Auto</h1>
        </header>

        <div class="form-container">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $token ?>">
                
                <?php if(!empty($errores)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="form-label">Marca*</label>
                    <input type="text" name="marca" class="form-control <?= isset($errores['marca']) ? 'error-input' : '' ?>" 
                           value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Modelo*</label>
                    <input type="text" name="modelo" class="form-control" 
                           value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Precio*</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="precio" class="form-control" 
                                   step="0.01" value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Stock*</label>
                        <input type="number" name="stock" class="form-control" 
                               min="0" value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>" required>
                    </div>
                </div>
                
                <!-- Sección Estado -->
<div class="mb-4">
    <label class="form-label">Estado*</label>
    <select name="estado" class="form-select" required>
        <option value="disponible" 
                style="background-color: #333; color: white;"
                <?= ($_POST['estado'] ?? '') === 'disponible' ? 'selected' : '' ?>>
            Disponible
        </option>
        <option value="mantenimiento" 
                style="background-color: #333; color: white;"
                <?= ($_POST['estado'] ?? '') === 'mantenimiento' ? 'selected' : '' ?>>
            En Mantenimiento
        </option>
        <option value="reservado" 
                style="background-color: #333; color: white;"
                <?= ($_POST['estado'] ?? '') === 'reservado' ? 'selected' : '' ?>>
            Reservado
        </option>
    </select>
</div>

<!-- Sección Descripción -->
<div class="mb-4">
    <label class="form-label">Descripción</label>
    <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
</div>

<!-- Sección Imagen -->
<div class="mb-4">
    <label class="form-label">Imagen del Auto*</label>
    <div class="file-upload">
        <input type="file" name="imagen" id="imagen" class="d-none" required accept="image/jpeg, image/png, image/webp">
        <label for="imagen" class="d-block">
            <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: var(--secondary);"></i>
            <p>Haz clic para subir imagen (Max. 2MB)</p>
            <img id="preview" class="preview-img d-none">
        </label>
    </div>
</div>

<!-- Botones de Acción -->
<div class="d-flex justify-content-between align-items-center mt-5">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i> Guardar Auto
    </button>
    <a href="gestionar-autos.php" class="btn btn-secondary">
        <i class="fas fa-times me-2"></i> Cancelar
    </a>
</div>

<!-- Estilos Adicionales para el Select -->
<style>
/* Estilo para las opciones del select */
.form-select option {
    background-color: #333 !important;
    color: white !important;
    padding: 12px;
}

/* Estilo para el hover (funciona en algunos navegadores) */
.form-select option:hover {
    background-color: #2575fc !important;
}

/* Estilo para la flecha del select */
.form-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23e0e0e0'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
}

/* Mejoras para el file-upload */
.file-upload {
    transition: all 0.3s ease;
    border: 2px dashed rgba(255, 255, 255, 0.3);
}

.file-upload:hover {
    border-color: rgba(255, 255, 255, 0.5);
}

.preview-img {
    max-height: 200px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>

<!-- Scripts -->
<script>
// Vista previa de imagen
document.getElementById('imagen').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    }
});

// Validación de tamaño
document.querySelector('form').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('imagen');
    const maxSize = 2 * 1024 * 1024;
    
    if (fileInput.files[0] && fileInput.files[0].size > maxSize) {
        e.preventDefault();
        alert('La imagen excede el tamaño máximo de 2MB');
    }
});

// Mejorar la experiencia del select
document.querySelector('.form-select').addEventListener('focus', function() {
    this.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
});

document.querySelector('.form-select').addEventListener('blur', function() {
    this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
});
</script>

</body>
</html>
