<template>
    <div class="game app-background-primary">
        <!-- Estado del juego -->
        <div class="game-status">
            <div v-if="yourTurn" class="timer">
                <i class="fas fa-clock"></i>
                <span>{{ timeLeft }}s</span>
            </div>
            <div v-else class="waiting-message">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Esperando a que el oponente juegue...</span>
            </div>
        </div>

        <!-- Sistema de notificaciones -->
        <div v-if="notification.show" class="notification" :class="notification.type">
            <span>{{ notification.message }}</span>
        </div>

        <div class="boards-container">
            <!-- Tablero del usuario -->
            <div class="board-section">
                <h2 class="title">Sus barcos</h2>
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`row-${row}`" class="board-row">
                            <div v-for="col in 10" :key="`cell-${row}-${col}`" 
                                class="board-cell"
                                :class="{ 
                                    'ship': userBoard[row - 1][col - 1],
                                    'hit': userBoard[row - 1][col - 1] === 'X',
                                    'miss': userBoard[row - 1][col - 1] === 'O'
                                }">
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
import { ref, onMounted, onUnmounted } from "vue";
import { authStore } from "../../store/auth";
import { useRoute, useRouter } from 'vue-router';
import { useGameStore } from "../../store/game";

/* -- VARIABLES -- */
const route = useRoute();
const router = useRouter();
const gameStore = useGameStore();
const yourTurn = ref(null);
const timeLeft = ref(25);
const isGameActive = ref(true);
const userBoard = ref(Array(10).fill(null).map(() => Array(10).fill(null)));
const attackBoard = ref(Array(10).fill(null).map(() => Array(10).fill(null)));
const notification = ref({ show: false, message: '', type: 'info' });

/* -- FUNCTIONS -- */

// Función para mostrar notificaciones
const showNotification = (message, type = 'info') => {
    notification.value = { show: true, message, type };
    setTimeout(() => { notification.value.show = false; }, 3000);
};

// Función para manejar el ataque
const handleAttack = async (row, col) => {
    if (!yourTurn.value || attackBoard.value[row][col] || !isGameActive.value) {
        console.log("Ataque inválido. Turno no permitido o celda ya atacada.");
        return;
    }

    try {
        console.log("Realizando ataque en la posición:", row, col);
        const response = await axios.post('/api/games/attack', {
            gameCode: gameStore.matchCode,
            user: authStore().user,
            coordinates: `${row + 1},${col + 1}`
        });

        if (response.data.status === 'success') {
            const result = response.data.message;
            attackBoard.value[row][col] = result === 'hit' ? '✓' : '✗';

            if (result === 'hit' && response.data.ship) {
                showNotification(`¡Has acertado al ${response.data.ship}!`, 'success');
            }

            await checkMatchWinner(); // Llamar a la nueva función para verificar el ganador
            yourTurn.value = false; // Cambiar el turno al oponente
        }
    } catch (error) {
        console.error("Error en el ataque:", error);
    }
};

// Función para verificar si el usuario ha ganado la partida
const checkMatchWinner = async () => {
    try {
        // Obtener las coordenadas de los barcos del oponente
        const response = await axios.post('/api/games/get-opponent-ship-placement-game', {
            gameCode: gameStore.matchCode,
            user: authStore().user
        });

        console.log("Respuesta de la función:", response.data);

        if(response.data.has_winned == false){

            if(response.data.move_count < 100){
                showNotification("Te quedan " + response.data.ships_left + " barcos por hundir.", "info");
            }else{
                console.log("Has llegado al límite de movimientos.");
                // Finalizar el juego
                await endGame("draw");
            }
            // console.log("Te quedan", response.data.ships_left , " barcos por hundir.");
            

        }else if(response.data.has_winned == true){

            console.log("Has ganado la partida!");

            // Mostrar notificación de victoria
            showNotification("¡Has ganado la partida!", "success");

            // Finalizar el juego
            await endGame("winner");
        }else{
            console.log("No se ha podido determinar el ganador.");
        }

    } catch (error) {
        console.error("Error al verificar el ganador:", error);
    }
};

// Función para finalizar la partida (ganando)
const endGame = async (status) => {
    try {
        console.log("Setteando el ganador de la partida...");

        const response = await axios.post('/api/games/set-game-ending', {
            gameCode: gameStore.matchCode,
            user: authStore().user,
            status: status
        });

        if (response.data.status === 'success') {
            console.log("Partida finalizada con éxito.");

            if(status == "winner"){
                console.log("GANADOR");
                gameStore.setShowWin(true);
            } else {
                console.log("EMPATE");
                gameStore.setShowDraw(true);
            }
        }
    } catch (error) {
        console.error("Error al finalizar la partida:", error);
    }
};

