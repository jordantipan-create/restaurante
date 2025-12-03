<?php
session_start();
include 'db_config.php'; // Incluimos la conexión

$id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario_editar = null;

// =============================================
// PROCESAR EDICIÓN (POST)
// =============================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $rol = $_POST['rol'];
    
    // Se inicializa el mensaje
    $mensaje = "✅ Usuario actualizado exitosamente";

    if (!empty($_POST['contrasena'])) {
        // 🔒 Hashear nueva contraseña si se proporciona
        $contrasena_hash = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
        $sql = "UPDATE usuario SET nombre_usuario=?, contraseña=?, rol=? WHERE id_usuario=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $contrasena_hash, $rol, $id);
    } else {
        // Mantener contraseña actual si está vacía
        $sql = "UPDATE usuario SET nombre_usuario=?, rol=? WHERE id_usuario=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $rol, $id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = $mensaje;
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        if ($conn->errno == 1062) {
            $_SESSION['mensaje'] = "❌ Error: El nombre de usuario ya existe.";
        } else {
            $_SESSION['mensaje'] = "❌ Error al actualizar usuario: " . $conn->error;
        }
        $_SESSION['tipo_mensaje'] = "error";
    }
    $stmt->close();
    
    // Redirigir de vuelta a la lista principal después de la edición
    header("Location: usuario.php");
    exit();
}

// =============================================
// OBTENER DATOS DE USUARIO A EDITAR (GET)
// =============================================
if ($id_usuario > 0) {
    $sql_editar = "SELECT id_usuario, nombre_usuario, rol FROM usuario WHERE id_usuario = ?";
    $stmt_editar = $conn->prepare($sql_editar);
    $stmt_editar->bind_param("i", $id_usuario);
    $stmt_editar->execute();
    $result_editar = $stmt_editar->get_result();
    $usuario_editar = $result_editar->fetch_assoc();
    $stmt_editar->close();
    
    if (!$usuario_editar) {
        $_SESSION['mensaje'] = "❌ Error: Usuario no encontrado.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: usuario.php");
        exit();
    }
} else {
    header("Location: usuario.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="usuario.css">
</head>
<body>
    <div class="container">
        <h1>✏️ Editar Usuario: <?php echo htmlspecialchars($usuario_editar['nombre_usuario']); ?></h1>
        <div class="card">
            <form method="POST" action="">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" value="<?php echo $usuario_editar['id_usuario']; ?>">
                
                <div class="form-group">
                    <label>Nombre de Usuario:</label>
                    <input type="text" name="nombre" 
                           value="<?php echo htmlspecialchars($usuario_editar['nombre_usuario']); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label>Nueva Contraseña:</label>
                    <input type="password" name="contrasena" 
                           placeholder="Dejar en blanco para mantener la actual"
                           minlength="4">
                    <small>Solo llena este campo si deseas cambiar la contraseña.</small>
                </div>
                
                <div class="form-group">
                    <label>Rol:</label>
                    <select name="rol" required>
                        <option value="admin" <?php echo ($usuario_editar['rol'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="empleado" <?php echo ($usuario_editar['rol'] == 'empleado') ? 'selected' : ''; ?>>Mesero</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">💾 Actualizar Usuario</button>
                    <a href="usuario.php" class="btn btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>