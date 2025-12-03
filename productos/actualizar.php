<?php
// actualizar.php (Maneja la lógica de edición de un producto existente, usado por el modal)
include '../login/conexion.php'; 

if (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id_producto = filter_var($_POST['id_producto'], FILTER_SANITIZE_NUMBER_INT);
    $nombre = trim($_POST['nombre']);
    $precio = filter_var($_POST['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $id_categoria = filter_var($_POST['id_categoria'], FILTER_SANITIZE_NUMBER_INT);
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
    $descripcion = trim($_POST['descripcion']);

    $stmt = $conn->prepare("UPDATE productos SET nombre = ?, precio = ?, id_categoria = ?, stock = ?, descripcion = ? WHERE id_producto = ?");
    
    if ($stmt === false) {
        $error_msg = "Error al preparar la consulta de edición: " . $conn->error;
        header("Location: productos.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    
    // Bind types: string, double, integer, integer, string, integer (ID)
    $stmt->bind_param("sdiisi", $nombre, $precio, $id_categoria, $stock, $descripcion, $id_producto);

    if ($stmt->execute()) {
        $conn->close();
        header("Location: productos.php?msg=" . urlencode("Producto ID " . $id_producto . " actualizado correctamente.") . "&status=success");
        exit();
    } else {
        $error_msg = "Error al actualizar: " . $stmt->error;
        $conn->close();
        header("Location: productos.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    $stmt->close();
} else {
    header("Location: productos.php?msg=" . urlencode("Acceso no autorizado a la edición.") . "&status=error");
    exit();
}
$conn->close(); 
?>