<template>
    <div class="view-game">
        <!-- Estado de carga inicial -->
        <div v-if="isInitialLoading" class="loading-state">
            <div class="loading-overlay">
                <i class="fas fa-spinner fa-spin"></i>
                <p class="p3-dark">La partida está a punto de empezar...</p>
            </div>
        </div>

        <h2 class="white-color">Visualizando Partida: {{ gameCode }}</h2>
        <div class="boards-container">
            <!-- Primer tablero - Muestra barcos del jugador 1 y ataques del jugador 2 -->
            <div class="board-section">
                <ViewUserComponent :userId="player1Id" />
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`row-${row}`" class="board-row">
                            <div v-for="col in 10" :key="`cell-${row}-${col}`" 
                                class="board-cell"
                                :class="{
                                    ship: player1Board[row - 1][col - 1],
                                    hit: player2Moves[`${row},${col}`] === 'hit',
                                    miss: player2Moves[`${row},${col}`] === 'miss'
                                }">
                                <svg v-if="player2Moves[`${row},${col}`] === 'hit'"
                                    class="hit-marker"
                                    viewBox="0 0 24 24">
                                    <path d="M12 2 L14 7 L19 5 L16 10 L21 12 L16 14 L19 19 L14 17 L12 22 L10 17 L5 19 L8 14 L3 12 L8 10 L5 5 L10 7 Z"
                                        fill="red" stroke="darkred" stroke-width="1"/>
                                    <circle cx="12" cy="12" r="3" fill="darkred"/>
                                </svg>
                                <svg v-if="player2Moves[`${row},${col}`] === 'miss'"
                                    class="miss-marker"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="none"/>
                                    <circle cx="12" cy="12" r="3" fill="#0d6efd"/>
                                    <line x1="12" y1="2" x2="12" y2="22" stroke="#0d6efd" stroke-width="2"/>
                                    <line x1="2" y1="12" x2="22" y2="12" stroke="#0d6efd" stroke-width="2"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segundo tablero - Muestra barcos del jugador 2 y ataques del jugador 1 -->
            <div class="board-section">
                <ViewUserComponent :userId="player2Id" />
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`attack-row-${row}`" class="board-row">
                            <div v-for="col in 10" :key="`attack-cell-${row}-${col}`" 
                                class="board-cell"
                                :class="{
                                    ship: player2Board[row - 1][col - 1],
                                    hit: player1Moves[`${row},${col}`] === 'hit',
                                    miss: player1Moves[`${row},${col}`] === 'miss'
                                }">
                                <svg v-if="player1Moves[`${row},${col}`] === 'hit'"
                                    class="hit-marker"
                                    viewBox="0 0 24 24">
                                    <path d="M12 2 L14 7 L19 5 L16 10 L21 12 L16 14 L19 19 L14 17 L12 22 L10 17 L5 19 L8 14 L3 12 L8 10 L5 5 L10 7 Z"
                                        fill="red" stroke="darkred" stroke-width="1"/>
                                    <circle cx="12" cy="12" r="3" fill="darkred"/>
                                </svg>
                                <svg v-if="player1Moves[`${row},${col}`] === 'miss'"
                                    class="miss-marker"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="#0d6efd" stroke-width="2" fill="none"/>
                                    <circle cx="12" cy="12" r="3" fill="#0d6efd"/>
                                    <line x1="12" y1="2" x2="12" y2="22" stroke="#0d6efd" stroke-width="2"/>
                                    <line x1="2" y1="12" x2="22" y2="12" stroke="#0d6efd" stroke-width="2"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ViewGameResultComponent
            :visible="showResult"
            :winner-name="winnerName"
            :is-draw="isDraw"
            @cleanup="handleCleanup"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { authStore } from "@/store/auth";
import ViewGameResultComponent from "./ViewGameResultComponent.vue";
import ViewUserComponent from './ViewUserComponent.vue';
import { useRouter } from "vue-router";

