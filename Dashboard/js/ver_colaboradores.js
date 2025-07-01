const API_COLABORADORES_URL = window.location.origin + '/Elecciones_hn/backend/api/GestionColaboradores/';

let currentPageColaboradores = 1;
const rowsPerPageColaboradores = 8;
let totalPagesColaboradores = 1;
let currentSearchText = '';
let currentRoleFilter = '';
let currentSortOrder = ''; 

async function renderVerColaboradoresView() {
    const dynamicContent = document.getElementById('dynamic-content');
    dynamicContent.innerHTML = `
        <div class="ver-colaboradores-container">
            <h1 class="page-title">Gestión de Colaboradores</h1>

            <div class="filters-container-colaboradores">
                <input type="text" id="search-collaborator" placeholder="Buscar por DNI o nombre...">
                <select id="filter-role">
                    <option value="">Todos los Roles</option>
                </select>
                <label class="checkbox-container">
                    Ordenar de A-Z
                    <input type="checkbox" id="sort-a-z">
                    <span class="checkmark"></span>
                </label>
                <button id="clear-filters-btn-colaboradores" class="btn-clear-filters">Limpiar Filtros</button>
            </div>

            <div class="table-container">
                <table id="colaboradores-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-colaboradores"></th>
                            <th>Número de Identidad</th>
                            <th>Nombre del Usuario</th>
                            <th>Rol del Usuario</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="5" style="text-align: center;">Cargando colaboradores...</td></tr>
                    </tbody>
                </table>
                <p id="no-data-message-colaboradores" style="text-align: center; display: none;">No hay colaboradores para mostrar con los filtros aplicados.</p>
            </div>

            <div class="pagination-container">
                <button id="prev-page-colaboradores" class="pagination-btn">&lt;</button>
                <span id="page-info-colaboradores">Página 1 de 1</span>
                <button id="next-page-colaboradores" class="pagination-btn">&gt;</button>
            </div>

            <div class="action-buttons-container">
                <button id="btn-toggle-status" class="action-button-colaboradores toggle-status-btn">Deshabilitar</button>
                <button id="btn-edit-collaborator" class="action-button-colaboradores" disabled>Editar</button>
                <button id="btn-add-collaborator" class="action-button-colaboradores">Agregar...</button>
            </div>
        </div>

        <div id="edit-collaborator-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2>Editar información del colaborador</h2>
                <form id="edit-collaborator-form">
                    <div class="form-group">
                        <label for="edit-dni">Número de Identidad:</label>
                        <input type="text" id="edit-dni" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit-name">Nombre del Colaborador:</label>
                        <input type="text" id="edit-name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-role">Rol del Usuario:</label>
                        <select id="edit-role" required>
                            </select>
                    </div>
                    <button type="submit" class="modal-action-button">Actualizar información</button>
                </form>
            </div>
        </div>

        <div id="add-collaborator-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2>Agregar nuevo colaborador</h2>
                <form id="add-collaborator-form">
                    <div class="form-group">
                        <label for="add-dni">Número de Identidad:</label>
                        <input type="text" id="add-dni" placeholder="0501-2003-07600" required>
                        <small class="error-message" id="dni-error"></small>
                    </div>
                    <div class="form-group">
                        <label for="add-name">Nombre Completo:</label>
                        <input type="text" id="add-name" placeholder="Heyden Alfonso Aldana Varela" required>
                    </div>
                    <div class="form-group">
                        <label for="add-email">Correo Electrónico:</label>
                        <input type="email" id="add-email" placeholder="ejemplo1@gmail.com" required>
                        <small class="error-message" id="email-error"></small>
                    </div>
                    <div class="form-group">
                        <label for="add-role">Rol del Usuario:</label>
                        <select id="add-role" required>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add-password">Contraseña:</label>
                        <input type="password" id="add-password" placeholder="***********" required>
                        <small class="error-message" id="password-error"></small>
                    </div>
                    <button type="submit" class="modal-action-button">Crear usuario</button>
                </form>
            </div>
        </div>

        <div id="toast-notification" class="toast"></div>
    `;

    const searchInput = document.getElementById('search-collaborator');
    const roleFilterSelect = document.getElementById('filter-role');
    const sortAZCheckbox = document.getElementById('sort-a-z');
    const clearFiltersBtn = document.getElementById('clear-filters-btn-colaboradores');
    const tbody = document.querySelector('#colaboradores-table tbody');
    const noDataMessage = document.getElementById('no-data-message-colaboradores');
    const prevPageBtn = document.getElementById('prev-page-colaboradores');
    const nextPageBtn = document.getElementById('next-page-colaboradores');
    const pageInfoSpan = document.getElementById('page-info-colaboradores');
    const selectAllCheckbox = document.getElementById('select-all-colaboradores');
    const btnToggleStatus = document.getElementById('btn-toggle-status');
    const btnEditCollaborator = document.getElementById('btn-edit-collaborator');
    const btnAddCollaborator = document.getElementById('btn-add-collaborator');

    const editModal = document.getElementById('edit-collaborator-modal');
    const addModal = document.getElementById('add-collaborator-modal');
    const closeButtons = document.querySelectorAll('.close-button');
    const editForm = document.getElementById('edit-collaborator-form');
    const addForm = document.getElementById('add-collaborator-form');
    const editDniInput = document.getElementById('edit-dni');
    const editNameInput = document.getElementById('edit-name');
    const editRoleSelect = document.getElementById('edit-role');
    const addDniInput = document.getElementById('add-dni');
    const addNameInput = document.getElementById('add-name');
    const addEmailInput = document.getElementById('add-email');
    const addRoleSelect = document.getElementById('add-role');
    const addPasswordInput = document.getElementById('add-password');
    const dniError = document.getElementById('dni-error');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    const toastNotification = document.getElementById('toast-notification');

    let selectedCollaborators = new Set();
    let currentCollaboratorsData = [];

    function showToast(message) {
        toastNotification.textContent = message;
        toastNotification.classList.add('show');
        setTimeout(() => {
            toastNotification.classList.remove('show');
        }, 3000);
    }

    function resetModalForm(formId) {
        document.getElementById(formId).reset();
        document.querySelectorAll(`#${formId} .error-message`).forEach(el => el.textContent = '');
    }

    function closeModal(modalElement) {
        modalElement.style.display = 'none';
        resetModalForm(modalElement === editModal ? 'edit-collaborator-form' : 'add-collaborator-form');
    }

    async function fetchRoles() {
        try {
            const response = await fetch(`${API_COLABORADORES_URL}getRoles.php`);
            const data = await response.json();
            if (data.status === 'success') {
                roleFilterSelect.innerHTML = '<option value="">Todos los Roles</option>';
                editRoleSelect.innerHTML = '';
                addRoleSelect.innerHTML = '';
                data.data.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role;
                    option.textContent = role.charAt(0).toUpperCase() + role.slice(1);
                    roleFilterSelect.appendChild(option);

                    const editOption = option.cloneNode(true);
                    editRoleSelect.appendChild(editOption);

                    const addOption = option.cloneNode(true);
                    addRoleSelect.appendChild(addOption);
                });
            } else {
                console.error('Error fetching roles:', data.message);
            }
        } catch (error) {
            console.error('Network error fetching roles:', error);
        }
    }

    async function fetchColaboradores() {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Cargando colaboradores...</td></tr>';
        noDataMessage.style.display = 'none';
        try {
            const params = new URLSearchParams({
                page: currentPageColaboradores,
                limit: rowsPerPageColaboradores,
                searchText: currentSearchText,
                roleFilter: currentRoleFilter,
                sortOrder: currentSortOrder
            });
            const response = await fetch(`${API_COLABORADORES_URL}getColaboradores.php?${params.toString()}`);
            const data = await response.json();

            if (data.status === 'success' && data.data.length > 0) {
                currentCollaboratorsData = data.data;
                populateTable(data.data);
                updatePagination(data.totalRows, data.currentPage, rowsPerPageColaboradores);
            } else {
                tbody.innerHTML = '';
                noDataMessage.style.display = 'block';
                updatePagination(0, 1, rowsPerPageColaboradores);
                currentCollaboratorsData = [];
            }
            updateActionButtons();
        } catch (error) {
            console.error('Error al obtener los colaboradores:', error);
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Error al cargar los datos. Por favor, intente de nuevo.</td></tr>';
            noDataMessage.style.display = 'none';
            updatePagination(0, 1, rowsPerPageColaboradores);
            currentCollaboratorsData = [];
            updateActionButtons();
        }
    }

    function populateTable(collaborators) {
        tbody.innerHTML = '';
        if (collaborators.length === 0) {
            noDataMessage.style.display = 'block';
            return;
        }
        noDataMessage.style.display = 'none';
        collaborators.forEach(colaborador => {
            const row = tbody.insertRow();
            const checkboxCell = row.insertCell();
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'collaborator-checkbox';
            checkbox.value = colaborador.idPersona;
            checkbox.dataset.estado = colaborador.estado; 
            checkbox.checked = selectedCollaborators.has(colaborador.idPersona.toString());
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    selectedCollaborators.add(colaborador.idPersona.toString());
                } else {
                    selectedCollaborators.delete(colaborador.idPersona.toString());
                }
                updateSelectAllCheckbox();
                updateActionButtons();
            });
            checkboxCell.appendChild(checkbox);
            row.insertCell().textContent = colaborador.dni;
            row.insertCell().textContent = colaborador.nombreCompleto;
            row.insertCell().textContent = colaborador.rol.charAt(0).toUpperCase() + colaborador.rol.slice(1);
            row.insertCell().textContent = colaborador.estado ? 'Activo' : 'Deshabilitado';
        });
        updateSelectAllCheckbox();
    }

    function updatePagination(totalRows, currentPage, rowsPerPage) {
        totalPagesColaboradores = Math.ceil(totalRows / rowsPerPage);
        pageInfoSpan.textContent = `Página ${currentPage} de ${totalPagesColaboradores}`;

        prevPageBtn.disabled = currentPage <= 1;
        nextPageBtn.disabled = currentPage >= totalPagesColaboradores;
    }

    function updateSelectAllCheckbox() {
        const checkboxes = document.querySelectorAll('.collaborator-checkbox');
        let allChecked = true;
        let anyChecked = false;
        if (checkboxes.length === 0) {
            allChecked = false;
        } else {
            checkboxes.forEach(cb => {
                if (!cb.checked) {
                    allChecked = false;
                } else {
                    anyChecked = true;
                }
            });
        }
        selectAllCheckbox.checked = allChecked && anyChecked;
        selectAllCheckbox.indeterminate = anyChecked && !allChecked;
    }

    function updateActionButtons() {
        const selectedCheckboxes = Array.from(document.querySelectorAll('.collaborator-checkbox:checked'));
        if (selectedCheckboxes.length > 0) {
            const firstState = selectedCheckboxes[0].dataset.estado;
            let allSameState = true;
            selectedCheckboxes.forEach(cb => {
                if (cb.dataset.estado !== firstState) {
                    allSameState = false;
                }
            });

            if (allSameState) {
                btnToggleStatus.disabled = false;
                btnToggleStatus.textContent = firstState === '1' ? 'Deshabilitar' : 'Habilitar';
            } else {
                btnToggleStatus.disabled = true;
                btnToggleStatus.textContent = 'Deshabilitar / Habilitar'; 
            }
        } else {
            btnToggleStatus.disabled = true;
            btnToggleStatus.textContent = 'Deshabilitar';
        }
        btnEditCollaborator.disabled = selectedCheckboxes.length !== 1;
    }

    searchInput.addEventListener('input', () => {
        currentSearchText = searchInput.value;
        currentPageColaboradores = 1;
        fetchColaboradores();
    });

    roleFilterSelect.addEventListener('change', () => {
        currentRoleFilter = roleFilterSelect.value;
        currentPageColaboradores = 1;
        fetchColaboradores();
    });

    sortAZCheckbox.addEventListener('change', () => {
        currentSortOrder = sortAZCheckbox.checked ? 'asc' : '';
        currentPageColaboradores = 1;
        fetchColaboradores();
    });

    clearFiltersBtn.addEventListener('click', () => {
        searchInput.value = '';
        roleFilterSelect.value = '';
        sortAZCheckbox.checked = false;
        currentSearchText = '';
        currentRoleFilter = '';
        currentSortOrder = '';
        currentPageColaboradores = 1;
        fetchColaboradores();
    });

    prevPageBtn.addEventListener('click', () => {
        if (currentPageColaboradores > 1) {
            currentPageColaboradores--;
            fetchColaboradores();
        }
    });

    nextPageBtn.addEventListener('click', () => {
        if (currentPageColaboradores < totalPagesColaboradores) {
            currentPageColaboradores++;
            fetchColaboradores();
        }
    });

    selectAllCheckbox.addEventListener('change', (event) => {
        const isChecked = event.target.checked;
        document.querySelectorAll('.collaborator-checkbox').forEach(cb => {
            cb.checked = isChecked;
            if (isChecked) {
                selectedCollaborators.add(cb.value);
            } else {
                selectedCollaborators.delete(cb.value);
            }
        });
        updateActionButtons();
    });

    btnToggleStatus.addEventListener('click', async () => {
        if (selectedCollaborators.size === 0) return;

        const idsToToggle = Array.from(selectedCollaborators);
        try {
            const response = await fetch(`${API_COLABORADORES_URL}toggleColaboradorStatus.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ idPersonas: idsToToggle })
            });
            const data = await response.json();
            if (data.status === 'success') {
                showToast(data.message);
                selectedCollaborators.clear();
                fetchColaboradores();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            alert('Error de red al intentar cambiar el estado.');
        }
    });

    btnEditCollaborator.addEventListener('click', () => {
        const selectedId = Array.from(selectedCollaborators)[0];
        const collaborator = currentCollaboratorsData.find(c => c.idPersona.toString() === selectedId);
        if (collaborator) {
            editDniInput.value = collaborator.dni;
            editNameInput.value = collaborator.nombreCompleto;
            editRoleSelect.value = collaborator.rol;
            editModal.dataset.idPersona = selectedId;
            editModal.style.display = 'flex';
        }
    });

    editForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const idPersona = editModal.dataset.idPersona;
        const nombreCompleto = editNameInput.value;
        const rol = editRoleSelect.value;

        try {
            const response = await fetch(`${API_COLABORADORES_URL}updateColaborador.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    idPersona: idPersona,
                    nombreCompleto: nombreCompleto,
                    rol: rol
                })
            });
            const data = await response.json();
            if (data.status === 'success') {
                showToast(data.message);
                closeModal(editModal);
                selectedCollaborators.clear();
                fetchColaboradores();
            } else {
                alert('Error al actualizar: ' + data.message);
            }
        } catch (error) {
            console.error('Error updating collaborator:', error);
            alert('Error de red al actualizar el colaborador.');
        }
    });

    btnAddCollaborator.addEventListener('click', () => {
        addModal.style.display = 'flex';
    });

    addForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        let isValid = true;

        const dni = addDniInput.value.replace(/-/g, '');
        if (!/^\d{13}$/.test(dni)) {
            dniError.textContent = 'El DNI debe contener exactamente 13 dígitos numéricos.';
            isValid = false;
        } else {
            dniError.textContent = '';
        }

        const email = addEmailInput.value;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            emailError.textContent = 'Ingrese un correo electrónico válido.';
            isValid = false;
        } else {
            emailError.textContent = '';
        }

        const password = addPasswordInput.value;
        if (password.length < 8 ||
            !/[A-Z]/.test(password) ||
            !/[a-z]/.test(password) ||
            !/\d/.test(password) ||
            !/[^A-Za-z0-9]/.test(password)) {
            passwordError.textContent = 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas, un número y un símbolo especial.';
            isValid = false;
        } else {
            passwordError.textContent = '';
        }

        if (!isValid) {
            return;
        }

        const nombreCompleto = addNameInput.value;
        const rol = addRoleSelect.value;
        
        const hashedPassword = await hashPassword(password);

        try {
            const response = await fetch(`${API_COLABORADORES_URL}addColaborador.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    dni: dni,
                    nombreCompleto: nombreCompleto,
                    correoElectronico: email,
                    rol: rol,
                    clave: hashedPassword
                })
            });
            const data = await response.json();
            if (data.status === 'success') {
                showToast(data.message);
                closeModal(addModal);
                fetchColaboradores();
            } else {
                alert('Error al agregar: ' + data.message);
            }
        } catch (error) {
            console.error('Error adding collaborator:', error);
            alert('Error de red al agregar el colaborador.');
        }
    });

    async function hashPassword(password) {
        const msgUint8 = new TextEncoder().encode(password);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgUint8);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        return hashHex;
    }

    closeButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const modal = event.target.closest('.modal');
            if (modal) {
                closeModal(modal);
            }
        });
    });

    window.addEventListener('click', (event) => {
        if (event.target === editModal) {
            closeModal(editModal);
        }
        if (event.target === addModal) {
            closeModal(addModal);
        }
    });

    fetchRoles();
    fetchColaboradores();
}