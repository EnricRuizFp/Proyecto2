<template>
    <div class="view-game">
        <!-- Estado de carga inicial mientras se preparan los tableros -->
        <div v-if="isInitialLoading" class="loading-state">
            <div class="loading-overlay">
                <i class="fas fa-spinner fa-spin"></i>
                <p class="p3-dark">La partida está a punto de empezar...</p>
            </div>
        </div>

        <h2 class="white-color">Visualizando Partida: {{ gameCode }}</h2>
        <div class="boards-container">
            <!-- Tablero del jugador 1: Muestra sus barcos y los ataques del jugador 2 -->
            <div class="board-section">
                <ViewUserComponent :userId="player1Id" />
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
                                    ship: player1Board[row - 1][col - 1], // Marca si hay barco
                                    hit:
                                        player2Moves[`${row},${col}`] === 'hit', // Marca si el jugador 2 acertó aquí
                                    miss:
                                        player2Moves[`${row},${col}`] ===
                                        'miss', // Marca si el jugador 2 falló aquí
                                }"
                            >
                                <!-- Icono de acierto -->
                                <svg
                                    v-if="
                                        player2Moves[`${row},${col}`] === 'hit'
                                    "
                                    class="hit-marker"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 2 L14 7 L19 5 L16 10 L21 12 L16 14 L19 19 L14 17 L12 22 L10 17 L5 19 L8 14 L3 12 L8 10 L5 5 L10 7 Z"
                                        fill="red"
                                        stroke="darkred"
                                        stroke-width="1"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                        fill="darkred"
                                    />
                                </svg>
                                <!-- Icono de fallo -->
                                <svg
                                    v-if="
                                        player2Moves[`${row},${col}`] === 'miss'
                                    "
                                    class="miss-marker"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="#0d6efd"
                                        stroke-width="2"
                                        fill="none"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                        fill="#0d6efd"
                                    />
                                    <line
                                        x1="12"
                                        y1="2"
                                        x2="12"
                                        y2="22"
                                        stroke="#0d6efd"
                                        stroke-width="2"
                                    />
                                    <line
                                        x1="2"
                                        y1="12"
                                        x2="22"
                                        y2="12"
                                        stroke="#0d6efd"
                                        stroke-width="2"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tablero del jugador 2: Muestra sus barcos y los ataques del jugador 1 -->
            <div class="board-section">
                <ViewUserComponent :userId="player2Id" />
                <div class="board-container">
                    <div class="board-grid">
                        <div
                            v-for="row in 10"
                            :key="`attack-row-${row}`"
                            class="board-row"
                        >
                            <div
                                v-for="col in 10"
                                :key="`attack-cell-${row}-${col}`"
                                class="board-cell"
                                :class="{
                                    ship: player2Board[row - 1][col - 1], // Marca si hay barco
                                    hit:
                                        player1Moves[`${row},${col}`] === 'hit', // Marca si el jugador 1 acertó aquí
                                    miss:
                                        player1Moves[`${row},${col}`] ===
                                        'miss', // Marca si el jugador 1 falló aquí
                                }"
                            >
                                <!-- Icono de acierto -->
                                <svg
                                    v-if="
                                        player1Moves[`${row},${col}`] === 'hit'
                                    "
                                    class="hit-marker"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 2 L14 7 L19 5 L16 10 L21 12 L16 14 L19 19 L14 17 L12 22 L10 17 L5 19 L8 14 L3 12 L8 10 L5 5 L10 7 Z"
                                        fill="red"
                                        stroke="darkred"
                                        stroke-width="1"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                        fill="darkred"
                                    />
                                </svg>
                                <!-- Icono de fallo -->
                                <svg
                                    v-if="
                                        player1Moves[`${row},${col}`] === 'miss'
                                    "
                                    class="miss-marker"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="#0d6efd"
                                        stroke-width="2"
                                        fill="none"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                        fill="#0d6efd"
                                    />
                                    <line
                                        x1="12"
                                        y1="2"
                                        x2="12"
                                        y2="22"
                                        stroke="#0d6efd"
                                        stroke-width="2"
                                    />
                                    <line
                                        x1="2"
                                        y1="12"
                                        x2="22"
                                        y2="12"
                                        stroke="#0d6efd"
                                        stroke-width="2"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Componente para mostrar el resultado final de la partida -->
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
import ViewUserComponent from "./ViewUserComponent.vue";
import { useRouter } from "vue-router";

// Propiedades recibidas, principalmente el código de la partida
const props = defineProps({
    gameCode: {
        type: String,
        required: true,
    },
});

// Función para volver a la página de inicio
const backToHome = () => {
    router.push("/");
};

// Referencia para almacenar los datos de la partida actual
const currentGame = ref(null);

