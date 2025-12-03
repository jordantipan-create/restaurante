<?php
// eliminar.php (Maneja la lógica de eliminación)
include '../login/conexion.php'; 

if (isset($_GET['id_inventario'])) {
    $id_inventario = filter_var($_GET['id_inventario'], FILTER_SANITIZE_NUMBER_INT);

    $stmt = $conn->prepare("DELETE FROM inventario WHERE id_inventario = ?");
    
    if ($stmt === false) {
        $error_msg = "Error al preparar la consulta de eliminación: " . $conn->error;
        header("Location: inventario.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }

    $stmt->bind_param("i", $id_inventario);

    if ($stmt->execute()) {
        $conn->close();
        header("Location: inventario.php?msg=" . urlencode("Ítem ID " . $id_inventario . " eliminado correctamente.") . "&status=success");
        exit();
    } else {
        $error_msg = "Error al eliminar: " . $stmt->error;
        $conn->close();
        header("Location: inventario.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    $stmt->close();
} else {
    header("Location: inventario.php?msg=" . urlencode("ID de ítem no especificado para eliminar.") . "&status=error");
    exit();
}
$conn->close(); 
?>