const API = '../api';

// Cargar productos al iniciar
document.addEventListener("DOMContentLoaded", cargarProductos);

async function cargarProductos() {
    const container = document.getElementById("productos");

    try {
        const res = await fetch(API + "/products.php", {
            method: "GET",
            credentials: "include"
        });

        const j = await res.json();

        if (!j.ok) {
            container.innerHTML = `<div class="alert alert-danger">Error: ${j.message}</div>`;
            return;
        }

        const productos = j.productos;

        container.innerHTML = productos
            .map(p => `
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">

                        <img src="../uploads/${p.imagen}" 
                            class="card-img-top"
                            style="height: 200px; object-fit: cover;">

                        <div class="card-body">

                            <h5 class="card-title fw-semibold">${p.nombre}</h5>

                            <p class="text-success fw-bold mb-2" style="font-size: 1.1rem;">
                                $${Number(p.precio).toFixed(2)}
                            </p>

                            <p class="text-muted small mb-1">
                                <b>Stock:</b> ${p.stock}
                            </p>

                            <p class="small text-muted" style="min-height: 40px;">
                                ${p.descripcion}
                            </p>

                            <button 
                                class="btn btn-success w-100 add-cart mt-2"
                                data-id="${p.id}">
                                🛒 Agregar al carrito
                            </button>

                        </div>
                    </div>
                </div>
            `)
            .join("");

        activarBotonesCarrito();

    } catch (err) {
        container.innerHTML = `
            <div class="alert alert-danger">No se pudieron cargar los productos.</div>
        `;
        console.error(err);
    }
}

function activarBotonesCarrito() {
    document.querySelectorAll(".add-cart").forEach(btn => {
        btn.onclick = async () => {
            const id = btn.dataset.id;

            const res = await fetch(API + "/carrito_add.php", {
                method: "POST",
                credentials: "include",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ product_id: id })   // ← CORREGIDO
            });

            const j = await res.json();

            if (j.ok) {
                mostrarAlerta("Producto añadido al carrito", "success");
                actualizarContador();
            } else {
                mostrarAlerta(j.message || "Error al agregar", "danger");
            }
        };
    });
}

function mostrarAlerta(msg, tipo = "success") {
    const cont = document.getElementById("alert-container");
    const div = document.createElement("div");

    div.className = `alert alert-${tipo} shadow`;
    div.textContent = msg;

    cont.appendChild(div);

    setTimeout(() => div.remove(), 3000);
}

async function actualizarContador() {
    const res = await fetch(API + "/carrito_count.php", {
        method: "GET",
        credentials: "include"
    });

    const j = await res.json();
    if (j.ok) {
        document.getElementById("cart-count").textContent = j.count;
    }
}

document.getElementById("logout").onclick = async () => {
    await fetch(API + "/logout.php", { method: "POST", credentials: "include" });
    window.location.href = "login.html";
};
