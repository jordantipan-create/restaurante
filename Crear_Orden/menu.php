<?php
// Crear_Orden/menu.php
session_start();
// Ajusta la ruta si tu conexion.php está en otra carpeta
require_once __DIR__ . '/../login/conexion.php';

// obtener productos
$sql = "SELECT id_producto, nombre, precio, id_categoria, stock, descripcion FROM productos WHERE stock > 0 ORDER BY nombre";
$res = $conn->query($sql);

// obtener lista de clientes para permitir seleccionar (opcional)
$clientes = $conn->query("SELECT id_cliente, nombre FROM clientes ORDER BY nombre");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Crear Orden - Menú</title>
    <link rel="stylesheet" href="crear_orden.css">
</head>
<body>
<header class="header">
    <h1>Menú - Crear Orden</h1>
    <div class="acciones-header">
        <a href="../ordenes/ordenes.php" class="btn small">Historial</a>
        <a href="../productos/productos.php" class="btn small">Productos</a>
    </div>
</header>

<main class="main-grid">
    <section class="menu-section card">
        <h2 class="section-title">Productos</h2>

        <div class="productos-grid">
            <?php while($p = $res->fetch_assoc()): ?>
            <div class="producto-card">
                <div class="producto-nombre"><?php echo htmlspecialchars($p['nombre']); ?></div>
                <div class="producto-desc"><?php echo htmlspecialchars($p['descripcion']); ?></div>
                <div class="producto-meta">
                    <span class="precio">$<?php echo number_format($p['precio'],2); ?></span>
                    <span class="stock">stk: <?php echo (int)$p['stock']; ?></span>
                </div>
                <div class="producto-acciones">
                    <button 
                        class="btn agregar-btn" 
                        data-id="<?php echo $p['id_producto']; ?>" 
                        data-nombre="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES); ?>" 
                        data-precio="<?php echo $p['precio']; ?>"
                        data-stock="<?php echo (int)$p['stock']; ?>"
                    >+ Agregar</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <aside class="carrito-section card">
        <h2 class="section-title">Carrito</h2>

        <!-- selector cliente (opcional) -->
        <div class="form-row">
            <label for="selectCliente">Cliente</label>
            <select id="selectCliente">
                <option value="1">Cliente genérico</option>
                <?php while($c = $clientes->fetch_assoc()): ?>
                    <option value="<?php echo $c['id_cliente']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div id="carritoList" class="carrito-list">
            <!-- items agregados por JS -->
            <p class="empty">No hay productos en el carrito.</p>
        </div>

        <div class="carrito-totales">
            <div><strong>Subtotal:</strong> <span id="subtotal">$0.00</span></div>
            <div><strong>Total:</strong> <span id="total">$0.00</span></div>
        </div>

        <div style="margin-top:12px;">
            <button id="btnFinalizar" class="btn primary" disabled>Finalizar Orden</button>
            <button id="btnVaciar" class="btn outline" disabled>Vaciar</button>
        </div>
    </aside>
</main>

<script src="carrito.js"></script>
</body>
</html>
<?php $conn->close(); ?>
