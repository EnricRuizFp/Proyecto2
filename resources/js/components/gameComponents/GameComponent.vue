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
let matchHost = ref(null);
const yourTurn = ref(null);
const timeLeft = ref(25);
const timerInterval = ref(null);

// Estado de los tableros
const userBoard = ref(Array(10).fill(null).map(() => Array(10).fill(null)));
const attackBoard = ref(Array(10).fill(null).map(() => Array(10).fill(null)));

// Referencia para las notificaciones
const notification = ref({
    show: false,
    message: '',
    type: 'info'
});

/* -- FUNCTIONS -- */

// Función para mostrar notificaciones
const showNotification = (message, type = 'info') => {
    notification.value = {
        show: true,
        message,
        type
    };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

// Función para manejar la cuenta atrás y los turnos
const startTimer = (seconds = 25) => {
    timeLeft.value = seconds;
    timerInterval.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval.value);
            backToHome(true, "Se acabó el tiempo sin realizar ningún movimiento");
        }
    }, 1000);
};

// Función para verificar movimientos del oponente
const checkOpponentMove = async () => {
    try {
        const response = await axios.post('/api/games/get-last-move', {
            gameCode: gameStore.matchCode,
            user: authStore().user
        });

        console.log("Status de la búsqueda:", response.data.status);

        if (response.data.status === 'success' && response.data.move) {
            const move = response.data.move;
            console.log("Movimiento encontrado en coordenada:", move.coordinate);
            console.log("Resultado:", move.result, move.result === 'hit' ? `- Barco tocado: ${move.ship}` : '- Agua');
            
            // Actualizar tablero
            const [row, col] = move.coordinate.split(',').map(num => parseInt(num) - 1);
            userBoard.value[row][col] = move.result === 'hit' ? 'X' : 'O';
            
            // Cambiar turno
            yourTurn.value = true;
            startTimer(25);
            return true;
        }

        console.log("No se encontró ningún movimiento nuevo");
        return false;
    } catch (error) {
        console.error("Error al verificar movimiento:", error);
        backToHome(true, "Error de conexión al verificar el movimiento del oponente");
        return false;
    }
};

// Función para manejar el ataque
const handleAttack = async (row, col) => {
    if (!yourTurn.value || attackBoard.value[row][col]) {
        return;
    }

    try {
        const response = await axios.post('/api/games/attack', {
            gameCode: gameStore.matchCode,
            user: authStore().user,
            coordinates: `${row + 1},${col + 1}`
        });

        if (response.data.status === 'success') {
            const result = response.data.message;
            attackBoard.value[row][col] = result === 'hit' ? '✓' : '✗';
            
            // Mostrar notificación si se ha acertado
            if (result === 'hit' && response.data.ship) {
                showNotification(`¡Has acertado al ${response.data.ship}!`, 'success');
            }

            // Limpiar timer y cambiar turno
            clearInterval(timerInterval.value);
            yourTurn.value = false;
            
            console.log("Ataque realizado, esperando al oponente");
        }
    } catch (error) {
        console.error("Error en el ataque:", error);
    }
};

// Inicialización del juego
onMounted(async () => {
    await loadShips();
    const response = await getMatchInfo();
    matchHost = response.game.created_by;
    yourTurn.value = matchHost == authStore().user.id;

    // Iniciar el ciclo de juego
    if (yourTurn.value) {
        console.log("Empiezas primero");
        startTimer(25);
    }

    // Iniciar verificación periódica cuando no es tu turno
    setInterval(async () => {
        if (!yourTurn.value) {
            await checkOpponentMove();
        }
    }, 2500);
});

// Limpiar el intervalo cuando se desmonta el componente
onUnmounted(() => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
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
        backToHome(true, "No tienes permiso para acceder a esta página (user)");
    }else if(gameStore.matchCode == "null"){
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
            backToHome(true, "Error al cargar la partida.");
            return;
        }

        // Posicionar los barcos en el tablero
        setUserBoard(JSON.parse(response.data.data));

    } catch (error) {
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

// Función para dormir X milisegundos
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

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