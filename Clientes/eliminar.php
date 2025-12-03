<?php
include '../login/conexion.php';

// Verificar si se ha enviado un ID por GET
if (isset($_GET['id'])) {
    $id_cliente = $_GET['id'];

    // Preparar la consulta SQL para eliminar el cliente
    $sql = "DELETE FROM clientes WHERE id_cliente = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Vincular el parámetro
        $stmt->bind_param("i", $id_cliente);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "<script>alert('Cliente eliminado exitosamente.'); window.location.href='clientes.php';</script>";
        } else {
            echo "<script>alert('Error al eliminar el cliente.'); window.location.href='clientes.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error en la preparación de la consulta.'); window.location.href='clientes.php';</script>";
    }
} else {
    echo "<script>alert('ID de cliente no especificado.'); window.location.href='clientes.php';</script>";
}

// Cerrar la conexión
$conn->close();
?>