// Variables reactivas para los tableros y nombres de jugadores
const player1Board = ref(
    Array(10)
        .fill(null)
        .map(() => Array(10).fill(null))
);
const player2Board = ref(
    Array(10)
        .fill(null)
        .map(() => Array(10).fill(null))
);
const player1Name = ref("");
const player2Name = ref("");
const isInitialLoading = ref(true); // Controla la pantalla de carga inicial

// Variables para almacenar los movimientos de cada jugador
const player1Moves = ref({}); // { "fila,col": "hit" | "miss" }
const player2Moves = ref({});

// IDs de los jugadores para pasarlos al componente ViewUserComponent
const player1Id = ref(null);
const player2Id = ref(null);

const router = useRouter();
// Variables para controlar y mostrar el resultado final
const showResult = ref(false);
const winnerName = ref("");
const isDraw = ref(false);

// Coloca los barcos en el tablero visual a partir de los datos recibidos
const setBoard = (board, shipsData) => {
    // Limpia el tablero antes de colocar los nuevos barcos
    board.value = Array(10)
        .fill(null)
        .map(() => Array(10).fill(null));
    // Itera sobre los barcos y sus posiciones
    Object.entries(shipsData).forEach(([shipName, positions]) => {
        positions.forEach((pos) => {
            // Convierte la coordenada "fila,col" a índices de array (0-9)
            const [row, col] = pos.split(",").map((num) => parseInt(num) - 1);
            // Marca la celda con el nombre del barco
            board.value[row][col] = shipName;
        });
    });
};

// Obtiene el estado inicial de la partida (jugadores, tableros)
const getGameStatus = async () => {
    try {
        const response = await axios.post(
            "/api/games/get-current-match-status",
            {
                gameCode: props.gameCode,
            }
        );

        if (response.data.status === "success") {
            currentGame.value = response.data.data;

            // Asigna los IDs y nombres de los jugadores
            player1Id.value = currentGame.value.players[0]?.id || null;
            player2Id.value = currentGame.value.players[1]?.id || null;
            player1Name.value = currentGame.value.players[0]?.username || "";
            player2Name.value = currentGame.value.players[1]?.username || "";

            // Coloca los barcos en los tableros si existen las coordenadas
            let hasBoards = false;
            if (currentGame.value.players[0]?.coordinates) {
                setBoard(
                    player1Board,
                    JSON.parse(currentGame.value.players[0].coordinates)
                );
                console.log("Tablero 1 cargado.");
                hasBoards = true;
            }
            if (currentGame.value.players[1]?.coordinates) {
                setBoard(
                    player2Board,
                    JSON.parse(currentGame.value.players[1].coordinates)
                );
                console.log("Tablero 2 cargado.");
                hasBoards = true;
            }

            // Oculta la pantalla de carga inicial solo si ambos tableros están listos
            isInitialLoading.value = !hasBoards;
        }
    } catch (error) {
        console.error(
            "Error al obtener los datos iniciales de la partida:",
            error
        );
        isInitialLoading.value = false; // Oculta la carga incluso si hay error para no bloquear
    }
};

// Registra al usuario actual como observador de la partida en el backend
const viewGame = async (gameCode) => {
    try {
        const response = await axios.post("/api/games/view-game", {
            gameCode: gameCode,
            user: authStore().user, // Envía la información del usuario autenticado
        });

        return response.data.status === "success";
    } catch (error) {
        console.error("Error al registrarse como observador:", error);
        return false;
    }
};

