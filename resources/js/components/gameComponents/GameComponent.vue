<template>
    <div class="game app-background-primary">
        <!-- Estado de carga inicial -->
        <div v-if="isInitialLoading" class="loading-state">
            <div class="loading-overlay">
                <i class="fas fa-spinner fa-spin"></i>
                <p class="p3-dark">Cargando tableros...</p>
            </div>
        </div>

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
        <div
            v-if="notification.show"
            class="notification"
            :class="[notification.type, notification.position]"
        >
            <span>{{ notification.message }}</span>
        </div>

        <div class="boards-container">
            <!-- Tablero del usuario -->
            <div class="board-section">
                <ViewUserComponent
                    v-if="currentUserId"
                    :userId="currentUserId"
                />
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
                                    ship:
                                        userBoard[row - 1][col - 1] &&
                                        userBoard[row - 1][col - 1] !== 'X' &&
                                        userBoard[row - 1][col - 1] !== 'O',
                                    hit: userBoard[row - 1][col - 1] === 'X',
                                    miss: userBoard[row - 1][col - 1] === 'O',
                                }"
                            >
                                <svg
                                    v-if="userBoard[row - 1][col - 1] === 'X'"
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
                                <svg
                                    v-if="userBoard[row - 1][col - 1] === 'O'"
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

            <!-- Tablero de ataque -->
            <div class="board-section">
                <ViewUserComponent v-if="opponentId" :userId="opponentId" />
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
                                class="board-cell clickable"
                                @click="handleAttack(row - 1, col - 1)"
                                :class="{
                                    hit: attackBoard[row - 1][col - 1] === '✓',
                                    miss: attackBoard[row - 1][col - 1] === '✗',
                                }"
                            >
                                <svg
                                    v-if="attackBoard[row - 1][col - 1] === '✓'"
                                    class="hit-marker"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="red"
                                        stroke-width="2"
                                        fill="none"
                                    />
                                    <circle cx="12" cy="12" r="3" fill="red" />
                                    <line
                                        x1="12"
                                        y1="2"
                                        x2="12"
                                        y2="22"
                                        stroke="red"
                                        stroke-width="2"
                                    />
                                    <line
                                        x1="2"
                                        y1="12"
                                        x2="22"
                                        y2="12"
                                        stroke="red"
                                        stroke-width="2"
                                    />
                                </svg>
                                <svg
                                    v-if="attackBoard[row - 1][col - 1] === '✗'"
                                    class="miss-marker"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"
                                        fill="#0d6efd"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat de juego -->
        <div class="game-chat">
            <div class="chat-toggle" @click="toggleChat">
                <i
                    class="fas"
                    :class="isChatOpen ? 'fa-chevron-down' : 'fa-comment'"
                ></i>
                <span
                    v-if="!isChatOpen && unreadMessages > 0"
                    class="unread-badge"
                    >{{ unreadMessages }}</span
                >
            </div>

            <div class="chat-container" :class="{ 'chat-open': isChatOpen }">
                <div class="chat-header">
                    <h3>Chat con {{ opponent?.username || "Oponente" }}</h3>
                </div>

                <div class="chat-messages" ref="chatMessages">
                    <div v-if="messages.length === 0" class="no-messages">
                        No hay mensajes aún. ¡Inicia la conversación!
                    </div>
                    <div
                        v-for="(message, index) in messages"
                        :key="index"
                        class="message"
                        :class="
                            message.user_id === authStore().user.id
                                ? 'own-message'
                                : 'other-message'
                        "
                    >
                        <div class="message-header">
                            <span class="message-user">{{
                                message.username
                            }}</span>
                        </div>
                        <div class="message-content">{{ message.message }}</div>
                    </div>
                </div>

                <div class="chat-input">
                    <input
                        v-model="newMessage"
                        @keyup.enter="sendMessage"
                        placeholder="Escribe un mensaje..."
                        type="text"
                    />
                    <button @click="sendMessage" :disabled="!newMessage.trim()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTS -- */
