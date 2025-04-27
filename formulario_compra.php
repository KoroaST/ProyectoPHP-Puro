<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verificar carrito y calcular total

if (empty($_SESSION['carrito'])) {
    echo "<div class='empty-cart' style='text-align:center; padding:2rem;'>
            <h2><i class='fas fa-shopping-cart'></i> Carrito Vacío</h2>
            <a href='autos-disponibles.php' class='btn-primary'>
                <i class='fas fa-arrow-left'></i> Volver a la tienda
            </a>
          </div>";
    exit();
}


$total = array_sum(array_column($_SESSION['carrito'], 'precio'));

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario']['id'];
$query_usuario = "SELECT 
    primer_nombre, 
    primer_apellido, 
    segundo_apellido 
    FROM usuarios 
    WHERE id = ?";

$stmt_usuario = $conn->prepare($query_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$segundo_nombre = $segundo_apellido = $email_usuario = $telefono_usuario = '';

if($result_usuario->num_rows > 0) {
    $usuario = $result_usuario->fetch_assoc();
  
    $primer_nombre = $usuario['primer_nombre'] ?? '';
    $segundo_nombre = $usuario['segundo_nombre'] ?? '';
    $primer_apellido = $usuario['primer_apellido'] ?? '';
    $segundo_apellido = $usuario['segundo_apellido'] ?? '';
    $email_usuario = $usuario['email'] ?? '';
    $telefono_usuario = $usuario['telefono'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Compra</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%) fixed;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: white;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        h1 {
            color: white;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .full-width {
            grid-column: span 2;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
        }

        input, select {
            width: 100%;
            padding: 0.8rem 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        input:focus, select:focus {
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
            outline: none;
            background-color: rgba(255, 255, 255, 0.15);
        }

        .btn-confirmar {
            background: #28a745;
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            cursor: pointer;
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 2rem auto 0;
            transition: all 0.3s;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .btn-confirmar:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .resumen-compra {
            background: rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .resumen-compra p {
            margin-bottom: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .resumen-compra strong {
            color: white;
        }

        .payment-methods {
            display: flex;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .payment-method {
            flex: 1;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.05);
        }

        .payment-method:hover {
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.1);
        }

        .payment-method.selected {
            border: 2px solid #007bff;
            background: rgba(0, 123, 255, 0.1);
        }

        .payment-method img {
            height: 30px;
            margin-right: 10px;
            vertical-align: middle;
            filter: brightness(0) invert(1);
        }

        .payment-option {
            padding: 1.5rem;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.1);
            margin: 1.5rem 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .error-message {
            color: #ff6b6b;
            font-size: 0.9em;
            margin-top: 0.3rem;
        }

        .botones-container {
            text-align: center;
            margin-top: 2rem;
        }

        .regresar-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .regresar-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }

            .payment-methods {
                flex-direction: column;
            }
        }
    </style>
</head>


<body>
    <div class="form-container">
        <h1><i class="fas fa-credit-card"></i> Finalizar Compra</h1>
        
        <form action="completar_compra.php" method="POST">
            <div class="form-grid">

                <!-- Sección de nombres y apellidos -->
                <div class="form-group">
                    <label for="primer_nombre"><i class="fas fa-user"></i> Primer Nombre</label>
                    <input type="text" name="primer_nombre" id="primer_nombre" required 
                           value="<?= htmlspecialchars($primer_nombre) ?>"
                           style="width: 90%; padding: 0.7rem 1rem;">
                </div>
                
                <div class="form-group">
                    <label for="primer_apellido"><i class="fas fa-user-tag"></i> Primer Apellido</label>
                    <input type="text" name="primer_apellido" id="primer_apellido" required 
                           value="<?= htmlspecialchars($primer_apellido) ?>"
                           style="width: 90%; padding: 0.7rem 1rem;">
                </div>

                <div class="form-group">
                    <label for="segundo_nombre"><i class="fas fa-user-tag"></i> Segundo Nombre</label>
                    <input type="text" name="segundo_nombre" id="segundo_nombre" 
                           value="<?= htmlspecialchars($segundo_nombre) ?>"
                           style="width: 90%; padding: 0.7rem 1rem;">
                </div>
                
                <div class="form-group">
                    <label for="segundo_apellido"><i class="fas fa-user-tag"></i> Segundo Apellido</label>
                    <input type="text" name="segundo_apellido" id="segundo_apellido" 
                           value="<?= htmlspecialchars($segundo_apellido) ?>"
                           style="width: 90%; padding: 0.7rem 1rem;">
                </div>

                <!-- Direcciones -->
                <div class="form-group full-width">
                    <label for="direccion_residencia"><i class="fas fa-home"></i> Dirección de Residencia</label>
                    <input type="text" name="direccion_residencia" id="direccion_residencia" required
                           style="padding: 0.7rem 1rem;">
                </div>
                
                <div class="form-group full-width">
                    <label for="direccion_envio"><i class="fas fa-truck"></i> Dirección de Envío</label>
                    <input type="text" name="direccion_envio" id="direccion_envio" required
                           style="padding: 0.7rem 1rem;">
                </div>

                <!-- Contacto -->
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" name="email" id="email" required 
                           value="<?= htmlspecialchars($email_usuario) ?>"
                           style="width: 90%; padding: 0.7rem 1rem;">
                </div>
                
                <div class="form-group">
                    <label for="telefono"><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" required 
                           value="<?= htmlspecialchars($telefono_usuario) ?>"
                           style="width: 90%; padding: 0.7rem 1rem;">
                </div>

              <!-- Método de pago -->
<div class="form-group full-width">
    <label><i class="fas fa-credit-card"></i> Información de Pago</label>
    <div class="payment-option" style="
        background: rgba(0, 0, 0, 0.1); 
        border: 1px solid rgba(255, 255, 255, 0.2); 
        border-radius: 10px; 
        padding: 1.5rem;
    ">
        <!-- Tarjeta -->
        <div class="form-group" style="margin-top: 1.5rem;">
            <label for="numero_tarjeta"><i class="fas fa-credit-card"></i> Número de Tarjeta</label>
            <input type="text" name="numero_tarjeta" id="numero_tarjeta" 
                placeholder="•••• •••• •••• ••••" required
                style="
                    width: 85%; 
                    padding: 0.7rem 1rem; 
                    background: rgba(255, 255, 255, 0.1); 
                    border: 1px solid rgba(255, 255, 255, 0.3); 
                    color: white;
                ">
            <div class="error-message" id="tarjeta-error" style="color: #ff6b6b; display: none;">
                Por favor, ingrese 16 dígitos sin espacios.
            </div>
        </div>

        <!-- Vencimiento y CVV -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="vencimiento"><i class="fas fa-calendar-alt"></i> Vencimiento</label>
                <input type="text" name="vencimiento" id="vencimiento" 
                    placeholder="MM/AA" required
                    style="
                        width: 60%; 
                        padding: 0.7rem 1rem; 
                        background: rgba(255, 255, 255, 0.1); 
                        border: 1px solid rgba(255, 255, 255, 0.3); 
                        color: white;
                    ">
            </div>
            
            <div class="form-group">
                <label for="cvv"><i class="fas fa-lock"></i> CVV</label>
                <input type="text" name="cvv" id="cvv" placeholder="•••" required
                    style="
                        width: 50%; 
                        padding: 0.7rem 1rem; 
                        background: rgba(255, 255, 255, 0.1); 
                        border: 1px solid rgba(255, 255, 255, 0.3); 
                        color: white;
                    ">
            </div>
        </div>
    </div>
</div>

<!-- Contenedor padre para alinear verticalmente -->
<div style="display: flex; flex-direction: column; align-items: flex-start; gap: 20px; max-width: 500px;">

    <!-- Campo de notas adicionales -->
    <div class="form-group" style="width: 100%;">
        <label for="notas"><i class="fas fa-edit"></i> Notas Adicionales</label>
        <input type="text" name="notas" id="notas" placeholder="Instrucciones especiales para el envío" style="width: 100%;">
    </div>

    <!-- Resumen de compra -->
    <div class="resumen-compra" style="width: 100%;">
        <h3><i class="fas fa-receipt"></i> Resumen de Compra</h3>
        <p><strong>Total:</strong> $<?= number_format($total, 2) ?></p>
        <p><small>Los cargos finales pueden incluir impuestos según tu ubicación</small></p>
    </div>

    <!-- Botón de confirmar compra -->
    <div style="width: 100%;">
        <button type="submit" class="btn-confirmar" style="width: 100%;">
            <i class="fas fa-check-circle"></i> Confirmar Compra
        </button>
    </div>

</div>
