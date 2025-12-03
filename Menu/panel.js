// panel.js

document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos clave
    const navLinks = document.querySelectorAll('.nav-link');
    const contentFrame = document.getElementById('content-frame');
    const contentTitle = document.getElementById('content-title');
    const contentSub = document.getElementById('content-sub');

    // Mapeo de rutas a títulos y subtítulos (para actualizar el encabezado)
    const pageInfo = {
        'home.php': { 
            title: '🏠 Dashboard', 
            subtitle: 'Bienvenido al Panel de Administración.' 
        },
        '../categorias/categorias.php': { 
            title: '📂 Categorías', 
            subtitle: 'Gestión de categorías de productos.' 
        },
        '../clientes/clientes.php': { 
            title: '👥 Clientes', 
            subtitle: 'Gestión de la base de datos de clientes.' 
        },
        '../productos/productos.php': { 
            title: '🍔 Productos', 
            subtitle: 'Gestión y control de artículos del menú.' 
        },
        '../inventario/inventario.php': { 
            title: '📦 Inventario', 
            subtitle: 'Control de existencias e insumos.' 
        },
        '../ordenes/ordenes.php': { 
            title: '📝 Órdenes', 
            subtitle: 'Registro y seguimiento de pedidos.' 
        },
        '../detalle_orden/detalle_orden.php': { 
            title: '🧾 Detalle Orden', 
            subtitle: 'Revisión de contenido de órdenes específicas.' 
        },
        '../usuarios/usuarios.php': { 
            title: '👤 Usuarios', 
            subtitle: 'Gestión de cuentas de acceso.' 
        }
        // Agrega más módulos aquí si es necesario
    };

    /**
     * Función para actualizar el contenido del iframe y el encabezado
     * @param {string} url La ruta del archivo a cargar
     */
    function loadContent(url) {
        // Cargar la nueva URL en el iframe
        contentFrame.src = url;

        // Actualizar el encabezado basado en la información mapeada
        const info = pageInfo[url];
        if (info) {
            contentTitle.textContent = info.title;
            contentSub.textContent = info.subtitle;
        } else {
            // Si la ruta no está mapeada, usa un título genérico
            contentTitle.textContent = 'Página Cargada';
            contentSub.textContent = 'Contenido dinámico cargado.';
        }
    }

    // 1. Manejar el clic en los enlaces de navegación
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            // Obtener la URL del atributo data-src
            const targetSrc = this.getAttribute('data-src');

            // 1.1 Quitar 'active' de todos los enlaces
            navLinks.forEach(nav => nav.classList.remove('active'));

            // 1.2 Añadir 'active' solo al enlace clickeado
            this.classList.add('active');

            // 1.3 Cargar el contenido
            loadContent(targetSrc);
        });
    });

    // 2. Cargar el contenido inicial (el que está activo por defecto en el HTML)
    const initialLink = document.querySelector('.nav-link.active');
    if (initialLink) {
        // Usar la función loadContent para asegurar que el título se inicialice correctamente
        loadContent(initialLink.getAttribute('data-src'));
    }
});