<?php
session_start();
include "../login/conexion.php";

// Función simple para contar registros
function contar($conn, $tabla) {
    // Verificar que la tabla existe
    $check = $conn->query("SHOW TABLES LIKE '$tabla'");
    if ($check->num_rows > 0) {
        $res = $conn->query("SELECT COUNT(*) AS t FROM $tabla");
        return $res->fetch_assoc()['t'];
    }
    return 0;
}

// Obtener métricas básicas
function obtenerMetricasBasicas($conn) {
    $metricas = [];
    
    // Ventas del día
    $hoy = date('Y-m-d');
    $query = $conn->prepare("SELECT COALESCE(SUM(total), 0) as ventas_hoy FROM ordenes WHERE DATE(fecha) = ? AND estado = 'pagado'");
    $query->bind_param("s", $hoy);
    $query->execute();
    $metricas['ventas_hoy'] = $query->get_result()->fetch_assoc()['ventas_hoy'];
    
    // Órdenes pendientes
    $query = $conn->prepare("SELECT COUNT(*) as pendientes FROM ordenes WHERE estado = 'pendiente'");
    $query->execute();
    $metricas['ordenes_pendientes'] = $query->get_result()->fetch_assoc()['pendientes'];
    
    return $metricas;
}

// Solo contar las tablas que tienes
$ordenes = contar($conn, "ordenes");
$detalle = contar($conn, "detalle_orden");
$metricas = obtenerMetricasBasicas($conn);

// Órdenes recientes
$ordenes_recientes = $conn->query("
    SELECT o.id_orden, o.fecha, o.total, o.estado 
    FROM ordenes o 
    ORDER BY o.fecha DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Profesional - Restaurante POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            padding: 25px;
            background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
            min-height: 100vh;
            color: #FFFFFF;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #FF6600 0%, #FF8C42 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(255, 102, 0, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #2A2A2A;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border-left: 5px solid;
            transition: transform 0.3s ease;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.ventas { border-left-color: #FF6600; }
        .stat-card.pendientes { border-left-color: #FF6B6B; }
        .stat-card.orden { border-left-color: #9900FF; }
        .stat-card.detalle { border-left-color: #00AFFF; }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 0.9rem;
            color: #CCCCCC;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .chart-container, .recent-orders {
            background: #2A2A2A;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .chart-title, .orders-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #FF6600;
            margin-bottom: 20px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 2px solid #FF6600;
            color: #FF6600;
            font-weight: 600;
        }

        .orders-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #333333;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pendiente { background: rgba(255, 107, 107, 0.2); color: #FF6B6B; }
        .status-pagado { background: rgba(0, 204, 102, 0.2); color: #00CC66; }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            background: #333333;
            border: none;
            padding: 15px;
            border-radius: 10px;
            color: #FFFFFF;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .action-btn:hover {
            background: #FF6600;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
    <!-- Banner de Bienvenida -->
<div class="welcome-banner">
    <h1>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?>! 👋</h1>
    <p>Resumen ejecutivo del estado del restaurante</p>

    <div class="quick-actions">

        <!-- NUEVA ORDEN → CARRITO -->
        <a href="../Crear_Orden/menu.php" class="action-btn">
            <i class="fas fa-plus"></i> Nueva Orden
        </a>

        <!-- VER DETALLES → HISTORIAL ÓRDENES -->
        <a href="../detalle_orden/historial_ordenes.php" class="action-btn">
            <i class="fas fa-list"></i> Ver Detalles
        </a>

    </div>
</div>


        <!-- Grid de Métricas Principales -->
        <div class="stats-grid">
            <div class="stat-card ventas">
                <div class="stat-icon">💰</div>
                <div class="stat-title">Ventas Hoy</div>
                <div class="stat-value">$<?php echo number_format($metricas['ventas_hoy'], 2); ?></div>
            </div>

            <div class="stat-card pendientes">
                <div class="stat-icon">⏳</div>
                <div class="stat-title">Órdenes Pendientes</div>
                <div class="stat-value"><?php echo $metricas['ordenes_pendientes']; ?></div>
            </div>

            <div class="stat-card orden">
                <div class="stat-icon">📋</div>
                <div class="stat-title">Total Órdenes</div>
                <div class="stat-value"><?php echo $ordenes; ?></div>
            </div>

            <div class="stat-card detalle">
                <div class="stat-icon">🧾</div>
                <div class="stat-title">Items Detallados</div>
                <div class="stat-value"><?php echo $detalle; ?></div>
            </div>
        </div>

        <!-- Gráficos y Órdenes Recientes -->
        <div class="charts-grid">
            <div class="chart-container">
                <h3 class="chart-title">Actividad Reciente</h3>
                <canvas id="salesChart" height="250"></canvas>
            </div>

            <div class="recent-orders">
                <h3 class="orders-title">Órdenes Recientes</h3>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ordenes_recientes)): ?>
                            <?php foreach ($ordenes_recientes as $orden): ?>
                            <tr>
                                <td>#<?php echo $orden['id_orden']; ?></td>
                                <td>$<?php echo number_format($orden['total'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $orden['estado']; ?>">
                                        <?php echo ucfirst($orden['estado']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #888;">No hay órdenes recientes</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Gráfico simple de actividad
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Órdenes',
                    data: [12, 19, 15, 22, 18, 25, 20],
                    backgroundColor: '#FF6600',
                    borderColor: '#FF8C42',
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: '#FFFFFF'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        },
                        ticks: {
                            color: '#FFFFFF'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        },
                        ticks: {
                            color: '#FFFFFF'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>