import { ref, onMounted, onUnmounted, watch, nextTick } from "vue";
import { authStore } from "../../store/auth";
import { useRoute, useRouter } from "vue-router";
import { useGameStore } from "../../store/game";
import ViewUserComponent from "../viewGameComponents/ViewUserComponent.vue";

/* -- VARIABLES -- */
const route = useRoute();
const router = useRouter();
const gameStore = useGameStore();
const yourTurn = ref(null); // Indica si es el turno del jugador actual
const timeLeft = ref(25); // Tiempo restante para el turno
const isGameActive = ref(true); // Controla si el bucle principal del juego debe seguir
const userBoard = ref(
    // Tablero del jugador (dónde están sus barcos)
    Array(10)
        .fill(null)
        .map(() => Array(10).fill(null))
);
const attackBoard = ref(
    // Tablero de ataque (dónde ha disparado el jugador)
    Array(10)
        .fill(null)
        .map(() => Array(10).fill(null))
);
const notification = ref({
    // Para mostrar mensajes flotantes
    show: false,
    message: "",
    type: "info", // 'info', 'success', 'error', 'water'
    position: "derecha", // 'derecha', 'izquierda'
});
const currentUserId = ref(null); // ID del jugador actual
const opponentId = ref(null); // ID del oponente
const isInitialLoading = ref(true); // Para mostrar el spinner al cargar
const opponent = ref(null); // Datos del oponente

// Variables para el chat
const isChatOpen = ref(false); // ¿Está visible la ventana del chat?
const messages = ref([]); // Array con los mensajes del chat
const newMessage = ref(""); // Mensaje que se está escribiendo
const chatMessages = ref(null); // Referencia al contenedor de mensajes (para scroll)
const unreadMessages = ref(0); // Contador de mensajes no leídos
const lastMessageId = ref(0); // ID del último mensaje recibido (para detectar nuevos)
const pollingInterval = ref(null); // ID del intervalo para refrescar el chat

// Función de utilidad para pausar la ejecución
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

/* -- FUNCIONES -- */

// Muestra una notificación temporal
const showNotification = (message, type = "info") => {
    notification.value = {
        show: true,
        message,
        type,
        position: "derecha",
    };

    // Duración diferente si es el mensaje de abandono
    setTimeout(
        () => {
            notification.value.show = false;
        },
        message === "El oponente ha abandonado la partida" ? 6500 : 3000
    );
};

// Gestiona el clic en una celda del tablero de ataque
const handleAttack = async (row, col) => {
    // Validaciones: ¿Es tu turno? ¿Ya has disparado ahí?
    if (!isGameActive.value || !yourTurn.value || attackBoard.value[row][col]) {
        showNotification("Espera tu turno.", "error");
        return;
    }

    try {
        // Envía la coordenada atacada a la API
        const response = await axios.post("/api/games/attack", {
            gameCode: gameStore.matchCode,
            user: authStore().user,
            coordinates: `${row + 1},${col + 1}`, // Formato "fila,columna" (base 1)
        });

        if (response.data.status === "success") {
            const result = response.data.message; // 'hit' o 'miss'
            // Marca la celda en el tablero de ataque
            attackBoard.value[row][col] = result === "hit" ? "✓" : "✗";

            // Muestra notificación según el resultado
            if (result === "hit") {
                if (response.data.is_sunk) {
                    // ¿Hundiste un barco?
                    showNotification("¡Tocado y hundido!", "success");
                } else {
                    showNotification("¡Tocado!", "success");
                }
            } else {
                showNotification("¡Agua!", "water");
            }

            // Comprueba si este movimiento te ha hecho ganar
            await checkMatchWinner();
            // Cede el turno
            yourTurn.value = false;
        }
    } catch (error) {
        console.error("Error en el ataque:", error);
    }
};

