<?php
// inventario.php (Página principal de presentación, lectura, y modal de edición)
include '../login/conexion.php'; 

// Lógica para mostrar mensajes después de una redirección (CRUD completado)
$mensaje_alerta = '';
if (isset($_GET['msg']) && isset($_GET['status'])) {
    $icon = ($_GET['status'] === 'success') ? '✅ ' : '❌ ';
    $mensaje_alerta = $icon . htmlspecialchars(urldecode($_GET['msg']));
}

// --- OBTENER DATOS PARA LA TABLA ---
try {
    // Obtener todos los ítems de inventario
    $result = $conn->query("SELECT * FROM inventario");
    $inventario_items = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    // Manejo de error si la tabla no existe o la conexión falló
    $inventario_items = [];
    $mensaje_alerta = '❌ Error al cargar datos: ' . $e->getMessage();
}

// Cierre de la conexión antes del HTML
$conn->close(); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📦 Gestión de Inventario</title>
    <link rel="stylesheet" href="inventario.css"> 
</head>
<body class="iframe-body">

<main class="contenedor">
    
    <?php if ($mensaje_alerta): ?>
        <script>
            alert('<?php echo $mensaje_alerta; ?>');
            // Limpia la URL para que el mensaje no se muestre de nuevo al recargar
            window.history.replaceState({}, document.title, "inventario.php");
        </script>
    <?php endif; ?>

    <section class="formulario">
        <h2>➕ Agregar Nuevo Ítem</h2>
        
        <form id="form-inventario" method="POST" action="insert.php">
            
            <input type="hidden" name="accion" value="agregar">

            <label for="nombre_item">Nombre del Ítem:</label>
            <input type="text" name="nombre_item" id="nombre_item" placeholder="Ej: Harina de trigo" required>
            
            <label for="cantidad_actual">Cantidad Actual:</label>
            <input type="number" name="cantidad_actual" id="cantidad_actual" placeholder="Ej: 50" required>
            
            <label for="unidad">Unidad de Medida:</label>
            <input type="text" name="unidad" id="unidad" placeholder="Ej: kg, L, unidades" required>
            
            <label for="fecha_actualizacion">Fecha de Actualización:</label>
            <input type="date" name="fecha_actualizacion" id="fecha_actualizacion" value="<?php echo date('Y-m-d'); ?>" required>

            <div class="botones">
                <button type="submit" class="btn-principal btn-agregar">➕ Agregar Ítem</button>
                <button type="reset" class="btn-secundario btn-limpiar">🧹 Limpiar</button>
            </div>
        </form>
    </section>

    <section class="tabla">
        <h2>📋 Inventario Actual (Total: <?= count($inventario_items); ?> ítems)</h2>
        <div class="tabla-scroll">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Ítem</th>
                        <th>Cantidad Actual</th>
                        <th>Unidad</th>
                        <th>Fecha Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inventario_items)): ?>
                        <?php foreach ($inventario_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id_inventario']) ?></td>
                            <td><?= htmlspecialchars($item['nombre_item']) ?></td>
                            <td><?= htmlspecialchars($item['cantidad_actual']) ?></td>
                            <td><?= htmlspecialchars($item['unidad']) ?></td>
                            <td><?= htmlspecialchars($item['fecha_actualizacion']) ?></td>
                            <td class="acciones">
                                <button class="btn-accion btn-editar" 
                                        style="background-color: #00AFFF; color: #1A1A1A; border: none;" 
                                        type="button" 
                                        data-id="<?= htmlspecialchars($item['id_inventario']) ?>"
                                        data-nombre="<?= htmlspecialchars($item['nombre_item']) ?>"
                                        data-cantidad="<?= htmlspecialchars($item['cantidad_actual']) ?>"
                                        data-unidad="<?= htmlspecialchars($item['unidad']) ?>"
                                        data-fecha="<?= htmlspecialchars($item['fecha_actualizacion']) ?>">
                                    ✏️ Editar
                                </button> 
                                <a href="eliminar.php?id_inventario=<?php echo htmlspecialchars($item['id_inventario']); ?>" 
                                   class="btn-accion btn-eliminar" 
                                   onclick="return confirm('¿Estás seguro de eliminar el ítem ID <?php echo htmlspecialchars($item['id_inventario']); ?>?');">
                                   🗑️ Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #888;">No hay ítems registrados en el inventario.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div id="modal-actualizar" class="modal-inventario">
    <div class="modal-content-inventario">
        <span class="close-inventario">&times;</span>
        <h2>✏️ Actualizar Ítem: <span id="modal-item-nombre"></span></h2>
        
        <form id="form-modal-actualizar" method="POST" action="actualizar.php">
            
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_inventario" id="modal-id-inventario">
            
            <label for="modal-nombre_item">Nombre del Ítem:</label>
            <input type="text" name="nombre_item" id="modal-nombre_item" required>
            
            <label for="modal-cantidad_actual">Cantidad Actual:</label>
            <input type="number" name="cantidad_actual" id="modal-cantidad_actual" required>
            
            <label for="modal-unidad">Unidad de Medida:</label>
            <input type="text" name="unidad" id="modal-unidad" required>
            
            <label for="modal-fecha_actualizacion">Fecha de Actualización:</label>
            <input type="date" name="fecha_actualizacion" id="modal-fecha_actualizacion" required>

            <div class="botones-modal">
                <button type="submit" class="btn-principal btn-guardar">💾 Guardar Cambios</button>
                <button type="button" class="btn-secundario btn-cerrar-modal">❌ Cerrar</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById("modal-actualizar");
        const spanClose = document.getElementsByClassName("close-inventario")[0];
        const btnCerrar = document.getElementsByClassName("btn-cerrar-modal")[0];

        // 1. Manejar la apertura del modal al hacer clic en Editar
        document.querySelectorAll('.btn-editar').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); 

                // Obtiene los datos de los atributos data-* del botón
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const cantidad = this.getAttribute('data-cantidad');
                const unidad = this.getAttribute('data-unidad');
                const fecha = this.getAttribute('data-fecha');

                // Llena el modal con los datos
                document.getElementById('modal-id-inventario').value = id;
                document.getElementById('modal-nombre_item').value = nombre;
                document.getElementById('modal-cantidad_actual').value = cantidad;
                document.getElementById('modal-unidad').value = unidad;
                document.getElementById('modal-fecha_actualizacion').value = fecha;
                document.getElementById('modal-item-nombre').textContent = nombre;
                
                // Muestra el modal
                modal.style.display = "block";
            });
        });

        // 2. Manejar el cierre del modal
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