(function() {
    const currentPage = window.location.pathname;
    if (currentPage.includes('login.html') || currentPage.includes('register.html')) {
        return; 
    }

    async function checkAuth() {
        try {
            const response = await fetch('../api/me.php', {
                method: 'GET',
                credentials: 'include', 
                cache: 'no-store'
            });

            if (!response.ok) {
                window.location.href = new URL('login.html', window.location.href).href;
                return;
            }

            const data = await response.json();
            if (!data.ok || !data.user) {
                window.location.href = new URL('login.html', window.location.href).href;
                return;
            }

            const user = data.user;
            const path = window.location.pathname || '';
            const page = path.substring(path.lastIndexOf('/') + 1).toLowerCase();

            const adminOnlyPages = [
                'admin.html',
                'newad.html',
                'stock.html',
                'restock.html',
                'ventas.html'
            ];

            if (adminOnlyPages.includes(page) && user.rol !== 'admin') {
                window.location.href = new URL('productos.html', window.location.href).href;
                return;
            }

            console.log('Usuario autenticado:', user.nombre, 'rol:', user.rol);

        } catch (error) {
            console.error('Error en autenticación:', error);
            window.location.href = new URL('login.html', window.location.href).href;
        }
    }

    checkAuth();

    window.addEventListener('pageshow', function(ev) {
        checkAuth();
    });

    window.addEventListener('focus', function() {
        checkAuth();
    });

})();