// Comprueba si el jugador actual ha ganado la partida
const checkMatchWinner = async () => {
    try {
        // Pide al servidor si ya has ganado (basado en tus ataques)
        const response = await axios.post(
            "/api/games/get-opponent-ship-placement-game",
            {
                gameCode: gameStore.matchCode,
                user: authStore().user,
            }
        );

        // Analiza la respuesta
        if (response.data.has_winned == false) {
            // Aún no has ganado
            if (response.data.move_count < 100) {
                // Podrías mostrar cuántos barcos quedan, pero está comentado
                // showNotification(`Te quedan ${response.data.ships_left} barcos por hundir.`, "info");
            } else {
                // Se alcanzó el límite de movimientos (empate)
                console.log("Límite de movimientos alcanzado.");
                await endGame("draw", true); // Finaliza como empate
            }
        } else if (response.data.has_winned == true) {
            // ¡Has ganado!
            console.log("Ganador de la partida.");
            showNotification("¡Has ganado la partida!", "success");
            await endGame("winner", true); // Finaliza como ganador
        } else {
            // Respuesta inesperada
            console.log("No se ha podido determinar el ganador.");
        }
    } catch (error) {
        console.error("Error al verificar el ganador:", error);
    }
};

// Finaliza la partida y actualiza el estado en el servidor y localmente
const endGame = async (status, subirDato) => {
    if (subirDato) {
        console.log("ENDGAME COMO:", status, " subiendo dato.");
    } else {
        console.log("ENDGAME COMO:", status, " sin subir dato.");
    }

    // status: 'winner', 'loser', 'draw'
    try {
        // Detiene el bucle principal y los turnos
        isGameActive.value = false;
        yourTurn.value = false;

        // 'subirDato' indica si este cliente debe informar al servidor del resultado
        // (normalmente true si tú causas el fin, false si lo causa el oponente)
        if (subirDato) {
            console.log("Finalizando partida:");
            // Informa al servidor del resultado final
            const response = await axios.post("/api/games/set-game-ending", {
                gameCode: gameStore.matchCode,
                user: authStore().user,
                status: status,
            });

            if (response.data.status === "success") {
                // Actualiza el estado global (store) para mostrar la pantalla correcta
                if (status == "winner") {
                    console.log("Ganador.");
                    gameStore.setPoints(response.data.points_earned);
                    gameStore.setShowWin(true);
                } else if (status == "loser") {
                    console.log("Perdedor.");
                    gameStore.setPoints(response.data.points_lost_by_loser);
                    gameStore.setShowGameOver(true);
                } else {
                    // Empate
                    console.log("Empate.");
                    gameStore.setPoints(0);
                    gameStore.setShowDraw(true);
                }
            }
        } else {
            // Si no subes el dato, solo consulta la información final (ya actualizada por el oponente)
            const response = await axios.post("/api/games/get-match-info", {
                gameCode: gameStore.matchCode,
            });
            gameStore.setPoints(response.data.data.game.points);
            gameStore.setShowWin(true);
        }
    } catch (error) {
        console.error("Error al finalizar la partida: ", error);
    }
};

