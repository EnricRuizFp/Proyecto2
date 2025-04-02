<template>
    <div class="game app-background-primary">
        <div class="boards-container">
            <!-- Tablero del usuario -->
            <div class="board-section">
                <h2 class="title">Sus barcos</h2>
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

            <!-- Tablero de ataque -->
            <div class="board-section">
                <h2 class="title">Tablero de ataque</h2>
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`attack-row-${row}`" class="board-row">
                            <div v-for="col in 10" 
                                :key="`attack-cell-${row}-${col}`" 
                                class="board-cell clickable"
                                @click="handleAttack(row - 1, col - 1)"
                                :class="{ hit: attackBoard[row - 1][col - 1] === '✓', miss: attackBoard[row - 1][col - 1] === '✗' }"
                            >
                                <span v-if="attackBoard[row - 1][col - 1]">{{ attackBoard[row - 1][col - 1] }}</span>
                            </div>
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
import { useRoute, useRouter } from 'vue-router';
import { useGameStore } from "../../store/game";

/* -- VARIABLES -- */
const route = useRoute();
const router = useRouter();
const gameStore = useGameStore(); // Utilizado para settear las fases del juego
let matchHost = ref(null);
const yourTurn = ref(null);

// Estado del tablero del usuario
const userBoard = ref(Array(10).fill(null).map(() => Array(10).fill(null)));

// Estado del tablero de ataque
const attackBoard = ref(Array(10).fill(null).map(() => Array(10).fill(null)));

/* -- FUNCTIONS -- */

// Función onMounted
onMounted( async () => {
    console.log("GameComponent mounted");
    console.log("Game code:", gameStore.matchCode);
    console.log("User: ", authStore().user);

    // Cargar el tablero del usuario
    await loadShips();

    // Decidir qué usuario empieza
    const response = await getMatchInfo();
    matchHost = response.game.created_by;
    console.log("Match host:", matchHost);
    if(matchHost == authStore().user.id) {
        
        console.log("Eres el creador, empiezas");
        yourTurn.value = true;

    } else {
        console.log("No eres el creador, esperas tu turno");
        yourTurn.value = false;
    }


});

// Función para volver a inicio
const backToHome = (type, message = "Ha ocurrido un error desconocido.") => {
    if(type){alert(message);}
    router.push('/');
};

// Función loadShips (cargar los barcos del jugador)
const loadShips = async () => {

    // Verificación de usuario autenticado y código de partida
    if(authStore().user == null) {
        // console.log("User not authenticated. Cannot load ships.");
        backToHome(true, "No tienes permiso para acceder a esta página (user)");
    }else if(gameStore.matchCode == "null"){
        // console.log("No match code. Cannot load ships.");
        backToHome(false, "No tienes permiso para acceder a esta página (code)");
    }

    console.log("Obteniendo coordenadas de los barcos del jugador");

    // Obtener los barcos del jugador
    try {
        const response = await axios.post('/api/games/get-user-ship-placement', {
            gameCode: gameStore.matchCode,
            user: authStore().user
        });
        
        if (response.data.status === 'failed') {
            // console.error("Error loading ships:", response.data.message);
            backToHome(true, "Error al cargar la partida.");
            return;
        }

        // Posicionar los barcos en el tablero
        setUserBoard(JSON.parse(response.data.data));

    } catch (error) {
        // console.error("Error loading ships:", error);
        backToHome(true, "Error al cargar la partida.");
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

// Obtener los datos de la partida actual
const getMatchInfo = async () => {

    // Obtener los datos de la partida de la API
    const response = await axios.post('/api/games/get-match-info', {
        gameCode: gameStore.matchCode
    });
    return response.data.data;
}

// Función para manejar el ataque
const handleAttack = async (row, col) => {
    if (!attackBoard.value[row][col]) { // Solo si la celda está vacía
        try {
            const response = await axios.post('/api/games/attack', {
                gameCode: gameStore.matchCode,
                user: authStore().user,
                coordinates: `${row + 1},${col + 1}`
            });

            if (response.data.status === 'success') {
                // Marcar la celda según el resultado
                attackBoard.value[row][col] = response.data.message === 'hit' ? '✓' : '✗';
                console.log(`Ataque en ${row + 1},${col + 1} - Resultado: ${response.data.message}` + 
                    (response.data.ship ? ` - Barco: ${response.data.ship}` : ''));
            } else {
                console.error('Error en el ataque:', response.data.message);
            }
        } catch (error) {
            console.error('Error al realizar el ataque:', error);
        }
    }
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

.board-cell.hit {
    background-color: #28a745;
}

.board-cell.miss {
    background-color: #dc3545;
}

/* Nuevos estilos */
.boards-container {
    display: flex;
    gap: 2rem;
    justify-content: center;
    align-items: start;
    width: 100%;
    padding: 2rem;
}

.board-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.clickable {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.clickable:hover {
    background-color: #7048ec33;
}

/* Media query para dispositivos móviles */
@media (max-width: 960px) {
    .boards-container {
        flex-direction: column;
        align-items: center;
    }
}
</style>