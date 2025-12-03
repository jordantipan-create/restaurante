<?php
// detalles.php - Contenido que se carga en el modal de historial_ordenes.php
// NOTA: Se asume que 'conexion.php' y la función get_status_class existen en el entorno de ejecución
// del script principal o que la función get_status_class se define al final como se muestra.

$conexion = new mysqli("localhost", "root", "", "pos_res");
if ($conexion->connect_error) {
    // Nota: En un entorno real, manejar errores de conexión con más gracia.
    die("Error al conectar a la base de datos.");
}

if (!isset($_GET['id'])) {
    die("No se proporcionó un ID.");
}

$id_orden = (int)$_GET['id']; // Usamos (int) para seguridad

// Consulta de la orden
$sql = "
    SELECT 
        o.id_orden,
        o.fecha,
        o.total,
        o.estado,
        o.metodo_pago,
        c.nombre as nombre_cliente,
        c.telefono,
        c.email
    FROM ordenes o
    INNER JOIN clientes c ON o.id_cliente = c.id_cliente
    WHERE o.id_orden = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_orden);
$stmt->execute();
$result = $stmt->get_result();
$orden = $result->fetch_assoc();
$stmt->close();

if (!$orden) {
    die("Orden no encontrada.");
}

// Consulta de detalles (productos)
$sql_detalle = "
    SELECT 
        d.cantidad,
        d.subtotal,
        p.nombre as producto,
        p.precio
        -- Si tu tabla 'productos' tiene 'descripcion', puedes agregarla aquí
    FROM detalle_orden d
    INNER JOIN productos p ON d.id_producto = p.id_producto
    WHERE d.id_orden = ?
";

$stmt_detalle = $conexion->prepare($sql_detalle);
$stmt_detalle->bind_param("i", $id_orden);
$stmt_detalle->execute();
$detalles = $stmt_detalle->get_result();
$stmt_detalle->close();

?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Detalles de Orden #<?= $orden['id_orden'] ?></title>
    <style>
        /* Variables Globales (Para consistencia) */
        :root {
            --primario: #FF8C00; /* Naranja Oscuro */
            --secundario: #000000; /* Negro */
            --fondo-modal: #1c1c1c; /* Negro muy oscuro para el fondo del modal */
            --texto-claro: #ffffff;
            --texto-acento: #FFD700; /* Oro/Naranja claro para acentos */
            --padding-base: 15px; /* Define un padding base */
            --margin-seccion: 30px; /* Margen entre secciones principales */
        }
        
        /* Estilos específicos para el contenido del Modal (Factura) 🧾 */
        
        .factura-modal {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px; /* Aumentamos el padding interno del contenedor */
            margin: 0;
            background-color: var(--fondo-modal);
            color: var(--texto-claro);
            border-radius: 8px;
        }

        /* Encabezado: Número de Orden y Datos */
        .factura-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px; /* Más espacio bajo el encabezado */
            margin-bottom: var(--margin-seccion);
            border-bottom: 2px solid var(--primario);
        }
        
        .factura-header h2 {
            color: var(--primario);
            margin: 0;
            font-size: 2em; /* Título más grande */
        }

        .datos-factura p, .info-grid p {
            margin: 8px 0; /* Más espacio entre líneas de datos */
            font-size: 1em;
        }
        
        /* Títulos de Sección */
        h3 {
            color: var(--texto-acento);
            border-left: 5px solid var(--primario); /* Borde más grueso */
            padding-left: 15px; /* Más espacio a la izquierda del título */
            margin-top: var(--margin-seccion); /* Margen superior para separar de la sección anterior */
            margin-bottom: 15px;
            font-weight: 600;
        }

        /* Grid de Información (Cliente) */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px; /* Más espacio entre columnas */
            padding: 10px 0;
        }

        .info-grid strong {
            color: var(--texto-acento);
        }

        /* Tabla de Detalles (Productos) 🍽️ */
        .tabla-detalle {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(255, 140, 0, 0.2);
        }

        .tabla-detalle thead {
            background-color: var(--primario);
            color: var(--secundario);
            font-weight: bold;
        }

        .tabla-detalle th, .tabla-detalle td {
            padding: 15px; /* Aumentamos el padding de las celdas */
            text-align: left;
            border: none;
            /* Usamos line-height para asegurar espacio dentro de la celda si el contenido es largo */
            line-height: 1.4; 
        }

        .tabla-detalle tbody tr {
            background-color: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            transition: background-color 0.3s ease;
        }

        .tabla-detalle tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Total */
        .totales {
            margin-top: var(--margin-seccion); /* Más espacio antes del total */
            padding-top: 20px; /* Más padding en el borde superior */
            border-top: 2px solid var(--primario);
            text-align: right;
        }

        .totales strong {
            color: var(--primario);
        }
        .totales p {
             font-size: 1.8em; /* Hacemos el total más prominente */
             margin: 0;
        }
        
        /* Badge de Estado (Reutilizado de historial_ordenes.php) */
        .status-badge {
            display: inline-block;
            padding: 5px 10px; /* Ajustamos padding del badge */
            border-radius: 15px;
            font-weight: bold;
            font-size: 0.9em;
            text-transform: uppercase;
            color: var(--secundario); 
        }

        /* Clases de estado (Colores Negro y Naranja) */
        .status-pagado, .status-completo {
            background-color: #FFD700; 
            color: var(--secundario);
        }
        .status-pendiente, .status-pendiente_pago {
            background-color: #FFA07A; 
            color: var(--secundario);
        }
        .status-enviado, .status-en_curso {
            background-color: #36454F; 
            color: var(--texto-claro); 
        }
        .status-cancelado {
            background-color: #D22B2B;
            color: var(--texto-claro);
        }
        .status-procesando {
            background-color: #FFC0CB; 
            color: var(--secundario);
        }
        
        p, td {
            color: var(--texto-claro);
        }

    </style>
