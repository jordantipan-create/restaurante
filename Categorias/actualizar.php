<?php
include '../login/conexion.php';

// Verificar si se han enviado datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $id_categoria = $_POST['id_categoria'];
    $nombre_categoria = $_POST['nombre_categoria'];
    $descripcion = $_POST['descripcion'];

    // Preparar la consulta SQL para actualizar la categoría
    $sql = "UPDATE categorias SET nombre_categoria = ?, descripcion = ? WHERE id_categoria = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Vincular los parámetros
        $stmt->bind_param("ssi", $nombre_categoria, $descripcion, $id_categoria);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "<script>alert('Categoría actualizada exitosamente.'); window.location.href='Categorias.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar la categoría.'); window.location.href='Categorias.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error en la preparación de la consulta.'); window.location.href='Categorias.php';</script>";
    }
}

$conn->close();
?>
