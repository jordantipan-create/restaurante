// Crear_Orden/carrito.js
(() => {
    // elementos
    const agregarBtns = document.querySelectorAll('.agregar-btn');
    const carritoList = document.getElementById('carritoList');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    const btnFinalizar = document.getElementById('btnFinalizar');
    const btnVaciar = document.getElementById('btnVaciar');
    const selectCliente = document.getElementById('selectCliente');

    // obtener carrito desde sessionStorage
    function obtenerCarrito() {
        const raw = sessionStorage.getItem('carrito');
        return raw ? JSON.parse(raw) : [];
    }

    function guardarCarrito(cart) {
        sessionStorage.setItem('carrito', JSON.stringify(cart));
        renderCarrito();
    }

    // agregar producto
    function agregarProducto(producto) {
        const cart = obtenerCarrito();
        const existe = cart.find(it => it.id_producto === producto.id_producto);
        if (existe) {
            if (producto.stock && existe.cantidad + 1 > producto.stock) {
                alert('No hay suficiente stock.');
                return;
            }
            existe.cantidad += 1;
            existe.subtotal = parseFloat((existe.cantidad * existe.precio).toFixed(2));
        } else {
            producto.cantidad = 1;
            producto.subtotal = parseFloat(producto.precio);
            cart.push(producto);
        }
        guardarCarrito(cart);
    }

    // eliminar producto
    function eliminarProducto(id) {
        let cart = obtenerCarrito();
        cart = cart.filter(it => it.id_producto !== id);
        guardarCarrito(cart);
    }

    // cambiar cantidad
    function cambiarCantidad(id, delta) {
        const cart = obtenerCarrito();
        const it = cart.find(x => x.id_producto === id);
        if (!it) return;
        const nueva = it.cantidad + delta;
        if (nueva < 1) return;
        if (it.stock && nueva > it.stock) {
            alert('No hay suficiente stock.');
            return;
        }
        it.cantidad = nueva;
        it.subtotal = parseFloat((it.cantidad * it.precio).toFixed(2));
        guardarCarrito(cart);
    }

    // calcular totales
    function calcularTotales() {
        const cart = obtenerCarrito();
        const subtotal = cart.reduce((s, x) => s + parseFloat(x.subtotal), 0);
        const total = subtotal; // puedes añadir impuestos aquí
        return {
            subtotal: subtotal,
            total: total
        };
    }

    // render
    function renderCarrito() {
        const cart = obtenerCarrito();
        carritoList.innerHTML = '';
        if (cart.length === 0) {
            carritoList.innerHTML = '<p class="empty">No hay productos en el carrito.</p>';
            subtotalEl.textContent = '$0.00';
            totalEl.textContent = '$0.00';
            btnFinalizar.disabled = true;
            btnVaciar.disabled = true;
            return;
        }

        const table = document.createElement('table');
        table.className = 'mini-table';
        table.innerHTML = '<thead><tr><th>Producto</th><th>Cant</th><th>Precio</th><th>Subtotal</th><th></th></tr></thead>';
        const tbody = document.createElement('tbody');

        cart.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(it.nombre)}</td>
                <td>
                    <button class="qty" data-action="minus" data-id="${it.id_producto}">-</button>
                    <span class="cant">${it.cantidad}</span>
                    <button class="qty" data-action="plus" data-id="${it.id_producto}">+</button>
                </td>
                <td>$${parseFloat(it.precio).toFixed(2)}</td>
                <td>$${parseFloat(it.subtotal).toFixed(2)}</td>
                <td><button class="btn small danger remove" data-id="${it.id_producto}">X</button></td>
            `;
            tbody.appendChild(tr);
        });

        table.appendChild(tbody);
        carritoList.appendChild(table);

        const totals = calcularTotales();
        subtotalEl.textContent = '$' + totals.subtotal.toFixed(2);
        totalEl.textContent = '$' + totals.total.toFixed(2);

        btnFinalizar.disabled = false;
        btnVaciar.disabled = false;

        // eventos en botones
        document.querySelectorAll('.remove').forEach(b => b.addEventListener('click', e => {
            const id = parseInt(e.target.getAttribute('data-id'));
            eliminarProducto(id);
        }));

        document.querySelectorAll('.qty').forEach(b => b.addEventListener('click', e => {
            const action = e.target.getAttribute('data-action');
            const id = parseInt(e.target.getAttribute('data-id'));
            if (action === 'plus') cambiarCantidad(id, +1);
            else cambiarCantidad(id, -1);
        }));
    }

    // escapeHTML simple
    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>"']/g, function(m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; });
    }

    // inicializar botones agregar
    agregarBtns.forEach(btn => {
        btn.addEventListener('click', function(){
            const id = parseInt(this.getAttribute('data-id'));
            const nombre = this.getAttribute('data-nombre');
            const precio = parseFloat(this.getAttribute('data-precio'));
            const stock = parseInt(this.getAttribute('data-stock')) || null;
            agregarProducto({id_producto: id, nombre: nombre, precio: precio, stock: stock});
        });
    });

    // vaciar carrito
    btnVaciar.addEventListener('click', () => {
        if (!confirm('Vaciar el carrito?')) return;
        sessionStorage.removeItem('carrito');
        renderCarrito();
    });

    // finalizar -> redirige a finalizar.php con cliente y carrito data (en POST)
    btnFinalizar.addEventListener('click', () => {
        const cart = obtenerCarrito();
        if (cart.length === 0) return alert('Carrito vacío');
        const cliente = selectCliente.value || 1;

        // crear form dinámico para enviar por POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'finalizar.php';

        const inputCart = document.createElement('input');
        inputCart.type = 'hidden';
        inputCart.name = 'cart';
        inputCart.value = JSON.stringify(cart);
        form.appendChild(inputCart);

        const inputCliente = document.createElement('input');
        inputCliente.type = 'hidden';
        inputCliente.name = 'id_cliente';
        inputCliente.value = cliente;
        form.appendChild(inputCliente);

        document.body.appendChild(form);
        form.submit();
    });

    // init
    renderCarrito();
})();