</head>
<body>

<div class='factura-modal'>
    <div class='factura-header'>
        <div class='empresa-block'>
            <h2>Orden N° <?= htmlspecialchars($orden['id_orden']) ?></h2>
            <p><strong>Cliente:</strong> <?= htmlspecialchars($orden['nombre_cliente']) ?></p>
        </div>
        <div class='datos-factura'>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($orden['fecha']) ?></p>
            <p><strong>Método Pago:</strong> <?= htmlspecialchars($orden['metodo_pago'] ?? 'N/A') ?></p>
            <p><strong>Estado:</strong> <span class="status-badge <?php echo get_status_class($orden['estado']); ?>"><?= htmlspecialchars($orden['estado']) ?></span></p>
        </div>
    </div>
    
    <h3>Detalles del Cliente</h3>
    <div class="info-grid">
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($orden['telefono']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($orden['email']) ?></p>
    </div>

    <h3>Productos</h3>
    <table class='tabla-detalle'>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio Unitario</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($d = $detalles->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($d['producto'] ?? 'Producto Desconocido') ?></td>
                <td>$<?= number_format($d['precio'], 2) ?></td>
                <td><?= htmlspecialchars($d['cantidad']) ?></td>
                <td>$<?= number_format($d['subtotal'], 2) ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <div class='totales'>
        <p><strong>TOTAL:</strong> $<?= number_format($orden['total'], 2) ?></p>
    </div>
</div>

</body>
</html>
<?php
function get_status_class($estado) {
    $estado = strtolower($estado);
    if ($estado === 'pagado' || $estado === 'completo') return 'status-pagado';
    if ($estado === 'pendiente' || $estado === 'pendiente_pago') return 'status-pendiente';
    if ($estado === 'enviado' || $estado === 'en_curso') return 'status-enviado';
    if ($estado === 'cancelado') return 'status-cancelado';
    if ($estado === 'procesando') return 'status-procesando';
    return '';
}

$conexion->close();
?>