const API_BASE_URL = window.location.origin + '/Elecciones_hn/backend/api/Dashboard/'; 

let currentPage = 1;
const rowsPerPage = 8;
let totalPages = 1;
let currentPartyId = '';
let currentCargo = '';

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
                        
                    </tbody>
                </table>
                <p id="no-data-message" style="display: none; text-align: center; color: #555; margin-top: 20px;">No hay registro de datos con estos criterios. Cambie los filtros o intente mas tarde</p>
            </div>

            <div class="pagination-controls">
                <button id="prev-page-btn" class="btn-pagination">Anterior</button>
                <span id="page-info">Página 1 de 1</span>
                <button id="next-page-btn" class="btn-pagination">Siguiente</button>
            </div>
        </div>
    `;

    const filterPartySelect = document.getElementById('filter-party');
    const filterCargoSelect = document.getElementById('filter-cargo');
    const clearFiltersBtn = document.getElementById('clear-filters-btn');
    const prevPageBtn = document.getElementById('prev-page-btn');
    const nextPageBtn = document.getElementById('next-page-btn');

    filterPartySelect.addEventListener('change', (e) => {
        currentPartyId = e.target.value;
        currentPage = 1; 
        fetchVotos(currentPage, rowsPerPage, currentPartyId, currentCargo);
    });

    filterCargoSelect.addEventListener('change', (e) => {
        currentCargo = e.target.value;
        currentPage = 1; 
        fetchVotos(currentPage, rowsPerPage, currentPartyId, currentCargo);
    });

    clearFiltersBtn.addEventListener('click', () => {
        currentPartyId = '';
        currentCargo = '';
        filterPartySelect.value = '';
        filterCargoSelect.value = '';
        currentPage = 1;
        fetchVotos(currentPage, rowsPerPage, currentPartyId, currentCargo);
    });

    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            fetchVotos(currentPage, rowsPerPage, currentPartyId, currentCargo);
        }
    });

    nextPageBtn.addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            fetchVotos(currentPage, rowsPerPage, currentPartyId, currentCargo);
        }
    });

    await populateFilters();
    await fetchVotos(currentPage, rowsPerPage, currentPartyId, currentCargo);
}

async function populateFilters() {
    const filterPartySelect = document.getElementById('filter-party');
    const filterCargoSelect = document.getElementById('filter-cargo');

    try {
        const partiesResponse = await fetch(`${API_BASE_URL}get_partidos.php`);
        const partiesData = await partiesResponse.json();
        if (partiesData.status === 'success') {
            partiesData.data.forEach(party => {
                const option = document.createElement('option');
                option.value = party.idPartido;
                option.textContent = party.nombrePartido;
                filterPartySelect.appendChild(option);
            });
        }

        const cargosResponse = await fetch(`${API_BASE_URL}get_cargos.php`);
        const cargosData = await cargosResponse.json();
        if (cargosData.status === 'success') {
            cargosData.data.forEach(cargo => {
                const option = document.createElement('option');
                option.value = cargo.cargo;
                option.textContent = cargo.cargo;
                filterCargoSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar filtros:', error);
    }
}

async function fetchVotos(page, limit, partyId, cargo) {
    const tbody = document.querySelector('#votos-table tbody');
    const noDataMessage = document.getElementById('no-data-message');
    const pageInfoSpan = document.getElementById('page-info');
    const prevPageBtn = document.getElementById('prev-page-btn');
    const nextPageBtn = document.getElementById('next-page-btn');

    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Cargando datos...</td></tr>';
    noDataMessage.style.display = 'none';

    try {
        const response = await fetch(`${API_BASE_URL}votos_data.php?page=${page}&limit=${limit}&partyId=${partyId}&cargo=${cargo}`);
        const data = await response.json();

        if (data.status === 'success' && data.data.length > 0) {
            tbody.innerHTML = ''; 
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