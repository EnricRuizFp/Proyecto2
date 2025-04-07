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
}

.ships-dock {
    width: 450px; /* Aumentado para igualar el ancho total del board-container */
    height: 450px; /* Altura fija para igualar el board-container */
    padding: 0.75rem;
    background: var(--neutral-color-1);
    border-radius: 12px;
    border: 2px solid var(--primary-color);
    display: flex;
    flex-direction: column;
}

.ships-dock h3 {
    color: var(--white-color);
    margin-bottom: 0.5rem; /* Reducido para dar espacio al separador */
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
    gap: 0.25rem; /* Reducido de 0.5rem */
    align-items: center; /* Centrar los barcos */
    flex-grow: 1;
    padding: 0.25rem; /* Reducido de 0.5rem */
}

.board-container {
    width: 450px; /* Aumentado para igualar el ships-dock */
    height: 450px; /* Altura fija para igualar el ships-dock */
    padding: 1.5rem;
    border-radius: 12px;
    border: 2px solid var(--primary-color);
    background: var(--background-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.board-grid {
    width: 400px; /* Tamaño fijo para la cuadrícula */
    height: 400px; /* Tamaño fijo para la cuadrícula */
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

.ships-dock {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
    padding: 1rem;
    background: var(--neutral-v2-color);
    border-radius: 8px;
}

.ship-item {
    cursor: grab;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: transparent; /* Cambiado de neutral-color a transparent */
    border: none; /* Quitado el borde */
    margin: 0.25rem;
}

.ship-item:hover {
    border: none; /* Quitado el borde en hover */
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
    flex-direction: row; /* Siempre horizontal */
    background: transparent;
    border-radius: 4px;
    position: relative;
    height: 40px;
    width: 100%;
}

.ship-segment {
    width: 36px; /* Ancho fijo para cada segmento */
    height: 36px; /* Alto fijo para cada segmento */
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
        padding: 1rem;
        min-height: auto;
    }

    .game-layout {
        gap: 0.5rem;
        padding: 0.5rem;
        flex-direction: column;
    }

    .board-container {
        width: 320px;
        height: 320px;
        padding: 0.5rem;
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
        width: 320px;
        min-height: 120px; /* Reducido aún más al usar dos columnas */
        height: auto;
        padding: 0.5rem 0.25rem; /* Ajustar padding para dar más espacio al título */
        margin-bottom: 0.5rem;
        overflow: hidden; /* Evitar desbordamiento */
    }

    .ships-container {
        display: grid; /* Cambio a grid */
        grid-template-columns: repeat(2, 1fr); /* Dos columnas */
        gap: 0.15rem;
        padding: 0.15rem;
        justify-items: center;
        align-items: center;
        max-width: 100%; /* Asegurar que no exceda el contenedor */
        overflow-x: hidden; /* Evitar scroll horizontal */
    }

    .ship-item {
        --ship-size: calc(var(--width, 40px) / 40);
        transform: scale(0.8);
        transform-origin: center;
        margin: 0;
        padding: 0.25rem;
        width: auto;
        height: 40px; /* Aumentado de 35px */
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent; /* Cambiado a transparente */
        border: none; /* Quitado el borde */
        border-radius: 8px;
        cursor: grab;
        transition: all 0.3s ease;
    }

    .ship-preview {
        display: flex;
        flex-direction: row;
        background: transparent;
        border-radius: 4px;
        position: relative;
        height: 100%;
        width: 100%;
        justify-content: center;
        align-items: center;
        gap: 1px; /* Reducido para que los segmentos estén más juntos */
    }

    .ship-segment {
        width: 34px; /* Aumentado de 28px */
        height: 34px; /* Aumentado de 28px */
        margin: 1px;
        border-radius: 3px;
        flex: 0 0 auto;
    }

    .ship-item:hover {
        border: none; /* Quitado el borde en hover */
        transform: scale(0.8) translateY(-2px);
        box-shadow: 0 4px 12px
            rgba(112, 72, 236, calc(0.2 * var(--ship-size, 1)));
    }

    .ship-item.selected .ship-segment {
        background-color: var(--primary-color);
    }

    .ship-item.placed {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .controls {
        width: 320px; /* Mismo ancho que board-container y ships-dock */
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between; /* Cambio para mejor distribución */
        gap: 0.5rem;
        padding: 0.5rem;
    }

    .control-button {
        width: 36px; /* Reducido ligeramente */
        height: 36px;
        flex: 0 0 auto;
    }

    .timer {
        padding: 0.5rem 1rem;
        font-size: 16px;
        margin: 0;
        flex: 1;
        justify-content: center;
    }

    .timer-container {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .desktop-only {
        display: none;
    }

    .mobile-only {
        display: flex;
    }

    .timer.mobile-only {
        max-width: 120px; /* Limitar el ancho del timer */
        padding: 0.5rem;
        font-size: 14px;
        margin: 0;
        flex: 0 0 auto; /* Evitar que se expanda */
    }

    .controls {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 0.5rem;
        width: 100%;
    }

    .control-button {
        flex: 0 0 auto;
    }

    .timer-container.desktop-only {
        display: none;
    }

    .timer.mobile-only {
        display: flex;
        padding: 0.5rem 1rem;
        font-size: 16px;
        margin: 0;
        flex: 1;
        justify-content: center;
    }

    .dock-title {
        font-size: 13px; /* Reducido para mejor ajuste */
        margin-bottom: 0.15rem;
        padding: 0 0.25rem;
        line-height: 1.2;
        white-space: normal;
    }

    .ships-container {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.1rem;
        padding: 0.15rem;
        width: 100%;
        max-width: 310px; /* Ajustado al ancho del dock menos los paddings */
        margin: 0 auto;
    }

    .ship-item {
        transform: scale(0.45); /* Reducido más para evitar desbordamiento */
        transform-origin: center;
        margin: 0;
        padding: 0.1rem;
        width: 100%;
        max-width: 150px; /* Limitar ancho máximo */
    }

    .ship-segment {
        width: 18px; /* Reducido */
        height: 18px;
        margin: 1px;
    }
}

@media (max-width: 480px) {
    .ships-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        padding: 0.5rem;
        justify-items: center;
        align-items: center;
        width: 100%;
    }

    .ship-item {
        margin: 0;
        padding: 0.25rem;
        width: auto;
        height: auto; /* Cambiado a auto para ajustarse al contenido */
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent; /* Cambiado a transparente */
        border: none; /* Quitado el borde */
        border-radius: 8px;
        cursor: grab;
        transition: all 0.3s ease;
    }

    .ship-preview {
        display: flex;
        flex-direction: row;
        background: transparent;
        position: relative;
        justify-content: center;
        align-items: center;
        padding: 0;
        gap: 2px;
    }

    .ship-segment {
        width: 36px;
        height: 36px;
        margin: 0;
        border-radius: 4px;
        flex: 0 0 auto;
        background-color: var(--secondary-color);
    }
}

@media (max-width: 480px) {
    .desktop-only {
        display: none !important;
    }

    .mobile-only {
        display: flex !important;
    }

    .controls {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 0.5rem;
        width: 100%;
    }

    .timer.mobile-only {
        padding: 0.5rem 1rem;
        font-size: 16px;
        margin: 0;
        flex: 1;
        justify-content: center;
    }
}

.dock-title {
    text-align: center;
    margin-bottom: 0.25rem; /* Reducido de 0.5rem */
    color: var(--white-color);
    font-size: 18px; /* Reducido de 22px */
}

.separator {
    height: 2px;
    background: var(--primary-color);
    opacity: 0.5;
    margin-bottom: 0.5rem; /* Reducido de 1rem */
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
</style>