// Gestiona la lógica cuando es tu turno
const playTurn = async () => {
    if (!isGameActive.value) return; // Sal si el juego ya terminó

    try {
        // --- INICIO: Comprobación inicial del estado de la partida ---
        const initialMatchResponse = await axios.post(
            "/api/games/get-match-info",
            {
                gameCode: gameStore.matchCode,
            }
        );
        const initialGameData = initialMatchResponse.data.data.game;

        // ¿La partida ha terminado y el ganador NO eres tú? (Perdiste)
        if (
            initialGameData.is_finished &&
            initialGameData.winner &&
            initialGameData.winner !== authStore().user.id
        ) {
            isGameActive.value = false;
            yourTurn.value = false;
            console.log("Perdedor (detectado al inicio de playTurn).");
            gameStore.setPoints(initialGameData.points); // Asumiendo que 'points' son los perdidos
            gameStore.setShowGameOver(true);
            return; // Salimos de la función playTurn
        }
        // ¿La partida ha terminado SIN ganador? (Empate)
        else if (initialGameData.is_finished && !initialGameData.winner) {
            isGameActive.value = false;
            yourTurn.value = false;
            console.log("Empate (detectado al inicio de playTurn).");
            gameStore.setPoints(0);
            gameStore.setShowDraw(true);
            return; // Salimos de la función playTurn
        }
        // ¿La partida ha terminado y el ganador ERES tú? (Oponente abandonó antes de tu turno)
        else if (
            initialGameData.is_finished &&
            initialGameData.winner === authStore().user.id
        ) {
            console.log(
                "Oponente ha abandonado la partida (detectado al inicio de playTurn)."
            );
            showNotification("El oponente ha abandonado la partida", "error");
            await sleep(3000); // Pausa para ver la notificación
            isGameActive.value = false;
            yourTurn.value = false;
            await endGame("winner", false); // Finaliza como ganador (sin subir dato)
            return; // Salimos de la función playTurn
        }
        // --- FIN: Comprobación inicial del estado de la partida ---
    } catch (error) {
        console.error(
            "Error en la comprobación inicial del estado de la partida (playTurn):",
            error
        );
        // Considerar si se debe detener el juego o reintentar
    }

    timeLeft.value = 25; // Reinicia el temporizador

    // Bucle que espera a que ataques o se acabe el tiempo
    while (timeLeft.value > 0 && yourTurn.value && isGameActive.value) {
        // Añadido isGameActive.value
        await sleep(1000);
        timeLeft.value--;
    }

    // Si se acabó el tiempo y no has atacado (yourTurn sigue true) y el juego sigue activo
    if (timeLeft.value === 0 && yourTurn.value && isGameActive.value) {
        console.log("Tiempo agotado. Has perdido el turno.");
        showNotification("¡Tiempo agotado! Has perdido el turno.", "error");
        yourTurn.value = false; // Pierdes el turno, pasa al oponente
        // No se llama a endGame aquí, solo se pierde el turno.
        // El oponente podría ganar si tú no juegas repetidamente.
    }
    // Si atacaste, handleAttack ya puso yourTurn a false y el bucle termina
    // Si el juego terminó por la comprobación inicial, isGameActive será false y no entra aquí.
};

