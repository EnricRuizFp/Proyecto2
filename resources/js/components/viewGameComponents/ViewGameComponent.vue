<template>
    <div class="view-game">
        <h2 class="white-color">Visualizando Partida: {{ gameCode }}</h2>
        <div class="boards-container">
            <!-- Primer tablero -->
            <div class="board-section">
                <h2 class="title">Jugador 1</h2>
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`row-${row}`" class="board-row">
                            <div v-for="col in 10" :key="`cell-${row}-${col}`" class="board-cell">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segundo tablero -->
            <div class="board-section">
                <h2 class="title">Jugador 2</h2>
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`attack-row-${row}`" class="board-row">
                            <div v-for="col in 10" :key="`attack-cell-${row}-${col}`" class="board-cell">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";

defineProps({
    gameCode: {
        type: String,
        required: true
    }
});

const currentGame = ref(null);

// Función para obtener los datos de la partida
const getGameStatus = async () => {
    try {
        const response = await axios.post('/api/games/current-match-status', {
            gameCode: props.gameCode
        });
        
        if (response.data.status === 'success') {
            currentGame.value = response.data.data;
            console.log("Datos de la partida:", currentGame.value);
        }
    } catch (error) {
        console.error("Error al obtener los datos de la partida:", error);
    }
};

// Llamar a la función cuando el componente se monta
onMounted(() => {

    // Obtener y mostrar los datos actuales
    getGameStatus();

    // Game loop
});
</script>

<style scoped>
.view-game {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    width: 100%;
    height: 100vh; /* Cambiado de min-height a height */
    overflow: hidden; /* Evita el scroll */
}

.boards-container {
    display: flex;
    gap: 4rem; /* Reducido el gap para mejor ajuste */
    justify-content: center;
    align-items: center;
    width: 100%;
    height: calc(100% - 4rem); /* Altura restante después del título */
    padding: 0.5rem;
}

.board-container {
    width: min(400px, calc((100vh - 10rem) / 2)); /* Ajustado para altura */
    height: min(400px, calc((100vh - 10rem) / 2));
    padding: 0.25rem;
    border-radius: 8px;
    border: 2px solid var(--primary-color);
    background: var(--background-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.board-grid {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    border: 2px solid var(--primary-color);
    background: var(--neutral-color);
}

.board-row {
    display: flex;
    flex: 1;
}

.board-cell {
    flex: 1;
    aspect-ratio: 1;
    border: 1px solid var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: var(--neutral-color);
}

/* Media queries para responsividad */
@media (max-width: 1000px) {
    .boards-container {
        flex-direction: column;
        gap: 1rem; /* Reducido aún más para vista móvil */
    }

    .board-container {
        width: min(85vw, calc(100vh - 400px));
        height: min(85vw, calc(100vh - 400px));
        max-width: 300px; /* Reducido para mejor ajuste */
        max-height: 300px;
    }
}

.title {
    color: var(--white-color);
    text-align: center;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}
</style>
