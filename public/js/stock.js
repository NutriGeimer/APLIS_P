const API = "../api";

document.addEventListener("DOMContentLoaded", () => {
    cargarProductos();
});

document.getElementById("logout").onclick = async () => {
    await fetch(API + "/logout.php", { method: "POST", credentials: "include" });
    window.location.href = "login.html";
};

document.getElementById("product-form").onsubmit = async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target); // AHORA se envían archivos reales

    const res = await fetch(API + "/add_product.php", {
        method: "POST",
        credentials: "include",
        body: formData
    });

    const j = await res.json().catch(() => null);

    if (!j) return mostrarAlerta("Error inesperado", "danger");

    if (!j.ok) return mostrarAlerta(j.message, "danger");

    mostrarAlerta("Producto agregado correctamente ✔", "success");

    e.target.reset();
    cargarProductos();
};

async function cargarProductos() {
    const container = document.getElementById("productos");
    container.innerHTML = `<div class="text-center py-4">Cargando productos...</div>`;

    const res = await fetch(API + "/products.php", { credentials: "include" });
    const data = await res.json();

    if (!data.ok) {
        container.innerHTML = `<p class="text-danger">Error al cargar productos</p>`;
        return;
    }

    const productos = data.productos;

    if (!productos || productos.length === 0) {
        container.innerHTML = `<p class="text-center text-muted">No hay productos aún.</p>`;
        return;
    }

    container.innerHTML = productos
        .map(
            p => `
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100 border-0">

                    <img src="../uploads/${p.imagen}" 
                        class="card-img-top"
                        style="height: 200px; object-fit: cover; border-radius: 10px 10px 0 0;">

                    <div class="card-body">

                        <h5 class="card-title fw-semibold">${p.nombre}</h5>

                        <p class="text-success fw-bold mb-3" style="font-size: 1.1rem;">
                            $${Number(p.precio).toFixed(2)}
                        </p>

                        <p class="text-muted mb-1"><b>Stock:</b> ${p.stock}</p>

                        <p class="small text-muted" style="min-height: 40px;">
                            ${p.descripcion}
                        </p>

                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-primary flex-fill"
                                onclick="mostrarEditarProducto(${p.id}, '${p.nombre}', ${p.precio}, '${p.descripcion}', '${p.imagen}')">
                                Editar
                            </button>

                            <button class="btn btn-warning flex-fill"
                                onclick="mostrarRestock(${p.id})">
                                Restock
                            </button>

                            <button class="btn btn-danger flex-fill"
                                onclick="eliminarProducto(${p.id})">
                                Eliminar
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        `
        )
        .join("");
}

async function eliminarProducto(id) {
    if (!confirm("¿Seguro que deseas eliminar este producto?")) return;

    const res = await fetch(API + "/delete_product.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ id })
    });

    const j = await res.json();

    if (j.ok) {
        mostrarAlerta("Producto eliminado ✔", "warning");
        cargarProductos();
    } else {
        mostrarAlerta(j.message || "Error al eliminar producto", "danger");
    }
}

function mostrarAlerta(msg, tipo = "info") {
    document.getElementById("alert-container").innerHTML = `
        <div class="alert alert-${tipo}">${msg}</div>
    `;
}

let modalEditar;

document.addEventListener("DOMContentLoaded", () => {
    modalEditar = new bootstrap.Modal(document.getElementById("modalEditar"));
    cargarProductos();
});

function mostrarEditarProducto(id, nombre, precio, descripcion, stock, imagen) {
    document.getElementById("edit-id").value = id;
    document.getElementById("edit-nombre").value = nombre;
    document.getElementById("edit-precio").value = precio;
    document.getElementById("edit-desc").value = descripcion;
    document.getElementById("edit-stock").value = stock;

    modalEditar.show();
}

async function guardarEdicion() {
    const form = new FormData();
    form.append("id", document.getElementById("edit-id").value);
    form.append("nombre", document.getElementById("edit-nombre").value);
    form.append("precio", document.getElementById("edit-precio").value);
    form.append("descripcion", document.getElementById("edit-desc").value);
    form.append("stock", document.getElementById("edit-stock").value);

    const imagen = document.getElementById("edit-imagen").files[0];
    if (imagen) {
        form.append("imagen", imagen);
    }

    const res = await fetch(API + "/update_product.php", {
        method: "POST",
        credentials: "include",
        body: form
    });

    const j = await res.json().catch(() => null);

    if (!j || !j.ok) {
        mostrarAlerta(j?.message || "Error al actualizar producto", "danger");
        return;
    }

    mostrarAlerta("Producto actualizado ✔", "success");
    modalEditar.hide();
    cargarProductos();
}

let modalRestock = null;

document.addEventListener("DOMContentLoaded", () => {
    modalRestock = new bootstrap.Modal(document.getElementById("modalRestock"));
});

function mostrarRestock(id) {
    document.getElementById("restock-id").value = id;
    document.getElementById("restock-cantidad").value = "";
    document.getElementById("restock-costo").value = "";
    modalRestock.show();
}

async function hacerRestock() {
    const id = document.getElementById("restock-id").value;
    const cantidad = Number(document.getElementById("restock-cantidad").value);
    const costo = Number(document.getElementById("restock-costo").value);

    if (cantidad <= 0 || costo <= 0)
        return mostrarAlerta("Datos inválidos", "danger");

    const res = await fetch(API + "/restock.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ product_id: id, cantidad, costo })
    });

    const j = await res.json();

    if (!j.ok) return mostrarAlerta(j.message, "danger");

    mostrarAlerta("Restock aplicado ✔", "success");

    modalRestock.hide();
    cargarProductos();
}