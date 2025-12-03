<?php
// historial_ordenes.php
require_once 'conexion.php';

// 1. Función para la clase CSS del estado (DEJADA AQUÍ UNA SOLA VEZ)
function get_status_class($estado) {
    $estado = strtolower($estado);
    if ($estado === 'pagado' || $estado === 'completo') return 'status-pagado'; // Verde/Naranja fuerte
    if ($estado === 'pendiente' || $estado === 'pendiente_pago') return 'status-pendiente'; // Amarillo/Naranja claro
    if ($estado === 'enviado' || $estado === 'en_curso') return 'status-enviado'; // Azul/Negro
    if ($estado === 'cancelado') return 'status-cancelado'; // Rojo
    if ($estado === 'procesando') return 'status-procesando'; // Gris/Naranja
    return '';
}

// 2. Lógica de Búsqueda
$search_id = null;
$where_clause = "";
$bind_types = "";
$bind_params = [];

if (isset($_GET['search_id']) && !empty($_GET['search_id'])) {
    $search_id = trim($_GET['search_id']);
    
    // Solo permitimos IDs numéricos, por seguridad
    if (is_numeric($search_id)) {
        $where_clause = " WHERE o.id_orden = ? ";
        $bind_types = "i";
        $bind_params[] = (int)$search_id;
    }
}

// 3. Consulta SQL dinámica
$sql = "SELECT o.id_orden, o.fecha, o.total, o.estado, c.nombre as nombre_cliente, c.id_cliente
        FROM ordenes o
        JOIN clientes c ON o.id_cliente = c.id_cliente
        " . $where_clause . "
        ORDER BY o.fecha DESC";

