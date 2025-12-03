<?php
// insert.php (Maneja la lógica de inserción de un nuevo ítem)
include '../login/conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre_item = trim($_POST['nombre_item']);
    $cantidad_actual = filter_var($_POST['cantidad_actual'], FILTER_SANITIZE_NUMBER_INT);
    $unidad = trim($_POST['unidad']);
    $fecha_actualizacion = $_POST['fecha_actualizacion'];

    $stmt = $conn->prepare("INSERT INTO inventario (nombre_item, cantidad_actual, unidad, fecha_actualizacion) VALUES (?, ?, ?, ?)");
    
    if ($stmt === false) {
        $error_msg = "Error al preparar la consulta de inserción: " . $conn->error;
        header("Location: inventario.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    
    // Bind types: string, integer, string, string (fecha)
    $stmt->bind_param("siss", $nombre_item, $cantidad_actual, $unidad, $fecha_actualizacion);

    if ($stmt->execute()) {
        $conn->close();
        header("Location: inventario.php?msg=" . urlencode("Ítem agregado correctamente: " . $nombre_item) . "&status=success");
        exit();
    } else {
        $error_msg = "Error al insertar: " . $stmt->error;
        $conn->close();
        header("Location: inventario.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    $stmt->close();
} else {
    header("Location: inventario.php?msg=" . urlencode("Acceso no autorizado a la inserción.") . "&status=error");
    exit();
}
$conn->close(); 
?>