// Función para manejar el turno de jugar
const playTurn = async () => {
    console.log("Tu turno. Tienes 25 segundos para atacar.");
    timeLeft.value = 25;

    const timer = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timer);
            console.log("Se acabó el tiempo de tu turno.");
        }
    }, 1000);

    while (timeLeft.value > 0 && yourTurn.value) {
        await new Promise(resolve => setTimeout(resolve, 500));
    }

    clearInterval(timer);
    console.log("Turno finalizado.");
};

// Función para manejar el turno de esperar
const waitTurn = async () => {
    console.log("Esperando el movimiento del oponente...");
    let opponentMoved = false;
    let attempts = 0;

    while (!opponentMoved && attempts < 12) { // 12 intentos de 2.5 segundos = 30 segundos
        attempts++;
        console.log(`Intento ${attempts}/12: Verificando movimiento del oponente...`);

        try {
            const response = await axios.post('/api/games/get-last-move', {
                gameCode: gameStore.matchCode,
                user: authStore().user
            });

            if (response.data.status === 'success' && response.data.move) {
                const move = response.data.move;
                const [row, col] = move.coordinate.split(',').map(num => parseInt(num) - 1);
                userBoard.value[row][col] = move.result === 'hit' ? 'X' : 'O';
                opponentMoved = true;
                console.log("Movimiento del oponente detectado:", move);
            } else {
                console.log("No se detectó movimiento del oponente.");
            }
        } catch (error) {
            console.error("Error al verificar el movimiento del oponente:", error);
        }

        if (!opponentMoved) {
            await new Promise(resolve => setTimeout(resolve, 2500));
        }
    }

    if (!opponentMoved) {
        console.log("El oponente no realizó ningún movimiento en 30 segundos.");
    } else {
        console.log("Movimiento del oponente procesado. Cambiando al estado de jugar.");
        yourTurn.value = true; // Cambiar el turno al usuario
    }
};

// Función principal del juego
const gameLoop = async () => {
    console.log("Iniciando el bucle del juego...");
    while (isGameActive.value) {
        if (yourTurn.value) {
            await playTurn();
        } else {
            await waitTurn();
        }
    }
    console.log("El juego ha terminado.");
};

// Función loadShips (cargar los barcos del jugador)
const loadShips = async () => {

    // Verificación de usuario autenticado y código de partida
    if(authStore().user == null) {
        // backToHome(true, "No tienes permiso para acceder a esta página (user)");
    }else if(gameStore.matchCode == "null"){
        // backToHome(false, "No tienes permiso para acceder a esta página (code)");
    }

    console.log("Obteniendo coordenadas de los barcos del jugador");

    // Obtener los barcos del jugador
    try {
        const response = await axios.post('/api/games/get-user-ship-placement', {
            gameCode: gameStore.matchCode,
            user: authStore().user
        });
        
        if (response.data.status === 'failed') {
            // backToHome(true, "Error al cargar la partida.");
            console.log("Error al cargar la partida. [respuesta es failed]");
            return;
        }

        // Posicionar los barcos en el tablero
        setUserBoard(JSON.parse(response.data.data));

    } catch (error) {
        // backToHome(true, "Error al cargar la partida.");
        console.log("Error al cargar la partida: ", error);
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

// Inicialización del juego
onMounted(async () => {

    // Montar el tablero del usuario
    await loadShips();

    // Obtener los datos de la partida
    const response = await axios.post('/api/games/get-match-info', {
        gameCode: gameStore.matchCode
    });
    yourTurn.value = response.data.data.game.created_by === authStore().user.id;

    // Iniciar el bucle del juego
    gameLoop();
});

// Limpiar cuando se desmonta el componente
onUnmounted(() => {
    console.log("Desmontando componente de juego...");
    isGameActive.value = false;
});
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
    background-color: #dc3545 !important; /* Rojo para impactos */
}

.board-cell.miss {
    background-color: #28a745 !important; /* Verde para fallos */
}

.board-cell.ship.hit::after {
    background-color: #dc3545; /* Rojo para barcos impactados */
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

.game-status {
    width: 100%;
    padding: 1rem;
    text-align: center;
    margin-bottom: 1rem;
}

.timer {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--primary-color);
    color: white;
    border-radius: 8px;
    font-size: 1.2rem;
}

.waiting-message {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--secondary-color);
    color: white;
    border-radius: 8px;
    font-size: 1.2rem;
}

/* Estilos para las notificaciones */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    color: white;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
}

.notification.success {
    background-color: #28a745;
}

.notification.info {
    background-color: var(--primary-color);
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>