<?php
// ==========================================================
// actualizar.php - Lógica de actualización del cliente
// ==========================================================

// 1. Conexión a la base de datos
// Se asume la misma ruta que en clientes.php
include '../login/conexion.php'; 

// 2. Verificar la recepción de datos POST
// El ID es un campo OBLIGATORIO para saber qué registro actualizar.
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['id_cliente'], $_POST['nombre'], $_POST['telefono'], $_POST['email'])) {
    // Si no vienen los datos esperados, redirigir con un mensaje de error.
    header("Location: clientes.php?error=datos_incompletos");
    exit();
}

// 3. Capturar y limpiar/validar los datos
$id_cliente = filter_var($_POST['id_cliente'], FILTER_SANITIZE_NUMBER_INT);
$nombre     = trim($_POST['nombre']);
$telefono   = trim($_POST['telefono']);
$email      = trim($_POST['email']);

// **IMPORTANTE**: No necesitamos actualizar el ID, solo lo usamos para el WHERE.

// 4. Preparar la consulta UPDATE con sentencias preparadas
$sql = "
    UPDATE clientes 
    SET nombre = ?, 
        telefono = ?, 
        email = ? 
    WHERE id_cliente = ?
";

if ($stmt = $conn->prepare($sql)) {
    // Vincular los parámetros: "sssi" -> 3 strings (nombre, tel, email) y 1 integer (id)
    $stmt->bind_param("sssi", $nombre, $telefono, $email, $id_cliente);
    
    // 5. Ejecutar la consulta
    if ($stmt->execute()) {
        
        // 6. Redirección exitosa con mensaje
        // Usamos un script para forzar la recarga dentro del iframe si es necesario
        echo "<script>alert('Cliente con ID $id_cliente actualizado exitosamente.'); window.location.href='clientes.php';</script>";
        
    } else {
        // Redirección con error
        echo "<script>alert('Error al actualizar el cliente: " . $stmt->error . "'); window.location.href='clientes.php';</script>";
    }
    
    $stmt->close();
} else {
    // Error en la preparación de la consulta
    echo "<script>alert('Error al preparar la consulta de actualización: " . $conn->error . "'); window.location.href='clientes.php';</script>";
}

// 7. Cerrar la conexión
$conn->close();

?>