// Ejecución de la consulta
if (isset($conn)) {
    if (!empty($where_clause)) {
        // Si hay búsqueda, usamos prepared statements
        $stmt = $conn->prepare($sql);
        // Verificar si la preparación fue exitosa antes de usar bind_param
        if ($stmt) {
            $stmt->bind_param($bind_types, ...$bind_params);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else {
            // Manejar error de preparación si es necesario
            $result = false;
        }
    } else {
        // Si no hay búsqueda, usamos query simple
        $result = $conn->query($sql);
    }
} else {
    // Manejar error si la conexión no está definida
    $result = false;
}
// NOTA: Se asume que 'conexion.php' establece $conn correctamente.

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Órdenes</title>
    <link rel="stylesheet" href="detalle_orden.css"> 
    <style>
        /* Variables y Estilos Base 🎨 */
        :root {
            --primario: #FF8C00; /* Naranja Oscuro */
            --secundario: #000000; /* Negro */
            --fondo-claro: #f4f4f9;
            --fondo-oscuro: rgba(0, 0, 0, 0.85); /* Negro semi-transparente para contenido */
            --texto-claro: #ffffff;
            --texto-oscuro: #333333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            /* Fondo con la imagen solicitada */
            background: url('https://images.unsplash.com/photo-1529042410759-befb1204b468?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat fixed;
            color: var(--texto-claro); /* Texto principal claro */
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 50px auto;
            padding: 30px;
            background-color: var(--fondo-oscuro); /* Contenedor principal semi-transparente negro */
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }

        h1 {
            text-align: center;
            color: var(--primario);
            margin-bottom: 30px;
            border-bottom: 3px solid var(--primario);
            padding-bottom: 15px;
        }

        /* Estilo para el formulario de búsqueda 🔍 */
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1); /* Blanco muy tenue para contraste */
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .search-form input[type="text"] {
            padding: 10px;
            border: 1px solid var(--primario);
            border-radius: 4px;
            flex-grow: 1;
            background-color: var(--texto-claro);
            color: var(--texto-oscuro);
        }
        .search-form button {
            background: var(--primario);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 700;
            transition: background 0.3s ease;
        }
        .search-form button:hover {
            background: #FF4500; /* Naranja más fuerte en hover */
        }
        .search-form button[onclick] {
             background: #6c757d; /* Gris para el botón Limpiar */
        }
        .search-form button[onclick]:hover {
             background: #5a6268;
        }
        
        /* Estilos de la tabla 📊 */
        table {
            width: 100%;
            border-collapse: separate; /* Para border-radius */
            border-spacing: 0;
            margin-top: 20px;
            overflow: hidden; /* Necesario para los bordes redondeados en el contenedor */
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            border-radius: 8px;
        }
        thead th {
            background-color: var(--primario);
            color: var(--texto-claro);
            padding: 12px 15px;
            text-align: left;
            font-size: 1.05em;
        }
        tbody tr {
            background-color: rgba(255, 255, 255, 0.05); /* Filas transparentes claras */
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: background-color 0.3s ease;
        }
        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.15); /* Efecto hover más notable */
        }
        tbody td {
            padding: 12px 15px;
            color: var(--texto-claro);
            vertical-align: middle;
        }
        tbody tr:last-child {
            border-bottom: none;
        }

        /* Badge de Estado 🏷️ */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 0.9em;
            text-transform: uppercase;
            color: var(--secundario); /* Texto del badge negro */
        }

        /* Clases de estado (Colores Negro y Naranja) */
        .status-pagado, .status-completo {
            background-color: #FFD700; /* Oro */
            border: 1px solid #FF8C00;
        }
        .status-pendiente, .status-pendiente_pago {
            background-color: #FFA07A; /* Salmón claro */
            border: 1px solid #FF4500;
        }
        .status-enviado, .status-en_curso {
            background-color: #36454F; /* Gris carbón */
            color: var(--texto-claro); /* Texto blanco */
            border: 1px solid #1C1C1C;
        }
        .status-cancelado {
            background-color: #D22B2B; /* Rojo */
            color: var(--texto-claro);
            border: 1px solid #8B0000;
        }
        .status-procesando {
            background-color: #FFC0CB; /* Rosa claro */
            border: 1px solid #FF1493;
        }

        /* Botón de Detalles y Acciones */
        .acciones .btn-detalle {
            background-color: var(--primario);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .acciones .btn-detalle:hover {
            background-color: #FF4500;
        }
        .acciones a { margin-right: 8px; color: var(--primario); }

        /* Estilos del Modal (Añadidos/Mejorados) 팝업 */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.75); /* Oscurece el fondo */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: var(--fondo-oscuro); /* Fondo del modal oscuro */
            color: var(--texto-claro);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(255, 140, 0, 0.5); /* Sombra naranja */
            width: 90%;
            max-width: 600px;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primario);
            color: white;
            border: none;
            font-size: 1.2em;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            line-height: 1;
            transition: background-color 0.3s;
        }
        .modal-close:hover {
            background: #FF4500;
        }

        /* Estilo para el mensaje "No se encontraron órdenes" */
        table tr td[colspan="6"] {
            text-align: center;
            font-style: italic;
            padding: 20px;
            background-color: rgba(255, 140, 0, 0.1); /* Naranja muy tenue */
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Historial de Órdenes</h1>

    <form method="GET" class="search-form">
        <input type="text" name="search_id" placeholder="Buscar por ID de Orden..." value="<?php echo htmlspecialchars($search_id ?? ''); ?>">
        <button type="submit">🔍 Buscar</button>
        <?php if ($search_id): ?>
            <button type="button" onclick="window.location.href='historial_ordenes.php'">❌ Limpiar</button>
        <?php endif; ?>
    </form>
    
    <table>
        <thead>
            <tr>
                <th>ID Orden</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id_orden']); ?></td>
                <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre_cliente']); ?></td>
                <td>$<?php echo number_format($row['total'], 2); ?></td>
                <td>
                    <span class="status-badge <?php echo get_status_class($row['estado'] ?? ''); ?>">
                        <?php echo htmlspecialchars($row['estado']); ?>
                    </span>
                </td>
                <td class="acciones">
                    <button class="btn-detalle" data-id="<?php echo $row['id_orden']; ?>">📋 Detalles</button>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No se encontraron órdenes<?php echo $search_id ? ' con el ID ' . htmlspecialchars($search_id) : '.'; ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="modalOverlay" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <button id="modalClose" class="modal-close">✕</button>
        <div id="modalBody">
            </div>
    </div>
</div>

<script>
// Funciones de modal y carga AJAX
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('btn-detalle')) {
        const id = e.target.getAttribute('data-id');
        abrirModalConDetalles(id);
    }
});

document.getElementById('modalClose').addEventListener('click', cerrarModal);
document.getElementById('modalOverlay').addEventListener('click', function(e){
    if (e.target === this) cerrarModal();
});

function abrirModalConDetalles(id) {
    const overlay = document.getElementById('modalOverlay');
    const body = document.getElementById('modalBody');
    overlay.style.display = 'flex';
    body.innerHTML = '<p style="padding:20px;text-align:center;color:var(--primario);">Cargando detalles...</p>';

    // NOTA: Se usa 'id' en la URL, que es lo que espera detalles.php
    fetch('detalles.php?id=' + encodeURIComponent(id)) 
        .then(resp => {
            if (!resp.ok) throw new Error('Error en la petición');
            return resp.text();
        })
        .then(html => {
            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<p style="padding:20px;text-align:center;color:#D22B2B;">Error al cargar los detalles.</p>';
            console.error(err);
        });
}

function cerrarModal(){
    document.getElementById('modalOverlay').style.display = 'none';
    document.getElementById('modalBody').innerHTML = '';
}
</script>
</body>
</html>

<?php
if (isset($conn)) {
    $conn->close();
}
?>