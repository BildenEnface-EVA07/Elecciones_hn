document.addEventListener('DOMContentLoaded', () => {
    const dynamicContent = document.getElementById('dynamic-content');
    const navDashboard = document.getElementById('nav-dashboard');
    const navConsultarVotos = document.getElementById('nav-consultar-votos');
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

    function updateUserInfo(name, role) {
        userProfileName.textContent = name;
        welcomeTitle.textContent = `Bienvenido, ${name.split(' ')[0]}`;
        roleInfo.textContent = `Su rol como usuario es: ${role}`;
    }

    updateUserInfo("Heyden Aldana", "Colaborador");

    async function loadView(viewName) {
        dynamicContent.innerHTML = '';

        document.querySelector('.main-nav a.active')?.classList.remove('active');

        if (viewName === 'dashboard') {
            navDashboard.classList.add('active');
            await renderDashboardView();
        } else if (viewName === 'consultar-votos') {
            navConsultarVotos.classList.add('active');
            await renderConsultarVotosView();
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

    loadView('dashboard');
});