document.addEventListener('DOMContentLoaded', () => {
    const dynamicContent = document.getElementById('dynamic-content');
    const navDashboard = document.getElementById('nav-dashboard');
    const navConsultarVotos = document.getElementById('nav-consultar-votos');
    const navGestionEleccionesItem = document.getElementById('nav-gestion-elecciones-item');
    const navGestionElecciones = document.getElementById('nav-gestion-elecciones');
    const navVerColaboradoresItem = document.getElementById('nav-ver-colaboradores-item');
    const navVerColaboradores = document.getElementById('nav-ver-colaboradores');

    const userProfileName = document.querySelector('.user-profile .user-name');
    const welcomeTitle = document.querySelector('.welcome-title');
    const roleInfo = document.querySelector('.role-info');
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-mobile-toggle');

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('expanded');
        });
    }
    
    let currentView = 'dashboard';
    let currentUserRole = '';

    function updateUserInfo(name, role) {
        userProfileName.textContent = name;
        welcomeTitle.textContent = `Bienvenido, ${name.split(' ')[0]}`;
        roleInfo.textContent = `Su rol como usuario es: ${role}`;
        currentUserRole = role;
        updateSidebarVisibility(role);
    }

    function updateSidebarVisibility(role) {
        if (navGestionEleccionesItem) {
            if (role === 'admin') {
                navGestionEleccionesItem.style.display = 'list-item';
            } else {
                navGestionEleccionesItem.style.display = 'none';
            }
        }
        if (navVerColaboradoresItem) {
            if (role === 'admin') {
                navVerColaboradoresItem.style.display = 'list-item';
            } else {
                navVerColaboradoresItem.style.display = 'none';
            }
        }
    }

    updateUserInfo("Heyden Aldana", "admin");

    async function loadView(viewName) {
        dynamicContent.innerHTML = '';

        document.querySelector('.main-nav a.active')?.classList.remove('active');

        if (viewName === 'dashboard') {
            navDashboard.classList.add('active');
            await renderDashboardView();
        } else if (viewName === 'consultar-votos') {
            navConsultarVotos.classList.add('active');
            await renderConsultarVotosView();
        } else if (viewName === 'gestion-elecciones' && currentUserRole === 'admin') {
            navGestionElecciones.classList.add('active');
            await renderGestionEleccionesView();
        } else if (viewName === 'ver-colaboradores' && currentUserRole === 'admin') {
            navVerColaboradores.classList.add('active');
            await renderVerColaboradoresView();
        }
        currentView = viewName;
    }

    navDashboard.addEventListener('click', (e) => {
        e.preventDefault();
        loadView('dashboard');
    });

    navConsultarVotos.addEventListener('click', (e) => {
        e.preventDefault();
        loadView('consultar-votos');
    });

    if (navGestionElecciones) {
        navGestionElecciones.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentUserRole === 'admin') {
                loadView('gestion-elecciones');
            } else {
                alert('No tienes permisos para acceder a esta sección.');
            }
        });
    }

    if (navVerColaboradores) {
        navVerColaboradores.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentUserRole === 'admin') {
                loadView('ver-colaboradores');
            } else {
                alert('No tienes permisos para acceder a esta sección.');
            }
        });
    }

    loadView('dashboard');
});