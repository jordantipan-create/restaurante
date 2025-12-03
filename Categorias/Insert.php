<?php
include '../login/conexion.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_categoria = $_POST['nombre_categoria'];
    $descripcion = $_POST['descripcion'];

    // Preparamos la consulta SIN el campo "estado" ni "activo"
    $stmt = $conn->prepare("INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre_categoria, $descripcion);

    if ($stmt->execute()) {
        echo "✅ Categoría registrada correctamente";
    } else {
        echo "❌ Error al insertar: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
