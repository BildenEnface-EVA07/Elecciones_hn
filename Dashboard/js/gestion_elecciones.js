// js/gestion_elecciones.js

const API_BASE_URL_GESTION = window.location.origin + '/Elecciones_hn/backend/api/GestionElecciones/';

async function renderGestionEleccionesView() {
    const dynamicContent = document.getElementById('dynamic-content');
    dynamicContent.innerHTML = `
        <div class="gestion-container">
            <h1 class="page-title">Gestión de Elecciones</h1>
            <div id="gestion-content">
                </div>
        </div>
    `;

    await loadGestionEleccionesContent();
}

async function loadGestionEleccionesContent() {
    const gestionContentDiv = document.getElementById('gestion-content');
    if (!gestionContentDiv) return;

    try {
        const response = await fetch(`${API_BASE_URL_GESTION}getProcesoVotacionStatus.php`);
        const result = await response.json();

        let sePuedeVotar = false;
        let idProcesoActual = null;

        if (result.status === 'success' && result.data) {
            sePuedeVotar = result.data.sePuedeVotar;
            idProcesoActual = result.data.idProcesoVotacion;
        }

        if (sePuedeVotar) {
            // Caso 1: Elecciones activas, mostrar botón para finalizar
            gestionContentDiv.innerHTML = `
                <div class="gestion-text-block">
                    <div class="text-content">
                        <p>En este momento, las elecciones generales se encuentran activas.</p>
                        <p>Cuando decidas ponerle fin, simplemente presione el botón para darlas por finalizado. Las personas ya no podrán votar cuando se finalicen las elecciones.</p>
                        <p>El ganador será automaticamente anunciado de manera pública, mostrando la cantidad de votos conseguidas en total, a cual partido político pertenecen y a cual cargo aspiraba este.</p>
                    </div>
                    <div class="action-section">
                        <div class="warning-box">
                            AVISO: Una vez presionado el botón, esta acción
        no se podrá revertir. Queda a su propia responsabilidad si usted está de
         acuerdo en hacerlo y leyó toda la información.
                        </div>
                        <button id="finalizar-elecciones-btn" class="action-button">Finalizar Elecciones</button>
                    </div>
                </div>
            `;
            document.getElementById('finalizar-elecciones-btn').addEventListener('click', () => handleFinalizarElecciones(idProcesoActual));
        } else {
            // Caso 2: No hay elecciones activas, mostrar botón para iniciar
            gestionContentDiv.innerHTML = `
                <div class="gestion-text-block">
                    <div class="text-content">
                        <p>Actualmente no hay elecciones activas.</p>
                        <p>Al habilitar el sitio para votar, todos podrán acceder y ejercer su voto. Cualquier proceso de elecciones previa ya no se toma en cuenta, es decir, es un “borrón y cuenta nueva”; sin embargo, los registros de los votos de esos procesos anteriores si quedan guardados en la base de datos.</p>
                        <p>Para iniciar un nuevo proceso de votación, haga click en el botón a continuación. Al iniciar, se reseteará el conteo de votos anterior y se habilitará la votación para el público.</p>
                    </div>
                    <div class="action-section">
                        <div class="warning-box">
                            AVISO: Una vez presionado el botón, esta acción
        no se podrá revertir. Queda a su propia responsabilidad si usted está de
         acuerdo en hacerlo y leyó toda la información.
                        </div>
                        <button id="iniciar-elecciones-btn" class="action-button">Iniciar Nuevas Elecciones</button>
                    </div>
                </div>
            `;
            document.getElementById('iniciar-elecciones-btn').addEventListener('click', handleIniciarElecciones);
        }

    } catch (error) {
        console.error('Error al obtener el estado del proceso de votación:', error);
        gestionContentDiv.innerHTML = `<p style="color: red; text-align: center;">Error al cargar la información de gestión de elecciones.</p>`;
    }
}

async function handleFinalizarElecciones(idProceso) {
    if (!confirm("¿Está seguro de que desea finalizar las elecciones actuales? Esta acción no se podrá revertir.")) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL_GESTION}finalizarElecciones.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ idProcesoVotacion: idProceso })
        });
        const result = await response.json();

        if (result.status === 'success') {
            alert('Elecciones finalizadas exitosamente.');
            await loadGestionEleccionesContent(); // Recargar la vista
        } else {
            alert('Error al finalizar las elecciones: ' + result.message);
        }
    } catch (error) {
        console.error('Error al comunicarse con la API de finalizar elecciones:', error);
        alert('Error de red al intentar finalizar las elecciones.');
    }
}

async function handleIniciarElecciones() {
    if (!confirm("¿Está seguro de que desea iniciar nuevas elecciones? Esto creará un nuevo proceso de votación.")) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL_GESTION}iniciarNuevasElecciones.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        const result = await response.json();

        if (result.status === 'success') {
            alert('Nuevas elecciones iniciadas exitosamente.');
            await loadGestionEleccionesContent(); // Recargar la vista
        } else {
            alert('Error al iniciar nuevas elecciones: ' + result.message);
        }
    } catch (error) {
        console.error('Error al comunicarse con la API de iniciar elecciones:', error);
        alert('Error de red al intentar iniciar nuevas elecciones.');
    }
}