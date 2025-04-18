<template>
    <div class="ship-placement app-background-primary">
        <!-- Sistema de notificaciones -->
        <div
            v-if="notification.show"
            class="notification"
            :class="[notification.type, notification.position]"
        >
            <span>{{ notification.message }}</span>
        </div>

        <!-- Pantalla de carga -->
        <div v-if="isLoading" class="loading-state">
            <div class="loading-overlay">
                <i class="fas fa-spinner fa-spin"></i>
                <p class="p3-dark">Esperando al oponente...</p>
            </div>
        </div>

        <div class="game-layout">
            <!-- Temporizador (solo escritorio) -->
            <div class="timer-container desktop-only">
                <div class="timer" :class="{ 'timer-warning': timeLeft <= 10 }">
                    <i class="fas fa-clock"></i>
                    <span class="timer-text">{{ formatTime(timeLeft) }}</span>
                </div>
            </div>
            <!-- Lista de barcos para colocar -->
            <div class="ships-dock">
                <h4 class="h4-dark dock-title">
                    POSICIONA LOS BARCOS DISPONIBLES
                </h4>
                <div class="separator"></div>
                <div class="ships-container">
                    <div
                        v-for="(ship, index) in availableShips"
                        :key="index"
                        class="ship-item"
                        :class="{
                            selected: selectedShip === ship,
                            placed: ship.placed,
                        }"
                        :style="{
                            width: `${ship.size * 40}px`,
                            '--width': `${ship.size * 40}px`,
                        }"
                        draggable="true"
                        @dragstart="handleDragStart($event, ship)"
                        @click="selectShip(ship)"
                    >
                        <div class="ship-preview">
                            <div
                                v-for="i in ship.size"
                                :key="i"
                                class="ship-segment"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controles -->
            <div class="controls">
                <!-- Temporizador (móvil) -->
                <div
                    class="timer mobile-only"
                    :class="{ 'timer-warning': timeLeft <= 10 }"
                >
                    <i class="fas fa-clock"></i>
                    <span class="timer-text">{{ formatTime(timeLeft) }}</span>
                </div>

                <button
                    @click="rotateShip"
                    class="control-button"
                    title="Rotar"
                >
                    <i
                        :class="[
                            'fas',
                            isVertical ? 'fa-arrows-alt-v' : 'fa-arrows-alt-h',
                        ]"
                    ></i>
                </button>
                <button
                    @click="resetPlacement"
                    class="control-button"
                    title="Reiniciar"
                >
                    <i class="fas fa-redo"></i>
                </button>
                <button
                    @click="confirmPlacementButton"
                    :disabled="!isPlacementComplete"
                    class="control-button primary"
                    title="Confirmar"
                >
                    <i class="fas fa-check"></i>
                </button>
            </div>

            <!-- Tablero -->
            <div class="board-container">
                <div class="board-grid">
                    <div
                        v-for="row in 10"
                        :key="`row-${row}`"
                        class="board-row"
                    >
                        <div
                            v-for="col in 10"
                            :key="`cell-${row}-${col}`"
                            class="board-cell"
                            :class="{
                                'cell-hover': isValidPlacement(
                                    row - 1,
                                    col - 1
                                ),
                                ship: board[row - 1][col - 1],
                            }"
                            @dragover.prevent
                            @drop.prevent="handleDrop($event, row - 1, col - 1)"
                            @dragenter.prevent
                        >
                            {{ getCellContent(row - 1, col - 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTS -- */
import { ref, computed, onMounted, onUnmounted } from "vue";
import { authStore } from "../../store/auth";
import { useRoute, useRouter } from "vue-router";
import { useGameStore } from "../../store/game";

/* -- VARIABLES -- */
const route = useRoute();
const router = useRouter();
const gameStore = useGameStore(); // Para manejar las fases del juego

/* -- FUNCIONES -- */

// Estado del tablero (matriz 10x10)
const board = ref(
    Array(10)
        .fill(null)
        .map(() => Array(10).fill(null))
);
const selectedShip = ref(null); // Barco seleccionado actualmente
const isVertical = ref(false); // Orientación del barco seleccionado

// Barcos disponibles para colocar
const availableShips = ref([]);

// Carga los tipos de barcos desde la API
const loadShips = async () => {
    try {
        const response = await fetch("/api/game-ships");
        const ships = await response.json();
        // Añade la propiedad 'placed' a cada barco
        availableShips.value = ships.map((ship) => ({
            ...ship,
            placed: false,
        }));
    } catch (error) {
        console.error("Error cargando los barcos:", error);
    }
};

// Configuración del temporizador
const timeLeft = ref(25); // Tiempo inicial en segundos
const timerInterval = ref(null);
const isLoading = ref(false); // Para mostrar la pantalla de carga
const notification = ref({
    // Para mostrar mensajes al usuario
    show: false,
    message: "",
    type: "info", // 'info', 'success', 'error', 'water'
    position: "derecha", // 'derecha', 'izquierda'
});

// Muestra una notificación temporal
const showNotification = (message, type = "info") => {
    notification.value = {
        show: true,
        message,
        type,
        position: "derecha",
    };
    // Oculta la notificación después de 3 segundos
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

// Redirige al usuario a la página de inicio, opcionalmente mostrando un error
const backToHome = (
    showError,
    message = "Ha ocurrido un error desconocido."
) => {
    if (showError) {
        showNotification(message, "error");
        // Espera a que se vea la notificación antes de redirigir
        setTimeout(() => {
            router.replace({ path: "/", replace: true });
        }, 3000);
    } else {
        router.replace({ path: "/", replace: true });
    }
};

// Formatea el tiempo restante en MM:SS
const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
};

// Inicia la cuenta atrás del temporizador
const startTimer = () => {
    timerInterval.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval.value);
            // Si se acaba el tiempo, confirma la colocación automáticamente
            confirmPlacement();
        }
    }, 1000); // Se ejecuta cada segundo
};