const props = defineProps({
    gameCode: {
        type: String,
        required: true
    }
});

// Función para volver a inicio
const backToHome = () => {
    router.push('/');
};

const currentGame = ref(null);

// Variables para los tableros
const player1Board = ref(Array(10).fill(null).map(() => Array(10).fill(null)));
const player2Board = ref(Array(10).fill(null).map(() => Array(10).fill(null)));
const player1Name = ref('');
const player2Name = ref('');
const isInitialLoading = ref(true); // Nueva variable para controlar la carga inicial

// Variables para los movimientos
const player1Moves = ref({});
const player2Moves = ref({});

// Referencias para IDs de jugadores
const player1Id = ref(null);
const player2Id = ref(null);

const router = useRouter();
const showResult = ref(false);
const winnerName = ref('');
const isDraw = ref(false);

// Función para colocar los barcos en el tablero
const setBoard = (board, shipsData) => {
    Object.entries(shipsData).forEach(([shipName, positions]) => {
        positions.forEach((pos) => {
            const [row, col] = pos.split(",").map((num) => parseInt(num) - 1);
            board.value[row][col] = shipName;
        });
    });
};

// Función para obtener los datos de la partida
const getGameStatus = async () => {
    try {
        const response = await axios.post('/api/games/get-current-match-status', {
            gameCode: props.gameCode
        });
        
        if (response.data.status === 'success') {
            currentGame.value = response.data.data;

            // Asignar IDs de jugadores
            player1Id.value = currentGame.value.players[0]?.id || null;
            player2Id.value = currentGame.value.players[1]?.id || null;

            // Establecer nombres de jugadores
            player1Name.value = currentGame.value.players[0]?.username || '';
            player2Name.value = currentGame.value.players[1]?.username || '';

            // Establecer los tableros de cada jugador y verificar si hay datos
            let hasBoards = false;
            if (currentGame.value.players[0].coordinates) {
                setBoard(player1Board, JSON.parse(currentGame.value.players[0].coordinates));
                console.log("Tablero 1 cargado: ", player1Board.value);
                hasBoards = true;
            }
            if (currentGame.value.players[1].coordinates) {
                setBoard(player2Board, JSON.parse(currentGame.value.players[1].coordinates));
                console.log("Tablero 2 cargado: ", player2Board.value);
                hasBoards = true;
            }

            // Ocultar la pantalla de carga solo si hay tableros
            isInitialLoading.value = !hasBoards;
        }
    } catch (error) {
        console.error("Error al obtener los datos de la partida:", error);
        isInitialLoading.value = false;
    }
};

// Unir el usuario a la observación de la partida
const viewGame = async (gameCode) => {
    try {
        const response = await axios.post('/api/games/view-game', {
            gameCode: gameCode,
            user: authStore().user
        });
        
        if (response.data.status === 'success') {
            return true;
        }
    } catch (error) {
        console.error("Error al observar la partida:", error);
        return false;
    }
};

// Función para verificar si hay un ganador
const checkWinner = (game) => {
    if (!game) return false;

    const player1Ships = Object.values(JSON.parse(game.players[0].coordinates)).flat().length;
    const player2Ships = Object.values(JSON.parse(game.players[1].coordinates)).flat().length;
    const player1Hits = game.players[0].moves.filter(move => move.result === 'hit').length;
    const player2Hits = game.players[1].moves.filter(move => move.result === 'hit').length;

    // Si algún jugador ha hundido todos los barcos del oponente
    if (player1Hits === player2Ships) {
        winnerName.value = game.players[0].username;
        showResult.value = true;
        return true;
    } else if (player2Hits === player1Ships) {
        winnerName.value = game.players[1].username;
        showResult.value = true;
        return true;
    }

    // Verificar empate (cuando se agotan los movimientos)
    const maxMoves = player1Ships * 2; // Ajusta este valor según tus reglas
    if (game.players[0].moves.length >= maxMoves && game.players[1].moves.length >= maxMoves) {
        isDraw.value = true;
        showResult.value = true;
        return true;
    }

    return false;
};

