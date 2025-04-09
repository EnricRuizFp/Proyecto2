<template>
    <div class="ship-placement app-background-primary">
        <!-- Agregar la pantalla de carga -->
        <div v-if="isLoading" class="loading-state">
            <div class="loading-overlay">
                <i class="fas fa-spinner fa-spin"></i>
                <p class="p3-dark">Esperando al oponente...</p>
            </div>
        </div>

        <div class="game-layout">
            <!-- Temporizador solo visible en desktop -->
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
                <!-- Temporizador para móvil -->
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

            <!-- Grid del tablero -->
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
const gameStore = useGameStore(); // Utilizado para settear las fases del juego

/* -- FUNCIONES -- */

// Función para volver a inicio
const backToHome = (type, message = "Ha ocurrido un error desconocido.") => {
    if (type) {
        alert(message);
    }
    router.push("/");
};

// Estado del tablero
const board = ref(
    Array(10)
        .fill(null)
        .map(() => Array(10).fill(null))
);
const selectedShip = ref(null);
const isVertical = ref(false);

// Definición de barcos disponibles
const availableShips = ref([]);

// Cargar barcos desde la API
const loadShips = async () => {
    try {
        const response = await fetch("/api/game-ships");
        const ships = await response.json();
        availableShips.value = ships.map((ship) => ({
            ...ship,
            placed: false,
        }));
    } catch (error) {
        console.error("Error loading ships:", error);
    }
};

// Temporizador
const timeLeft = ref(20);
const timerInterval = ref(null);

const formatTime = (seconds) => {
    return `${Math.floor(seconds / 60)}:${(seconds % 60)
        .toString()
        .padStart(2, "0")}`;
};

const startTimer = () => {
    timerInterval.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval.value);
            // Aquí puedes manejar qué sucede cuando se acaba el tiempo
            confirmPlacement();
        }
    }, 1000);
};

// Función sleep que devuelve una promesa
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

onMounted(() => {
    console.log("SHIP PLACEMENT.");
    console.log("Game code:", gameStore.matchCode);
    console.log("User: ", authStore().user);

    loadShips();
    startTimer();
});

onUnmounted(() => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }
});

// Computed para verificar si todos los barcos están colocados
const isPlacementComplete = computed(() => {
    return availableShips.value.every((ship) => ship.placed);
});

// Métodos para el manejo de la colocación
const selectShip = (ship) => {
    if (!ship.placed) {
        selectedShip.value = ship;
    }
};

const rotateShip = () => {
    isVertical.value = !isVertical.value;
};

const isValidPlacement = (row, col) => {
    if (!selectedShip.value) return false;

    const size = selectedShip.value.size;

    // Verificar límites del tablero
    if (isVertical.value) {
        if (row + size > 10) return false;
    } else {
        if (col + size > 10) return false;
    }

    // Verificar superposición con otros barcos
    for (let i = 0; i < size; i++) {
        if (isVertical.value) {
            if (board.value[row + i][col] !== null) return false;
        } else {
            if (board.value[row][col + i] !== null) return false;
        }
    }

    return true;
};

const handleDrop = (event, row, col) => {
    if (selectedShip.value && isValidPlacement(row, col)) {
        placeShip(row, col);
    }
};

const placeShip = (row, col) => {
    if (!selectedShip.value) return;

    const ship = selectedShip.value;

    // Colocar el barco en el tablero
    for (let i = 0; i < ship.size; i++) {
        if (isVertical.value) {
            board.value[row + i][col] = ship.name;
        } else {
            board.value[row][col + i] = ship.name;
        }
    }

    // Marcar el barco como colocado
    ship.placed = true;
    selectedShip.value = null;
};

const resetPlacement = () => {
    board.value = Array(10)
        .fill(null)
        .map(() => Array(10).fill(null));
    availableShips.value.forEach((ship) => (ship.placed = false));
    selectedShip.value = null;
};

const getShipsPositions = () => {
    const shipsInfo = {};

    // Recorremos el tablero buscando barcos
    for (let row = 0; row < 10; row++) {
        for (let col = 0; col < 10; col++) {
            const shipName = board.value[row][col];
            if (shipName && !shipsInfo[shipName]) {
                const positions = [];

                // Verificar dirección horizontal
                let isHorizontal =
                    col + 1 < 10 && board.value[row][col + 1] === shipName;

                if (isHorizontal) {
                    // Recolectar todas las posiciones horizontalmente (+1 para empezar desde 1)
                    for (
                        let c = col;
                        c < 10 && board.value[row][c] === shipName;
                        c++
                    ) {
                        positions.push(`${row + 1},${c + 1}`);
                    }
                } else {
                    // Recolectar todas las posiciones verticalmente (+1 para empezar desde 1)
                    for (
                        let r = row;
                        r < 10 && board.value[r][col] === shipName;
                        r++
                    ) {
                        positions.push(`${r + 1},${col + 1}`);
                    }
                }

                shipsInfo[shipName] = positions;
            }
        }
    }
    return JSON.stringify(shipsInfo);
};