// Función de utilidad para pausar la ejecución
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Al montar el componente...
onMounted(() => {
    // console.log("Iniciando fase de colocación de barcos.");
    // console.log("Código de partida:", gameStore.matchCode);
    // console.log("Usuario:", authStore().user);

    loadShips(); // Carga los barcos
    startTimer(); // Inicia el temporizador
});

// Al desmontar el componente...
onUnmounted(() => {
    // Limpia el intervalo del temporizador para evitar fugas de memoria
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }
});

// Propiedad computada: ¿Están todos los barcos colocados?
const isPlacementComplete = computed(() => {
    // Verifica si la propiedad 'placed' es true para todos los barcos
    return availableShips.value.every((ship) => ship.placed);
});

// Métodos para manejar la colocación de barcos

// Selecciona un barco de la lista (si no está ya colocado)
const selectShip = (ship) => {
    if (!ship.placed) {
        selectedShip.value = ship;
    }
};

// Cambia la orientación del barco seleccionado
const rotateShip = () => {
    isVertical.value = !isVertical.value;
};

// Verifica si la posición actual (row, col) es válida para colocar el barco seleccionado
const isValidPlacement = (row, col) => {
    if (!selectedShip.value) return false; // No hay barco seleccionado

    const size = selectedShip.value.size;

    // Comprueba si el barco cabe dentro del tablero
    if (isVertical.value) {
        if (row + size > 10) return false; // Se sale por abajo
    } else {
        if (col + size > 10) return false; // Se sale por la derecha
    }

    // Comprueba si alguna casilla ya está ocupada
    for (let i = 0; i < size; i++) {
        if (isVertical.value) {
            if (board.value[row + i][col] !== null) return false; // Casilla ocupada (vertical)
        } else {
            if (board.value[row][col + i] !== null) return false; // Casilla ocupada (horizontal)
        }
    }

    return true; // La posición es válida
};

