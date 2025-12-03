<?php
// actualizar.php (Maneja la lógica de edición de un ítem existente, usado por el modal)
include '../login/conexion.php'; 

if (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id_inventario = filter_var($_POST['id_inventario'], FILTER_SANITIZE_NUMBER_INT);
    $nombre_item = trim($_POST['nombre_item']);
    $cantidad_actual = filter_var($_POST['cantidad_actual'], FILTER_SANITIZE_NUMBER_INT);
    $unidad = trim($_POST['unidad']);
    $fecha_actualizacion = $_POST['fecha_actualizacion'];

    $stmt = $conn->prepare("UPDATE inventario SET nombre_item = ?, cantidad_actual = ?, unidad = ?, fecha_actualizacion = ? WHERE id_inventario = ?");
    
    if ($stmt === false) {
        $error_msg = "Error al preparar la consulta de edición: " . $conn->error;
        header("Location: inventario.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    
    // Bind types: string, integer, string, string (fecha), integer (ID)
    $stmt->bind_param("sissi", $nombre_item, $cantidad_actual, $unidad, $fecha_actualizacion, $id_inventario);

    if ($stmt->execute()) {
        $conn->close();
        header("Location: inventario.php?msg=" . urlencode("Ítem ID " . $id_inventario . " actualizado correctamente.") . "&status=success");
        exit();
    } else {
        $error_msg = "Error al actualizar: " . $stmt->error;
        $conn->close();
        header("Location: inventario.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    $stmt->close();
} else {
    header("Location: inventario.php?msg=" . urlencode("Acceso no autorizado a la edición.") . "&status=error");
    exit();
}
$conn->close(); 
?>