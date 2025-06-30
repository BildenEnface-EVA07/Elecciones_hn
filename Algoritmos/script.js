document.addEventListener('DOMContentLoaded', () => {
    // Mobile Navbar Toggle
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
    const mobileNavMenu = document.querySelector('.mobile-nav-menu');

    mobileNavToggle.addEventListener('click', () => {
        mobileNavMenu.classList.toggle('show');
        mobileNavToggle.classList.toggle('active');
    });

    // Tab Switching Logic
    const tabButtons = document.querySelectorAll('.tab-button');
    const infoPanels = document.querySelectorAll('.info-panel');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Deactivate all buttons and panels
            tabButtons.forEach(btn => btn.classList.remove('active'));
            infoPanels.forEach(panel => panel.classList.remove('active'));

            // Activate the clicked button
            button.classList.add('active');

            // Show the corresponding info panel
            const targetTab = button.dataset.tab;
            document.getElementById(`${targetTab}-content`).classList.add('active');

            // Clear console output when switching tabs
            const currentConsoleInput = document.querySelector(`#${targetTab}-content .console-input`);
            if (currentConsoleInput) {
                const outputElementId = currentConsoleInput.dataset.target;
                document.getElementById(outputElementId).textContent = '';
                currentConsoleInput.value = '';
            }
        });
    });

    // Initialize the first tab as active on load
    if (tabButtons.length > 0) {
        tabButtons[0].click();
    }

    // Console Command Logic
    const consoleInputs = document.querySelectorAll('.console-input');

    // Mapped functions for algorithm simulations
    const algorithmSimulations = {
        'avestruz': runOstrichAlgorithm,
        'banquero-un-recurso': runBankerSingleResource,
        'banquero-varios-recursos': runBankerMultiResource,
        'filosofos': runPhilosophersProblem,
        'lectores-escritores': runReadersWritersProblem,
        'barbero-durmiente': runSleepingBarberProblem
    };

    consoleInputs.forEach(input => {
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                const command = input.value.trim().toLowerCase();
                const outputElementId = input.dataset.target;
                const outputElement = document.getElementById(outputElementId);
                const algorithmId = input.dataset.algorithm;

                outputElement.textContent = ''; // Clear previous output

                switch (command) {
                    case '/run':
                        if (algorithmSimulations[algorithmId]) {
                            const simulationOutput = algorithmSimulations[algorithmId]();
                            outputElement.textContent = simulationOutput;
                        } else {
                            outputElement.textContent = 'Error: No hay simulación disponible para este algoritmo.';
                        }
                        break;
                    case '/clear':
                        outputElement.textContent = '';
                        break;
                    default:
                        outputElement.textContent = 'ERROR: Comando no reconocido. Usa /run o /clear.';
                        break;
                }
                input.value = ''; // Clear the input field
            }
        });
    });

    // --- Algorithm Simulation Functions ---

    // Algoritmo del Avestruz
    function runOstrichAlgorithm() {
        let data = 0;
        try {
            // 10% chance of "error"
            if (Math.random() < 0.1) {
                throw new Error("Error poco frecuente ocurrido.");
            }
            // Simulate occasional division by zero
            data = 100 / (Math.random() > 0.5 ? 1 : 0);
            return `Operación completada, resultado: ${data}`;
        } catch (e) {
            // Ignoring the error as per Ostrich Algorithm
            return "Un error ocurrió, pero fue ignorado (Algoritmo del Avestruz).";
        }
    }

    // Algoritmo del Banquero (un solo recurso)
    function runBankerSingleResource() {
        let totalResources = 10;
        let availableResources = 10;
        let maxNeeds = { P1: 5, P2: 4, P3: 6 };
        let allocated = { P1: 0, P2: 0, P3: 0 };
        let processes = ['P1', 'P2', 'P3'];
        let output = [];

        function requestResources(process, amount) {
            output.push(`\n${process} solicita ${amount} recurso(s).`);
            if (amount > maxNeeds[process] - allocated[process]) {
                output.push(`  Error: ${process} excede su necesidad máxima.`);
                return false;
            }
            if (amount > availableResources) {
                output.push(`  ${process} debe esperar: recursos insuficientes disponibles.`);
                return false;
            }

            availableResources -= amount;
            allocated[process] += amount;

            output.push(`  Estado actual: Disponible=${availableResources}, Asignado: P1=${allocated.P1}, P2=${allocated.P2}, P3=${allocated.P3}`);

            // Simple safety check, real algorithm is more complex
            let isSafe = availableResources >= 0;
            output.push(`  ¿Es un estado seguro? ${isSafe ? 'Sí' : 'No'}`);

            return isSafe;
        }

        output.push("Inicio de la simulación del Banquero (un recurso):");
        requestResources('P1', 2);
        requestResources('P2', 3);
        requestResources('P3', 1);
        requestResources('P1', 3);
        requestResources('P2', 1);
        requestResources('P3', 5);

        return output.join('\n');
    }

    // Algoritmo del Banquero (varios recursos)
    function runBankerMultiResource() {
        let available = [3, 3, 2]; // Available resources (R1, R2, R3)
        let max = [             // Max need of each process
            [7, 5, 3], // P0
            [3, 2, 2], // P1
            [9, 0, 2]  // P2
        ];
        let allocation = [      // Resources already allocated
            [0, 1, 0], // P0
            [2, 0, 0], // P1
            [3, 0, 2]  // P2
        ];
        let need = [            // Remaining resources each process needs
            [7, 4, 3], // P0
            [1, 2, 2], // P1
            [6, 0, 0]  // P2
        ];
        let processes = ['P0', 'P1', 'P2'];
        let output = [];

        output.push("Estado inicial:");
        output.push(`  Available: [${available.join(', ')}]`);
        output.push(`  Need: ${JSON.stringify(need)}`);
        output.push(`  Allocation: ${JSON.stringify(allocation)}`);

        let isSystemSafe = true;

        // Basic safety check; real algorithm finds an execution sequence.
        for (let i = 0; i < processes.length; i++) {
            for (let j = 0; j < available.length; j++) {
                // If any process needs more than what's available + its current allocation
                if (need[i][j] > (available[j] + allocation[i][j])) {
                    isSystemSafe = false;
                    break;
                }
            }
            if (!isSystemSafe) break;
        }

        output.push(`\n¿Es el sistema un estado seguro (verificación básica)? ${isSystemSafe ? 'Sí' : 'No'}`);
        output.push("  Nota: La verificación de seguridad completa es más compleja y busca una secuencia de ejecución.");
        return output.join('\n');
    }

    // Problema de los Filósofos Comelones
    function runPhilosophersProblem() {
        let output = [];
        const numPhilosophers = 5;
        const chopsticks = Array(numPhilosophers).fill(true); // true = available, false = taken
        const philosopherStates = Array(numPhilosophers).fill('thinking'); // thinking, hungry, eating

        function tryEat(philosopherId) {
            const leftChopstick = philosopherId;
            const rightChopstick = (philosopherId + 1) % numPhilosophers;

            output.push(`Filósofo ${philosopherId} está ${philosopherStates[philosopherId]}.`);

            if (philosopherStates[philosopherId] === 'hungry') {
                if (chopsticks[leftChopstick] && chopsticks[rightChopstick]) {
                    chopsticks[leftChopstick] = false;
                    chopsticks[rightChopstick] = false;
                    philosopherStates[philosopherId] = 'eating';
                    output.push(`  Filósofo ${philosopherId} tomó los palillos y está comiendo.`);
                    return true;
                } else {
                    output.push(`  Filósofo ${philosopherId} no pudo tomar ambos palillos, sigue hambriento.`);
                    return false;
                }
            }
            return false;
        }

        output.push("Inicio de la simulación de los Filósofos Comelones:");

        for (let i = 0; i < numPhilosophers; i++) {
            philosopherStates[i] = 'hungry';
        }

        let ateCount = 0;
        let attempts = 0;
        while (ateCount < numPhilosophers && attempts < 10) { // Limit attempts to prevent infinite loop
            output.push(`\n--- Intento de ciclo ${attempts + 1} ---`);
            ateCount = 0;
            for (let i = 0; i < numPhilosophers; i++) {
                if (philosopherStates[i] === 'hungry') {
                    if (tryEat(i)) {
                        ateCount++;
                    }
                } else if (philosopherStates[i] === 'eating') {
                    output.push(`  Filósofo ${i} sigue comiendo.`);
                    ateCount++;
                } else {
                    output.push(`  Filósofo ${i} está pensando.`);
                }
            }
            attempts++;
        }

        if (ateCount < numPhilosophers) {
            output.push("\nCondición de interbloqueo simulada: Algunos filósofos siguen hambrientos.");
        } else {
            output.push("\nSimulación completada: Todos los filósofos comieron.");
        }
        return output.join('\n');
    }

    // Problema de Lectores y Escritores
    function runReadersWritersProblem() {
        let output = [];
        let database = "Datos iniciales";
        let readersReading = 0; // Number of active readers
        let writerActive = false; // true if a writer is writing
        let writerWaiting = false; // true if a writer is waiting

        function acquireReadLock(readerId) {
            if (writerActive || writerWaiting) {
                output.push(`Lector ${readerId}: Esperando (escritor activo o esperando).`);
                return false;
            }
            readersReading++;
            output.push(`Lector ${readerId}: Acceso de lectura concedido. Lectores activos: ${readersReading}`);
            return true;
        }

        function releaseReadLock(readerId) {
            readersReading--;
            output.push(`Lector ${readerId}: Lectura completada. Lectores activos: ${readersReading}`);
        }

        function acquireWriteLock(writerId) {
            if (readersReading > 0 || writerActive) {
                writerWaiting = true;
                output.push(`Escritor ${writerId}: Esperando (lectores activos o otro escritor).`);
                return false;
            }
            writerActive = true;
            writerWaiting = false;
            output.push(`Escritor ${writerId}: Acceso de escritura concedido.`);
            return true;
        }

        function releaseWriteLock(writerId) {
            writerActive = false;
            output.push(`Escritor ${writerId}: Escritura completada.`);
        }

        output.push("Inicio de la simulación de Lectores-Escritores (Prioridad a Lectores):");

        // Test scenario
        output.push("\n-- Escenario 1: Múltiples lectores acceden --");
        if (acquireReadLock(1)) { database; releaseReadLock(1); }
        if (acquireReadLock(2)) { database; releaseReadLock(2); }
        if (acquireReadLock(3)) { database; releaseReadLock(3); }

        output.push("\n-- Escenario 2: Escritor intenta acceder mientras hay lectores --");
        acquireReadLock(4); // Reader 4 enters
        if (acquireWriteLock(1)) { database = "Nuevos datos"; releaseWriteLock(1); } // Writer 1 tries
        releaseReadLock(4); // Reader 4 exits

        output.push("\n-- Escenario 3: Escritor accede cuando no hay lectores --");
        if (acquireWriteLock(2)) { database = "Más datos"; releaseWriteLock(2); }

        output.push(`\nEstado final de la base de datos: "${database}"`);
        return output.join('\n');
    }

    // Problema del Barbero Durmiente
    function runSleepingBarberProblem() {
        let output = [];
        const MAX_CHAIRS = 3;
        let waitingCustomers = 0;
        let barberSleeping = true;
        let barberChairOccupied = false;

        function simulateHaircut(customerId) {
            return new Promise(resolve => {
                barberChairOccupied = true;
                output.push(`Barbero: Cortando el pelo al Cliente ${customerId}.`);
                setTimeout(() => {
                    output.push(`Barbero: Terminé con Cliente ${customerId}.`);
                    barberChairOccupied = false;
                    resolve();
                }, 50);
            });
        }

        async function barberLoop() {
            // Keep the loop running for a few "ticks" to simulate ongoing process
            for(let i = 0; i < 20; i++) { // Limit iterations for a finite simulation output
                if (waitingCustomers > 0 && !barberChairOccupied) {
                    barberSleeping = false;
                    output.push(`Barbero: Un cliente espera. Clientes esperando: ${waitingCustomers}`);
                    const currentCustomer = "Cliente_" + (Math.random() * 100).toFixed(0); // Simple placeholder
                    waitingCustomers--;
                    await simulateHaircut(currentCustomer);
                } else if (waitingCustomers === 0 && !barberSleeping) {
                    barberSleeping = true;
                    output.push(`Barbero: No hay clientes, me voy a dormir.`);
                }
                await new Promise(resolve => setTimeout(resolve, 10)); // Small delay for simulation
            }
        }

        output.push("Inicio de la simulación del Barbero Durmiente:");
        
        // Initial state or first customer arrival simulation
        if (waitingCustomers === 0 && barberSleeping) {
            output.push("Barbero: Esperando clientes (dormido).");
        }

        // Simulate customer arrivals at different times
        function customerArrives(id, delay) {
            setTimeout(() => {
                output.push(`\nCliente ${id} llega.`);
                if (waitingCustomers < MAX_CHAIRS) {
                    waitingCustomers++;
                    output.push(`  Cliente ${id} se sienta en la sala de espera. Clientes esperando: ${waitingCustomers}`);
                    if (barberSleeping && !barberChairOccupied) { // If barber is sleeping and chair is free
                        barberSleeping = false;
                        output.push(`  Cliente ${id} despierta al barbero.`);
                        barberLoop(); // Start the barber loop if woken up
                    }
                } else {
                    output.push(`  Sala de espera llena. Cliente ${id} se va.`);
                }
            }, delay);
        }

        customerArrives(1, 50);
        customerArrives(2, 150);
        customerArmero(3, 250);
        customerArrives(4, 350); 
        customerArrives(5, 450);

        barberLoop(); // Initial call to potentially start barber activity if customers are waiting

        return "Simulación asíncrona iniciada. La salida aparecerá con el tiempo.";
    }
});