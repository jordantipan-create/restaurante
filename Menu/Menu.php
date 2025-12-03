<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login/login.html");
    exit();
}

$usuario = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

if ($rol !== 'admin') {
    header("Location: menuusu.php");
    exit();
}

// Ya no necesitamos contar aquí, el panel lo mostrará en el 'home' o se omitirá
// include "../login/conexion.php"; 
// ... Las funciones de contar se eliminan para este archivo ...
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Panel Administrador - Restaurante POS</title>
    <link rel="stylesheet" href="Menu.css"> 
    <link href="https://images.unsplash.com/photo-1529042410759-befb1204b468?auto=format&fit=crop&w=1600&q=80" rel="stylesheet">
</head>
<body>

<div class="topbar">
    <div class="logo">
        <span class="icon">🍽️</span> Restaurante POS
    </div>
    <div class="user-profile">
        <span>👤 **<?php echo htmlspecialchars($usuario); ?>**</span>
        <form action="../logout.php" method="POST" style="display:inline;">
            <button class="btn-salir" type="submit">🚪 Salir</button>
        </form>
    </div>
</div>

<aside class="sidebar">
    <nav class="main-nav">
      <ul>
    <li><a href="#" class="nav-link active" data-src="home.php">🏠 Dashboard</a></li>
    <li><a href="#" class="nav-link" data-src="../Categorias/Categorias.php">📂 Categorías</a></li>
    <li><a href="#" class="nav-link" data-src="../clientes/clientes.php">👥 Clientes</a></li>
    <li><a href="#" class="nav-link" data-src="../Productos/productos.php">🍔 Productos</a></li>
    <li><a href="#" class="nav-link" data-src="../inventario/inventario.php">📦 Inventario</a></li>

    <!-- ⭐ NUEVA OPCIÓN PRINCIPAL -->
    <li><a href="#" class="nav-link" data-src="../Crear_Orden/menu.php">🛒 Crear Orden</a></li>

    <li><a href="#" class="nav-link" data-src="../ordenes/ordenes.php">📝 Órdenes</a></li>
    <li><a href="#" class="nav-link" data-src="../detalle_orden/historial_ordenes.php">🧾 Detalle Orden</a></li>
    <li><a href="#" class="nav-link" data-src="../usuarios/usuario.php">👤 Usuarios</a></li>
</ul>
    </nav>
</aside>

<main class="content-wrapper">
    <header class="content-header">
        <h1 id="content-title">🏠 Dashboard</h1>
        <p id="content-sub">Bienvenido al Panel de Administración.</p>
    </header>

    <section class="content-frame-container">
        <iframe id="content-frame" src="home.php" frameborder="0"></iframe>
    </section>
</main>

<footer class="footer">
    <p>© 2025 Restaurante POS | Panel Administrador</p>
</footer>

<script src="panel.js"></script>

</body>
</html>