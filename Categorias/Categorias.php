<?php
include '../login/conexion.php';

// Obtener todas las categorías desde la base de datos
$resultado = $conn->query("SELECT * FROM categorias");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📁 Gestión de Categorías</title>
    <link rel="stylesheet" href="Categorias.css"> 

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="iframe-body"> 

    <main class="contenedor">
        <section class="formulario">
            <h2>Agregar Categoría</h2>

            <form action="Insert.php" method="POST">

                <label for="nombre">Nombre de la Categoría</label>
                <input type="text" name="nombre_categoria" id="nombre" placeholder="Ejemplo: Bebidas" required>

                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" placeholder="Describe la categoría..." required></textarea>

                <div class="botones">
                    <button type="submit" class="btn-agregar">💾 Agregar</button>
                    <button type="reset" class="btn-limpiar">🧹 Limpiar</button>
                    </div>
            </form>
        </section>

        <section class="tabla">
            <h2>Listado de Categorías</h2>
            <div style="overflow-x: auto;"> 
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $resultado->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['id_categoria']) ?></td>
                            <td><?= htmlspecialchars($fila['nombre_categoria']) ?></td>
                            <td><?= htmlspecialchars($fila['descripcion']) ?></td>
                            <td class="acciones">
                                <button 
                                    class="btn-editar" 
                                    data-id="<?= $fila['id_categoria'] ?>"
                                    data-nombre="<?= htmlspecialchars($fila['nombre_categoria']) ?>"
                                    data-descripcion="<?= htmlspecialchars($fila['descripcion']) ?>"
                                    data-toggle="modal" data-target="#modalActualizar"> ✏️ Editar
                                </button>
                                <a href="eliminar.php?id=<?= $fila['id_categoria'] ?>" 
                                   class="btn-eliminar" 
                                   onclick="return confirm('¿Seguro que deseas eliminar esta categoría?')">🗑️ Eliminar</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modal fade" id="modalActualizar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white" style="background-color: #FF6600 !important; border-bottom: none;">
                    <h5 class="modal-title">Actualizar Categoría</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: #FFF !important;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="actualizar.php" method="POST">
                    <div class="modal-body" style="background-color: #212121; color: #FFF;">
                        <input type="hidden" name="id_categoria" id="id_categoria">
                        
                        <label>Nombre:</label>
                        <input type="text" name="nombre_categoria" id="nombre_categoria" class="form-control" required style="background-color: #1A1A1A; border-color: #FF6600;">
                        
                        <label>Descripción:</label>
                        <textarea name="descripcion" id="descripcion_modal" class="form-control" required style="background-color: #1A1A1A; border-color: #FF6600;"></textarea>
                    </div>
                    <div class="modal-footer" style="background-color: #212121; border-top: 1px solid #333333;">
                        <button type="submit" class="btn btn-success" style="background-color: #FF6600; border-color: #FF6600; color: #000;">Actualizar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background-color: #333333; border-color: #333333; color: #FFF;">Cancelar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        // Al hacer clic en el botón editar, se cargan los datos en el modal
        // La lógica de jQuery se mantiene, pero se usa 'data-toggle' y 'data-target' en el botón para abrir el modal.
        $(document).on('click', '.btn-editar', function() {
            var id = $(this).data('id');
            var nombre = $(this).data('nombre');
            var descripcion = $(this).data('descripcion');

            $('#id_categoria').val(id);
            $('#nombre_categoria').val(nombre);
            $('#descripcion_modal').val(descripcion);

            // $('#modalActualizar').modal('show'); // Esto ya no es estrictamente necesario si se usa data-toggle/target
        });
    </script>

</body>
</html>