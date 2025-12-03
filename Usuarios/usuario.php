<?php
session_start();
// Asegúrate de que este archivo contenga la conexión a tu base de datos 'pos_res'
include 'db_config.php'; 

// =============================================
// PROCESAR CREAR USUARIO
// =============================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    // 1. Sanitización/Validación de entrada
    $nombre = trim($_POST['nombre']);
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol']; // Asume que 'rol' es 'admin' o 'empleado'

    // Validación simple
    if (empty($nombre) || empty($contrasena) || empty($rol)) {
        $_SESSION['mensaje'] = "❌ Error: Todos los campos son obligatorios.";
        $_SESSION['tipo_mensaje'] = "error";
    } else {
        // 🔒 IMPORTANTE: Hashear la contraseña antes de guardarla
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        // 2. Sentencia preparada
        $sql = "INSERT INTO usuario (nombre_usuario, contraseña, rol) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
             $_SESSION['mensaje'] = "❌ Error de preparación SQL: " . $conn->error;
             $_SESSION['tipo_mensaje'] = "error";
        } else {
            $stmt->bind_param("sss", $nombre, $contrasena_hash, $rol);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "✅ Usuario creado exitosamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                if ($conn->errno == 1062) {
                    $_SESSION['mensaje'] = "❌ Error: El nombre de usuario **" . htmlspecialchars($nombre) . "** ya existe.";
                } else {
                    $_SESSION['mensaje'] = "❌ Error al crear usuario: " . $stmt->error;
                }
                $_SESSION['tipo_mensaje'] = "error";
            }
            $stmt->close();
        }
    }
    
    // Redirección para evitar reenvío de formulario
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// =============================================
// OBTENER LISTA DE USUARIOS
// =============================================
$sql_usuarios = "SELECT id_usuario, nombre_usuario, rol FROM usuario ORDER BY id_usuario";
$resultado = $conn->query($sql_usuarios);
// Capturamos el resultado antes de que el HTML pueda fallar
$usuarios = [];
if ($resultado && $resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
    // 3. Liberación del resultado
    $resultado->free();
}

