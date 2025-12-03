<?php
include '../login/conexion.php';

// Verificar si se ha enviado un ID por GET
if (isset($_GET['id'])) {
    $id_categoria = $_GET['id'];

    // Preparar la consulta SQL para eliminar la categoría
    $sql = "DELETE FROM categorias WHERE id_categoria = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Vincular el parámetro
        $stmt->bind_param("i", $id_categoria);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "<script>alert('Categoría eliminada exitosamente.'); window.location.href='Categorias.php';</script>";
        } else {
            echo "<script>alert('Error al eliminar la categoría.'); window.location.href='Categorias.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error en la preparación de la consulta.'); window.location.href='Categorias.php';</script>";
    }
} else {
    echo "<script>alert('ID de categoría no especificado.'); window.location.href='Categorias.php';</script>";
}

$conn->close();
?>
