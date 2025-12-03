<?php
// Usamos el código PHP inicial que proporcionaste, adaptado para la nueva estructura visual.
$conexion = new mysqli("localhost", "root", "", "pos_restaurante");
if ($conexion->connect_error) {
    die("Error al conectar: " . $conexion->connect_error);
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("No se proporcionó un ID.");
}

$idOrden = intval($_GET["id"]);

// ---------- CONSULTA PRINCIPAL ----------
$sqlOrden = "
    SELECT 
        o.id_orden,
        o.fecha,
        o.total,
        o.estado,
        o.metodo_pago,
        c.nombre AS cliente,
        c.telefono,
        c.email
    FROM ordenes o
    JOIN clientes c ON o.id_cliente = c.id_cliente
    WHERE o.id_orden = ?
";
$stmt = $conexion->prepare($sqlOrden);
$stmt->bind_param("i", $idOrden);
$stmt->execute();
$resultadoOrden = $stmt->get_result();

if ($resultadoOrden->num_rows === 0) {
    die("No existe la orden solicitada.");
}

$orden = $resultadoOrden->fetch_assoc();

// ---------- DETALLE ----------
$sqlDetalle = "
    SELECT 
        d.id_detalle,
        p.nombre AS producto,
        p.descripcion,
        p.precio,
        d.cantidad,
        d.subtotal
    FROM detalle_orden d
    JOIN productos p ON d.id_producto = p.id_producto
    WHERE d.id_orden = ?
";
$stmt2 = $conexion->prepare($sqlDetalle);
$stmt2->bind_param("i", $idOrden);
$stmt2->execute();
$detalle = $stmt2->get_result();

// Función para obtener la clase de estado (basada en el CSS proporcionado)
function get_status_class($estado) {
    $estado = strtolower($estado);
    if ($estado === 'pagado' || $estado === 'completo') return 'status-pagado';
    if ($estado === 'pendiente' || $estado === 'pendiente_pago') return 'status-pendiente';
    if ($estado === 'enviado' || $estado === 'en_curso') return 'status-enviado';
    if ($estado === 'cancelado') return 'status-cancelado';
    if ($estado === 'procesando') return 'status-procesando';
    return 'status-default';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Orden #<?php echo $orden["id_orden"]; ?></title>
    <style>
/* ==========================================================
    📋 ESTILO PROFESIONAL - DETALLE DE ORDEN (CSS INCLUIDO)
    ========================================================== */

/* Variables actualizadas */
:root {
    --primario: #FF6600;
    --fondo: #212121;
    --texto: #FFFFFF;
    --accent: #333333;
    --exito: #27ae60;
    --alerta: #f39c12;
    --error: #e74c3c;
}

/* ======= Fondo con overlay para mejor legibilidad ======= */
body {
    font-family: 'Roboto', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: 
        linear-gradient(rgba(26, 26, 26, 0.92), rgba(26, 26, 26, 0.96)),
        url('https://images.unsplash.com/photo-1529042410759-befb1204b468?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat fixed;
    color: var(--texto);
    margin: 0;
    padding: 30px;
    min-height: 100vh;
    position: relative;
}

/* Efecto de partículas */
body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 10% 15%, rgba(255, 102, 0, 0.1) 0%, transparent 40%),
        radial-gradient(circle at 90% 85%, rgba(39, 174, 96, 0.08) 0%, transparent 40%);
    pointer-events: none;
    z-index: 0;
}

/* ======= Contenedor principal ======= */
.container {
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

/* ======= Encabezado ======= */
.header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 3px solid rgba(255, 102, 0, 0.3);
}

.header h1 {
    font-size: 2.5rem;
    margin: 0 0 10px 0;
    background: linear-gradient(135deg, #FF6600, #FF8C42);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
    letter-spacing: 0.5px;
}

.subtitle {
    color: #AAAAAA;
    font-size: 1.1rem;
    margin: 0;
}

/* ======= Grid de información mejorado ======= */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 35px;
}

.info-card {
    background: rgba(26, 26, 26, 0.9);
    backdrop-filter: blur(10px);
    padding: 25px;
    border-left: 5px solid var(--primario);
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--primario), transparent);
}

