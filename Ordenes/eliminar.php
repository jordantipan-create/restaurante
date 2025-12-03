<?php
// eliminar.php (Maneja la lógica de eliminación)
include '../login/conexion.php'; 

if (isset($_GET['id_orden'])) {
    $id_orden = filter_var($_GET['id_orden'], FILTER_SANITIZE_NUMBER_INT);

    $stmt = $conn->prepare("DELETE FROM ordenes WHERE id_orden = ?");
    
    if ($stmt === false) {
        $error_msg = "Error al preparar la consulta de eliminación: " . $conn->error;
        header("Location: ordenes.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }

    $stmt->bind_param("i", $id_orden);

    if ($stmt->execute()) {
        $conn->close();
        header("Location: ordenes.php?msg=" . urlencode("Orden ID " . $id_orden . " eliminada correctamente.") . "&status=success");
        exit();
    } else {
        $error_msg = "Error al eliminar: " . $stmt->error;
        $conn->close();
        header("Location: ordenes.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    $stmt->close();
} else {
    header("Location: ordenes.php?msg=" . urlencode("ID de orden no especificado para eliminar.") . "&status=error");
    exit();
}
$conn->close(); 
?>