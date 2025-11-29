const API = "../api";

async function loadHistory() {
    const res = await fetch(API + "/historial.php");
    const data = await res.json();

    const container = document.getElementById("history-list");
    container.innerHTML = "";

    if (!data.ok || data.items.length === 0) {
        container.innerHTML = "<p>No tienes compras registradas.</p>";
        return;
    }

    // Agrupar por ID de venta
    const ventasMap = {};

    data.items.forEach(item => {
        if (!ventasMap[item.id]) ventasMap[item.id] = {
            fecha: item.fecha,
            total: item.total,
            items: []
        };
        ventasMap[item.id].items.push(item);
    });

    Object.entries(ventasMap).forEach(([id, venta]) => {
        let html = `
            <div class="purchase-card">
                <h5>Venta #${id}</h5>
                <p class="text-muted">${venta.fecha}</p>
                <div>
        `;

        venta.items.forEach(prod => {
            html += `
                <div class="product-item">
                    <img src="../uploads/${prod.imagen}">
                    <div>
                        <b>${prod.nombre}</b>
                        <p class="small">Cantidad: ${prod.cantidad}</p>
                        <p class="small">$${prod.precio_unitario}</p>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
                <h5 class="mt-3 text-success">Total: $${venta.total}</h5>
            </div>
        `;

        container.innerHTML += html;
    });
}

loadHistory();

document.getElementById("logout").onclick = async () => {
    await fetch(API + "/logout.php", { method: "POST", credentials: "include" });
    window.location.href = "login.html";
};