const API='../api';

document.getElementById('form').onsubmit = async (e) => {
    e.preventDefault();
    const inputs = Object.fromEntries(new FormData(e.target).entries());
    const res = await fetch(API + '/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(inputs),
        credentials: 'include'
    });
    const j = await res.json();
    if (j.ok) {
        window.location.href = 'productos.html';
    } else {
        alert(j.message || 'Error al iniciar sesión');
    }
}