<?php
// productos.php (Página principal de presentación, lectura, y modal de edición)
include '../login/conexion.php'; 

// Lógica para mostrar mensajes después de una redirección (CRUD completado)
$mensaje_alerta = '';
if (isset($_GET['msg']) && isset($_GET['status'])) {
    $icon = ($_GET['status'] === 'success') ? '✅ ' : '❌ ';
    $mensaje_alerta = $icon . htmlspecialchars(urldecode($_GET['msg']));
}

// --- OBTENER DATOS PARA LA TABLA ---
try {
    // Obtener todos los ítems de productos
    $result = $conn->query("SELECT id_producto, nombre, precio, id_categoria, stock, descripcion FROM productos");
    $productos_items = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    // Manejo de error si la tabla no existe o la conexión falló
    $productos_items = [];
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
    <title>🛒 Gestión de Productos</title>
    <link rel="stylesheet" href="productos.css"> 
</head>
<body class="iframe-body">

<main class="contenedor">
    
    <?php if ($mensaje_alerta): ?>
        <script>
            alert('<?php echo $mensaje_alerta; ?>');
            // Limpia la URL para que el mensaje no se muestre de nuevo al recargar
            window.history.replaceState({}, document.title, "productos.php");
        </script>
    <?php endif; ?>

    <section class="formulario">
        <h2>➕ Agregar Nuevo Producto</h2>
        
        <form id="form-productos" method="POST" action="insert.php">
            
            <input type="hidden" name="accion" value="agregar">

            <label for="nombre">Nombre del Producto:</label>
            <input type="text" name="nombre" id="nombre" placeholder="Ej: Hamburguesa Clásica" required>
            
            <label for="precio">Precio:</label>
            <input type="number" name="precio" id="precio" placeholder="Ej: 4.50" step="0.01" required>
            
            <label for="id_categoria">ID Categoría:</label>
            <input type="number" name="id_categoria" id="id_categoria" placeholder="Ej: 2" required>
            
            <label for="stock">Stock:</label>
            <input type="number" name="stock" id="stock" placeholder="Ej: 25" required>
            
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" id="descripcion" placeholder="Descripción breve del producto..."></textarea>

            <div class="botones">
                <button type="submit" class="btn-principal btn-agregar">➕ Agregar Producto</button>
                <button type="reset" class="btn-secundario btn-limpiar">🧹 Limpiar</button>
            </div>
        </form>
    </section>

    <section class="tabla">
        <h2>📋 Listado de Productos (Total: <?= count($productos_items); ?>)</h2>
        <div class="tabla-scroll">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cat. ID</th>
                        <th>Stock</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos_items)): ?>
                        <?php foreach ($productos_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id_producto']) ?></td>
                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                            <td><?= "$" . number_format($item['precio'], 2) ?></td>
                            <td><?= htmlspecialchars($item['id_categoria']) ?></td>
                            <td><?= htmlspecialchars($item['stock']) ?></td>
                            <td><?= htmlspecialchars($item['descripcion']) ?></td>
                            <td class="acciones">
                                <button class="btn-accion btn-editar" 
                                        style="background-color: #00AFFF; color: #1A1A1A; border: none;" 
                                        type="button" 
                                        data-id="<?= htmlspecialchars($item['id_producto']) ?>"
                                        data-nombre="<?= htmlspecialchars($item['nombre']) ?>"
                                        data-precio="<?= htmlspecialchars($item['precio']) ?>"
                                        data-id-categoria="<?= htmlspecialchars($item['id_categoria']) ?>"
                                        data-stock="<?= htmlspecialchars($item['stock']) ?>"
                                        data-descripcion="<?= htmlspecialchars($item['descripcion']) ?>">
                                    ✏️ Editar
                                </button> 
                                <a href="eliminar.php?id_producto=<?php echo htmlspecialchars($item['id_producto']); ?>" 
                                   class="btn-accion btn-eliminar" 
                                   onclick="return confirm('¿Estás seguro de eliminar el producto ID <?php echo htmlspecialchars($item['id_producto']); ?>?');">
                                   🗑️ Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #888;">No hay productos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div id="modal-actualizar" class="modal-productos">
    <div class="modal-content-productos">
        <span class="close-productos">&times;</span>
        <h2>✏️ Actualizar Producto: <span id="modal-producto-nombre"></span></h2>
        
        <form id="form-modal-actualizar" method="POST" action="actualizar.php">
            
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_producto" id="modal-id-producto">
            
            <label for="modal-nombre">Nombre del Producto:</label>
            <input type="text" name="nombre" id="modal-nombre" required>
            
            <label for="modal-precio">Precio:</label>
            <input type="number" name="precio" id="modal-precio" step="0.01" required>
            
            <label for="modal-id_categoria">ID Categoría:</label>
            <input type="number" name="id_categoria" id="modal-id_categoria" required>
            
            <label for="modal-stock">Stock:</label>
            <input type="number" name="stock" id="modal-stock" required>
            
            <label for="modal-descripcion">Descripción:</label>
            <textarea name="descripcion" id="modal-descripcion"></textarea>

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
        const spanClose = document.getElementsByClassName("close-productos")[0];
        const btnCerrar = document.getElementsByClassName("btn-cerrar-modal")[0];

        // 1. Manejar la apertura del modal al hacer clic en Editar
        document.querySelectorAll('.btn-editar').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); 

                // Obtiene los datos de los atributos data-* del botón
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const precio = this.getAttribute('data-precio');
                const idCategoria = this.getAttribute('data-id-categoria');
                const stock = this.getAttribute('data-stock');
                const descripcion = this.getAttribute('data-descripcion');

                // Llena el modal con los datos
                document.getElementById('modal-id-producto').value = id;
                document.getElementById('modal-nombre').value = nombre;
                document.getElementById('modal-precio').value = precio;
                document.getElementById('modal-id_categoria').value = idCategoria;
                document.getElementById('modal-stock').value = stock;
                document.getElementById('modal-descripcion').value = descripcion;
                document.getElementById('modal-producto-nombre').textContent = nombre;
                
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