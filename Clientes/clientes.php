<?php
// ==========================================================
// clientes.php - Gestión de Clientes
// ==========================================================

// Conexión a la base de datos
// Se asume que la ruta '../login/conexion.php' es correcta.
include '../login/conexion.php'; 

// Verificar que la conexión se haya establecido correctamente
if ($conn->connect_error) {
    // Si la conexión falla, se detiene el script y se muestra el error.
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Inicializar el array de clientes
$clientes = [];

// --- LÓGICA DE GESTIÓN DE CLIENTES ---

// 1. Manejo del registro (INSERT)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {
    
    // Capturar y limpiar/sanitizar las variables POST
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);

    // Usamos sentencias preparadas (mejor seguridad)
    $sql = "INSERT INTO clientes (nombre, telefono, email) VALUES (?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        // "sss" indica que los tres parámetros son strings
        $stmt->bind_param("sss", $nombre, $telefono, $email);
        
        if ($stmt->execute()) {
            // Muestra un mensaje y luego recarga la página dentro del iframe
            echo "<script>alert('Cliente registrado exitosamente.'); window.location.href='clientes.php';</script>";
        } else {
            echo "<script>alert('Error al registrar el cliente: " . $stmt->error . "'); window.location.href='clientes.php';</script>";
        }
        $stmt->close();
    } else {
          echo "<script>alert('Error al preparar la consulta de registro: " . $conn->error . "'); window.location.href='clientes.php';</script>";
    }
}

// 2. Manejo de la eliminación (DELETE)
if (isset($_GET['eliminar'])) {
    // Sanitizar y asegurar que el ID sea un entero
    $id_cliente = filter_var($_GET['eliminar'], FILTER_SANITIZE_NUMBER_INT);
    $id_cliente = intval($id_cliente); // Doble verificación de que es un número

    // Preparar la consulta SQL para eliminar el cliente
    $sql = "DELETE FROM clientes WHERE id_cliente = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_cliente); // "i" es para tipo entero
        if ($stmt->execute()) {
            echo "<script>alert('Cliente eliminado exitosamente.'); window.location.href='clientes.php';</script>";
        } else {
            echo "<script>alert('Error al eliminar el cliente: " . $stmt->error . "'); window.location.href='clientes.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error al preparar la consulta de eliminación: " . $conn->error . "'); window.location.href='clientes.php';</script>";
    }
}

// 3. Obtener todos los clientes (SELECT)
// Se utiliza consulta directa aquí, ya que no hay variables externas involucradas
$sql_select = "SELECT id_cliente, nombre, telefono, email FROM clientes ORDER BY id_cliente DESC";
$resultado = $conn->query($sql_select);

if ($resultado) {
    $clientes = $resultado->fetch_all(MYSQLI_ASSOC);
    $resultado->close(); // Liberar memoria del resultado
} else {
    // Manejo de error si la consulta SELECT falla
    echo "<script>console.error('Error al obtener clientes: " . $conn->error . "');</script>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👥 Clientes</title>
    <link rel="stylesheet" href="clientes.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="iframe-body">

<main class="contenedor">
    
    <section class="formulario">
        <h2>➕ Agregar Nuevo Cliente</h2>
        <form id="form-clientes" method="POST" action="clientes.php">
            
            <label for="nombre">Nombre Completo:</label>
            <input type="text" name="nombre" id="nombre" placeholder="Ej: Juan Pérez" required>
            
            <label for="telefono">Teléfono:</label>
            <input type="tel" name="telefono" id="telefono" placeholder="Ej: 555-1234" required>
            
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" placeholder="Ej: correo@ejemplo.com" required>

            <div class="botones">
                <button type="submit" name="registrar" class="btn-principal btn-agregar">💾 Registrar Cliente</button>
                <button type="reset" class="btn-secundario btn-limpiar">🧹 Limpiar Campos</button>
            </div>
        </form>
    </section>

    <section class="tabla">
        <h2>📋 Listado de Clientes (Total: <?= count($clientes); ?>)</h2>
        <div class="tabla-scroll">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['id_cliente']) ?></td>
                            <td><?= htmlspecialchars($fila['nombre']) ?></td>
                            <td><?= htmlspecialchars($fila['telefono']) ?></td>
                            <td><?= htmlspecialchars($fila['email']) ?></td>
                            <td class="acciones">
                                <button class="btn-accion btn-editar" 
                                    data-toggle="modal" 
                                    data-target="#modalActualizar"
                                    data-id="<?= htmlspecialchars($fila['id_cliente']) ?>"
                                    data-nombre="<?= htmlspecialchars($fila['nombre']) ?>"
                                    data-telefono="<?= htmlspecialchars($fila['telefono']) ?>"
                                    data-email="<?= htmlspecialchars($fila['email']) ?>">
                                    ✏️ Editar
                                </button>
                                <a href="clientes.php?eliminar=<?= htmlspecialchars($fila['id_cliente']) ?>" 
                                   class="btn-accion btn-eliminar" 
                                   onclick="return confirm('¿Seguro que deseas eliminar a <?= htmlspecialchars($fila['nombre']) ?>?')">
                                    🗑️ Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #888;">No hay clientes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div class="modal fade" id="modalActualizar" tabindex="-1" role="dialog" aria-labelledby="modalActualizarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalActualizarLabel">Actualizar Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="actualizar.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" id="modal_id_cliente">
                    
                    <label>Nombre:</label>
                    <input type="text" name="nombre" id="modal_nombre" class="form-control" required>
                    
                    <label>Teléfono:</label>
                    <input type="tel" name="telefono" id="modal_telefono" class="form-control" required>

                    <label>Email:</label>
                    <input type="email" name="email" id="modal_email" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" style="background-color: #FF6600; border-color: #FF6600;">Guardar Cambios</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script para cargar los datos del cliente en el modal de actualización
    $(document).on('click', '.btn-editar', function() {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        var telefono = $(this).data('telefono');
        var email = $(this).data('email');

        $('#modal_id_cliente').val(id);
        $('#modal_nombre').val(nombre);
        $('#modal_telefono').val(telefono);
        $('#modal_email').val(email);

        $('#modalActualizar').modal('show');
    });
</script>

</body>
</html>

<?php
// Cerrar la conexión a la base de datos al final del script
$conn->close();
?>