<?php
// db_config.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos_res";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Opcional: Establecer el juego de caracteres a utf8
$conn->set_charset("utf8");

// Crear tabla si no existe y asegurar que la contraseña sea VARCHAR(255)
$sql_create_table = "CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado') NOT NULL
)";

if (!$conn->query($sql_create_table)) {
    die("Error creando tabla: " . $conn->error);
}

// La conexión $conn ahora está lista para ser usada.
?>