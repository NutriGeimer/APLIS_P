const API = '../api';

function showAlert(message, type = "success") {
    const container = document.getElementById("alert-container");

    const alert = document.createElement("div");
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = "alert";
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    container.appendChild(alert);

    setTimeout(() => {
        alert.classList.remove("show");
        alert.classList.add("hide");
        setTimeout(() => alert.remove(), 500);
    }, 4000);
}

document.getElementById('new-admin-form').onsubmit = async (e) => {
    e.preventDefault();

    const inputs = Object.fromEntries(new FormData(e.target).entries());

    if (!inputs.nombre.trim()) {
        showAlert("El nombre es obligatorio", "danger");
        return;
    }

    if (!inputs.email.includes("@") || !inputs.email.includes(".")) {
        showAlert("El correo electrónico no es válido", "danger");
        return;
    }

    if ((inputs.password || "").length < 6) {
        showAlert("La contraseña debe tener al menos 6 caracteres", "danger");
        return;
    }

    const res = await fetch(API + '/add_admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(inputs)
    });

    const j = await res.json();

    if (j.ok) {
        showAlert('Nuevo admin añadido correctamente', "success");

        setTimeout(() => {
            window.location.href = 'admin.html';
        }, 1500);

    } else {
        showAlert(j.message || 'Error al añadir admin', "danger");
    }
};

document.getElementById("logout").onclick = async () => {
    await fetch(API + "/logout.php", { method: "POST", credentials: "include" });
    window.location.href = "login.html";
};