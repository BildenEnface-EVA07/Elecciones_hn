const API_BASE_URL = window.location.origin + '/Elecciones_hn/backend/api/Dashboard/';
const API_CARGOS_URL = window.location.origin + '/Elecciones_hn/backend/api/Dashboard/get_cargos.php';

let currentPage = 1;
const rowsPerPage = 8;
let totalPages = 1;
let currentPartyId = '';
let currentCargo = '';

async function fetchPartiesAndPopulateFilter() {
    try {
        const response = await fetch(`${API_BASE_URL}get_partidos.php`);
        const data = await response.json();
        const filterPartySelect = document.getElementById('filter-party');
        if (filterPartySelect && data.status === 'success' && data.data) {
            filterPartySelect.innerHTML = '<option value="">Todos los Partidos</option>';
            data.data.forEach(party => {
                const option = document.createElement('option');
                option.value = party.idPartido;
                option.textContent = party.nombrePartido;
                filterPartySelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error fetching parties:', error);
    }
}

async function fetchCargosAndPopulateFilter() {
    try {
        const response = await fetch(API_CARGOS_URL);
        const data = await response.json();
        const filterCargoSelect = document.getElementById('filter-cargo');
        if (filterCargoSelect && data.status === 'success' && data.data) {
            filterCargoSelect.innerHTML = '<option value="">Todas las Candidaturas</option>';
            data.data.forEach(cargo => {
                const option = document.createElement('option');
                option.value = cargo;
                option.textContent = cargo;
                filterCargoSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error fetching cargos:', error);
    }
}

async function renderConsultarVotosView() {
    const dynamicContent = document.getElementById('dynamic-content');
    dynamicContent.innerHTML = `
        <div class="consultar-votos-container">
            <h1 class="page-title">Consultar Votos</h1>

            <div class="filters-container">
                <div class="filter-group">
                    <label for="filter-party">Filtrar por:</label>
                    <select id="filter-party">
                        <option value="">Todos los Partidos</option>
                        </select>
                </div>
                <div class="filter-group">
                    <select id="filter-cargo">
                        <option value="">Todas las Candidaturas</option>
                        </select>
                </div>
                <button id="clear-filters-btn" class="btn-clear-filters">Limpiar Filtros</button>
            </div>

            <div class="table-container">
                <table id="votos-table">
                    <thead>
                        <tr>
                            <th>Partido Político</th>
                            <th>Nombre del Candidato</th>
                            <th>Cargo Aspirante</th>
                            <th>Departamento</th>
                            <th>Municipio</th>
                            <th>Total Votos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="6" style="text-align: center;">Cargando votos...</td></tr>
                    </tbody>
                </table>
                <div class="no-data-message" style="display: none;">No se encontraron votos para los filtros seleccionados.</div>
            </div>

            <div class="pagination-controls">
                <button id="prev-page" class="btn-pagination">Anterior</button>
                <span id="page-info">Página 1 de 1</span>
                <button id="next-page" class="btn-pagination">Siguiente</button>
            </div>
        </div>
    `;

    const filterPartySelect = document.getElementById('filter-party');
    const filterCargoSelect = document.getElementById('filter-cargo');
    const clearFiltersBtn = document.getElementById('clear-filters-btn');
    const tbody = document.querySelector('#votos-table tbody');
    const noDataMessage = document.querySelector('.consultar-votos-container .no-data-message');
    const pageInfoSpan = document.getElementById('page-info');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');

    await fetchPartiesAndPopulateFilter();
    await fetchCargosAndPopulateFilter();
    await fetchVotes();

    filterPartySelect.addEventListener('change', () => {
        currentPartyId = filterPartySelect.value;
        currentPage = 1;
        fetchVotes();
    });

    filterCargoSelect.addEventListener('change', () => {
        currentCargo = filterCargoSelect.value;
        currentPage = 1;
        fetchVotes();
    });

    clearFiltersBtn.addEventListener('click', () => {
        filterPartySelect.value = '';
        filterCargoSelect.value = '';
        currentPartyId = '';
        currentCargo = '';
        currentPage = 1;
        fetchVotes();
    });

    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            fetchVotes();
        }
    });

    nextPageBtn.addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            fetchVotes();
        }
    });
}

async function fetchVotes() {
    const tbody = document.querySelector('#votos-table tbody');
    const noDataMessage = document.querySelector('.consultar-votos-container .no-data-message');
    const pageInfoSpan = document.getElementById('page-info');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');

    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Cargando votos...</td></tr>';
    noDataMessage.style.display = 'none';

    try {
        const queryParams = new URLSearchParams({
            page: currentPage,
            limit: rowsPerPage,
            partyId: currentPartyId,
            cargo: currentCargo
        }).toString();

        const response = await fetch(`${API_BASE_URL}votos_data.php?${queryParams}`);
        const data = await response.json();

        tbody.innerHTML = '';
        if (data.status === 'success' && data.data.length > 0) {
            data.data.forEach(voto => {
                const row = tbody.insertRow();
                row.insertCell().textContent = voto.nombrePartido;
                row.insertCell().textContent = voto.nombreCandidato;
                row.insertCell().textContent = voto.cargo;
                row.insertCell().textContent = voto.nombreDepartamento || 'N/A';
                row.insertCell().textContent = voto.nombreMunicipio || 'N/A';
                row.insertCell().textContent = voto.totalVotos;
            });
            noDataMessage.style.display = 'none';

            totalPages = data.totalPages;
            pageInfoSpan.textContent = `Página ${data.currentPage} de ${totalPages}`;

            prevPageBtn.disabled = data.currentPage <= 1;
            nextPageBtn.disabled = data.currentPage >= totalPages;

        } else {
            tbody.innerHTML = '';
            noDataMessage.style.display = 'block';
            pageInfoSpan.textContent = `Página 1 de 1`;
            prevPageBtn.disabled = true;
            nextPageBtn.disabled = true;
        }

    } catch (error) {
        console.error('Error al obtener los votos:', error);
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error al cargar los datos. Por favor, intente de nuevo.</td></tr>';
        noDataMessage.style.display = 'none';
        pageInfoSpan.textContent = `Página 1 de 1`;
        prevPageBtn.disabled = true;
        nextPageBtn.disabled = true;
    }
}