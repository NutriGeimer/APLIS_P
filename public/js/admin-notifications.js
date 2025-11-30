(function(){
    const ADMIN_PAGES = ['admin.html','newad.html','stock.html','restock.html','ventas.html'];

    const page = (window.location.pathname || '').split('/').pop().toLowerCase();
    if (!ADMIN_PAGES.includes(page)) return;

    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = './css/admin-notifications.css';
    document.head.appendChild(link);

    const container = document.createElement('div');
    container.id = 'admin-notify-tab';

    const toggle = document.createElement('button');
    toggle.id = 'admin-notify-toggle';
    toggle.type = 'button';
    toggle.innerHTML = '🔔<span id="admin-notify-count">0</span>';

    const panel = document.createElement('div');
    panel.id = 'admin-notify-panel';
    panel.innerHTML = `
        <h4>Productos con stock bajo</h4>
        <ul id="admin-notify-list"></ul>
        <div class="admin-notify-footer"></div>
    `;

    container.appendChild(toggle);
    container.appendChild(panel);
    document.body.appendChild(container);

    const countEl = container.querySelector('#admin-notify-count');

    function togglePanel(){
        if (panel.style.display === 'none' || panel.style.display === ''){
            panel.style.display = 'block';
            toggle.classList.add('active');
        } else {
            panel.style.display = 'none';
            toggle.classList.remove('active');
        }
    }

    toggle.addEventListener('click', function(e){
        e.preventDefault();
        togglePanel();
    });

    async function fetchLowStock(){
        try{
            const res = await fetch('../api/products.php', { credentials: 'include', cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.ok || !Array.isArray(data.productos)) return;

            const low = data.productos.filter(p => Number(p.stock) <= 5);

            countEl.textContent = String(low.length);

            const footer = panel.querySelector('.admin-notify-footer');
            if (low.length === 0){
                toggle.classList.remove('active');
                panel.innerHTML = '<div class="admin-notify-empty">No hay notificaciones.</div>';
                return;
            }

            panel.innerHTML = `\
                <h4>Productos con stock bajo</h4>\
                <ul id="admin-notify-list"></ul>\
                <div class="admin-notify-footer"></div>\
            `;

            const listElNew = panel.querySelector('#admin-notify-list');
            const footerNew = panel.querySelector('.admin-notify-footer');

            toggle.classList.add('active');

            low.forEach(p => {
                const li = document.createElement('li');
                li.className = 'admin-notify-item';
                const name = document.createElement('div');
                name.innerHTML = `<div><b>${escapeHtml(p.nombre)}</b><div style="font-size:12px;color:#666">ID: ${p.id}</div></div>`;
                const right = document.createElement('div');
                right.innerHTML = `<div>Stock: <b>${p.stock}</b></div>`;
                li.appendChild(name);
                li.appendChild(right);
                listElNew.appendChild(li);
            });

            const btn = document.createElement('a');
            btn.className = 'btn-primary-blue';
            btn.href = 'stock.html';
            btn.textContent = 'Hacer restock';
            footerNew.appendChild(btn);

        }catch(e){
            console.error('admin-notify error', e);
        }
    }

    function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    fetchLowStock();
    const interval = setInterval(fetchLowStock, 15000);
    window.addEventListener('pageshow', fetchLowStock);
    window.addEventListener('focus', fetchLowStock);

})();