const verifyAllShipsPlaced = (shipsInfo) => {
    // Convertir el JSON string a objeto
    const placedShips = JSON.parse(shipsInfo);

    // Obtener nombres de los barcos colocados
    const placedShipNames = Object.keys(placedShips);

    // Obtener nombres de los barcos disponibles
    const availableShipNames = availableShips.value.map((ship) => ship.name);

    // Verificar que todos los barcos disponibles están colocados
    return availableShipNames.every((shipName) =>
        placedShipNames.includes(shipName)
    );
};

const isLoading = ref(false);

const confirmPlacementButton = () => {
    // Mostrar pantalla de carga
    isLoading.value = true;
};

const confirmPlacement = async () => {
    if (isPlacementComplete.value || timeLeft.value <= 0) {
        const shipsInfo = getShipsPositions();

        if (shipsInfo === "{}") {
            // console.log("No ships have been placed.");
            backToHome(true, "No se han colocado barcos en el tablero.");
        } else {
            // Verificar que todos los barcos están colocados
            if (!verifyAllShipsPlaced(shipsInfo)) {
                // console.log("Not all ships have been placed correctly.");
                backToHome(
                    true,
                    "No se han colocado todos los barcos correctamente."
                );
                return;
            }

            // Subir las coordenadas de los barcos a la DB
            try {
                const response = await axios.post(
                    "/api/games/store-ship-placement",
                    {
                        gameCode: gameStore.matchCode,
                        user: authStore().user,
                        shipsInfo: shipsInfo,
                    }
                );

                // En caso de haberse subido correctamente, esperar 5 segundos
                if (response.data.status == "success") {
                    // Mostrar pantalla de carga
                    isLoading.value = true;

                    // Esperar 5 segundos a que el otro usuario suba los barcos
                    // console.log("Esperando 5 segs a que el otro usuario ponga datos.");
                    await sleep(5000);

                    // Obtener si el otro usuario ha subido barcos
                    const response = await axios.post(
                        "/api/games/get-opponent-ship-placement-validation",
                        {
                            gameCode: gameStore.matchCode,
                            user: authStore().user,
                        }
                    );

                    // Validación de subida de barcos del oponente
                    if (
                        response.data.status == "success" &&
                        response.data.message == "OK"
                    ) {
                        console.log("El oponente ha subido barcos.");

                        // Eliminar pantalla de carga
                        isLoading.value = false;

                        // Cambiar a la fase de juego: gamePlay
                        gameStore.setGamePhase("playing");
                    } else if (
                        response.data.status == "success" &&
                        response.data.message == "NOK"
                    ) {
                        // console.log("El oponente no ha subido barcos.");
                        backToHome(
                            true,
                            "El oponente no ha colocado sus barcos."
                        );
                    } else {
                        // console.log("Error al verificar los barcos del oponente.");
                        backToHome(
                            true,
                            "Error al verificar los barcos del oponente."
                        );
                    }
                } else {
                    // console.error("Error al subir los barcos:", response.data.message);
                    backToHome(
                        true,
                        "Error al guardar la posición de los barcos."
                    );
                }

                // Verificar si el otro usuario ha subido barcos
            } catch (error) {
                // console.error("Error al subir los barcos:", error);
                backToHome(true, "Error al conectar con el servidor.");
            }
        }
    } else {
        console.log("No se han colocado los barcos.");
        backToHome(false, "No se han colocado los barcos");
    }
};

// Event handlers para drag and drop
const handleDragStart = (event, ship) => {
    if (!ship.placed) {
        selectedShip.value = ship;
    }
};

// Modificar la función getCellContent
const getCellContent = (row, col) => {
    return ""; // Ya no necesitamos retornar el emoji
};

// Corregir la definición de emit
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
    padding-top: 3rem; /* Añadido padding-top para mejor visualización */
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
    overflow: hidden; /* Añadido para contener los hijos */
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
    overflow-y: auto; /* Permitir scroll vertical si es necesario */
    max-height: calc(100% - 35px); /* Restar espacio de título y separador */
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
    padding: 0.25rem; /* Reducido para ahorrar espacio */
    border-radius: 8px;
    transition: all 0.3s ease;
    background: transparent;
    border: none;
    margin: 0.15rem; /* Reducido para ahorrar espacio */
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
    font-size: 16px; /* Reducido para mejor ajuste */
    white-space: nowrap; /* Evitar saltos de línea */
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

/* Clases base de visibilidad */
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
        padding: 1rem 0.5rem 0.5rem 0.5rem; /* Ajustado padding para móviles */
        flex-direction: column;
    }
}

/* Estilos para la pantalla de carga */
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

/* Ajustes específicos para alturas pequeñas */
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

/* Para pantallas simultáneamente pequeñas y estrechas */
@media (max-height: 650px) and (max-width: 768px) {
    .ship-placement {
        padding: 2rem 0.5rem 0.5rem 0.5rem;
    }

    .game-layout {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
        padding-top: 1rem; /* Ajustado para pantallas pequeñas */
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

/* Ajustes extremos para pantallas muy pequeñas */
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
</style>