// La conexión se cerrará al final del archivo PHP
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <style>
        /* Variables y Estilos Base 🎨 */
        :root {
            --primario: #FF8C00; /* Naranja Oscuro */
            --secundario: #000000; /* Negro */
            --fondo-oscuro: rgba(0, 0, 0, 0.85); /* Negro semi-transparente para contenido */
            --texto-claro: #ffffff;
            --texto-oscuro: #333333;
            --color-success: #28a745; /* Verde para éxito */
            --color-error: #dc3545; /* Rojo para error */
            --color-admin: #FFD700; /* Oro para Admin */
            --color-empleado: #FFA07A; /* Naranja claro para Empleado */
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            /* Fondo con la imagen solicitada */
            background: url('https://images.unsplash.com/photo-1529042410759-befb1204b468?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat fixed;
            color: var(--texto-claro);
        }
        
        .container {
            width: 90%;
            max-width: 1000px;
            margin: 50px auto;
            padding: 30px;
            background-color: var(--fondo-oscuro); 
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }

        h1 {
            text-align: center;
            color: var(--primario);
            margin-bottom: 30px;
            border-bottom: 3px solid var(--primario);
            padding-bottom: 15px;
            font-size: 2.5em;
        }
        
        h2 {
            color: var(--texto-claro);
            margin-bottom: 20px;
            border-left: 4px solid var(--primario);
            padding-left: 10px;
        }

        /* Tarjetas de Contenido */
        .card {
            background: rgba(255, 255, 255, 0.05); 
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Alertas */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
        }
        .alert-success {
            background-color: var(--color-success);
            color: var(--texto-claro);
        }
        .alert-error {
            background-color: var(--color-error);
            color: var(--texto-claro);
        }

        /* Formulario */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--texto-acento, #FFD700);
        }
        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--primario);
            border-radius: 4px;
            background-color: var(--secundario); 
            color: var(--texto-claro);
            box-sizing: border-box;
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #ccc;
            font-style: italic;
        }

        /* Botones */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.3s ease;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-success {
            background: var(--primario);
            color: var(--texto-claro);
        }
        .btn-success:hover {
            background: #FF4500;
        }
        .btn-primary {
            background: #1e90ff; 
            color: var(--texto-claro);
        }
        .btn-primary:hover {
            background: #007bff;
        }
        .btn-danger {
            background: var(--color-error); 
            color: var(--texto-claro);
        }
        .btn-danger:hover {
            background: #c82333;
        }

        /* Tabla de Usuarios 📊 */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            overflow: hidden;
            border-radius: 6px;
        }
        thead th {
            background-color: var(--primario);
            color: var(--secundario);
            padding: 12px 15px;
            text-align: left;
            font-size: 1em;
        }
        tbody tr {
            background-color: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: background-color 0.3s ease;
        }
        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }
        tbody td {
            padding: 12px 15px;
            color: var(--texto-claro);
            vertical-align: middle;
        }
        
        /* Badges de Rol */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 0.85em;
        }
        .badge-admin {
            background-color: var(--color-admin);
            color: var(--secundario);
        }
        .badge-empleado {
            background-color: var(--color-empleado);
            color: var(--secundario);
        }
        
        /* Estado de Contraseña */
        .password-safe-status {
            font-style: italic;
            color: #aaa;
        }

        /* Acciones */
        .actions a {
            margin-right: 5px;
            white-space: nowrap; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👥 Gestión de Usuarios</h1>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?>">
                <?php 
                echo $_SESSION['mensaje']; 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>➕ Crear Nuevo Usuario</h2>
            <form method="POST" action="">
                <input type="hidden" name="accion" value="crear">
                
                <div class="form-group">
                    <label>Nombre de Usuario:</label>
                    <input type="text" name="nombre" required maxlength="50">
                </div>
                
                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" name="contrasena" required minlength="4">
                    <small>Se almacenará de forma segura (hasheada).</small>
                </div>
                
                <div class="form-group">
                    <label>Rol:</label>
                    <select name="rol" required>
                        <option value="admin">Administrador</option>
                        <option value="empleado">Mesero/Empleado</option> 
                    </select>
                </div>
                
                <button type="submit" class="btn btn-success">➕ Crear Usuario</button>
            </form>
        </div>

        <div class="card">
            <h2>📊 Lista de Usuarios Registrados</h2>
            
            <?php if (!empty($usuarios)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Contraseña (Estado)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $fila): ?>
                        <tr>
                            <td>#<?php echo $fila['id_usuario']; ?></td>
                            <td><?php echo htmlspecialchars($fila['nombre_usuario']); ?></td> 
                            <td>
                                <span class="badge badge-<?php echo $fila['rol']; ?>">
                                    <?php 
                                        if ($fila['rol'] == 'admin') {
                                            echo '👑 Administrador';
                                        } else if ($fila['rol'] == 'empleado') {
                                            echo '👨‍💼 Mesero/Empleado';
                                        } else {
                                            echo htmlspecialchars(ucfirst($fila['rol']));
                                        }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="password-safe-status">🔐 Almacenado de forma segura</span>
                            </td>
                            <td class="actions">
                                <a href="editar_usuario.php?id=<?php echo $fila['id_usuario']; ?>" 
                                    class="btn btn-primary">✏️ Editar</a>
                                
                                <a href="eliminar_usuario.php?id=<?php echo $fila['id_usuario']; ?>" 
                                    class="btn btn-danger"
                                    onclick="return confirm('¿Está seguro de eliminar a <?php echo htmlspecialchars($fila['nombre_usuario']); ?>?')">
                                    🗑️ Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay usuarios registrados.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
// 4. Cierre de la conexión a la base de datos
if (isset($conn)) {
    $conn->close();
}
?>