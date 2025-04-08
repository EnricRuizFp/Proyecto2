<template>
    <div class="create-match app-background-primary game-fullscreen">
        <h3 class="h3-dark page-title">{{ loadingTitle }}</h3>

        <div class="match-setup" :class="{ 'loading-state': isLoading }">
            <div v-if="isLoading" class="loading-overlay">
                <div class="ship-loading-animation">
                    <i class="fas fa-ship"></i>
                </div>
                <p class="p3-dark">Cargando...</p>
            </div>

            <div v-else-if="error" class="error-container">
                <i class="fas fa-exclamation-circle"></i>
                <p class="p2-dark">{{ error }}</p>
                <button @click="backToHome" class="primary-button">
                    Volver a inicio
                </button>
            </div>

            <template v-else>
                <div class="players-container">
                    <!-- Contenedor izquierdo con UserComponent -->
                    <div class="player-side">
                        <UserComponent variant="profile" />
                    </div>

                    <!-- VS separator -->
                    <div class="vs-separator">
                        <span class="h2-dark">VS</span>
                    </div>

                    <!-- Contenedor derecho (esperando oponente) -->
                    <div class="player-side">
                        <div class="player-card guest-player waiting">
                            <div class="player-content">
                                <div class="player-avatar">
                                    <div class="ship-loading-animation small">
                                        <i class="fas fa-ship"></i>
                                    </div>
                                </div>
                                <div>
                                    <p
                                        v-if="
                                            opponentUsername ==
                                            'Esperando oponente...'
                                        "
                                        class="p5-dark"
                                    >
                                        {{ opponentUsername }}
                                    </p>
                                    <p
                                        v-else
                                        class="p2-dark"
                                        style="
                                            font-size: 26px;
                                            position: relative;
                                            z-index: 10;
                                        "
                                    >
                                        {{ opponentUsername }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Código de la partida - Solo visible en partidas privadas -->
                <div v-if="isPrivateGame" class="match-code">
                    <p class="p3-dark">CÓDIGO DE LA PARTIDA</p>
                    <div class="code-display">
                        <div class="code-number">
                            <span class="code-text">{{ matchCode }}</span>
                            <button
                                class="copy-button"
                                @click="copyMatchCode"
                                title="Copiar código"
                            >
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTS -- */
import { ref, onMounted, watch, computed, onUnmounted } from "vue";
import axios from "axios";
import { authStore } from "../../store/auth";
import { useRoute, useRouter } from "vue-router";
import UserComponent from "../navbar/UserComponent.vue";
import { useGameStore } from "../../store/game";

/* -- VARIABLES -- */
const matchCode = ref(null);
const isLoading = ref(true);
const isPrivateGame = ref(null);
const error = ref(null);
const auth = authStore();
const route = useRoute();
const router = useRouter();
const gameStore = useGameStore(); // Utilizado para settear las fases del juego
const opponentUsername = ref("Esperando oponente...");
let matchStatus = ref(null);

// Nueva variable para controlar el tiempo mínimo de carga
const minLoadingTime = 2500; // Tiempo mínimo de carga en milisegundos (2.5 segundos)
const loadingStartTime = ref(Date.now());
const forceLoading = ref(true);

const loadingTitle = computed(() => {
    // Devuelve el título de la página dependiendo del tipo de juego y el código
    if (route.params.gameType === "public") {
        return "Uniéndote a una partida pública...";
    } else if (
        route.params.gameType === "private" &&
        route.params.gameCode != "null"
    ) {
        return "Uniéndote a una partida privada...";
    } else {
        return "Creando partida...";
    }
});

/* -- FUNCIONES -- */
// Función para volver a inicio
const backToHome = (type, message = "Ha ocurrido un error desconocido.") => {
    if (type) {
        alert(message);
    }
    router.push("/");
};

// Función para copiar el código de la partida
const copyMatchCode = () => {
    navigator.clipboard
        .writeText(matchCode.value)
        .then(() => {
            showNotification("Código copiado al portapapeles", "success");
        })
        .catch((err) => {
            console.error("Error al copiar:", err);
            showNotification("No se pudo copiar el código", "error");
        });
};

// Sistema de notificaciones
const notification = ref({
    show: false,
    message: "",
    type: "info",
});
const showNotification = (message, type = "info") => {
    notification.value = {
        show: true,
        message,
        type,
    };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

// Función sleep que devuelve una promesa
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Función para asegurar un tiempo mínimo de carga
const ensureMinimumLoadingTime = async () => {
    const elapsedTime = Date.now() - loadingStartTime.value;
    if (elapsedTime < minLoadingTime) {
        // Si no ha pasado suficiente tiempo, esperar la diferencia
        console.log(
            `Esperando ${
                minLoadingTime - elapsedTime
            }ms adicionales para mejor experiencia de carga...`
        );
        await sleep(minLoadingTime - elapsedTime);
    }
    forceLoading.value = false;
};

// Función esperar al timestamp
const waitForTimestamp = async (timestamp) => {
    const targetDate = new Date(timestamp);
    const now = new Date();

    if (now < targetDate) {
        const timeToWait = targetDate.getTime() - now.getTime();
        await new Promise((resolve) => setTimeout(resolve, timeToWait));
    }
};

// Función onMounted
onMounted(() => {
    console.log("GameLoadingComponent mounted.");
    console.log("User: ", authStore().user);
    console.log("Game type: ", route.params.gameType);
    console.log("Game code: ", route.params.gameCode);

    // Verificación de usuario autenticado, modo de juego y código
    if (!authStore().user || !route.params.gameType) {
        backToHome(true, "No tienes permiso para acceder a esta página");
    }

    // Encontrar partida
    findMatch();
});

// Añadir onUnmounted para limpiar estilos
onUnmounted(() => {
    // No necesitamos restaurar estilos que no hemos modificado
});

// Función para cuando se sale de la página terminar la partida
window.addEventListener("beforeunload", () => {
    // Generar los datos a subir
    const url = "/api/games/finish-match";
    const datos = {
        gameCode: matchCode.value,
        user: authStore().user,
    };

    // Convertir los datos a formato JSON
    const blob = new Blob([JSON.stringify(datos)], {
        type: "application/json",
    });

    // Enviar la petición al servidor sin esperar respuesta
    navigator.sendBeacon(url, blob);

    console.log("Cerrando partida");
});

// Función FindMatch
const findMatch = async () => {
    console.log("Finding match...");
    isLoading.value = true;
    loadingStartTime.value = Date.now(); // Registrar el tiempo de inicio de carga

    try {
        // Llamar a la API para encontrar partida
        const response = await axios.post("/api/games/find-match", {
            gameType: route.params.gameType,
            gameCode: route.params.gameCode,
            user: authStore().user,
        });
        console.log("Match found:", response.data);

        // Si se ha encontrado partida, entrar
        if (response.data.status == "success") {
            // Asegurarnos de que la pantalla de carga se muestre por un tiempo mínimo
            await ensureMinimumLoadingTime();
            isLoading.value = false;

            // Obtener el creador de la partida
            if (response.data.game.created_by == authStore().user.id) {
                //Si eres el creador de la partida
                console.log("Creador de la partida");
                matchCode.value = response.data.game.code;

                if (!response.data.game.is_public) {
                    isPrivateGame.value = true;
                }

                // Obtener el username del oponente si existe
                if (
                    response.data.game.players &&
                    response.data.game.players.length > 1
                ) {
                    const opponent = response.data.game.players.find(
                        (player) => player.user_id !== authStore().user.id
                    );
                    opponentUsername.value = opponent
                        ? opponent.username
                        : "Esperando oponente...";
                }

                setTimestampMatchCreator();
            } else {
                // Si eres el invitado de la partida
                console.log("Invitado a la partida.");
                if (route.params.gameType === "private") {
                    matchCode.value = route.params.gameCode;
                } else {
                    matchCode.value = response.data.game.code;
                }

                pollMatchStatusGuest();
            }
        } else {
            await ensureMinimumLoadingTime();
            backToHome(true, "No se ha podido unir a la partida.");
        }
    } catch (err) {
        console.error("Error finding match:", err);
        error.value =
            "Error al encontrar partida. Inténtalo de nuevo más tarde.";
        await ensureMinimumLoadingTime();
        isLoading.value = false;
    }
};

// Función de polling para creador de la partida
const setTimestampMatchCreator = async () => {
    let contador = 0;

    // Esperar a que se una un usuario
    do {
        // Esperar 2 segundos entre pollings
        await sleep(2000);

        const response = await axios.post("/api/games/check-match-status", {
            gameCode: matchCode.value,
            user: auth.user,
        });
        matchStatus = response.data.message;
        contador++;

        // Mostrar por consola contador
        if (contador % 10 === 0) {
            console.log("Quedan ", 40 - contador, " trys.");
        }
    } while (matchStatus == "waiting" && contador <= 40);

    // Definir si se ha unido algún usuario
    if (matchStatus == "waiting") {
        await ensureMinimumLoadingTime();
        backToHome(true, "No se ha unido ningún jugador.");
    } else {
        const response = await axios.post("/api/games/create-timestamp", {
            gameCode: matchCode.value,
            data: "start_date",
        });

        if (response.data.status == "success") {
            console.log("Timestamp uploaded: ", response.data.game.start_date);

            await waitForTimestamp(response.data.game.start_date);
            gameStore.setMatchCode(matchCode.value);
            gameStore.setGamePhase("placement");
        } else {
            await ensureMinimumLoadingTime();
            backToHome(true, "Error al iniciar la partida.");
        }
    }
};

// Función de polling para invitado de la partida
const pollMatchStatusGuest = async () => {
    let contador = 0;
    let response = null;

    do {
        await sleep(2000);

        response = await axios.post("/api/games/check-timestamp", {
            gameCode: matchCode.value,
            data: "start_date",
        });

        matchStatus = response.data.status;
        contador++;

        if (contador % 10 === 0) {
            console.log("Quedan ", 40 - contador, " polls.");
        }
    } while (matchStatus != "success" && contador <= 40);

    if (matchStatus == "success") {
        console.log("Partida encontrada y unido");

        await waitForTimestamp(response.data.game.start_date);
        gameStore.setMatchCode(matchCode.value);
        gameStore.setGamePhase("placement");
    } else {
        await ensureMinimumLoadingTime();
        backToHome(true, "Error al unirse a la partida.");
    }
};
</script>

<style scoped>
.create-match {
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2rem;
    min-height: 100vh;
    height: 100vh;
    position: relative; /* Changed from fixed to relative to avoid overlay issues */
    width: 100%;
    overflow: hidden;
    z-index: 50; /* Reduced z-index to avoid covering everything */
}

.match-setup {
    margin-top: 0;
    background: var(--background-primary);
    padding: 2rem;
    border-radius: 8px;
    min-width: 300px;
    display: flex;
    flex-direction: column;
    gap: 3rem;
    align-items: center;
    max-width: 90%;
}

.loading-state {
    position: relative; /* Changed from fixed to relative */
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50; /* Reduced z-index */
    background-color: var(--background-primary);
}

.players-container {
    display: grid;
    grid-template-columns: minmax(250px, 1fr) auto minmax(250px, 1fr);
    gap: 2rem;
    padding: 1rem;
    width: 100%;
    align-items: center;
    justify-content: space-between;
}

.player-side {
    display: flex;
    justify-content: center;
    width: 100%;
}

.player-card {
    width: 100%;
    min-width: 250px;
    max-width: 300px;
    height: 120px;
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background: var(--neutral-color-1);
    border-radius: 8px;
    border: 2px solid var(--primary-color);
    transition: all 0.3s ease;
}

.guest-player {
    border-color: var(--secondary-color);
}

.player-avatar {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--neutral-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--primary-color);
}

.vs-separator {
    padding: 0 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 60px;
    color: var(--primary-color);
    font-weight: bold;
}

.waiting .player-avatar {
    color: var(--secondary-color);
}

.match-code {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    margin-top: 2rem;
    padding: 1.5rem 2rem;
    border: 2px solid var(--primary-color);
    border-radius: 12px;
    background: var(--neutral-color-1);
    min-width: 300px;
}

.code-display {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 60px;
}

.code-number {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border: 2px solid var(--primary-color);
    border-radius: 12px;
    background: var(--neutral-color);
    width: 100%;
    max-width: 400px;
    position: relative;
}

.code-text {
    text-align: center;
    font-family: "Rubik", sans-serif;
    font-size: 24px;
    font-weight: 600;
    letter-spacing: 3px;
    color: var(--white-color);
}

.copy-button {
    position: absolute;
    right: 1rem;
    background: none;
    border: none;
    color: var(--primary-color);
    cursor: pointer;
    padding: 0.25rem;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.copy-button:hover {
    color: var(--primary-v2-color);
    transform: scale(1.1);
}

.loading-overlay {
    position: relative;
    width: 300px;
    padding: 2rem;
    background: var(--background-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    border-radius: 8px;
}

.error-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
}

.error-container i {
    font-size: 3rem;
    color: var(--secondary-color);
}

.player-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    width: 100%;
}

.player-info {
    position: relative;
}

.player-info p {
    position: relative;
    z-index: 10;
    color: var(--white-color) !important;
}

.page-title {
    margin-bottom: 3rem;
}

.player-side :deep(.profile) {
    min-height: unset;
    width: 100%;
    padding: 1rem;
    margin: 0;
    background-color: var(--neutral-color-1);
    border: 2px solid var(--primary-color);
}

.player-side :deep(.profile-content) {
    height: auto;
    padding: 0;
}

/* Pulse animation */
.pulse {
    animation: pulse 2s infinite;
}

/* Animación del barco */
.ship-loading-animation {
    position: relative;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ship-loading-animation.small {
    width: 60px;
    height: 60px;
}

.ship-loading-animation i {
    font-size: 2.5rem;
    color: var(--primary-color);
    position: relative;
    animation: shipMovement 3s infinite ease-in-out;
}

.ship-loading-animation.small i {
    font-size: 1.8rem;
    color: var(--secondary-color);
}

@keyframes shipMovement {
    0% {
        transform: translateY(0px) rotate(-3deg);
    }
    50% {
        transform: translateY(-5px) rotate(3deg);
    }
    100% {
        transform: translateY(0px) rotate(-3deg);
    }
}

@media (max-width: 600px) {
    .players-container {
        grid-template-columns: 1fr;
        gap: 1rem;
        justify-items: center;
    }

    .player-content {
        flex-direction: column !important;
        gap: 1rem;
    }

    .player-info {
        text-align: center !important;
    }

    .player-card {
        width: 100%;
        max-width: 250px;
    }

    .match-setup {
        padding: 1rem;
        gap: 1.5rem;
    }
}
</style>
