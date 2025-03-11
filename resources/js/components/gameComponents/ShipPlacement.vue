<template>
    <div class="ship-placement">
        <div class="game-layout">
            <!-- Lista de barcos para colocar -->
            <div class="ships-dock">
                <h3>Barcos Disponibles</h3>
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
                    @click="confirmPlacement"
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
import { ref, computed } from "vue";

// Estado del tablero
const board = ref(
    Array(10)
        .fill(null)
        .map(() => Array(10).fill(null))
);
const selectedShip = ref(null);
const isVertical = ref(false);

// Definición de barcos disponibles
const availableShips = ref([
    { name: "Portaaviones", size: 5, placed: false },
    { name: "Acorazado", size: 4, placed: false },
    { name: "Crucero", size: 3, placed: false },
    { name: "Submarino", size: 3, placed: false },
    { name: "Destructor", size: 2, placed: false },
]);

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

const confirmPlacement = () => {
    if (isPlacementComplete.value) {
        emit("placement-confirmed", board.value);
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
    gap: 2rem;
    padding: 2rem;
}

.game-layout {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.ships-dock {
    width: 400px;
    min-height: 400px;
    padding: 1rem;
    background: var(--neutral-v2-color);
    border-radius: 8px;
    border: 2px solid var(--primary-color);
}

.ships-dock h3 {
    color: var(--white-color);
    margin-bottom: 1rem;
    text-align: center;
}

.ships-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem; /* Reducido de 1rem */
    align-items: center; /* Centrar los barcos */
}

.board-grid {
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
    padding: 0.25rem; /* Reducido de 0.5rem */
    border-radius: 4px;
    transition: all 0.3s ease;
    background: var(--neutral-color); /* Fondo para el contenedor del barco */
    margin: 0.25rem; /* Reducido de 0.5rem */
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
}

.control-button i {
    font-size: 1.5rem;
    color: var(--primary-color);
    transition: all 0.3s ease;
    text-shadow: 0 0 3px #7048ec4d;
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
    color: var(--white-color);
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
</style>
