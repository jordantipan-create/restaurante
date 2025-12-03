<?php
// insert.php (Maneja la lógica de inserción de una nueva orden)
include '../login/conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $fecha = $_POST['fecha'];
    $id_cliente = filter_var($_POST['id_cliente'], FILTER_SANITIZE_NUMBER_INT);
    $id_usuario = filter_var($_POST['id_usuario'], FILTER_SANITIZE_NUMBER_INT);
    $total = filter_var($_POST['total'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $estado = trim($_POST['estado']);

    $stmt = $conn->prepare("INSERT INTO ordenes (fecha, id_cliente, id_usuario, total, estado) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        $error_msg = "Error al preparar la consulta de inserción: " . $conn->error;
        // Redirige y cierra la conexión si la preparación falla
        $conn->close();
        header("Location: ordenes.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    
    // Bind types: string (fecha), integer, integer, double, string
    $stmt->bind_param("siids", $fecha, $id_cliente, $id_usuario, $total, $estado);

    if ($stmt->execute()) {
        $conn->close(); // Cierre exitoso
        header("Location: ordenes.php?msg=" . urlencode("Orden registrada correctamente con ID: " . $conn->insert_id) . "&status=success");
        exit();
    } else {
        $error_msg = "Error al insertar: " . $stmt->error;
        $conn->close(); // Cierre si la ejecución falla
        header("Location: ordenes.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    $stmt->close();
} else {
    // Redirige y cierra la conexión si el acceso es incorrecto
    $conn->close(); 
    header("Location: ordenes.php?msg=" . urlencode("Acceso no autorizado a la inserción.") . "&status=error");
    exit();
}
// NOTA: ELIMINAR LA LÍNEA FINAL '$conn->close();'
?>