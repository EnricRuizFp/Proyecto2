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
        <div v-if="notification.show" class="notification" :class="[notification.type, notification.position]">
            <span>{{ notification.message }}</span>
        </div>

        <div class="boards-container">
            <!-- Tablero del usuario -->
            <div class="board-section">
                <h2 class="title">{{ username }}</h2>
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`row-${row}`" class="board-row">
                            <div v-for="col in 10" :key="`cell-${row}-${col}`" 
                                class="board-cell"
                                :class="{ 
                                    'ship': userBoard[row - 1][col - 1] && userBoard[row - 1][col - 1] !== 'X' && userBoard[row - 1][col - 1] !== 'O',
                                    'hit': userBoard[row - 1][col - 1] === 'X',
                                    'miss': userBoard[row - 1][col - 1] === 'O'
                                }">
                                <svg v-if="userBoard[row - 1][col - 1] === 'X'" 
                                    class="hit-marker" 
                                    viewBox="0 0 24 24">
                                    <path d="M12 2 L14 7 L19 5 L16 10 L21 12 L16 14 L19 19 L14 17 L12 22 L10 17 L5 19 L8 14 L3 12 L8 10 L5 5 L10 7 Z" 
                                          fill="red" 
                                          stroke="darkred" 
                                          stroke-width="1"/>
                                    <circle cx="12" cy="12" r="3" fill="darkred"/>
                                </svg>
                                <svg v-if="userBoard[row - 1][col - 1] === 'O'" 
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

            <!-- Tablero de ataque -->
            <div class="board-section">
                <h2 class="title">{{ opponentUsername }}</h2>
                <div class="board-container">
                    <div class="board-grid">
                        <div v-for="row in 10" :key="`attack-row-${row}`" class="board-row">
                            <div v-for="col in 10" 
                                :key="`attack-cell-${row}-${col}`" 
                                class="board-cell clickable"
                                @click="handleAttack(row - 1, col - 1)"
                                :class="{ hit: attackBoard[row - 1][col - 1] === '✓', miss: attackBoard[row - 1][col - 1] === '✗' }"
                            >
                                <svg v-if="attackBoard[row - 1][col - 1] === '✓'" class="hit-marker" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="red" stroke-width="2" fill="none"/>
                                    <circle cx="12" cy="12" r="3" fill="red"/>
                                    <line x1="12" y1="2" x2="12" y2="22" stroke="red" stroke-width="2"/>
                                    <line x1="2" y1="12" x2="22" y2="12" stroke="red" stroke-width="2"/>
                                </svg>
                                <svg v-if="attackBoard[row - 1][col - 1] === '✗'" class="miss-marker" viewBox="0 0 24 24">
                                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" fill="#0d6efd"/>
                                </svg>
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
const notification = ref({ 
    show: false, 
    message: '', 
    type: 'info',
    position: 'derecha' 
});
const username = ref("Sus barcos");
const opponentUsername = ref("Tablero de ataque");

/* -- FUNCTIONS -- */

// Función para mostrar notificaciones
const showNotification = (message, type = 'info') => {
    notification.value = { 
        show: true, 
        message, 
        type,
        position: 'derecha' 
    };
    setTimeout(() => { notification.value.show = false; }, 3000);
};

