<?php
// Crear_Orden/finalizar.php
session_start();
require_once __DIR__ . '/../login/conexion.php';

// recibir datos
if (!isset($_POST['cart']) || empty($_POST['cart'])) {
    die("Carrito vacío.");
}

$cart_json = $_POST['cart'];
$id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 1;

$cart = json_decode($cart_json, true);
if (!is_array($cart) || count($cart) === 0) {
    die("Carrito inválido.");
}

// calcular totales (confiamos en los subtotales enviados pero recalculamos por seguridad)
$subtotal = 0.0;
foreach ($cart as $it) {
    $subtotal += floatval($it['subtotal']);
}
$total = $subtotal; // si deseas impuestos/agregar cargos, hacerlo aquí

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Finalizar Orden</title>
<link rel="stylesheet" href="crear_orden.css">
</head>
<body>
<header class="header">
    <h1>Finalizar Orden</h1>
</header>

<main style="max-width:900px;margin:24px auto;">
    <div class="card">
        <h2 class="section-title">Resumen de la Orden</h2>

        <div>
            <p><strong>Cliente:</strong>
                <?php
                // mostrar nombre cliente
                $stmtC = $conn->prepare("SELECT nombre FROM clientes WHERE id_cliente = ?");
                $stmtC->bind_param("i", $id_cliente);
                $stmtC->execute();
                $resC = $stmtC->get_result();
                $clienteNombre = ($resC->num_rows>0) ? $resC->fetch_assoc()['nombre'] : 'Cliente genérico';
                $stmtC->close();
                echo htmlspecialchars($clienteNombre);
                ?>
            </p>

            <table style="width:100%;border-collapse:collapse;margin-top:10px;">
                <thead>
                    <tr style="background:#f3f4f6;">
                        <th style="padding:8px;text-align:left">Producto</th>
                        <th style="padding:8px;text-align:right">Precio</th>
                        <th style="padding:8px;text-align:center">Cant</th>
                        <th style="padding:8px;text-align:right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cart as $it): ?>
                    <tr>
                        <td style="padding:8px;border-bottom:1px solid #eee;"><?php echo htmlspecialchars($it['nombre']); ?></td>
                        <td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">$<?php echo number_format($it['precio'],2); ?></td>
                        <td style="padding:8px;border-bottom:1px solid #eee;text-align:center;"><?php echo intval($it['cantidad']); ?></td>
                        <td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">$<?php echo number_format($it['subtotal'],2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;padding:8px;font-weight:700">Total:</td>
                        <td style="text-align:right;padding:8px;font-size:16px;font-weight:700">$<?php echo number_format($total,2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <form method="POST" action="guardar.php" style="margin-top:14px;">
            <input type="hidden" name="cart" value="<?php echo htmlspecialchars($cart_json, ENT_QUOTES); ?>">
            <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">

            <div style="display:flex;gap:10px;align-items:center;margin-top:12px;">
                <label style="min-width:120px;">Método de pago:</label>
                <select name="metodo_pago" required style="padding:8px;border-radius:6px;border:1px solid #ddd;">
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>

            <div style="margin-top:12px;display:flex;gap:10px;align-items:center;">
                <label style="min-width:120px;">Notas (opcional):</label>
                <input type="text" name="notas" placeholder="Sin cebolla, extra queso..." style="flex:1;padding:8px;border-radius:6px;border:1px solid #ddd;">
            </div>

            <div style="margin-top:16px;">
                <button type="submit" class="btn primary">Confirmar y Guardar Orden</button>
                <a href="menu.php" class="btn outline" style="margin-left:8px;">Volver</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
<?php $conn->close(); ?>
