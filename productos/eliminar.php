<?php
// eliminar.php (Maneja la lógica de eliminación)
include '../login/conexion.php'; 

if (isset($_GET['id_producto'])) {
    $id_producto = filter_var($_GET['id_producto'], FILTER_SANITIZE_NUMBER_INT);

    $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto = ?");
    
    if ($stmt === false) {
        $error_msg = "Error al preparar la consulta de eliminación: " . $conn->error;
        header("Location: productos.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }

    $stmt->bind_param("i", $id_producto);

    if ($stmt->execute()) {
        $conn->close();
        header("Location: productos.php?msg=" . urlencode("Producto ID " . $id_producto . " eliminado correctamente.") . "&status=success");
        exit();
    } else {
        $error_msg = "Error al eliminar: " . $stmt->error;
        $conn->close();
        header("Location: productos.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    $stmt->close();
} else {
    header("Location: productos.php?msg=" . urlencode("ID de producto no especificado para eliminar.") . "&status=error");
    exit();
}
$conn->close(); 
?>