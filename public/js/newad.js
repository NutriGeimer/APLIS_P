const API = '../api';

document.getElementById('new-admin-form').onsubmit = async (e) => {
    e.preventDefault();
    const inputs = Object.fromEntries(new FormData(e.target).entries());
    const res = await fetch(API + '/add_admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(inputs)
    })
    const j = await res.json()
    if (j.ok) {
        alert('Nuevo Admin Añadido')
        window.location.href = 'admin.html'
    } else {
        alert(j.message || 'Error')
    }
}

document.getElementById("logout").onclick = async () => {
    await fetch(API + "/logout.php", { method: "POST", credentials: "include" });
    window.location.href = "login.html";
};