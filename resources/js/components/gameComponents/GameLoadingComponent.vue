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

// Settear el CHAT a 0 siempre que se entre a una partida
const isChatOpen = ref(false);
const messages = ref([]);
const newMessage = ref("");
const chatMessages = ref(null);
const unreadMessages = ref(0);
const lastMessageId = ref(0);
const pollingInterval = ref(null);

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
        console.log(`Esperando ${minLoadingTime - elapsedTime} ms adicionales para mejor experiencia de carga...`);
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
    // console.log("GameLoadingComponent cargado.");
    // console.log("User: ", authStore().user);
    // console.log("Game type: ", route.params.gameType);
    // console.log("Game code: ", route.params.gameCode);

    // Verificación de usuario autenticado, modo de juego y código
    if (!authStore().user || !route.params.gameType) {
        backToHome(true, "No tienes permiso para acceder a esta página");
    }

    // Esconder finales de partida (en caso de estar abiertos)
    gameStore.setShowWin(false);
    gameStore.setShowGameOver(false);
    gameStore.setShowDraw(false);

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
    // console.log("Finding match...");
    isLoading.value = true;
    loadingStartTime.value = Date.now(); // Registrar el tiempo de inicio de carga

    try {
        // Llamar a la API para encontrar partida
        const response = await axios.post("/api/games/find-match", {
            gameType: route.params.gameType,
            gameCode: route.params.gameCode,
            user: authStore().user,
        });
        // console.log("Match found:", response.data);
        console.log("Match code: ",response.data.game.code);

        // Si se ha encontrado partida, entrar
        if (response.data.status == "success") {
            // Asegurarnos de que la pantalla de carga se muestre por un tiempo mínimo
            await ensureMinimumLoadingTime();
            isLoading.value = false;

            // Obtener el creador de la partida
            if (response.data.game.created_by == authStore().user.id) {
                //Si eres el creador de la partida
                console.log("Creador.");
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
                console.log("Invitado.");
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
            // console.log("Quedan ", 40 - contador, " trys.");
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
            // console.log("Timestamp uploaded: ", response.data.game.start_date);

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
            // console.log("Quedan ", 40 - contador, " polls.");
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
    grid-template-columns: 1fr auto 1fr; /* Mantener fracciones flexibles */
    gap: 1.5rem;
    padding: 1rem;
    width: 100%;
    align-items: center;
    justify-content: center;
}

.player-side {
    display: flex;
    justify-content: center;
    width: 100%;
    /* Eliminar max-width para permitir adaptación */
}

/* Contenedor player-card y UserComponent compartiendo mismas dimensiones */
.player-card,
.player-side :deep(.profile) {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 300px !important; /* Establecer un max-width común para ambos */
    min-height: 100px !important;
    height: auto !important;
    display: flex !important;
    align-items: center !important;
    padding: 0.75rem !important;
    background-color: var(--neutral-color-1) !important;
    border-radius: 8px !important;
    border: 2px solid var(--primary-color) !important;
    transition: all 0.3s ease !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
}

/* Arreglar el icono de carga del oponente */
.player-avatar {
    flex-shrink: 0;
    width: 55px; /* Ajustar para que coincida con el tamaño del avatar del usuario */
    height: 55px;
    border-radius: 50%;
    background: var(--neutral-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--primary-color);
    overflow: hidden; /* Para evitar desbordamientos del icono */
}

.guest-player {
    border-color: var(--secondary-color) !important;
}

/* Ajustes para el contenido dentro de UserComponent */
.player-side :deep(.profile-content) {
    padding: 0 !important;
    height: auto !important;
    width: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
}

.player-side :deep(.left-side) {
    display: flex !important;
    align-items: center !important;
    gap: 1rem !important;
    width: 100% !important;
    overflow: hidden !important;
    justify-content: flex-start !important;
    padding-left: 0.5rem !important;
}

.vs-separator {
    padding: 0 2rem;
    display: flex;
    align-items: center;
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
    margin-bottom: 2.5rem;
    text-align: center;
    width: 90%;
    max-width: 800px;
    font-size: 2.2rem; /* Tamaño base para pantallas grandes */
}

@media (max-width: 1200px) {
    .page-title {
        font-size: 2rem;
        margin-bottom: 2rem;
    }
}

@media (max-width: 992px) {
    .page-title {
        font-size: 1.8rem;
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: 1.6rem;
        margin-bottom: 1.5rem;
        width: 95%;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.4rem;
        margin-bottom: 1.2rem;
        width: 100%;
    }
}

@media (max-width: 400px) {
    .page-title {
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }
}

/* Estilos específicos para hacer que UserComponent coincida con player-card */
.player-side :deep(.profile) {
    min-height: 100px !important;
    height: 100% !important;
    width: 100% !important;
    max-width: none !important; /* Eliminado max-width para ocupar todo el espacio */
    background-color: var(--neutral-color-1) !important;
    border: 2px solid var(--primary-color) !important;
    border-radius: 8px !important;
    padding: 0.75rem !important;
    margin: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
}

.player-side :deep(.profile-content) {
    padding: 0 !important;
    height: auto !important;
    width: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important; /* Cambiado a center desde flex-start */
}

.player-side :deep(.left-side) {
    display: flex !important;
    align-items: center !important;
    gap: 1rem !important;
    width: 100% !important;
    overflow: hidden !important;
    justify-content: flex-start !important; /* Explícitamente alineado a la izquierda */
    padding-left: 0.5rem !important; /* Añadido padding izquierdo para mover el contenido */
}

/* Ajustes específicos para pantallas grandes (mayores a 800px) */
@media (min-width: 801px) {
    .players-container {
        grid-template-columns: minmax(250px, 300px) auto minmax(250px, 300px);
        gap: 2rem;
        padding: 1.5rem;
    }

    .player-side {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }

    .player-card,
    .player-side :deep(.profile) {
        max-width: 300px !important;
        min-height: 110px !important;
        padding: 0.75rem 1rem !important;
    }

    .vs-separator {
        padding: 0 1.5rem !important;
        min-width: 50px;
    }

    /* Ajustar el contenido de UserComponent específicamente para pantallas grandes */
    .player-side :deep(.userImageContainer) {
        width: 55px !important;
        height: 55px !important;
        min-width: 55px !important;
    }

    .player-side :deep(.username) {
        font-size: 1.1rem !important;
        max-width: 200px !important; /* Dar más espacio en pantallas grandes */
    }

    .player-side :deep(.points) {
        font-size: 0.9rem !important;
    }
}

/* Ajustes específicos para resoluciones medianas */
@media (max-width: 800px) and (min-width: 601px) {
    .players-container {
        grid-template-columns: 1fr auto 1fr;
        gap: 1rem;
    }

    .vs-separator {
        padding: 0 0.5rem !important;
        min-width: 30px; /* Reducir el ancho mínimo */
    }

    .player-card,
    .player-side :deep(.profile) {
        max-width: 240px !important;
        min-height: 90px !important;
    }

    .player-side :deep(.userImageContainer) {
        width: 45px !important;
        height: 45px !important;
        min-width: 45px !important;
    }

    .player-side :deep(.username) {
        font-size: 0.95rem !important;
    }

    .player-side :deep(.points) {
        font-size: 0.8rem !important;
    }

    .player-avatar {
        width: 45px !important; /* Reducir tamaño del avatar del oponente */
        height: 45px !important;
    }

    .ship-loading-animation.small {
        width: 45px !important;
        height: 45px !important;
    }

    .ship-loading-animation.small i {
        font-size: 1.6rem !important;
    }
}

/* Corrección específica para el rango de 760px a 600px donde ocurre el comportamiento irregular */
@media (max-width: 760px) and (min-width: 601px) {
    .players-container {
        grid-template-columns: 1fr auto 1fr;
        gap: 0.5rem !important;
    }

    .vs-separator {
        padding: 0 0.25rem !important;
        min-width: 20px !important;
        font-size: 0.9rem;
    }

    .vs-separator span {
        font-size: 1.5rem !important;
    }

    .player-card,
    .player-side :deep(.profile) {
        max-width: 200px !important; /* Reducir aún más para este rango específico */
        min-height: 80px !important;
        padding: 0.5rem !important;
    }

    .player-avatar {
        width: 40px !important;
        height: 40px !important;
    }

    .player-side :deep(.userImageContainer) {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
    }

    .player-content {
        gap: 0.75rem !important;
    }

    .player-side :deep(.username) {
        font-size: 0.85rem !important;
        max-width: 100px !important;
    }

    .player-side :deep(.points) {
        font-size: 0.75rem !important;
    }

    .player-side :deep(.points img) {
        width: 14px !important;
        height: 14px !important;
    }
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

    .player-card,
    .player-side :deep(.profile) {
        max-width: 90% !important; /* Ajustado para móviles */
        width: 100% !important;
    }

    .player-side :deep(.profile-content) {
        flex-direction: row !important; /* Mantener horizontal en móviles */
        align-items: center !important;
        text-align: left !important; /* Alineación de texto a la izquierda */
        gap: 0.5rem !important;
        justify-content: flex-start !important; /* Alineado a la izquierda */
        padding-left: 0.25rem !important; /* Padding más pequeño en móviles */
    }

    .player-side :deep(.left-side) {
        flex-direction: row !important; /* Mantener horizontal en móviles */
        text-align: left !important;
        gap: 0.75rem !important;
        align-items: center !important;
        justify-content: flex-start !important;
    }

    .player-side :deep(.userImageContainer) {
        margin-right: 0.5rem !important; /* Espacio entre avatar y texto en móvil */
    }

    .player-side :deep(.username-wrapper) {
        align-items: flex-start !important; /* Alinear a la izquierda */
    }

    .player-avatar {
        width: 40px !important; /* Reducir aún más para móviles */
        height: 40px !important;
    }

    .ship-loading-animation.small {
        width: 40px !important;
        height: 40px !important;
    }

    .ship-loading-animation.small i {
        font-size: 1.4rem !important;
    }
}
</style>