// Función para manejar el ataque
const handleAttack = async (row, col) => {
    if (!isGameActive.value || !yourTurn.value || attackBoard.value[row][col]) {
        console.log("Ataque inválido.");
        showNotification("Espera tu turno.", "error");
        return;
    }

    try {
        console.log("Atacando a:", row, "/", col);
        const response = await axios.post('/api/games/attack', {
            gameCode: gameStore.matchCode,
            user: authStore().user,
            coordinates: `${row + 1},${col + 1}`
        });

        if (response.data.status === 'success') {
            const result = response.data.message;
            attackBoard.value[row][col] = result === 'hit' ? '✓' : '✗';

            if (result === 'hit') {
                if (response.data.is_sunk) {
                    showNotification('¡Tocado y hundido!', 'success');
                } else {
                    showNotification('¡Tocado!', 'success');
                }
            } else {
                showNotification('¡Agua!', 'water');
            }

            await checkMatchWinner();
            yourTurn.value = false;
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

        // Validación de ganador de la partida
        if(response.data.has_winned == false){

            if(response.data.move_count < 100){
                //showNotification("Te quedan " + response.data.ships_left + " barcos por hundir.", "info");
            }else{
                console.log("Límite de movimientos alcanzado.");

                // Finalizar el juego
                await endGame("draw");
            }
            

        }else if(response.data.has_winned == true){

            console.log("Ganador de la partida.");

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

// Función para reiniciar el estado del juego
const resetGameState = () => {
    userBoard.value = Array(10).fill(null).map(() => Array(10).fill(null));
    attackBoard.value = Array(10).fill(null).map(() => Array(10).fill(null));
    yourTurn.value = null;
    timeLeft.value = 25;
    isGameActive.value = false;
    notification.value = { 
        show: false, 
        message: '', 
        type: 'info',
        position: 'derecha' 
    };
};

// Función para finalizar la partida (ganando)
const endGame = async (status) => {
    try {
        console.log("Setteando el ganador de la partida...");
        console.log("Status: ", status);
        // Detener todas las funciones estableciendo isGameActive a false
        isGameActive.value = false;
        yourTurn.value = false;

        const response = await axios.post('/api/games/set-game-ending', {
            gameCode: gameStore.matchCode,
            user: authStore().user,
            status: status
        });

        console.log("Respuesta del servidor:", response.data);
        console.log("UPDATED");

        if (response.data.status === 'success') {
            console.log("Partida finalizada con éxito.");

            if(status == "winner"){
                console.log("GANADOR");
                // Almacenar los puntos ganados
                gameStore.setPoints(response.data.points_earned);
                gameStore.setShowWin(true);
            } else {
                console.log("EMPATE");
                gameStore.setPoints(0);
                gameStore.setShowDraw(true);
            }
            resetGameState();
        }
    } catch (error) {
        console.error("Error al finalizar la partida:", error);
    }
};

// Función para manejar el turno de jugar
const playTurn = async () => {
    if (!isGameActive.value) return;
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
    if (!isGameActive.value) return;
    console.log("Esperando el movimiento del oponente...");
    let opponentMoved = false;
    let attempts = 0;

    while (!opponentMoved && attempts < 12 && isGameActive.value) {
        attempts++;
        console.log(`Intento ${attempts}/12: Verificando movimiento del oponente...`);

        try {
            // Verificar estado de la partida
            const matchResponse = await axios.post('/api/games/get-match-info', {
                gameCode: gameStore.matchCode
            });

            // Verificar si la partida ha terminado y si hay un ganador que no es el usuario actual
            if (matchResponse.data.data.game.is_finished && 
                matchResponse.data.data.game.winner && 
                matchResponse.data.data.game.winner !== authStore().user.id) {
                console.log("PERDEDOR");

                // Mostrar los puntos perdidos
                gameStore.setPoints(matchResponse.data.data.game.points);
                gameStore.setShowGameOver(true);
                resetGameState();
                return;
            }

            // Si la partida sigue activa, verificar el último movimiento
            const moveResponse = await axios.post('/api/games/get-last-move', {
                gameCode: gameStore.matchCode,
                user: authStore().user
            });

            if (moveResponse.data.status === 'success' && moveResponse.data.move) {
                const move = moveResponse.data.move;
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
        yourTurn.value = true;
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

    // Obtener el nombre del usuario actual y del oponente
    username.value = authStore().user.username;
    opponentUsername.value = response.data.data.players.find(
        player => player.user_id !== authStore().user.id
    ).username;

    // Definir quién empieza
    yourTurn.value = response.data.data.game.created_by === authStore().user.id;
    // Iniciar el bucle del juego
    gameLoop();
});

// Limpiar cuando se desmonta el componente
onUnmounted(() => {
    isGameActive.value = false;
});
</script>

<style scoped>
.game {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 4.5rem 1rem 1rem 1rem;
    width: 100%;
    min-height: 100vh;
}

.boards-container {
    display: flex;
    gap: 8rem; /* Espacio grande entre tableros para pantallas anchas */
    justify-content: center;
    align-items: center;
    width: 100%;
    padding: 0.5rem;
    flex: 1;
}

.board-container {
    /* Tamaño base para pantallas grandes */
    width: min(450px, calc((100vw - 6rem) / 2)); /* Aumentado el tamaño máximo y ajustado el cálculo */
    height: min(450px, calc((100vw - 6rem) / 2));
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

.board-cell.hit, .board-cell.miss {
    background-color: transparent !important;
}

.hit-marker, .miss-marker {
    width: 30px;
    height: 30px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.hit-marker {
    width: 30px;
    height: 30px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.miss-marker {
    width: 24px;
    height: 24px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* Asegurarse que el SVG esté por encima del fondo del barco */
.board-cell.ship.hit::after {
    background-color: transparent;
}

/* Nuevos estilos */
.clickable {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.clickable:hover {
    background-color: #7048ec33;
}

/* Media query para dispositivos móviles */
@media (max-width: 1000px) {
    .game {
        padding: 4rem 0.25rem 0.25rem 0.25rem;
    }

    .boards-container {
        flex-direction: column;
        gap: 0.25rem;
        padding: 0.15rem;
    }

    .board-container {
        /* Aumentado el tamaño para pantallas estrechas */
        width: min(85vw, calc(100vh - 320px));
        height: min(85vw, calc(100vh - 320px));
        max-width: 350px;
        max-height: 350px;
        padding: 0.15rem;
    }

    .game-status {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .timer, .waiting-message {
        padding: 0.35rem 0.75rem;
        font-size: 0.9rem;
    }
}

@media (max-height: 700px) {
    .board-container {
        width: min(60vw, calc(100vh - 300px));
        height: min(60vw, calc(100vh - 300px));
        max-width: 250px;
        max-height: 250px;
    }
}

@media (max-height: 800px) {
    .game {
        padding: 3.5rem 0.5rem 0.5rem 0.5rem;
    }

    .boards-container {
        gap: 0.25rem;
    }

    .board-container {
        padding: 0.15rem;
    }

    .board-cell {
        border-width: 1px;
    }
}

@media (max-height: 700px) {
    .game {
        padding: 3rem 0.5rem 0.5rem 0.5rem;
    }
}

/* Para pantallas muy pequeñas */
@media (max-width: 400px) or (max-height: 600px) {
    .game {
        padding: 2.5rem 0.25rem 0.25rem 0.25rem;
    }

    .boards-container {
        gap: 0.15rem;
    }

    .board-container {
        width: min(95vw, calc(100vh - 200px));
        height: min(95vw, calc(100vh - 200px));
    }

    .game-status {
        padding: 0.25rem;
    }

    .timer, .waiting-message {
        font-size: 1rem;
        padding: 0.25rem 0.75rem;
    }
}

@media (max-width: 960px) {
    .boards-container {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .board-container {
        width: min(calc(100vh - 450px), 85vw, 400px);
        height: min(calc(100vh - 450px), 85vw, 400px);
    }
    
    /* Reordena los tableros en móvil */
    .board-section:first-child {
        order: 2;
    }
    
    .board-section:last-child {
        order: 1;
    }
}

@media (max-height: 900px) {
    .game {
        padding: 0.5rem;
    }
    
    .boards-container {
        padding: 0.5rem;
    }
    
    .board-container {
        padding: 0.5rem;
    }
    
    .game-status {
        padding: 0.5rem;
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
    padding: 1rem 1.5rem;
    border-radius: 8px;
    color: white;
    z-index: 1000;
}

.notification.success {
    background-color: #28a745;
}

.notification.info {
    background-color: var(--primary-color);
}

.notification.error {
    background-color: #dc3545;
}

.notification.water {
    background-color: #0d6efd;
}

.notification.derecha {
    right: 20px;
    animation: slideInRight 0.3s ease-out;
}

/* Remover o comentar las otras posiciones que no usaremos
.notification.izquierda {
    left: 20px;
    animation: slideInLeft 0.3s ease-out;
}

.notification.centro {
    left: 50%;
    transform: translateX(-50%);
    animation: slideInTop 0.3s ease-out;
}
*/

@keyframes slideInRight {
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