<?php
session_start();
include "../login/conexion.php";

// Contadores específicos para usuario
function contarOrdenesUsuario($conn, $id_usuario) {
    $query = $conn->prepare("SELECT COUNT(*) as total FROM ordenes WHERE id_usuario = ?");
    $query->bind_param("i", $id_usuario);
    $query->execute();
    return $query->get_result()->fetch_assoc()['total'];
}

function obtenerMetricasUsuario($conn, $id_usuario) {
    $metricas = [];
    
    // Ventas del usuario hoy
    $hoy = date('Y-m-d');
    $query = $conn->prepare("SELECT COALESCE(SUM(total), 0) as ventas_hoy FROM ordenes WHERE id_usuario = ? AND DATE(fecha) = ? AND estado = 'pagado'");
    $query->bind_param("is", $id_usuario, $hoy);
    $query->execute();
    $metricas['ventas_hoy'] = $query->get_result()->fetch_assoc()['ventas_hoy'];
    
    // Órdenes pendientes del usuario
    $query = $conn->prepare("SELECT COUNT(*) as pendientes FROM ordenes WHERE id_usuario = ? AND estado = 'pendiente'");
    $query->bind_param("i", $id_usuario);
    $query->execute();
    $metricas['ordenes_pendientes'] = $query->get_result()->fetch_assoc()['pendientes'];
    
    return $metricas;
}

// Asumiendo que el ID de usuario está en la sesión
$id_usuario = $_SESSION['id_usuario'] ?? 1; // Valor por defecto si no existe
$ordenes_usuario = contarOrdenesUsuario($conn, $id_usuario);
$metricas = obtenerMetricasUsuario($conn, $id_usuario);

// Órdenes recientes del usuario
$ordenes_recientes_stmt = $conn->prepare("SELECT id_orden, fecha, total, estado FROM ordenes WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 5");
$ordenes_recientes_stmt->bind_param("i", $id_usuario);
$ordenes_recientes_stmt->execute();
$ordenes_recientes = $ordenes_recientes_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$ordenes_recientes_stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Usuario - Restaurante POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-container {
            padding: 25px;
            background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
            min-height: 100vh;
            color: #FFFFFF;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .user-welcome {
            background: linear-gradient(135deg, #00AFFF 0%, #4ECDC4 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 175, 255, 0.3);
        }

        .user-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .user-stat-card {
            background: #2A2A2A;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: transform 0.3s ease;
            border-left: 5px solid #00AFFF;
        }

        .user-stat-card:hover {
            transform: translateY(-5px);
        }

        .user-stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #00AFFF;
        }

        .user-stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
            color: #00AFFF;
        }

        .user-stat-title {
            font-size: 0.9rem;
            color: #CCCCCC;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .user-stat-desc {
            font-size: 0.8rem;
            color: #888;
        }

        .user-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .user-action-btn {
            background: #333333;
            border: none;
            padding: 20px;
            border-radius: 12px;
            color: #FFFFFF;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            border: 2px solid transparent;
        }

        .user-action-btn:hover {
            background: #00AFFF;
            transform: translateY(-3px);
            border-color: #00AFFF;
        }

        .user-action-icon {
            font-size: 1.5rem;
        }

        .user-recent-orders {
            background: #2A2A2A;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .performance-badge {
            background: rgba(0, 175, 255, 0.2);
            color: #00AFFF;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pendiente { background: rgba(255, 107, 107, 0.2); color: #FF6B6B; }
        .status-pagado { background: rgba(0, 204, 102, 0.2); color: #00CC66; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Banner de Bienvenida Usuario -->
        <div class="user-welcome">
            <h1>¡Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?>! 🎯</h1>
            <p>Tu centro de control personal - Resumen de tu actividad</p>
            <span class="performance-badge">Rendimiento: Alto</span>
        </div>

        <!-- Métricas del Usuario -->
        <div class="user-stats-grid">
            <div class="user-stat-card">
                <div class="user-stat-icon">💰</div>
                <div class="user-stat-title">Ventas Hoy</div>
                <div class="user-stat-value">$<?php echo number_format($metricas['ventas_hoy'], 2); ?></div>
                <div class="user-stat-desc">Total generado hoy</div>
            </div>

            <div class="user-stat-card">
                <div class="user-stat-icon">📋</div>
                <div class="user-stat-title">Mis Órdenes</div>
                <div class="user-stat-value"><?php echo $ordenes_usuario; ?></div>
                <div class="user-stat-desc">Órdenes procesadas</div>
            </div>

            <div class="user-stat-card">
                <div class="user-stat-icon">⏳</div>
                <div class="user-stat-title">Pendientes</div>
                <div class="user-stat-value"><?php echo $metricas['ordenes_pendientes']; ?></div>
                <div class="user-stat-desc">Por atender</div>
            </div>
        </div>

        <!-- Acciones Rápidas Usuario -->
<div class="user-actions">

    <!-- NUEVA ORDEN → CARRITO -->
    <a href="../Crear_Orden/menu.php" class="user-action-btn">
        <i class="fas fa-plus user-action-icon"></i>
        <span>Nueva Orden</span>
    </a>

    <!-- DETALLES ÓRDENES → HISTORIAL -->
    <a href="../detalle_orden/historial_ordenes.php" class="user-action-btn">
        <i class="fas fa-receipt user-action-icon"></i>
        <span>Detalles Órdenes</span>
    </a>

    <a href="../clientes/clientes.php" class="user-action-btn">
        <i class="fas fa-users user-action-icon"></i>
        <span>Gestionar Clientes</span>
    </a>

    <a href="../productos/productos.php" class="user-action-btn">
        <i class="fas fa-utensils user-action-icon"></i>
        <span>Ver Productos</span>
    </a>

</div>


        <!-- Órdenes Recientes del Usuario -->
        <div class="user-recent-orders">
            <h3 style="color: #00AFFF; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-clock"></i> Mis Órdenes Recientes
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 12px; border-bottom: 2px solid #00AFFF; color: #00AFFF;">ID</th>
                        <th style="text-align: left; padding: 12px; border-bottom: 2px solid #00AFFF; color: #00AFFF;">Fecha</th>
                        <th style="text-align: left; padding: 12px; border-bottom: 2px solid #00AFFF; color: #00AFFF;">Total</th>
                        <th style="text-align: left; padding: 12px; border-bottom: 2px solid #00AFFF; color: #00AFFF;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ordenes_recientes)): ?>
                        <?php foreach ($ordenes_recientes as $orden): ?>
                        <tr style="border-bottom: 1px solid #333;">
                            <td style="padding: 12px;">#<?php echo $orden['id_orden']; ?></td>
                            <td style="padding: 12px;"><?php echo date('H:i', strtotime($orden['fecha'])); ?></td>
                            <td style="padding: 12px;">$<?php echo number_format($orden['total'], 2); ?></td>
                            <td style="padding: 12px;">
                                <span class="status-badge status-<?php echo $orden['estado']; ?>">
                                    <?php echo ucfirst($orden['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px; color: #888;">
                                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                No has creado órdenes recientemente
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>