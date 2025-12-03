<?php
// Crear_Orden/guardar.php
session_start();
require_once __DIR__ . '/../login/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido.");
}

if (!isset($_POST['cart']) || empty($_POST['cart'])) {
    die("Carrito vacío.");
}

$cart = json_decode($_POST['cart'], true);
if (!is_array($cart) || count($cart) === 0) {
    die("Carrito inválido.");
}

$id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 1;
$metodo_pago = isset($_POST['metodo_pago']) ? $conn->real_escape_string($_POST['metodo_pago']) : 'efectivo';
$notas = isset($_POST['notas']) ? $conn->real_escape_string($_POST['notas']) : '';
// id_usuario: si hay sesión, úsala; si no, 1 por defecto
$id_usuario = isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 1;

// recalcular total por seguridad
$total = 0;
foreach ($cart as $it) {
    $total += floatval($it['subtotal']);
}
$total = round($total, 2);

try {
    // iniciar transacción
    $conn->begin_transaction();

    // insertar orden
    $sqlOrden = "INSERT INTO ordenes (fecha, id_cliente, id_usuario, total, estado, metodo_pago) VALUES (NOW(), ?, ?, ?, 'pendiente', ?)";
    $stmt = $conn->prepare($sqlOrden);
    if (!$stmt) throw new Exception("Error prepare orden: " . $conn->error);
    $stmt->bind_param("iiis", $id_cliente, $id_usuario, $total, $metodo_pago);
    if (!$stmt->execute()) throw new Exception("Error execute orden: " . $stmt->error);
    $id_orden = $conn->insert_id;
    $stmt->close();

    // insertar cada detalle
    $sqlDetalle = "INSERT INTO detalle_orden (id_orden, id_producto, cantidad, subtotal) VALUES (?, ?, ?, ?)";
    $stmtDet = $conn->prepare($sqlDetalle);
    if (!$stmtDet) throw new Exception("Error prepare detalle: " . $conn->error);

    foreach ($cart as $it) {
        $id_producto = intval($it['id_producto']);
        $cantidad = intval($it['cantidad']);
        $subtotal = round(floatval($it['subtotal']), 2);

        $stmtDet->bind_param("iiid", $id_orden, $id_producto, $cantidad, $subtotal);
        if (!$stmtDet->execute()) throw new Exception("Error execute detalle: " . $stmtDet->error);

        // opcional: reducir stock si tu negocio lo requiere
        $sqlStock = "UPDATE productos SET stock = stock - ? WHERE id_producto = ? AND stock >= ?";
        $stmtStock = $conn->prepare($sqlStock);
        if ($stmtStock) {
            $stmtStock->bind_param("iii", $cantidad, $id_producto, $cantidad);
            $stmtStock->execute();
            $stmtStock->close();
        }
    }
    $stmtDet->close();

    // opcional: guardar notas en otra tabla o en ordenes (no existe columna notas en tu esquema)
    // si quieres notas en ordenes, previamente deberías agregar columna 'notas' en la tabla ordenes.

    $conn->commit();

    // limpiar carrito del lado cliente -> envia página que redirige
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Orden creada</title>";
    echo "<script>
            sessionStorage.removeItem('carrito');
            alert('Orden creada correctamente. ID: $id_orden');
            window.location.href = '../ordenes/ordenes.php';
          </script>";
    echo "</head><body></body></html>";
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Error al guardar la orden: " . $e->getMessage());
}
