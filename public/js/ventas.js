const API = "../api";

async function loadSales() {
    const res = await fetch(API + "/ventas.php");
    const data = await res.json();

    const cont = document.getElementById("sales-list");
    const totalEl = document.getElementById("total-ingresos");
    cont.innerHTML = "";

    if (!data.ok) {
        cont.innerHTML = "<p>Error al cargar ventas</p>";
        return;
    }

    let total = 0;
    const map = {};

    data.items.forEach(item => {
        total += item.precio_unitario * item.cantidad;

        if (!map[item.id]) map[item.id] = {
            cliente: item.cliente,
            fecha: item.fecha,
            total: item.total,
            items: []
        };

        map[item.id].items.push(item);
    });

    totalEl.textContent = "$" + total.toFixed(2);

    Object.entries(map).forEach(([id, venta]) => {
        let html = `
            <div class="purchase-card">
                <h5>Venta #${id} — Cliente: ${venta.cliente}</h5>
                <p class="text-muted">${venta.fecha}</p>
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
                <h5>Total: <b class="text-success">$${venta.total}</b></h5>
            </div>
        `;

        cont.innerHTML += html;
    });
}

loadSales();

document.getElementById("logout").onclick = async () => {
    await fetch(API + "/logout.php", { method: "POST", credentials: "include" });
    window.location.href = "login.html";
};