// Gestiona la lógica cuando estás esperando al oponente
const waitTurn = async () => {
    if (!isGameActive.value) return; // Sal si el juego ya terminó

    let opponentMoved = false; // ¿Ha movido ya el oponente?
    let attempts = 0; // Contador para evitar espera infinita

    // Bucle que consulta al servidor por el movimiento del oponente
    while (!opponentMoved && attempts < 12 && isGameActive.value) {
        attempts++;

        try {
            // 1. Comprueba el estado general de la partida (¿ha terminado?)
            const matchResponse = await axios.post(
                "/api/games/get-match-info",
                {
                    gameCode: gameStore.matchCode,
                }
            );

            const gameData = matchResponse.data.data.game;

            // ¿La partida ha terminado y el ganador NO eres tú? (Perdiste o empataste)
            if (
                gameData.is_finished &&
                gameData.winner &&
                gameData.winner !== authStore().user.id
            ) {
                isGameActive.value = false;
                yourTurn.value = false;
                console.log("Perdedor.");
                gameStore.setPoints(gameData.points); // Asumiendo que 'points' son los perdidos
                gameStore.setShowGameOver(true);
                return; // Salimos de la función waitTurn
            }
            // ¿La partida ha terminado y el ganador ERES tú? (Oponente abandonó)
            else if (
                gameData.is_finished &&
                gameData.winner === authStore().user.id
            ) {
                console.log("Oponente ha abandonado la partida.");
                showNotification(
                    "El oponente ha abandonado la partida",
                    "error"
                );
                await sleep(3000); // Pausa para ver la notificación
                isGameActive.value = false;
                yourTurn.value = false;
                await endGame("winner", false); // Finaliza como ganador (sin subir dato)
                return; // Salimos de la función waitTurn
            }
            // ¿La partida ha terminado SIN ganador? (Empate por límite de movimientos)
            else if (gameData.is_finished && !gameData.winner) {
                isGameActive.value = false;
                yourTurn.value = false;
                console.log("Empate por límite.");
                gameStore.setPoints(0);
                gameStore.setShowDraw(true);
                return; // Salimos de la función waitTurn
            }

            // 2. Si la partida sigue, consulta el último movimiento del oponente
            const moveResponse = await axios.post("/api/games/get-last-move", {
                gameCode: gameStore.matchCode,
                user: authStore().user, // Para saber qué movimiento buscar
            });

            // Si la API devuelve un movimiento válido...
            if (
                moveResponse.data.status === "success" &&
                moveResponse.data.move
            ) {
                const move = moveResponse.data.move;
                // Convierte coordenadas "fila,col" a índices [fila-1][col-1]
                const [row, col] = move.coordinate
                    .split(",")
                    .map((num) => parseInt(num) - 1);
                // Actualiza TU tablero con el resultado del ataque del oponente
                userBoard.value[row][col] = move.result === "hit" ? "X" : "O";
                opponentMoved = true; // Marcamos que ya movió
            } else {
                // Aún no hay movimiento nuevo del oponente
            }
        } catch (error) {
            console.error(
                "Error al verificar el movimiento del oponente:",
                error
            );
        }

        // Si no ha movido, esperamos antes de volver a preguntar
        if (!opponentMoved) {
            await sleep(2500);
        }
    } // Fin del bucle while

    // Si después de los intentos el oponente no movió...
    if (!opponentMoved && isGameActive.value) {
        // Añadido chequeo de isGameActive
        console.log(
            "El oponente no realizó ningún movimiento en el tiempo esperado."
        );
        isGameActive.value = false; // Detiene el juego
        await endGame("winner", true); // Ganas porque el oponente no jugó
    } else if (opponentMoved) {
        // Si movió, ahora es tu turno
        yourTurn.value = true;
    }
};

// Bucle principal que alterna entre playTurn y waitTurn
const gameLoop = async () => {
    while (isGameActive.value) {
        if (yourTurn.value) {
            await playTurn();
        } else {
            await waitTurn();
        }
    }
    console.log("El juego ha terminado.");
};

// Carga la disposición inicial de los barcos del jugador
const loadShips = async () => {
    isInitialLoading.value = true;

    // Validaciones básicas
    if (!authStore().user) {
        // Redirigir si no hay usuario
        return;
    }
    if (!gameStore.matchCode || gameStore.matchCode === "null") {
        // Redirigir si no hay código de partida
        return;
    }

    try {
        // Pide al servidor la posición de los barcos de este usuario
        const response = await axios.post(
            "/api/games/get-user-ship-placement",
            {
                gameCode: gameStore.matchCode,
                user: authStore().user,
            }
        );

        if (response.data.status === "failed") {
            // Error devuelto por la API
            console.log("Error al cargar la partida. [API devolvió failed]");
            // Redirigir o mostrar error
            return;
        }

        // Parsea los datos JSON y actualiza el tablero visual
        setUserBoard(JSON.parse(response.data.data));
        isInitialLoading.value = false; // Oculta el spinner
    } catch (error) {
        console.log("Error al cargar la partida: ", error);
        // Redirigir o mostrar error
    }
};

// Actualiza la matriz 'userBoard' con los datos de los barcos
const setUserBoard = (shipsData) => {
    // Reinicia el tablero a null
    userBoard.value = Array(10)
        .fill(null)
        .map(() => Array(10).fill(null));

    // Itera sobre cada tipo de barco y sus coordenadas
    Object.entries(shipsData).forEach(([shipName, positions]) => {
        positions.forEach((pos) => {
            // Convierte "fila,col" a índices [fila-1][col-1]
            const [row, col] = pos.split(",").map((num) => parseInt(num) - 1);
            // Marca la celda con el nombre del barco (o un identificador)
            userBoard.value[row][col] = shipName; // Podría ser solo 'S' si no necesitas el nombre
        });
    });
};

