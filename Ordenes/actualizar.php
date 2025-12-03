<?php
// actualizar.php (Maneja la lógica de edición de una orden existente, usado por el modal)
include '../login/conexion.php'; 

if (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id_orden = filter_var($_POST['id_orden'], FILTER_SANITIZE_NUMBER_INT);
    $fecha = $_POST['fecha'];
    $id_cliente = filter_var($_POST['id_cliente'], FILTER_SANITIZE_NUMBER_INT);
    $id_usuario = filter_var($_POST['id_usuario'], FILTER_SANITIZE_NUMBER_INT);
    $total = filter_var($_POST['total'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $estado = trim($_POST['estado']);

    $stmt = $conn->prepare("UPDATE ordenes SET fecha = ?, id_cliente = ?, id_usuario = ?, total = ?, estado = ? WHERE id_orden = ?");
    
    if ($stmt === false) {
        $error_msg = "Error al preparar la consulta de edición: " . $conn->error;
        header("Location: ordenes.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    
    // Bind types: string (fecha), integer, integer, double, string, integer (ID)
    $stmt->bind_param("siidsi", $fecha, $id_cliente, $id_usuario, $total, $estado, $id_orden);

    if ($stmt->execute()) {
        $conn->close();
        header("Location: ordenes.php?msg=" . urlencode("Orden ID " . $id_orden . " actualizada correctamente.") . "&status=success");
        exit();
    } else {
        $error_msg = "Error al actualizar: " . $stmt->error;
        $conn->close();
        header("Location: ordenes.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    $stmt->close();
} else {
    header("Location: ordenes.php?msg=" . urlencode("Acceso no autorizado a la edición.") . "&status=error");
    exit();
}
$conn->close(); 
?>