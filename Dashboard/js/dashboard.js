let votos24hChartInstance = null;
let votosCandidaturaChartInstance = null;

async function renderDashboardView() {
    const dynamicContent = document.getElementById('dynamic-content');

    dynamicContent.innerHTML = `
        <div class="dashboard-header">
            <h1 class="welcome-title">Bienvenido, Heyden Aldana</h1>
            <p class="role-info">Su rol como usuario es: Colaborador</p>
            <p class="role-description">Puedes revisar los movimientos y cantidad de votos del día aquí</p>
        </div>
        <div class="dashboard-metrics">
            <div class="chart-container">
                <h3>Cantidad de votos en las últimas 24h</h3>
                <canvas id="votos24hChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Votos totales actualmente por candidatura</h3>
                <canvas id="votosCandidaturaChart"></canvas>
            </div>
        </div>
    `;

    if (votos24hChartInstance) {
        votos24hChartInstance.destroy();
    }
    if (votosCandidaturaChartInstance) {
        votosCandidaturaChartInstance.destroy();
    }

    await fetchAndDrawVotos24hChart();
    await fetchAndDrawVotosCandidaturaChart();
}

function getPartidoColors(partyName) {
    switch (partyName) {
        case 'Partido Libre':
            return '#FD0000';
        case 'Partido Nacional':
            return '#00C8FF';
        case 'Partido Liberal':
            return '#FF4579';
        default:
            return '#CCCCCC';
    }
}

async function fetchAndDrawVotos24hChart() {
    try {
        const response = await fetch('http://localhost/Elecciones_hn/backend/api/Dashboard/dashboard_data.php?action=votos24h');
        const result = await response.json();

        if (result.status === 'success' && result.data.length > 0) {
            const labels = result.data.map(item => item.nombrePartido);
            const data = result.data.map(item => item.totalVotos);
            const backgroundColors = labels.map(getPartidoColors);

            const ctx = document.getElementById('votos24hChart').getContext('2d');
            votos24hChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        } else {
            const ctx = document.getElementById('votos24hChart').getContext('2d');
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            ctx.font = '16px Fredoka';
            ctx.textAlign = 'center';
            ctx.fillStyle = 'var(--color-dark-grey)';
            ctx.fillText('No hay datos disponibles para esta gráfica.', ctx.canvas.width / 2, ctx.canvas.height / 2);
        }
    } catch (error) {
        console.error('Error al obtener datos de votos 24h:', error);
        const ctx = document.getElementById('votos24hChart').getContext('2d');
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
        ctx.font = '16px Fredoka';
        ctx.textAlign = 'center';
        ctx.fillStyle = 'red';
        ctx.fillText('Error al cargar la gráfica.', ctx.canvas.width / 2, ctx.canvas.height / 2);
    }
}

async function fetchAndDrawVotosCandidaturaChart() {
    try {
        const response = await fetch('http://localhost/Elecciones_hn/backend/api/Dashboard/dashboard_data.php?action=votosCandidatura');
        const result = await response.json();

        if (result.status === 'success' && result.data.length > 0) {
            const cargos = ['Diputado', 'Alcalde', 'Presidente'];
            const partidos = ['Partido Libre', 'Partido Nacional', 'Partido Liberal'];

            const datasets = partidos.map(partido => {
                const dataForParty = cargos.map(cargo => {
                    const item = result.data.find(d => d.cargo === cargo && d.partido === partido);
                    return item ? item.totalVotos : 0;
                });
                return {
                    label: partido,
                    data: dataForParty,
                    backgroundColor: getPartidoColors(partido),
                    borderColor: getPartidoColors(partido),
                    borderWidth: 1
                };
            });

            const ctx = document.getElementById('votosCandidaturaChart').getContext('2d');
            votosCandidaturaChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: cargos,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Tipo de Candidatura'
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad de Votos'
                            },
                            ticks: {
                                callback: function(value) {
                                    return Math.ceil(value / 100) * 100;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        } else {
            const ctx = document.getElementById('votosCandidaturaChart').getContext('2d');
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            ctx.font = '16px Fredoka';
            ctx.textAlign = 'center';
            ctx.fillStyle = 'var(--color-dark-grey)';
            ctx.fillText('No hay datos disponibles para esta gráfica.', ctx.canvas.width / 2, ctx.canvas.height / 2);
        }
    } catch (error) {
        console.error('Error al obtener datos de votos por candidatura:', error);
        const ctx = document.getElementById('votosCandidaturaChart').getContext('2d');
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
        ctx.font = '16px Fredoka';
        ctx.textAlign = 'center';
        ctx.fillStyle = 'red';
        ctx.fillText('Error al cargar la gráfica.', ctx.canvas.width / 2, ctx.canvas.height / 2);
    }
}