// --- FUNCIONES DE CHAT ---

// Abre o cierra la ventana del chat
const toggleChat = () => {
    isChatOpen.value = !isChatOpen.value;
    if (isChatOpen.value) {
        unreadMessages.value = 0; // Resetea contador al abrir
        scrollToBottom(); // Baja el scroll
    }
};

// Pide los mensajes nuevos al servidor
const loadChatMessages = async () => {
    // Solo carga si el juego está activo
    if (!isGameActive.value) {
        if (pollingInterval.value) clearInterval(pollingInterval.value); // Detiene el polling si el juego acabó
        return;
    }
    try {
        const response = await axios.post("/api/games/chat/get-messages", {
            gameCode: gameStore.matchCode,
            // Podrías enviar lastMessageId.value para optimizar y solo pedir nuevos
        });

        if (response.data.status === "success") {
            const newMessages = response.data.data;

            // Comprueba si hay mensajes realmente nuevos comparando IDs o longitud
            if (
                newMessages.length > 0 &&
                newMessages[newMessages.length - 1].id > lastMessageId.value
            ) {
                const newlyReceived = newMessages.filter(
                    (msg) => msg.id > lastMessageId.value
                );

                if (!isChatOpen.value && newlyReceived.length > 0) {
                    unreadMessages.value += newlyReceived.length; // Incrementa no leídos
                }

                // Actualiza el ID del último mensaje
                lastMessageId.value = newMessages[newMessages.length - 1].id;

                // Actualiza la lista completa de mensajes
                messages.value = newMessages;

                // Si el chat está abierto, baja el scroll
                if (isChatOpen.value) {
                    await nextTick(); // Espera a que Vue actualice el DOM
                    scrollToBottom();
                }
            } else if (messages.value.length === 0 && newMessages.length > 0) {
                // Caso inicial: cargar mensajes por primera vez
                messages.value = newMessages;
                if (newMessages.length > 0) {
                    lastMessageId.value =
                        newMessages[newMessages.length - 1].id;
                }
                if (isChatOpen.value) {
                    await nextTick();
                    scrollToBottom();
                }
            }
        }
    } catch (error) {
        console.error("Error cargando mensajes:", error);
        // Podrías detener el polling si hay errores repetidos
    }
};

// Envía el mensaje escrito al servidor
const sendMessage = async () => {
    // No enviar mensajes vacíos
    if (!newMessage.value.trim()) return;

    try {
        const response = await axios.post("/api/games/chat/send-message", {
            gameCode: gameStore.matchCode,
            user: authStore().user,
            message: newMessage.value,
        });

        if (response.data.status === "success") {
            // Optimista: añade el mensaje localmente (la API debería devolverlo también)
            // messages.value.push(response.data.data); // Comentado si loadChatMessages lo trae
            newMessage.value = ""; // Limpia el input
            await nextTick();
            scrollToBottom(); // Baja el scroll
            // Forzar una recarga inmediata para ver el mensaje enviado
            await loadChatMessages();
        }
    } catch (error) {
        console.error("Error enviando mensaje:", error);
        showNotification("Error al enviar mensaje", "error");
    }
};

// Mueve el scroll del chat hasta el final
const scrollToBottom = () => {
    if (chatMessages.value) {
        chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
    }
};

// Inicia la consulta periódica de mensajes del chat
const startChatPolling = () => {
    // Carga inicial
    loadChatMessages();
    // Configura el intervalo para repetir la carga cada 3 segundos
    pollingInterval.value = setInterval(loadChatMessages, 3000);
};