// Maneja el evento 'drop' (soltar) sobre una celda del tablero
const handleDrop = (event, row, col) => {
    // Si hay un barco seleccionado y la posición es válida...
    if (selectedShip.value && isValidPlacement(row, col)) {
        placeShip(row, col); // Coloca el barco
    }
};

// Coloca el barco seleccionado en la posición (row, col)
const placeShip = (row, col) => {
    if (!selectedShip.value) return; // No debería pasar si isValidPlacement es true, pero por si acaso

    const ship = selectedShip.value;

    // Marca las casillas correspondientes en el tablero con el nombre del barco
    for (let i = 0; i < ship.size; i++) {
        if (isVertical.value) {
            board.value[row + i][col] = ship.name;
        } else {
            board.value[row][col + i] = ship.name;
        }
    }

    // Marca el barco como colocado y deselecciónalo
    ship.placed = true;
    selectedShip.value = null;
};

// Reinicia el tablero y el estado de los barcos
const resetPlacement = () => {
    // Vacía el tablero
    board.value = Array(10)
        .fill(null)
        .map(() => Array(10).fill(null));
    // Marca todos los barcos como no colocados
    availableShips.value.forEach((ship) => (ship.placed = false));
    // Deselecciona cualquier barco
    selectedShip.value = null;
};

// Obtiene las posiciones de todos los barcos colocados en formato JSON
const getShipsPositions = () => {
    const shipsInfo = {}; // Objeto para almacenar la información

    // Recorre el tablero buscando el inicio de cada barco
    for (let row = 0; row < 10; row++) {
        for (let col = 0; col < 10; col++) {
            const shipName = board.value[row][col];
            // Si es una casilla de barco y aún no hemos procesado este barco...
            if (shipName && !shipsInfo[shipName]) {
                const positions = []; // Array para guardar las coordenadas de este barco

                // Detecta la orientación (asume horizontal si no es vertical)
                let isHorizontal =
                    col + 1 < 10 && board.value[row][col + 1] === shipName;

                if (isHorizontal) {
                    // Recorre hacia la derecha para encontrar todas las partes
                    for (
                        let c = col;
                        c < 10 && board.value[row][c] === shipName;
                        c++
                    ) {
                        // Guarda la posición como "fila,columna" (empezando en 1)
                        positions.push(`${row + 1},${c + 1}`);
                    }
                } else {
                    // Recorre hacia abajo para encontrar todas las partes
                    for (
                        let r = row;
                        r < 10 && board.value[r][col] === shipName;
                        r++
                    ) {
                        // Guarda la posición como "fila,columna" (empezando en 1)
                        positions.push(`${r + 1},${col + 1}`);
                    }
                }

                // Guarda las posiciones del barco en el objeto principal
                shipsInfo[shipName] = positions;
            }
        }
    }
    // Devuelve la información como una cadena JSON
    return JSON.stringify(shipsInfo);
};

// Verifica si todos los barcos definidos están presentes en la información de posiciones
const verifyAllShipsPlaced = (shipsInfo) => {
    // Convierte la cadena JSON a un objeto
    const placedShips = JSON.parse(shipsInfo);

    // Obtiene los nombres de los barcos que se han colocado
    const placedShipNames = Object.keys(placedShips);

    // Obtiene los nombres de los barcos que deberían estar
    const availableShipNames = availableShips.value.map((ship) => ship.name);

    // Comprueba si cada barco disponible está en la lista de barcos colocados
    return availableShipNames.every((shipName) =>
        placedShipNames.includes(shipName)
    );
};

// Acción del botón de confirmar: solo muestra la pantalla de carga
const confirmPlacementButton = () => {
    isLoading.value = true;
    // La lógica real se ejecuta en confirmPlacement (llamado por el temporizador o después)
    confirmPlacement();
};