// Bucle principal que consulta periódicamente los movimientos y estado de la partida
const viewGameLoop = async () => {
    try {
        let partidaFinalizada = false;

        // Continúa mientras la partida no haya finalizado
        while (!partidaFinalizada) {
            try {
                // Pide al backend los últimos movimientos y estado
                const response = await axios.post(
                    "/api/games/view-game-moves",
                    {
                        gameCode: props.gameCode,
                        user: authStore().user,
                    }
                );

                if (response.data.status === "success") {
                    currentGame.value = response.data.data;

                    // Procesa y actualiza los movimientos del jugador 1
                    player1Moves.value = {}; // Limpia los movimientos anteriores
                    currentGame.value.players[0]?.moves?.forEach((move) => {
                        const [row, col] = move.coordinate.split(",");
                        player1Moves.value[`${row},${col}`] = move.result; // Guarda "hit" o "miss"
                    });

                    // Procesa y actualiza los movimientos del jugador 2
                    player2Moves.value = {}; // Limpia los movimientos anteriores
                    currentGame.value.players[1]?.moves?.forEach((move) => {
                        const [row, col] = move.coordinate.split(",");
                        player2Moves.value[`${row},${col}`] = move.result;
                    });

                    // Actualiza los tableros (por si acaso, aunque no deberían cambiar)
                    if (currentGame.value.players[0]?.coordinates) {
                        setBoard(
                            player1Board,
                            JSON.parse(currentGame.value.players[0].coordinates)
                        );
                    }
                    if (currentGame.value.players[1]?.coordinates) {
                        setBoard(
                            player2Board,
                            JSON.parse(currentGame.value.players[1].coordinates)
                        );
                    }

                    // Comprueba si la partida ha terminado según el estado recibido
                    if (currentGame.value.game_status?.is_finished) {
                        if (currentGame.value.game_status.winner === "draw") {
                            isDraw.value = true; // Marca como empate
                        } else {
                            winnerName.value =
                                currentGame.value.game_status.winner; // Guarda el nombre del ganador
                        }
                        showResult.value = true; // Muestra el modal de resultado
                        partidaFinalizada = true; // Termina el bucle
                    }
                }
            } catch (error) {
                // Si hay un error en una iteración (ej. problema de red), espera y reintenta
                console.error(
                    "Error en iteración del bucle de observación:",
                    error.response?.data || error
                );
                await new Promise((resolve) => setTimeout(resolve, 5000)); // Espera 5 segundos
            }
            // Si la partida no ha finalizado, espera antes de la siguiente consulta
            if (!partidaFinalizada) {
                await new Promise((resolve) => setTimeout(resolve, 2500)); // Espera 2.5 segundos
            }
        }
    } catch (error) {
        // Error grave en el bucle principal
        console.error("Error en el bucle de observación:", error);
    }
};

// Limpia el estado y redirige al inicio (llamado desde el modal de resultado)
const handleCleanup = () => {
    showResult.value = false;
    currentGame.value = null;
    // Podrías añadir aquí una llamada al backend para dejar de observar si es necesario
    router.push("/");
};

// Se ejecuta cuando el componente se monta en el DOM
onMounted(async () => {
    // Verifica si el usuario está logueado y si hay un código de partida
    if (!authStore().user) {
        console.error("Usuario no autenticado. Redirigiendo a inicio.");
        backToHome();
        return; // Detiene la ejecución si no está autenticado
    }
    if (!props.gameCode) {
        console.error(
            "Código de partida no proporcionado. Redirigiendo a inicio."
        );
        backToHome();
        return; // Detiene la ejecución si no hay código
    }

    try {
        // Intenta registrarse como observador
        const registered = await viewGame(props.gameCode);

        if (registered) {
            console.log("Registrado como observador. Iniciando visualización.");
            // Obtiene el estado inicial de la partida
            await getGameStatus();
            // Inicia el bucle para recibir actualizaciones
            viewGameLoop();
        } else {
            console.error(
                "No se pudo registrar como observador. Redirigiendo a inicio."
            );
            backToHome();
        }
    } catch (error) {
        console.error("Error al iniciar la observación de la partida:", error);
        backToHome(); // Redirige a inicio si falla el inicio
    }
});

// Se ejecuta cuando el componente se desmonta
onUnmounted(() => {
    // Llama a la función de limpieza para asegurar que se liberen recursos o estado
    handleCleanup();
    // Aquí podrías añadir una llamada al backend para indicar que el usuario dejó de observar
    // axios.post('/api/games/stop-viewing', { gameCode: props.gameCode, user: authStore().user });
});
</script>

<style scoped>
.view-game {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    width: 100%;
    height: 100vh;
    overflow: hidden;
}

.boards-container {
    display: flex;
    gap: 4rem;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: calc(100% - 4rem);
    padding: 0.5rem;
}

.board-container {
    width: min(400px, calc((100vh - 10rem) / 2));
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
    z-index: 1;
}

.board-cell.hit,
.board-cell.miss {
    background-color: transparent !important;
}

.board-cell.ship.hit::after {
    background-color: transparent;
}

@media (max-width: 1000px) {
    .boards-container {
        flex-direction: column;
        gap: 1rem;
        height: auto;
        overflow-y: auto;
    }

    .board-container {
        width: min(85vw, calc(100vh - 400px));
        height: min(85vw, calc(100vh - 400px));
        max-width: 300px;
        max-height: 300px;
    }
}

.title {
    color: var(--white-color);
    text-align: center;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

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
    padding: 2rem;
}

.loading-overlay {
    position: relative;
    width: 340px;
    padding: 2.5rem;
    background: var(--background-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.loading-overlay i {
    font-size: 2.5rem;
    color: var(--primary-color);
}

.loading-overlay p {
    margin: 0;
    padding: 0 1rem;
    text-align: center;
    width: 100%;
}

.board-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}
</style>