// Código que se ejecuta al montar el componente
onMounted(async () => {
    // Carga la posición de tus barcos
    await loadShips();

    // Obtiene información inicial de la partida (quién empieza, IDs)
    const response = await axios.post("/api/games/get-match-info", {
        gameCode: gameStore.matchCode,
    });

    currentUserId.value = authStore().user.id;
    // Encuentra al oponente en la lista de jugadores
    opponent.value = response.data.data.players.find(
        (player) => player.user_id !== authStore().user.id
    );
    opponentId.value = opponent.value?.user_id; // Usa optional chaining por si acaso

    // Determina quién empieza (el creador de la partida)
    yourTurn.value = response.data.data.game.created_by === authStore().user.id;

    // Inicia el bucle principal del juego
    gameLoop();

    // Inicia la consulta periódica del chat
    startChatPolling();
});

// Limpieza al desmontar el componente (salir de la página)
onUnmounted(() => {
    console.log("Desmontando GameComponent: Deteniendo juego y chat.");
    isGameActive.value = false; // Detiene el bucle gameLoop
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value); // Detiene la consulta del chat
        pollingInterval.value = null;
    }
    // Aquí podrías añadir lógica para informar al servidor si abandonas
});

// Observa cambios en la ruta para limpiar si se navega fuera
watch(
    () => route.path,
    (newPath, oldPath) => {
        // Si estabas en /game y te vas a otra ruta...
        if (oldPath.includes("/game") && !newPath.includes("/game")) {
            console.log("Saliendo de la pantalla de juego (watch route.path).");
            isGameActive.value = false; // Detiene el juego
            if (pollingInterval.value) {
                clearInterval(pollingInterval.value); // Detiene el chat
                pollingInterval.value = null;
            }
        }
    }
);

