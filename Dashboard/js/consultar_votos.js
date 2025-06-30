// js/consultar_votos.js

const API_BASE_URL = window.location.origin + '/Elecciones_hn/backend/api/Dashboard/'; 

let currentPage = 1;
const rowsPerPage = 8; // 8 filas de datos + 1 fila de encabezado
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
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
                <p id="no-data-message" style="display: none; text-align: center; margin-top: 20px;">No se encontraron votos con los filtros aplicados.</p>
            </div>

            <div class="pagination-controls">
                <button id="prev-page-btn" class="btn-pagination" disabled>Anterior</button>
                <span id="page-info">Página 1 de 1</span>
                <button id="next-page-btn" class="btn-pagination" disabled>Siguiente</button>
            </div>
        </div>
    `;

    setupConsultarVotosListeners();
    await loadFilterOptions();
    await fetchVotos();
}

function setupConsultarVotosListeners() {
    const filterPartySelect = document.getElementById('filter-party');
    const filterCargoSelect = document.getElementById('filter-cargo');
    const clearFiltersBtn = document.getElementById('clear-filters-btn');
    const prevPageBtn = document.getElementById('prev-page-btn');
    const nextPageBtn = document.getElementById('next-page-btn');

    if (filterPartySelect) {
        filterPartySelect.addEventListener('change', async (event) => {
            currentPartyId = event.target.value;
            currentPage = 1; 
            await fetchVotos();
        });
    }

    if (filterCargoSelect) {
        filterCargoSelect.addEventListener('change', async (event) => {
            currentCargo = event.target.value;
            currentPage = 1; 
            await fetchVotos();
        });
    }

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', async () => {
            currentPartyId = '';
            currentCargo = '';
            currentPage = 1;
            if (filterPartySelect) filterPartySelect.value = '';
            if (filterCargoSelect) filterCargoSelect.value = '';
            await fetchVotos();
        });
    }

    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', async () => {
            if (currentPage > 1) {
                currentPage--;
                await fetchVotos();
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', async () => {
            if (currentPage < totalPages) {
                currentPage++;
                await fetchVotos();
            }
        });
    }
}

async function loadFilterOptions() {
    const filterPartySelect = document.getElementById('filter-party');
    const filterCargoSelect = document.getElementById('filter-cargo');

    // Cargar Partidos
    if (filterPartySelect) {
        try {
            const response = await fetch(`${API_BASE_URL}get_partidos.php`);
            const data = await response.json();
            if (data.status === 'success' && data.data) {
                data.data.forEach(party => {
                    const option = document.createElement('option');
                    option.value = party.idPartido;
                    option.textContent = party.nombrePartido;
                    filterPartySelect.appendChild(option);
                });
            } else {
                console.error('Error al cargar partidos:', data.message || 'Error desconocido');
            }
        } catch (error) {
            console.error('Error de red al cargar partidos:', error);
        }
    }

    // Cargar Cargos
    if (filterCargoSelect) {
        try {
            const response = await fetch(`${API_BASE_URL}get_cargos.php`);
            const data = await response.json();
            if (data.status === 'success' && data.data) {
                data.data.forEach(cargo => {
                    const option = document.createElement('option');
                    option.value = cargo;
                    option.textContent = cargo;
                    filterCargoSelect.appendChild(option);
                });
            } else {
                console.error('Error al cargar cargos:', data.message || 'Error desconocido');
            }
        } catch (error) {
            console.error('Error de red al cargar cargos:', error);
        }
    }
}

async function fetchVotos() {
    const tbody = document.querySelector('#votos-table tbody');
    const noDataMessage = document.getElementById('no-data-message');
    const pageInfoSpan = document.getElementById('page-info');
    const prevPageBtn = document.getElementById('prev-page-btn');
    const nextPageBtn = document.getElementById('next-page-btn');

    if (!tbody || !noDataMessage || !pageInfoSpan || !prevPageBtn || !nextPageBtn) {
        console.error('Elementos del DOM de la tabla o paginación no encontrados.');
        return;
    }

    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Cargando votos...</td></tr>';
    noDataMessage.style.display = 'none';

    let url = `${API_BASE_URL}votos_data.php?page=${currentPage}&limit=${rowsPerPage}`;
    if (currentPartyId) {
        url += `&partyId=${currentPartyId}`;
    }
    if (currentCargo) {
        url += `&cargo=${currentCargo}`;
    }

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.status === 'success' && data.data && data.data.length > 0) {
            tbody.innerHTML = ''; 
            data.data.forEach(voto => {
                const row = tbody.insertRow();
                row.insertCell().textContent = voto.nombrePartido;
                row.insertCell().textContent = voto.nombreCandidato;
                row.insertCell().textContent = voto.cargo;
                row.insertCell().textContent = voto.nombreDepartamento || 'N/A';
                row.insertCell().textContent = voto.nombreMunicipio || 'N/A';
            });
            noDataMessage.style.display = 'none';

            totalPages = data.totalPages;
            pageInfoSpan.textContent = `Página ${data.currentPage} de ${totalPages}`;

            prevPageBtn.disabled = data.currentPage <= 1;
            nextPageBtn.disabled = data.currentPage >= totalPages;

        } else {
            tbody.innerHTML = ''; // Limpiar cualquier fila existente
            noDataMessage.style.display = 'block'; // Mostrar mensaje de no datos
            pageInfoSpan.textContent = `Página 1 de 1`;
            prevPageBtn.disabled = true;
            nextPageBtn.disabled = true;
        }

    } catch (error) {
        console.error('Error al obtener los votos:', error);
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Error al cargar los datos. Por favor, intente de nuevo.</td></tr>';
        noDataMessage.style.display = 'none';
        pageInfoSpan.textContent = `Página 1 de 1`;
        prevPageBtn.disabled = true;
        nextPageBtn.disabled = true;
    }
}