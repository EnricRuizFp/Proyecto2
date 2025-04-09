<template>
    <div class="view-game">
        <h2 class="white-color">Visualizando Partida: {{ gameCode }}</h2>
        <div class="boards-container">
            <!-- Primer tablero -->
            <div class="board-section">
                <h2 class="title">{{ player1Name }}</h2>
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`row-${row}`" class="board-row">
                            <div v-for="col in 10" :key="`cell-${row}-${col}`" 
                                class="board-cell"
                                :class="{
                                    ship: player1Board[row - 1][col - 1]
                                }">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segundo tablero -->
            <div class="board-section">
                <h2 class="title">{{ player2Name }}</h2>
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`attack-row-${row}`" class="board-row">
                            <div v-for="col in 10" :key="`attack-cell-${row}-${col}`" 
                                class="board-cell"
                                :class="{
                                    ship: player2Board[row - 1][col - 1]
                                }">
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
import { authStore } from "@/store/auth";

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

            console.log("GAME: ", currentGame.value);
            
            // Establecer nombres de jugadores
            player1Name.value = currentGame.value.players[0].username;
            player2Name.value = currentGame.value.players[1].username;

            // Establecer los tableros de cada jugador
            if (currentGame.value.players[0].coordinates) {
                setBoard(player1Board, JSON.parse(currentGame.value.players[0].coordinates));
                console.log("Tablero 1 cargado: ", player1Board.value);
            }
            if (currentGame.value.players[1].coordinates) {
                setBoard(player2Board, JSON.parse(currentGame.value.players[1].coordinates));
                console.log("Tablero 2 cargado: ", player2Board.value);
            }
        }
    } catch (error) {
        console.error("Error al obtener los datos de la partida:", error);
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

// Función para el bucle de observación de la partida
const viewGameLoop = async () => {
    try {
        let partidaFinalizada = false;

        while(!partidaFinalizada) {

            const response = await axios.post('/api/games/view-game-moves', {
                gameCode: props.gameCode,
                user: authStore().user
            });

            console.log("Datos obtenidos: ", response.data);
            
            if (response.data.status === 'success') {
                currentGame.value = response.data.data;

                // Actualizar los tableros
                if (currentGame.value.players[0].coordinates) {
                    setBoard(player1Board, JSON.parse(currentGame.value.players[0].coordinates));
                }
                if (currentGame.value.players[1].coordinates) {
                    setBoard(player2Board, JSON.parse(currentGame.value.players[1].coordinates));
                }
            }

            // Esperar 2.5 segundos antes de cada iteración
            await new Promise(resolve => setTimeout(resolve, 2500));
        }
    } catch (error) {
        console.error("Error en el bucle de observación:", error);
    }
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
