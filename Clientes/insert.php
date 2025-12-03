<?php
include '../login/conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];

    // Preparamos la consulta para insertar un nuevo cliente
    $stmt = $conn->prepare("INSERT INTO clientes (nombre, telefono, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $telefono, $email);

    if ($stmt->execute()) {
        echo "✅ Cliente registrado correctamente";
    } else {
        echo "❌ Error al insertar: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

