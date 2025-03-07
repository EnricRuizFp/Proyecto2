<template>
    <div class="ship-placement">
        <!-- Grid del tablero -->
        <div class="board-grid">
            <div v-for="row in 10" :key="`row-${row}`" class="board-row">
                <div
                    v-for="col in 10"
                    :key="`cell-${row}-${col}`"
                    class="board-cell"
                    :class="{
                        'cell-hover': isValidPlacement(row - 1, col - 1),
                    }"
                    @dragover.prevent
                    @drop.prevent="handleDrop($event, row - 1, col - 1)"
                    @dragenter.prevent
                >
                    {{ getCellContent(row - 1, col - 1) }}
                </div>
            </div>
        </div>

        <!-- Lista de barcos para colocar -->
        <div class="ships-dock">
            <div
                v-for="(ship, index) in availableShips"
                :key="index"
                class="ship-item"
                :class="{ selected: selectedShip === ship }"
                draggable="true"
                @dragstart="handleDragStart($event, ship)"
                @click="selectShip(ship)"
            >
                <div
                    class="ship-preview"
                    :style="{ width: `${ship.size * 40}px` }"
                >
                    {{ ship.name }} ({{ ship.size }})
                </div>
            </div>
        </div>

        <!-- Controles -->
        <div class="controls">
            <button @click="rotateShip" class="control-button">
                Rotar Barco (R)
            </button>
            <button @click="resetPlacement" class="control-button">
                Reiniciar
            </button>
            <button
                @click="confirmPlacement"
                :disabled="!isPlacementComplete"
                class="control-button primary"
            >
                Confirmar
            </button>
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

// Añadir función getCellContent
const getCellContent = (row, col) => {
    return board.value[row][col] ? "🚢" : ""; // Muestra un emoji de barco o nada
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
    border: 1px solid var(--secondary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cell-hover {
    background-color: rgba(var(--primary-color-rgb), 0.2);
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
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.ship-preview {
    background: var(--primary-color);
    height: 38px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.selected .ship-preview {
    background: var(--primary-v2-color);
    outline: 2px solid var(--secondary-color);
}

.controls {
    display: flex;
    gap: 1rem;
}

.control-button {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background: var(--neutral-v2-color);
    color: var(--white-color);
    transition: all 0.3s ease;
}

.control-button:hover {
    background: var(--primary-color);
}

.control-button.primary {
    background: var(--primary-color);
}

.control-button.primary:hover {
    background: var(--primary-v2-color);
}

.control-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