// Lógica principal para confirmar la colocación y pasar a la siguiente fase
const confirmPlacement = async () => {
    // Solo procede si todos los barcos están colocados o si se acabó el tiempo
    if (isPlacementComplete.value || timeLeft.value <= 0) {
        const shipsInfo = getShipsPositions(); // Obtiene las posiciones

        // Comprueba si se colocó algún barco
        if (shipsInfo === "{}") {
            backToHome(true, "No has colocado ningún barco.");
            return; // Detiene la ejecución
        }

        // Comprueba si se colocaron *todos* los barcos necesarios
        if (!verifyAllShipsPlaced(shipsInfo)) {
            backToHome(true, "Faltan barcos por colocar o hay un error.");
            return; // Detiene la ejecución
        }

        // Si todo está bien, intenta guardar la colocación en el servidor
        try {
            const response = await axios.post(
                "/api/games/store-ship-placement",
                {
                    gameCode: gameStore.matchCode,
                    user: authStore().user,
                    shipsInfo: shipsInfo, // Envía las posiciones como JSON string
                }
            );

            // Si el servidor confirma que se guardó...
            if (response.data.status == "success") {
                isLoading.value = true; // Muestra carga mientras espera al oponente
                await sleep(5000); // Espera 5 segundos (simula espera o da tiempo al oponente)

                // Pregunta al servidor si el oponente ya colocó sus barcos
                const opponentCheckResponse = await axios.post(
                    "/api/games/get-opponent-ship-placement-validation",
                    {
                        gameCode: gameStore.matchCode,
                        user: authStore().user,
                    }
                );

                // Analiza la respuesta del servidor sobre el oponente
                if (
                    opponentCheckResponse.data.status == "success" &&
                    opponentCheckResponse.data.message == "OK"
                ) {
                    // ¡El oponente está listo! Pasa a la fase de juego.
                    console.log(
                        "El oponente ha colocado sus barcos. ¡A jugar!"
                    );
                    isLoading.value = false; // Oculta la carga
                    gameStore.setGamePhase("playing"); // Cambia la fase en el store
                } else if (
                    opponentCheckResponse.data.status == "success" &&
                    opponentCheckResponse.data.message == "NOK"
                ) {
                    // El oponente no ha terminado o ha habido un problema
                    backToHome(
                        true,
                        "El oponente no ha colocado sus barcos a tiempo."
                    );
                } else {
                    // Error inesperado al verificar al oponente
                    backToHome(
                        true,
                        "Error al verificar el estado del oponente."
                    );
                }
            } else {
                // Error al guardar la colocación en el servidor
                backToHome(
                    true,
                    "No se pudo guardar la posición de los barcos."
                );
            }
        } catch (error) {
            // Error de red o del servidor
            backToHome(true, "Error de conexión con el servidor.");
        }
    } else {
        // Intento de confirmar sin haber terminado (no debería ser posible si el botón está desactivado)
        showNotification("Aún no has colocado todos los barcos.", "info");
        isLoading.value = false; // Asegúrate de ocultar la carga si se mostró
    }
};

// Manejadores de eventos para Drag and Drop

// Cuando se empieza a arrastrar un barco
const handleDragStart = (event, ship) => {
    if (!ship.placed) {
        selectShip(ship); // Selecciónalo (si no está colocado)
        // Podrías añadir datos al evento de arrastre si fuera necesario
        // event.dataTransfer.setData('text/plain', ship.name);
    } else {
        event.preventDefault(); // Evita arrastrar barcos ya colocados
    }
};

// Devuelve el contenido visual de una celda (actualmente vacío)
const getCellContent = (row, col) => {
    // Podría usarse para mostrar iconos o información de depuración
    return "";
};

// Define los eventos que este componente puede emitir (aunque no se usa actualmente)
const emit = defineEmits(["placement-confirmed"]);
</script>

<style scoped>
.ship-placement {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 6.5rem 1rem 1rem 1rem;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 100vh;
    height: 100%;
}

.page-title {
    margin-bottom: 2rem;
    text-align: center;
}