// Observa si llega un mensaje nuevo para reproducir sonido
watch(lastMessageId, (newVal, oldVal) => {
    // Si el ID ha cambiado (y no es la carga inicial)
    if (oldVal !== 0 && newVal > oldVal) {
        // Reproduce sonido (asegúrate que el archivo existe en /public/sounds)
        const audio = new Audio("/sounds/notification.mp3");
        audio
            .play()
            .catch((e) => console.log("Error al reproducir sonido:", e));
    }
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

/* --- Base Styles (> 1200px) --- */
.boards-container {
    display: flex;
    /* Layout horizontal por defecto */
    flex-direction: row;
    justify-content: center;
    align-items: flex-start;
    /* No usar wrap aquí, controlaremos el cambio con media query */
    /* flex-wrap: wrap; */
    gap: 8rem; /* Espacio horizontal */
    width: 100%;
    /* Aumentamos max-width para acomodar el gap grande y los tableros */
    max-width: 1100px;
    padding: 1rem;
    margin: 0 auto;
    flex: 1;
}

.board-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    /* Definimos un ancho fijo para el layout horizontal */
    width: 420px;
    max-width: 420px; /* Coincide con el board-container */
    /* Quitamos flex-grow/shrink/basis para un control más directo */
}

.board-container {
    width: 100%;
    aspect-ratio: 1 / 1;
    max-width: 420px;
    padding: 0.25rem;
    border-radius: 8px;
    border: 2px solid var(--primary-color);
    background: var(--background-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ... other base styles like .board-grid, .board-cell ... */

/* --- Vertical Layout (< 1200px) --- */
@media (max-width: 1200px) {
    .boards-container {
        flex-direction: column; /* Fuerza el layout vertical */
        align-items: center; /* Centra los tableros apilados */
        gap: 2.5rem; /* Espacio vertical entre tableros */
        max-width: 95%; /* Permite que el contenedor se ajuste */
    }

    .board-section {
        /* Ajustamos el ancho para el layout vertical */
        width: 90%; /* Ocupa la mayor parte del contenedor */
        /* Mantenemos el max-width por si el 90% es muy grande */
        max-width: 420px;
        /* Reseteamos flex por si acaso */
        flex: none;
    }

    /* Ordenamiento: Tu tablero (primero en HTML) va abajo */
    .board-section:first-child {
        order: 2;
    }

    /* Ordenamiento: Tablero oponente (segundo en HTML) va arriba */
    .board-section:last-child {
        order: 1;
    }
}

/* --- Ajustes para pantallas muy pequeñas (< 600px) --- */
@media (max-width: 600px) {
    .boards-container {
        gap: 1.5rem; /* Reduce más el espacio vertical */
    }

    .board-section {
        /* Reducimos el tamaño máximo en móviles */
        max-width: 330px;
        width: 95%; /* Ajusta el ancho si es necesario */
    }

    /* Aseguramos que el contenedor interno también se ajuste */
    .board-container {
        max-width: 330px;
    }
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

.board-cell.hit,
.board-cell.miss {
    background-color: transparent !important;
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

.board-cell.ship.hit::after {
    background-color: transparent;
}

.clickable {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.clickable:hover {
    background-color: #7048ec33;
}

.game-status {
    width: 100%;
    padding: 1rem;
    text-align: center;
    margin-bottom: 1rem;
    margin-top: 20px;
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

.game-chat {
    position: fixed;
    bottom: 1rem;
    right: 1rem;
    z-index: 1000;
}

.chat-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    position: relative;
    transition: transform 0.3s ease;
}

.chat-toggle:hover {
    transform: scale(1.1);
}

.unread-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: var(--secondary-color);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-container {
    position: absolute;
    bottom: 60px;
    right: 0;
    width: 300px;
    height: 0;
    background-color: var(--background-secondary);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    transition: height 0.3s ease;
    display: flex;
    flex-direction: column;
}

.chat-open {
    height: 400px;
}

.chat-header {
    padding: 10px;
    background-color: var(--primary-color);
    color: white;
}

.chat-header h3 {
    margin: 0;
    font-size: 1rem;
}

.chat-messages {
    flex-grow: 1;
    padding: 10px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background-color: var(--background-primary);
}

.no-messages {
    color: var(--white-color-1);
    text-align: center;
    margin-top: 2rem;
}

.message {
    padding: 10px;
    border-radius: 8px;
    max-width: 80%;
    word-wrap: break-word;
}

.own-message {
    align-self: flex-end;
    background-color: var(--primary-v3-color);
    color: var(--neutral-color);
}

.other-message {
    align-self: flex-start;
    background-color: var(--secondary-color);
    color: var(--white-color);
}

.message-header {
    font-size: 0.8rem;
    margin-bottom: 5px;
}

.own-message .message-header {
    color: var(--neutral-color-2);
}

.other-message .message-header {
    color: var(--neutral-color-3);
}

.message-user {
    font-weight: bold;
}

.chat-input {
    padding: 10px;
    display: flex;
    border-top: 1px solid var(--neutral-color-1);
    background-color: var(--background-secondary);
}

.chat-input input {
    flex-grow: 1;
    padding: 8px;
    border: 1px solid var(--neutral-color-1);
    border-radius: 4px;
    outline: none;
    background-color: var(--background-primary);
    color: var(--white-color);
}

.chat-input button {
    margin-left: 5px;
    padding: 8px 12px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.chat-input button:hover {
    background-color: var(--primary-v2-color);
}

.chat-input button:disabled {
    background-color: var(--neutral-color-1);
    cursor: not-allowed;
}

@media (max-width: 1000px) {
    .chat-container {
        width: 280px;
    }

    .chat-open {
        height: 350px;
    }
}

@media (max-width: 600px) {
    .game-chat {
        bottom: 0.5rem;
        right: 0.5rem;
    }

    .chat-toggle {
        width: 45px;
        height: 45px;
    }

    .chat-container {
        width: calc(100vw - 20px);
        max-width: 350px;
    }
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
}

.loading-overlay {
    position: relative;
    width: 300px;
    padding: 1rem;
    background: var(--background-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    border-radius: 8px;
}

.loading-overlay i {
    font-size: 2rem;
    color: var(--primary-color);
}
</style>
