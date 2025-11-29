const API = "../api";

async function loadCart() {
    const res = await fetch(API + "/carrito_list.php");
    const data = await res.json();

    const list = document.getElementById("cart-list");
    const subtotalEl = document.getElementById("subtotal");
    const totalEl = document.getElementById("total");

    list.innerHTML = "";
    let subtotal = 0;

    if (!data.ok || data.items.length === 0) {
        list.innerHTML = `<p class="text-muted">Tu carrito está vacío 🛒</p>`;
        subtotalEl.textContent = "$0.00";
        totalEl.textContent = "$0.00";
        return;
    }

    data.items.forEach(item => {
        subtotal += item.precio * item.cantidad;

        list.innerHTML += `
            <div class="cart-item">
                <img src="../uploads/${item.imagen}">
                <div class="flex-grow-1">
                    <h5 class="mb-1">${item.nombre}</h5>
                    <p class="text-muted small">${item.descripcion}</p>
                    <p class="fw-bold">$${item.precio.toFixed(2)}</p>
                </div>

                <div class="cart-controls d-flex flex-column">
                    <button class="btn btn-sm btn-success" onclick="updateQty(${item.cart_id}, '+')">+</button>
                    <span class="text-center">${item.cantidad}</span>
                    <button class="btn btn-sm btn-warning" onclick="updateQty(${item.cart_id}, '-')">-</button>
                </div>

                <button class="btn btn-danger btn-sm ms-3" onclick="deleteItem(${item.cart_id})">
                    Eliminar
                </button>
            </div>
        `;
    });

    subtotalEl.textContent = "$" + subtotal.toFixed(2);
    totalEl.textContent = "$" + subtotal.toFixed(2);
}

async function addToCart(id) {
    await fetch(API + "/carrito_add.php", {
        method: "POST",
        body: JSON.stringify({ product_id: id })
    });

    updateCartCount();
}

async function updateCartCount() {
    const res = await fetch(API + "/carrito_count.php");
    const data = await res.json();

    const badge = document.getElementById("cart-count");
    if (badge) badge.textContent = data.count;
}

async function updateQty(id, accion) {
    await fetch(API + "/carrito_update.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, accion })
    });

    loadCart();
    updateCartCount();
}

async function deleteItem(id) {
    await fetch(API + "/carrito_delete.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id })
    });

    loadCart();
    updateCartCount();
}

document.getElementById("pay-btn").onclick = async () => {
    const res = await fetch(API + "/crear_venta.php", {
        method: "POST"
    });

    const data = await res.json();

    if (!data.ok) {
        alert(data.message);
        return;
    }

    alert("Compra realizada con éxito 🛒💚");
    loadCart();
};


loadCart();
updateCartCount();

document.getElementById("logout").onclick = async () => {
    await fetch(API + "/logout.php", { method: "POST", credentials: "include" });
    window.location.href = "login.html";
};