const API = "../api";

async function cargarRestocks() {
    const res = await fetch(API + "/restock_get.php");
    const data = await res.json();

    if (!data.ok) return alert("Error al cargar restocks");

    document.getElementById("ingresos-total").innerText = 
        "$" + data.total_ventas.toFixed(2);

    document.getElementById("restock-list").innerHTML = `
        <h5 class="text-danger">Gasto total en restocks: <b>$${data.total_gasto.toFixed(2)}</b></h5>
        <h5 class="${data.balance >= 0 ? 'text-success' : 'text-danger'}">
            Balance: <b>$${data.balance.toFixed(2)}</b>
        </h5>
        <hr>
    `;

    data.restocks.forEach(r => {
        document.getElementById("restock-list").innerHTML += `
            <div class="card p-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="../uploads/${r.imagen}" width="60" class="rounded">
                    <div>
                        <b>${r.nombre}</b><br>
                        Cantidad agregada: ${r.cantidad}<br>
                        Costo unitario: $${r.costo_unitario}<br>
                        <small class="text-muted">${r.fecha}</small>
                    </div>
                </div>
            </div>
        `;
    });
}

cargarRestocks();

document.getElementById("logout").onclick = async () => {
    await fetch(API + "/logout.php", { method: "POST", credentials: "include" });
    window.location.href = "login.html";
};