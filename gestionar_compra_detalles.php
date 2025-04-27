<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Validar ID de compra
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de compra inválido");
}

$compra_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario']['id'];

// Obtener datos de la compra (tu versión es correcta)
$query = "SELECT 
    id,
    usuario_id,
    primer_nombre,
    primer_apellido,
    segundo_apellido,
    direccion_envio,
    ultimos_digitos,
    total,
    fecha_compra,
    estado
    FROM compras 
    WHERE id = ? AND usuario_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $compra_id, $usuario_id);
$stmt->execute();
$compra = $stmt->get_result()->fetch_assoc();


if (!$compra) {
    die("Compra no encontrada o no autorizada");
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Compra #<?= $compra_id ?></title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        .dashboard-container {
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        h1 {
            color: var(--dark);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        input[type="text"], select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s;
        }

        input[type="text"]:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        small {
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .btn-actualizar {
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            max-width: 300px;
            margin: 2rem auto;
            display: block;
            font-weight: 500;
        }

        .btn-actualizar:hover {
            background: #0069d9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.2);
        }

        .acciones {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn-cancelar, .card-link {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
            text-align: center;
            min-width: 200px;
        }

        .btn-cancelar {
            background: var(--danger);
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-cancelar:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.2);
        }

        .card-link {
            background: var(--success);
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .card-link:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.2);
        }

        /* Efectos para todos los botones */
        button, .card-link {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .acciones {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-cancelar, .card-link {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1><i class="fas fa-edit"></i> Editar Compra #<?= htmlspecialchars($compra_id) ?></h1>
        
        <form action="actualizar_compra.php" method="POST">
            <input type="hidden" name="compra_id" value="<?= htmlspecialchars($compra_id) ?>">
            
            <!-- Campo de método de pago (simulado) -->
            <div class="form-group">
                <label><i class="fas fa-credit-card"></i> Método de Pago:</label>
                <select name="metodo_pago" disabled>
                    <option><?= htmlspecialchars($compra['ultimos_digitos'] ? 'Tarjeta terminada en ' . $compra['ultimos_digitos'] : 'No especificado') ?></option>
                </select>
                <small>(No modificable)</small>
            </div>

            <!-- Dirección real (según tu estructura de base de datos) -->
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Dirección:</label>
                <input type="text" name="direccion_envio" 
                       value="<?= htmlspecialchars($compra['direccion_envio'] ?? '') ?>" 
                       required>
            </div>

            <button type="submit" class="btn-actualizar">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </form>

        <div class="acciones">
            <form action="cancelar_compra.php" method="POST" 
                  onsubmit="return confirm('¿Cancelar esta compra?')">
                <input type="hidden" name="compra_id" value="<?= htmlspecialchars($compra_id) ?>">
                <button type="submit" class="btn-cancelar">
                    <i class="fas fa-times-circle"></i> Cancelar Compra
                </button>
            </form>

            <a href="dashboard.php" class="card-link">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</body>
</html>