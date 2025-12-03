<?php
session_start();
include 'db_config.php'; // Incluimos la conexión

// Asegurar que se recibió un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = "❌ Error: ID de usuario no especificado para eliminar.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: usuario.php");
    exit();
}

$id = (int)$_GET['id'];

// =============================================
// LÓGICA DE ELIMINACIÓN
// =============================================

// Paso 1: Verificar si el usuario a eliminar es el único administrador
$sql_check_rol = "SELECT rol FROM usuario WHERE id_usuario = ?";
$stmt_check = $conn->prepare($sql_check_rol);
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$user_result = $stmt_check->get_result();
$user_data = $user_result->fetch_assoc();

if ($user_data && $user_data['rol'] == 'admin') {
    $sql_count_admin = "SELECT COUNT(*) as total_admin FROM usuario WHERE rol = 'admin'";
    $result_count = $conn->query($sql_count_admin);
    $admin_count = $result_count->fetch_assoc();
    
    if ($admin_count['total_admin'] <= 1) {
        $_SESSION['mensaje'] = "❌ Error: No se puede eliminar el único administrador del sistema.";
        $_SESSION['tipo_mensaje'] = "error";
        $stmt_check->close();
        header("Location: usuario.php");
        exit();
    }
}
$stmt_check->close();

// Paso 2: Ejecutar la eliminación
$sql_delete = "DELETE FROM usuario WHERE id_usuario=?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    $_SESSION['mensaje'] = "✅ Usuario eliminado exitosamente.";
    $_SESSION['tipo_mensaje'] = "success";
} else {
    $_SESSION['mensaje'] = "❌ Error al eliminar usuario: " . $conn->error;
    $_SESSION['tipo_mensaje'] = "error";
}

$stmt_delete->close();
$conn->close();

header("Location: usuario.php");
exit();
?>