// Función para el bucle de observación de la partida
const viewGameLoop = async () => {
    try {
        let partidaFinalizada = false;

        while(!partidaFinalizada) {
            try {
                const response = await axios.post('/api/games/view-game-moves', {
                    gameCode: props.gameCode,
                    user: authStore().user
                });

                if (response.data.status === 'success') {
                    currentGame.value = response.data.data;

                    // Procesar movimientos del jugador 1
                    player1Moves.value = {};
                    currentGame.value.players[0].moves.forEach(move => {
                        const [row, col] = move.coordinate.split(',');
                        player1Moves.value[`${row},${col}`] = move.result;
                    });

                    // Procesar movimientos del jugador 2
                    player2Moves.value = {};
                    currentGame.value.players[1].moves.forEach(move => {
                        const [row, col] = move.coordinate.split(',');
                        player2Moves.value[`${row},${col}`] = move.result;
                    });

                    // Actualizar los tableros
                    if (currentGame.value.players[0].coordinates) {
                        setBoard(player1Board, JSON.parse(currentGame.value.players[0].coordinates));
                    }
                    if (currentGame.value.players[1].coordinates) {
                        setBoard(player2Board, JSON.parse(currentGame.value.players[1].coordinates));
                    }

                    // Verificar si la partida ha terminado
                    if (currentGame.value.game_status.is_finished) {
                        if (currentGame.value.game_status.winner === 'draw') {
                            isDraw.value = true;
                        } else {
                            winnerName.value = currentGame.value.game_status.winner;
                        }
                        showResult.value = true;
                        partidaFinalizada = true;
                    }
                }
            } catch (error) {
                console.error("Error en iteración del bucle:", error.response?.data || error);
                await new Promise(resolve => setTimeout(resolve, 5000));
            }
            if (!partidaFinalizada) {
                await new Promise(resolve => setTimeout(resolve, 2500));
            }
        }
    } catch (error) {
        console.error("Error en el bucle de observación:", error);
    }
};

const handleCleanup = () => {
    showResult.value = false;
    currentGame.value = null;
    router.push('/');
};

// Llamar a la función cuando el componente se monta
onMounted(() => {

    // Verificar si el usuario está autenticado y se ha proporcionado un código de partida
    if(!authStore().user) {
        console.error("Usuario no autenticado.");
        backToHome();
    }else if(!props.gameCode) {
        console.error("Código de partida no proporcionado.");
        backToHome();
    }

    try{

        if(viewGame(props.gameCode)){

            console.log("Observando partida.");

            // Obtener y mostrar los datos actuales
            getGameStatus();

            // Game loop
            viewGameLoop();
        }


    }catch(error) {
        console.error("Error al observar la partida:", error);
    }
    
});

onUnmounted(() => {
    handleCleanup();
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

.hit-marker,
.miss-marker {
    width: 30px;
    height: 30px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.board-cell.hit,
.board-cell.miss {
    background-color: transparent !important;
}

/* Asegurar que el SVG esté por encima del fondo del barco */
.board-cell.ship.hit::after {
    background-color: transparent;
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
    padding: 2rem; /* Añadido padding exterior */
}

.loading-overlay {
    position: relative;
    width: 340px; /* Aumentado para compensar el padding interior */
    padding: 2.5rem;
    background: var(--background-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    border-radius: 12px; /* Aumentado para mejor apariencia */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); /* Añadido sombra para mejor profundidad */
}

.loading-overlay i {
    font-size: 2.5rem;
    color: var(--primary-color);
}

.loading-overlay p {
    margin: 0;
    padding: 0 1rem;
    text-align: center;
    width: 100%; /* Asegura que el texto use todo el ancho disponible */
}

.board-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}
</style>