.info-card:hover {
    transform: translateY(-5px);
    box-shadow: 
        0 12px 35px rgba(255, 102, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.info-title {
    color: var(--primario);
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0 0 15px 0;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-title::before {
    content: '📋';
    font-size: 1.4rem;
}

.info-data {
    color: #FFFFFF;
    font-size: 1.1rem;
    margin: 8px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
}

.info-label {
    color: #BBBBBB;
    font-weight: 500;
    min-width: 120px;
}

.info-value {
    font-weight: 600;
    text-align: right;
    flex-grow: 1;
    padding-left: 20px;
}

/* ======= Tabla mejorada ======= */
.table-container {
    background: rgba(26, 26, 26, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 35px;
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 102, 0, 0.2);
    overflow: hidden;
}

.table-container h2 {
    color: var(--primario);
    margin: 0 0 25px 0;
    font-size: 1.5rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(255, 102, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-container h2::before {
    content: '🛒';
    font-size: 1.8rem;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    color: var(--texto);
    background: rgba(38, 38, 38, 0.6);
    border-radius: 10px;
    overflow: hidden;
}

thead th {
    background: linear-gradient(135deg, #333333, #262626);
    color: var(--primario);
    padding: 18px 15px;
    text-align: left;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 800;
    border-bottom: 3px solid rgba(255, 102, 0, 0.4);
    position: sticky;
    top: 0;
}

tbody td {
    padding: 16px 15px;
    border-bottom: 1px solid rgba(51, 51, 51, 0.6);
    transition: all 0.2s ease;
    vertical-align: middle;
}

tbody tr {
    background: rgba(21, 21, 21, 0.7);
    transition: all 0.3s ease;
}

tbody tr:nth-child(even) {
    background: rgba(26, 26, 26, 0.8);
}

tbody tr:hover {
    background: rgba(38, 38, 38, 0.95);
    transform: translateX(5px);
    box-shadow: 
        -5px 0 0 rgba(255, 102, 0, 0.3),
        0 4px 15px rgba(0, 0, 0, 0.2);
}

tbody tr:hover td {
    color: #FFFFFF;
}

/* Fila total */
.total-row td {
    background: rgba(38, 38, 38, 0.9);
    font-weight: 800;
    border-top: 3px solid var(--primario);
    font-size: 1.1rem;
    color: #FFFFFF;
}

.total-row .total-label {
    color: var(--primario);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.total-row .total-value {
    color: var(--primario);
    font-size: 1.4rem;
}

/* ======= Badges de estado mejorados ======= */
.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 800;
    font-size: 0.85rem;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
}

.status-badge:hover::before {
    left: 100%;
}

.status-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.status-pagado, .status-completo { 
    background: linear-gradient(135deg, var(--exito), #2ecc71);
    color: #FFFFFF;
    box-shadow: 0 4px 10px rgba(39, 174, 96, 0.3);
}

.status-pendiente, .status-pendiente_pago { 
    background: linear-gradient(135deg, var(--error), #c0392b);
    color: #FFFFFF;
    box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
}

.status-enviado, .status-en_curso { 
    background: linear-gradient(135deg, var(--alerta), #e67e22);
    color: #222222;
    box-shadow: 0 4px 10px rgba(243, 156, 18, 0.3);
}

.status-cancelado { 
    background: linear-gradient(135deg, #95a5a6, #7f8c8d);
    color: #FFFFFF;
    box-shadow: 0 4px 10px rgba(149, 165, 166, 0.3);
}

.status-procesando { 
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: #FFFFFF;
    box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
}

/* ======= Botones mejorados ======= */
.btn-container {
    display: flex;
    gap: 15px;
    margin: 30px 0;
    flex-wrap: wrap;
}

.btn {
    background: linear-gradient(135deg, var(--primario), #E65C00);
    color: #1A1A1A;
    border: none;
    padding: 14px 28px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 800;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-width: 120px;
}

.btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn:hover::before {
    width: 300px;
    height: 300px;
}

.btn:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 
        0 8px 25px rgba(255, 102, 0, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
}

.btn i {
    font-size: 1.2rem;
}

.btn-secondary {
    background: linear-gradient(135deg, #666666, #444444);
    color: #FFFFFF;
}

.btn-secondary:hover {
    box-shadow: 
        0 8px 25px rgba(102, 102, 102, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

/* Link de regreso */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: var(--primario);
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    margin-bottom: 20px;
    padding: 10px 20px;
    background: rgba(255, 102, 0, 0.1);
    border-radius: 8px;
    border: 1px solid rgba(255, 102, 0, 0.2);
}

.back-link:hover {
    color: #FFFFFF;
    background: rgba(255, 102, 0, 0.2);
    transform: translateX(-5px);
    box-shadow: 0 4px 15px rgba(255, 102, 0, 0.2);
}

.back-link::before {
    content: '←';
    font-size: 1.2rem;
}

/* ======= Modal mejorado ======= */
/* (Se asume que estas clases aplican si se carga en un modal, si es página completa no tendrán efecto) */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
    backdrop-filter: blur(5px);
}

.modal-content {
    width: 100%;
    max-width: 1000px;
    background: rgba(26, 26, 26, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: 
        0 25px 60px rgba(0, 0, 0, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    position: relative;
    overflow: hidden;
    border: 2px solid rgba(255, 102, 0, 0.4);
}

.modal-close {
    position: absolute;
    right: 20px;
    top: 20px;
    background: rgba(255, 102, 0, 0.2);
    border: 2px solid rgba(255, 102, 0, 0.4);
    color: var(--primario);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10;
}

.modal-close:hover {
    background: var(--primario);
    color: #1A1A1A;
    transform: rotate(90deg);
}

/* Factura dentro del modal */
.factura-modal {
    padding: 30px;
    font-size: 0.95rem;
}

.factura-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 3px solid rgba(255, 102, 0, 0.3);
    padding-bottom: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.empresa-block h2 {
    margin: 0 0 10px 0;
    color: var(--primario);
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: 1px;
}

.datos-factura {
    text-align: right;
    background: rgba(38, 38, 38, 0.6);
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid var(--primario);
}

.datos-factura h3 {
    color: var(--primario);
    margin: 0 0 15px 0;
    font-size: 1.3rem;
    font-weight: 700;
}
.tabla-detalle {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 20px;
    background: rgba(38, 38, 38, 0.6);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.tabla-detalle th, .tabla-detalle td {
    border: 1px solid rgba(51, 51, 51, 0.6);
    padding: 15px;
    text-align: left;
    transition: all 0.2s ease;
}

.tabla-detalle thead th {
    background: linear-gradient(135deg, #333333, #262626);
    color: var(--primario);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    border-bottom: 3px solid rgba(255, 102, 0, 0.4);
    font-size: 0.9rem;
}

.tabla-detalle tbody tr {
    background: rgba(26, 26, 26, 0.8);
}

.tabla-detalle tbody tr:nth-child(even) {
    background: rgba(21, 21, 21, 0.8);
}

.tabla-detalle tbody tr:hover {
    background: rgba(38, 38, 38, 0.9);
    transform: translateX(3px);
}

.historial-block {
    margin-top: 30px;
    padding: 25px;
    background: rgba(38, 38, 38, 0.6);
    border-radius: 12px;
    border-left: 4px solid var(--primario);
}

.historial-block h3 {
    color: var(--primario);
    margin: 0 0 20px 0;
    font-size: 1.3rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.historial-block h3::before {
    content: '🕒';
    font-size: 1.5rem;
}

.hist-item {
    padding: 12px 0;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.hist-item:last-child {
    border-bottom: none;
}

.hist-fecha {
    color: #BBBBBB;
    font-size: 0.9rem;
    min-width: 150px;
}

.hist-accion {
    color: #FFFFFF;
    font-weight: 600;
    flex-grow: 1;
}

.hist-usuario {
    color: var(--primario);
    font-weight: 700;
    background: rgba(255, 102, 0, 0.1);
    padding: 4px 12px;
    border-radius: 20px;
}

/* ======= Responsive ======= */
@media (max-width: 992px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .factura-header {
        flex-direction: column;
        text-align: left;
        gap: 20px;
    }
    
    .datos-factura {
        text-align: left;
    }
    
    .btn-container {
        justify-content: center;
    }
    
    .btn {
        padding: 12px 24px;
        font-size: 0.95rem;
        min-width: 100px;
    }
}

@media (max-width: 768px) {
    body {
        padding: 20px;
    }
    
    .header h1 {
        font-size: 2rem;
    }
    
    .info-grid {
        gap: 20px;
    }
    
    .info-card {
        padding: 20px;
    }
    
    .table-container {
        padding: 20px;
    }
    
    table {
        display: block;
        overflow-x: auto;
    }
    
    .hist-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}

@media (max-width: 480px) {
    body {
        padding: 15px;
    }
    
    .header h1 {
        font-size: 1.6rem;
    }
    
    .subtitle {
        font-size: 1rem;
    }
    
    .info-data {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .info-label {
        min-width: auto;
        margin-bottom: 5px;
    }
    
    .info-value {
        text-align: left;
        padding-left: 0;
        width: 100%;
    }
    
    .btn-container {
        flex-direction: column;
        width: 100%;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Animaciones */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.container > * {
    animation: slideIn 0.5s ease-out;
}

.info-grid {
    animation-delay: 0.1s;
}

.table-container {
    animation-delay: 0.2s;
}

.btn-container {
    animation-delay: 0.3s;
}

.modal-content {
    animation: modalFadeIn 0.4s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Scrollbar personalizado */
/* Eliminado: Si se aplica al body o al container principal, puede causar la doble barra */

/* Efectos de impresión */
@media print {
    body {
        background: #FFFFFF !important;
        color: #000000 !important;
        padding: 20px;
    }
    
    .modal-overlay {
        position: relative;
        background: none;
    }
    
    .modal-content {
        box-shadow: none;
        border: 1px solid #000000;
    }
    
    .modal-close {
        display: none;
    }
    
    .btn-container,
    .back-link {
        display: none;
    }
}
    </style>
</head>
<body>

<div class="container">
    
    <a href="historial_ordenes.php" class="back-link">Volver al Historial</a>

    <div class="header">
        <h1>Detalle de Orden N° <?php echo $orden["id_orden"]; ?></h1>
        <p class="subtitle">Información detallada de la transacción y los productos.</p>
    </div>

    <div class="info-grid">
        
        <div class="info-card">
            <div class="info-title">Información Principal</div>
            <div class="info-data">
                <span class="info-label">Fecha</span>
                <span class="info-value"><?php echo $orden["fecha"]; ?></span>
            </div>
            <div class="info-data">
                <span class="info-label">Método de Pago</span>
                <span class="info-value"><?php echo $orden["metodo_pago"]; ?></span>
            </div>
            <div class="info-data">
                <span class="info-label">Estado</span>
                <span class="info-value">
                    <span class="status-badge <?php echo get_status_class($orden["estado"]); ?>">
                        <?php echo $orden["estado"]; ?>
                    </span>
                </span>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-title">Información del Cliente</div>
            <div class="info-data">
                <span class="info-label">Nombre</span>
                <span class="info-value"><?php echo $orden["cliente"]; ?></span>
            </div>
            <div class="info-data">
                <span class="info-label">Teléfono</span>
                <span class="info-value"><?php echo $orden["telefono"]; ?></span>
            </div>
            <div class="info-data">
                <span class="info-label">Email</span>
                <span class="info-value"><?php echo $orden["email"]; ?></span>
            </div>
        </div>

    </div>

    <div class="table-container">
        <h2>Detalle de Productos Pedidos</h2>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $detalle->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila["producto"]); ?></td>
                    <td><?php echo htmlspecialchars($fila["descripcion"]); ?></td>
                    <td>$<?php echo number_format($fila["precio"], 2); ?></td>
                    <td><?php echo $fila["cantidad"]; ?></td>
                    <td>$<?php echo number_format($fila["subtotal"], 2); ?></td>
                </tr>
                <?php } ?>
                
                <tr class="total-row">
                    <td colspan="4" class="total-label" style="text-align: right; letter-spacing: 2px;">TOTAL A PAGAR</td>
                    <td class="total-value">$<?php echo number_format($orden["total"], 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="btn-container">
        <button class="btn" onclick="window.print()">🖨️ Imprimir Factura</button>
        <button class="btn btn-secondary">🔄 Actualizar Estado</button>
    </div>

</div>

</body>
</html>

<?php
$conexion->close();
?>