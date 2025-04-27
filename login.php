<?php
session_start();
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    require_once('vendor/econea/nusoap/src/nusoap.php');
    $client = new nusoap_client('http://localhost/webservices/Proyecto/soap_login.php?wsdl', true);

    $params = array('usuario' => $usuario, 'clave' => $clave);
    $resultado = $client->call('validarUsuario', $params);

    if ($client->fault) {
        $message = 'Error en la respuesta del servicio SOAP.';
    } else {
        $err = $client->getError();
        if ($err) {
            $message = 'Error en la llamada al servicio SOAP: ' . $err;
        } else {
            if ($resultado !== 'false') {
                $data = json_decode($resultado, true);
                if ($data !== null && isset($data['id']) && isset($data['nombre']) && isset($data['rol'])) {
                    $_SESSION['usuario'] = [
                        'id' => $data['id'],
                        'nombre' => $data['nombre'],
                        'usuario' => $usuario, 
                        'rol' => $data['rol']
                    ];
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $message = "Error al decodificar los datos del usuario.";
                }
            } else {
                $message = "Credenciales incorrectas.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Concesionaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0d6efd;
            --primary-hover: #0b5ed7;
            --dark: #212529;
            --light: #f8f9fa;
            --danger: #dc3545;
            --success: #198754;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, #0b5ed7 100%);
            color: white;
            padding: 1.8rem;
            text-align: center;
            position: relative;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }

        .login-header i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .login-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            text-transform: uppercase;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        .form-floating label {
            color: #6c757d;
        }

        .message {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 4px solid var(--danger);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            color: var(--danger);
            font-weight: 500;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .password-container {
            position: relative;
        }

        .brand-logo {
            width: 80px;
            margin-bottom: 1rem;
        }

        .additional-links {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .additional-links a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s;
        }

        .additional-links a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-car"></i>
            <h2>Concesionaria</h2>
        </div>

        <div class="login-body">
            <?php if (isset($message)): ?>
                <div class="message">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-floating mb-4">
                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" required>
                    <label for="usuario"><i class="fas fa-user me-2"></i>Usuario</label>
                </div>

                <div class="form-floating mb-4 password-container">
                    <input type="password" class="form-control" id="clave" name="clave" placeholder="Contraseña" required>
                    <label for="clave"><i class="fas fa-lock me-2"></i>Contraseña</label>
                    <span class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </button>

                <div class="additional-links">
                    <a href="#"><i class="fas fa-question-circle me-1"></i>¿Olvidaste tu contraseña?</a><br>
                    <span class="text-muted">¿No tienes cuenta? <a href="registro.php">Regístrate</a></span>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('clave');
            const icon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
