<?php
// ordenes.php - Versión Corregida
include '../login/conexion.php';

// Lógica para mensajes
$mensaje_alerta = '';
if (isset($_GET['msg']) && isset($_GET['status'])) {
    $icon = ($_GET['status'] === 'success') ? '✅ ' : '❌ ';
    $mensaje_alerta = $icon . htmlspecialchars(urldecode($_GET['msg']));
}

// Obtener datos para la tabla
try {
    $result = $conn->query("SELECT id_orden, fecha, id_cliente, id_usuario, total, estado FROM ordenes ORDER BY id_orden DESC");
    $ordenes_items = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $ordenes_items = [];
    $mensaje_alerta = '❌ Error al cargar datos: ' . $e->getMessage();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📝 Gestión de Órdenes</title>
    <link rel="stylesheet" href="ordenes.css">
</head>
<body class="iframe-body">

<main class="contenedor">
    
    <?php if ($mensaje_alerta): ?>
        <script>
            alert('<?php echo $mensaje_alerta; ?>');
            window.history.replaceState({}, document.title, "ordenes.php");
        </script>
    <?php endif; ?>

    <section class="formulario">
        <h2>➕ Registrar Nueva Orden</h2>
        
        <form id="form-ordenes" method="POST" action="insert.php">
            <input type="hidden" name="accion" value="agregar">

            <label for="fecha">Fecha y Hora:</label>
            <input type="datetime-local" name="fecha" id="fecha" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
            
            <label for="id_cliente">ID Cliente:</label>
            <input type="number" name="id_cliente" id="id_cliente" placeholder="Ej: 1" required>
            
            <label for="id_usuario">ID Usuario (Mesero/Cajero):</label>
            <input type="number" name="id_usuario" id="id_usuario" placeholder="Ej: 2" required>
            
            <label for="total">Total:</label>
            <input type="number" name="total" id="total" placeholder="Ej: 15.50" step="0.01" required>
            
            <label for="estado">Estado:</label>
            <select name="estado" id="estado" required>
                <option value="pendiente">Pendiente</option>
                <option value="pagado" selected>Pagado</option>
                <option value="cancelado">Cancelado</option>
            </select>

            <div class="botones">
                <button type="submit" class="btn-principal btn-agregar">➕ Registrar Orden</button>
                <button type="reset" class="btn-secundario btn-limpiar">🧹 Limpiar</button>
            </div>
        </form>
    </section>

    <section class="tabla">
        <h2>📋 Listado de Órdenes (Total: <?= count($ordenes_items); ?>)</h2>
        <div class="tabla-scroll">
            <table>
                <thead>
                    <tr>
                        <th>ID Orden</th>
                        <th>Fecha</th>
                        <th>ID Cliente</th>
                        <th>ID Usuario</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ordenes_items)): ?>
                        <?php foreach ($ordenes_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id_orden']) ?></td>
                            <td><?= htmlspecialchars($item['fecha']) ?></td>
                            <td><?= htmlspecialchars($item['id_cliente']) ?></td>
                            <td><?= htmlspecialchars($item['id_usuario']) ?></td>
                            <td><?= "$" . number_format($item['total'], 2) ?></td>
                            <td><?= htmlspecialchars($item['estado']) ?></td>
                            <td class="acciones">
                                <button class="btn-accion btn-editar" 
                                        style="background-color: #00AFFF; color: #1A1A1A; border: none;" 
                                        type="button" 
                                        data-id="<?= htmlspecialchars($item['id_orden']) ?>"
                                        data-fecha="<?= htmlspecialchars(str_replace(' ', 'T', $item['fecha'])) ?>"
                                        data-id-cliente="<?= htmlspecialchars($item['id_cliente']) ?>"
                                        data-id-usuario="<?= htmlspecialchars($item['id_usuario']) ?>"
                                        data-total="<?= htmlspecialchars($item['total']) ?>"
                                        data-estado="<?= htmlspecialchars($item['estado']) ?>">
                                    ✏️ Editar
                                </button> 
                                <a href="eliminar.php?id_orden=<?php echo htmlspecialchars($item['id_orden']); ?>" 
                                   class="btn-accion btn-eliminar" 
                                   onclick="return confirm('¿Estás seguro de eliminar la Orden ID <?php echo htmlspecialchars($item['id_orden']); ?>?');">
                                   🗑️ Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #888;">No hay órdenes registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div id="modal-actualizar" class="modal-ordenes">
    <div class="modal-content-ordenes">
        <span class="close-ordenes">&times;</span>
        <h2>✏️ Actualizar Orden ID: <span id="modal-id-display"></span></h2>
        
        <form id="form-modal-actualizar" method="POST" action="actualizar.php">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_orden" id="modal-id-orden">
            
            <label for="modal-fecha">Fecha y Hora:</label>
            <input type="datetime-local" name="fecha" id="modal-fecha" required>
            
            <label for="modal-id_cliente">ID Cliente:</label>
            <input type="number" name="id_cliente" id="modal-id_cliente" required>
            
            <label for="modal-id_usuario">ID Usuario:</label>
            <input type="number" name="id_usuario" id="modal-id_usuario" required>
            
            <label for="modal-total">Total:</label>
            <input type="number" name="total" id="modal-total" step="0.01" required>

            <label for="modal-estado">Estado:</label>
            <select name="estado" id="modal-estado" required>
                <option value="pendiente">Pendiente</option>
                <option value="pagado">Pagado</option>
                <option value="cancelado">Cancelado</option>
            </select>

            <div class="botones">
                <button type="submit" class="btn-principal btn-agregar">💾 Guardar Cambios</button>
                <button type="button" class="btn-secundario btn-limpiar btn-cerrar-modal">❌ Cerrar</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById("modal-actualizar");
        const spanClose = document.getElementsByClassName("close-ordenes")[0];
        const btnCerrar = document.getElementsByClassName("btn-cerrar-modal")[0];

        // Función para llenar y mostrar el modal
        document.querySelectorAll('.btn-editar').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); 

                // Obtiene los datos de los atributos data-* del botón
                const id = this.getAttribute('data-id');
                const fecha = this.getAttribute('data-fecha');
                const idCliente = this.getAttribute('data-id-cliente');
                const idUsuario = this.getAttribute('data-id-usuario');
                const total = this.getAttribute('data-total');
                const estado = this.getAttribute('data-estado');

                // Llena el modal con los datos
                document.getElementById('modal-id-orden').value = id;
                document.getElementById('modal-id-display').textContent = id;
                document.getElementById('modal-fecha').value = fecha;
                document.getElementById('modal-id_cliente').value = idCliente;
                document.getElementById('modal-id_usuario').value = idUsuario;
                document.getElementById('modal-total').value = total;
                document.getElementById('modal-estado').value = estado;
                
                // Muestra el modal
                modal.style.display = "block";
            });
        });

        // Manejar el cierre del modal
        spanClose.onclick = function() {
            modal.style.display = "none";
        }
        
        btnCerrar.onclick = function() {
            modal.style.display = "none";
        }

        // Cierra si el usuario hace clic fuera del modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    });
</script>

</body>
</html>