.game-layout {
    display: flex;
    gap: 2rem;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    width: 100%;
    background: var(--background-primary);
    border-radius: 12px;
    padding-top: 3rem;
}

.ships-dock {
    width: 450px;
    height: 450px;
    padding: 0.75rem;
    background: var(--neutral-color-1);
    border-radius: 12px;
    border: 2px solid var(--primary-color);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.ships-dock h3 {
    color: var(--white-color);
    margin-bottom: 0.5rem;
    text-align: center;
}

.separator {
    border: none;
    height: 2px;
    background-color: var(--primary-color);
    width: 100%;
    opacity: 1;
    margin: 0;
}

.ships-container {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    align-items: center;
    flex-grow: 1;
    padding: 0.25rem;
    overflow-y: auto;
    max-height: calc(100% - 35px);
}

.board-container {
    width: 450px;
    height: 450px;
    padding: 1.5rem;
    border-radius: 12px;
    border: 2px solid var(--primary-color);
    background: var(--background-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.board-grid {
    width: 400px;
    height: 400px;
    display: flex;
    flex-direction: column;
    border: 2px solid var(--primary-color);
    background: var(--neutral-color);
}

.board-row {
    display: flex;
}

.board-cell {
    width: 40px;
    height: 40px;
    border: 1px solid var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    background: var(--neutral-color);
}

.board-cell.ship::after {
    content: "";
    position: absolute;
    top: 4px;
    left: 4px;
    right: 4px;
    bottom: 4px;
    background-color: var(--secondary-color);
    border-radius: 2px;
}

.cell-hover {
    background-color: #7048ec33;
    outline: 2px dashed var(--primary-color);
}

.ship-item {
    cursor: grab;
    padding: 0.25rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: transparent;
    border: none;
    margin: 0.15rem;
}

.ship-item:hover {
    border: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(112, 72, 236, calc(0.2 * var(--ship-size, 1)));
}

.ship-item:active {
    cursor: grabbing;
}

.ship-item.placed {
    opacity: 0.5;
    cursor: not-allowed;
}

.ship-preview {
    display: flex;
    flex-direction: row;
    background: transparent;
    border-radius: 4px;
    position: relative;
    height: 40px;
    width: 100%;
}

.ship-segment {
    width: 36px;
    height: 36px;
    background-color: var(--secondary-color);
    margin: 2px;
    border-radius: 2px;
}

.selected .ship-segment {
    background-color: var(--primary-color);
}

.controls {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 1rem;
}

.control-button {
    width: 48px;
    height: 48px;
    border: 2px solid var(--primary-color);
    border-radius: 50%;
    cursor: pointer;
    background: var(--neutral-color);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    color: var(--primary-color);
}

.control-button i {
    font-size: 1.5rem;
    color: var(--primary-color);
    transition: all 0.3s ease;
    text-shadow: 0 0 3px #7048ec4d;
}

.control-button:hover {
    background: var(--primary-color);
    color: var(--white-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(112, 72, 236, 0.2);
}

.control-button:hover i {
    color: var(--white-color);
    text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
}

.control-button.primary i {
    color: var(--white-color);
}

.control-button:disabled i {
    color: var(--neutral-color-1);
    text-shadow: none;
}

.control-button.primary {
    background: var(--primary-color);
    color: var (--white-color);
}

.control-button.primary:hover {
    background: var(--primary-v2-color);
    border-color: var(--primary-v2-color);
}

.control-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    border-color: var(--neutral-color-1);
    color: var (--neutral-color-1);
    background: var(--neutral-color);
}

.dock-title {
    text-align: center;
    margin-bottom: 0.25rem;
    color: var(--white-color);
    font-size: 16px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.separator {
    height: 2px;
    background: var(--primary-color);
    opacity: 0.5;
    margin-bottom: 0.5rem;
    width: 100%;
}

.timer-container {
    width: 100%;
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}

.timer {
    background: var(--neutral-color-1);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: 2px solid var(--primary-color);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--white-color);
    font-size: 24px;
    font-family: "Rubik", sans-serif;
}

.timer i {
    color: var(--primary-color);
}

.timer-warning {
    border-color: var(--secondary-color);
    animation: pulse 1s infinite;
}

.timer-warning i {
    color: var(--secondary-color);
}

.desktop-only {
    display: none;
}

.mobile-only {
    display: none;
}

@media (min-width: 481px) {
    .desktop-only {
        display: flex;
    }
}

@media (max-width: 480px) {
    .mobile-only {
        display: flex;
    }

    .game-layout {
        gap: 0.5rem;
        padding: 1rem 0.5rem 0.5rem 0.5rem;
        flex-direction: column;
    }
}

@media (max-width: 800px) {
    .ship-placement {
        padding-top: 1rem;
    }

    .timer-container {
        margin-bottom: 0;
    }
}

.loading-state {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.loading-overlay {
    position: relative;
    width: 300px;
    padding: 1rem;
    background: var(--background-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    border-radius: 8px;
}

.loading-overlay i {
    font-size: 2rem;
    color: var(--primary-color);
}

@keyframes pulse {
    0% {
        border-color: var(--secondary-color);
    }
    50% {
        border-color: transparent;
    }
    100% {
        border-color: var(--secondary-color);
    }
}

.notification {
    position: fixed;
    top: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    color: white;
    z-index: 1000;
}

.notification.success {
    background-color: #28a745;
}

.notification.info {
    background-color: var(--primary-color);
}

.notification.error {
    background-color: #dc3545;
}

.notification.water {
    background-color: #0d6efd;
}

.notification.derecha {
    right: 20px;
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@media (max-width: 1200px) {
    .game-layout {
        gap: 1.5rem;
    }

    .controls {
        flex-direction: row;
        width: 100%;
        justify-content: center;
        padding: 0.5rem;
        order: 2;
    }

    .ships-dock {
        order: 1;
    }

    .board-container {
        order: 3;
    }
}

@media (max-width: 480px) {
    .ship-placement {
        padding: 4.5rem 0.5rem 0.5rem 0.5rem;
        min-height: auto;
    }

    .game-layout {
        gap: 0.5rem;
        padding: 0.5rem;
        flex-direction: column;
    }

    .board-container,
    .ships-dock {
        width: 320px;
        height: 320px;
        padding: 0.5rem;
        margin: 0;
    }

    .board-grid {
        width: 300px;
        height: 300px;
    }

    .board-cell {
        width: 30px;
        height: 30px;
    }

    .ships-dock {
        min-height: 120px;
        height: auto;
        max-height: 320px;
        overflow: hidden;
    }

    .ships-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.25rem;
        padding: 0.25rem;
        justify-items: center;
        align-items: start;
        overflow-y: auto;
        max-height: calc(100% - 30px);
    }

    .ship-item {
        transform: scale(0.75);
        transform-origin: center;
        margin: 0;
        padding: 0.15rem;
    }

    .ship-segment {
        width: 30px;
        height: 30px;
        margin: 1px;
    }

    .controls {
        width: 320px;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.5rem;
    }

    .control-button {
        width: 36px;
        height: 36px;
        flex: 0 0 auto;
    }

    .timer {
        padding: 0.5rem;
        font-size: 16px;
        margin: 0;
    }

    .dock-title {
        font-size: 14px;
        margin-bottom: 0.15rem;
        padding: 0 0.25rem;
        line-height: 1.2;
        white-space: normal;
    }
}

@media (max-height: 800px) {
    .ship-placement {
        padding: 4rem 1rem 1rem 1rem;
    }

    .board-container,
    .ships-dock {
        width: 400px;
        height: 400px;
    }

    .board-grid {
        width: 360px;
        height: 360px;
    }

    .board-cell {
        width: 36px;
        height: 36px;
    }
}

@media (max-height: 700px) {
    .ship-placement {
        padding: 3.5rem 1rem 1rem 1rem;
    }

    .board-container,
    .ships-dock {
        width: 350px;
        height: 350px;
    }

    .board-grid {
        width: 320px;
        height: 320px;
    }

    .board-cell {
        width: 32px;
        height: 32px;
    }

    .ship-segment {
        width: 30px;
        height: 30px;
    }

    .dock-title {
        font-size: 15px;
    }

    .separator {
        margin-bottom: 0.3rem;
    }

    .ships-container {
        gap: 0.15rem;
    }
}

@media (max-height: 600px) {
    .ship-placement {
        padding: 2.5rem 0.5rem 0.5rem 0.5rem;
    }

    .game-layout {
        gap: 1rem;
    }

    .board-container,
    .ships-dock {
        width: 300px;
        height: 300px;
    }

    .board-grid {
        width: 280px;
        height: 280px;
    }

    .board-cell {
        width: 28px;
        height: 28px;
    }

    .ship-segment {
        width: 26px;
        height: 26px;
    }

    .controls {
        gap: 0.75rem;
    }

    .control-button {
        width: 40px;
        height: 40px;
    }

    .timer {
        font-size: 18px;
    }

    .dock-title {
        font-size: 13px;
    }

    .separator {
        margin-bottom: 0.2rem;
    }

    .ships-container {
        gap: 0.1rem;
        padding: 0.1rem;
        max-height: calc(100% - 25px);
    }

    .ship-item {
        transform: scale(0.9);
    }
}

@media (max-height: 650px) and (max-width: 768px) {
    .ship-placement {
        padding: 2rem 0.5rem 0.5rem 0.5rem;
    }

    .game-layout {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
        padding-top: 1rem;
    }

    .ships-dock {
        width: 48%;
        height: 280px;
        margin-right: 2%;
    }

    .board-container {
        width: 48%;
        height: 280px;
    }

    .board-grid {
        width: 100%;
        height: 100%;
        max-width: 260px;
        max-height: 260px;
    }

    .board-cell {
        width: 26px;
        height: 26px;
    }

    .controls {
        width: 100%;
        flex-direction: row;
        justify-content: center;
        padding: 0.25rem;
        gap: 1rem;
        order: 3;
    }

    .ships-container {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        max-height: 235px;
    }

    .ship-item {
        transform: scale(0.85);
        margin: 0;
        padding: 0.1rem;
    }
}

@media (max-width: 360px) {
    .board-container,
    .ships-dock {
        width: 280px;
        height: 280px;
    }

    .board-grid {
        width: 260px;
        height: 260px;
    }

    .board-cell {
        width: 26px;
        height: 26px;
    }

    .ship-item {
        transform: scale(0.7);
    }

    .controls {
        width: 280px;
    }

    .control-button {
        width: 32px;
        height: 32px;
    }

    .timer {
        padding: 0.3rem;
        font-size: 14px;
    }
}

@media (max-width: 1000px) {
    .board-container,
    .ships-dock {
        width: calc(450px - ((1000px - 100vw) * 0.3));
        height: calc(450px - ((1000px - 100vw) * 0.3));
    }

    .board-grid {
        width: calc(400px - ((1000px - 100vw) * 0.3));
        height: calc(400px - ((1000px - 100vw) * 0.3));
    }

    .board-cell {
        width: calc(40px - ((1000px - 100vw) * 0.03));
        height: calc(40px - ((1000px - 100vw) * 0.03));
    }

    .ship-segment {
        width: calc(36px - ((1000px - 100vw) * 0.03));
        height: calc(36px - ((1000px - 100vw) * 0.03));
    }

    .ship-preview {
        height: calc(40px - ((1000px - 100vw) * 0.03));
    }

    .ship-item {
        --cell-size: calc(40px - ((1000px - 100vw) * 0.03));
    }
}
</style>
