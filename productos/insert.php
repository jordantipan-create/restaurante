<?php
// insert.php (Maneja la lógica de inserción de un nuevo producto)
include '../login/conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre = trim($_POST['nombre']);
    $precio = filter_var($_POST['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $id_categoria = filter_var($_POST['id_categoria'], FILTER_SANITIZE_NUMBER_INT);
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
    $descripcion = trim($_POST['descripcion']);

    $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, id_categoria, stock, descripcion) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        $error_msg = "Error al preparar la consulta de inserción: " . $conn->error;
        header("Location: productos.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    
    // Bind types: string, double, integer, integer, string
    $stmt->bind_param("sdiss", $nombre, $precio, $id_categoria, $stock, $descripcion);

    if ($stmt->execute()) {
        $conn->close();
        header("Location: productos.php?msg=" . urlencode("Producto agregado correctamente: " . $nombre) . "&status=success");
        exit();
    } else {
        $error_msg = "Error al insertar: " . $stmt->error;
        $conn->close();
        header("Location: productos.php?msg=" . urlencode($error_msg) . "&status=error");
        exit();
    }
    $stmt->close();
} else {
    header("Location: productos.php?msg=" . urlencode("Acceso no autorizado a la inserción.") . "&status=error");
    exit();
}
$conn->close(); 
?>