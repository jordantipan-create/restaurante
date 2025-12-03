<?php
// conexion.php
$host = 'localhost';
$usuario = 'root';
$contraseña = '';
$nombre_bd = 'pos_res';

$conn = new mysqli($host, $usuario, $contraseña, $nombre_bd);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
