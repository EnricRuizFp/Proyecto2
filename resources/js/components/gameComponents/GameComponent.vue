<template>
    <div class="game app-background-primary">

        <h2 class="title">Sus barcos</h2>
        <!-- Tablero del usuario -->
        <div class="board-container">
            <div class="board-grid">
                <div v-for="row in 10" :key="`row-${row}`" class="board-row">
                    <div v-for="col in 10" :key="`cell-${row}-${col}`" 
                        class="board-cell"
                        :class="{ ship: userBoard[row - 1][col - 1] }">
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
import { useRoute, useRouter } from 'vue-router';
import { useGameStore } from "../../store/game";

/* -- VARIABLES -- */
const route = useRoute();
const router = useRouter();
const gameStore = useGameStore(); // Utilizado para settear las fases del juego

// Estado del tablero del usuario
const userBoard = ref(Array(10).fill(null).map(() => Array(10).fill(null)));

/* -- FUNCTIONS -- */

// Función onMounted
onMounted(() => {
    console.log("GameComponent mounted");
    console.log("Game code:", gameStore.matchCode);
    console.log("User: ", authStore().user);

    loadShips();
});

// Función para volver a inicio
const backToHome = () => {
    router.push('/');
};

// Función loadShips (cargar los barcos del jugador)
const loadShips = async () => {

    // Verificación de usuario autenticado y código de partida
    if(authStore().user == null) {
        console.log("User not authenticated. Cannot load ships.");
        backToHome();
    }else if(gameStore.matchCode == "null"){
        console.log("No match code. Cannot load ships.");
        backToHome();
    }

    console.log("Obteniendo coordenadas de los barcos del jugador");

    // Obtener los barcos del jugador
    try {
        const response = await axios.post('/api/games/get-user-ship-placement', {
            gameCode: gameStore.matchCode,
            user: authStore().user
        });
        
        if (response.data.status === 'failed') {
            console.error("Error loading ships:", response.data.message);
            backToHome();
            return;
        }

        // Posicionar los barcos en el tablero
        setUserBoard(JSON.parse(response.data.data));
        // startGame();

    } catch (error) {
        console.error("Error loading ships:", error);
        backToHome();
    }
};

// Función para colocar los barcos en el tablero
const setUserBoard = (shipsData) => {
    // Resetear el tablero
    userBoard.value = Array(10).fill(null).map(() => Array(10).fill(null));

    // Recorrer cada barco y sus posiciones
    Object.entries(shipsData).forEach(([shipName, positions]) => {
        positions.forEach(pos => {
            // Split la posición en coordenadas X,Y y restar 1 para ajustar al índice 0
            const [row, col] = pos.split(',').map(num => parseInt(num) - 1);
            userBoard.value[row][col] = shipName;
        });
    });
};

</script>

<style scoped>
.game {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
    width: 100%;
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
</style>