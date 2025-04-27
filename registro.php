<?php
session_start();
include 'conexion.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $clave = $_POST['clave'];
    $confirmar_clave = $_POST['confirmar_clave'];

    // Validaciones
    $errors = [];
    
    if(empty($nombre)) $errors[] = "El nombre es obligatorio";
    if(strlen($usuario) < 5) $errors[] = "El usuario debe tener mínimo 5 caracteres";
    if(!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) $errors[] = "El usuario solo puede contener letras, números y guiones bajos";
    if(strlen($clave) < 8) $errors[] = "La contraseña debe tener mínimo 8 caracteres";
    if($clave !== $confirmar_clave) $errors[] = "Las contraseñas no coinciden";

    if(empty($errors)) {
        // Verificar usuario existente
        $check_query = "SELECT id FROM usuarios WHERE usuario = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $usuario);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if($check_stmt->num_rows > 0) {
            $message = "⚠️ El usuario ya existe";
        } else {
            $hashed_password = password_hash($clave, PASSWORD_ARGON2ID);
            
            $query = "INSERT INTO usuarios (nombre, usuario, clave, rol, fecha_creacion) 
                     VALUES (?, ?, ?, 'usuario', NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $nombre, $usuario, $hashed_password);

            if ($stmt->execute()) {
                $message = "✅ Usuario registrado correctamente";
            } else {
                $message = "⚠️ Error al registrar: " . $stmt->error;
            }
        }
    } else {
        $message = "⚠️ " . implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0d6efd;
            --success: #198754;
            --danger: #dc3545;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .register-container {
            max-width: 500px;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--primary) 0%, #0b5ed7 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .register-body {
            padding: 2rem;
        }
        
        .form-floating label {
            color: #6c757d;
        }
        
        .btn-register {
            background: linear-gradient(135deg, var(--success) 0%, #146c43 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
        }
        
        .message {
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 1.5rem;
        }
        
        .message-success {
            background-color: rgba(25, 135, 84, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }
        
        .message-error {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="register-container">
            <div class="register-header">
                <h2><i class="fas fa-user-plus me-2"></i>Registro de Usuario</h2>
            </div>
            
            <div class="register-body">
                <?php if($message): ?>
                    <div class="message <?= strpos($message, '✅') !== false ? 'message-success' : 'message-error' ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" autocomplete="off">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               placeholder="Nombre completo" required>
                        <label for="nombre"><i class="fas fa-user me-2"></i>Nombre completo</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="usuario" name="usuario" 
                               placeholder="Usuario" required minlength="5">
                        <label for="usuario"><i class="fas fa-at me-2"></i>Usuario</label>
                    </div>
                    
                    <div class="form-floating mb-3 password-container">
                        <input type="password" class="form-control" id="clave" name="clave" 
                               placeholder="Contraseña" required minlength="8">
                        <label for="clave"><i class="fas fa-lock me-2"></i>Contraseña</label>
                        <span class="password-toggle" onclick="togglePassword('clave')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <div class="form-floating mb-4 password-container">
                        <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" 
                               placeholder="Confirmar contraseña" required>
                        <label for="confirmar_clave"><i class="fas fa-lock me-2"></i>Confirmar contraseña</label>
                        <span class="password-toggle" onclick="togglePassword('confirmar_clave')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-register w-100 mb-3">
                        <i class="fas fa-user-plus me-2"></i>Registrarse
                    </button>
                    
                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Volver al login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            
            if(input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
