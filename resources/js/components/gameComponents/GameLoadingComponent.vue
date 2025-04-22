<template>
    <div
        class="create-match app-background-primary game-fullscreen"
        :class="{ 'is-loading': isLoading }"
    >
        <h3 class="h3-dark page-title">{{ loadingTitle }}</h3>

        <div class="match-setup" :class="{ 'loading-state': isLoading }">
            <div v-if="isLoading" class="loading-overlay">
                <div class="ship-loading-animation">
                    <i class="fas fa-ship"></i>
                </div>
                <p class="p3-dark">{{$t("loading_message")}}</p>
            </div>

            <div v-else-if="error" class="error-container">
                <i class="fas fa-exclamation-circle"></i>
                <p class="p2-dark">{{ error }}</p>
                <button @click="backToHome" class="primary-button">
                    {{ $t("back_to_home") }}
                </button>
            </div>

            <template v-else>
                <div class="players-container">
                    <!-- Lado izquierdo: tu perfil -->
                    <div class="player-side">
                        <UserComponent variant="profile" />
                    </div>

                    <!-- Separador VS -->
                    <div class="vs-separator">
                        <span class="h2-dark">VS</span>
                    </div>

                    <!-- Lado derecho: esperando oponente -->
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

                <!-- Código de la partida (solo si es privada) -->
                <div v-if="isPrivateGame" class="match-code">
                    <p class="p3-dark">{{ $t("GAME_CODE") }}</p>
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
const matchCode = ref(null); // Código de la partida actual
const isLoading = ref(true); // ¿Estamos cargando algo?
const isPrivateGame = ref(null); // ¿Es una partida privada?
const error = ref(null); // Mensaje de error, si lo hay
const auth = authStore(); // Acceso al estado de autenticación
const route = useRoute(); // Información de la ruta actual
const router = useRouter(); // Para navegar entre páginas
const gameStore = useGameStore(); // Estado global del juego (fases, etc.)
const opponentUsername = ref("Esperando oponente..."); // Nombre del rival
let matchStatus = ref(null); // Estado actual de la partida ('waiting', 'ready', etc.)

// Variables del chat (inicializadas a 0 o vacío)
const isChatOpen = ref(false);
const messages = ref([]);
const newMessage = ref("");
const chatMessages = ref(null);
const unreadMessages = ref(0);
const lastMessageId = ref(0);
const pollingInterval = ref(null);

// Control para el tiempo mínimo de carga
const minLoadingTime = 2500; // 2.5 segundos para que se vea la animación
const loadingStartTime = ref(Date.now()); // Momento en que empezó la carga
const forceLoading = ref(true); // Forzar estado de carga al inicio

// Título dinámico de la página
const loadingTitle = computed(() => {
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
// Te manda de vuelta a la página principal
const backToHome = (type, message = "Ha ocurrido un error desconocido.") => {
    if (type) {
        //alert(message); // Muestra un mensaje si es necesario
    }
    router.push("/"); // Redirige a la raíz
};

// Copia el código de la partida al portapapeles
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

// Para mostrar notificaciones flotantes
const notification = ref({
    show: false,
    message: "",
    type: "info", // Puede ser 'success', 'error', etc.
});
const showNotification = (message, type = "info") => {
    notification.value = {
        show: true,
        message,
        type,
    };
    // La notificación desaparece sola después de 3 segundos
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

// Una pausa simple usando promesas
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Asegura que la pantalla de carga dure al menos 'minLoadingTime'
const ensureMinimumLoadingTime = async () => {
    const elapsedTime = Date.now() - loadingStartTime.value;
    if (elapsedTime < minLoadingTime) {
        // Si ha pasado menos tiempo del mínimo, esperamos lo que falte
        console.log(
            `Esperando ${minLoadingTime - elapsedTime} ms adicionales...`
        );
        await sleep(minLoadingTime - elapsedTime);
    }
    forceLoading.value = false; // Ya podemos quitar la pantalla de carga forzada
};

// Espera hasta que llegue una fecha/hora específica (timestamp)
const waitForTimestamp = async (timestamp) => {
    const targetDate = new Date(timestamp);
    const now = new Date();

    if (now < targetDate) {
        const timeToWait = targetDate.getTime() - now.getTime();
        await new Promise((resolve) => setTimeout(resolve, timeToWait));
    }
};

// Se ejecuta cuando el componente se monta en la página
onMounted(() => {
    // Verificaciones iniciales
    if (!authStore().user || !route.params.gameType) {
        backToHome(true, "No tienes permiso para acceder aquí.");
        return; // Salimos si no cumple los requisitos
    }

    // Ocultamos pantallas de fin de partida por si acaso
    gameStore.setShowWin(false);
    gameStore.setShowGameOver(false);
    gameStore.setShowDraw(false);

    // Empezamos a buscar o crear la partida
    findMatch();
});

// Se ejecuta cuando el componente se destruye (sales de la página)
onUnmounted(() => {
    // Aquí podríamos limpiar intervalos o listeners si los hubiera
});

// Intenta finalizar la partida si el usuario cierra la pestaña/navegador
window.addEventListener("beforeunload", () => {
    // Preparamos los datos para enviar al servidor
    const url = "/api/games/finish-match";
    const datos = {
        gameCode: matchCode.value,
        user: authStore().user,
    };

    // Usamos sendBeacon para enviar los datos de forma fiable sin esperar respuesta
    const blob = new Blob([JSON.stringify(datos)], {
        type: "application/json",
    });
    navigator.sendBeacon(url, blob);

    console.log("Intentando cerrar partida al salir...");
});

// Función principal para buscar/crear partida
const findMatch = async () => {
    isLoading.value = true;
    loadingStartTime.value = Date.now(); // Guardamos cuándo empezamos a cargar

    try {

        // Petición a la API para encontrar/crear
        const response = await axios.post("/api/games/find-match", {
            gameType: route.params.gameType,
            gameCode: route.params.gameCode,
            user: authStore().user,
        });

        console.log("Código de partida:", response.data.game.code);

        // Si la API dice que todo OK...
        if (response.data.status == "success") {
            // Esperamos el tiempo mínimo de carga antes de quitar el spinner
            await ensureMinimumLoadingTime();
            isLoading.value = false;

            // ¿Eres el creador de la partida?
            if (response.data.game.created_by == authStore().user.id) {
                console.log("Eres el creador.");
                matchCode.value = response.data.game.code;

                // Si no es pública, marcamos que es privada (para mostrar el código)
                if (!response.data.game.is_public) {
                    isPrivateGame.value = true;
                }

                // Buscamos si ya hay un oponente conectado
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

                // Empezamos a esperar a que se una el oponente y creamos el timestamp
                setTimestampMatchCreator();
            } else {
                // Eres el invitado
                console.log("Eres el invitado.");
                // Asignamos el código de partida (viene de la ruta si es privada, o de la respuesta si es pública)
                if (route.params.gameType === "private") {
                    matchCode.value = route.params.gameCode;
                } else {
                    matchCode.value = response.data.game.code;
                }

                // Empezamos a preguntar si el creador ya puso el timestamp
                pollMatchStatusGuest();
            }
        } else {
            // Si la API devuelve error al buscar/crear
            await ensureMinimumLoadingTime();
            backToHome(true, "No se ha podido unir a la partida.");
        }
    } catch (err) {
        // Si hay un error de conexión o en el servidor
        console.error("Error buscando partida:", err);
        error.value =
            "Error al encontrar partida. Inténtalo de nuevo más tarde.";
        await ensureMinimumLoadingTime();
        isLoading.value = false;
    }
};

// Función para el creador: espera al oponente y crea el timestamp de inicio
const setTimestampMatchCreator = async () => {
    let contador = 0; // Para no esperar indefinidamente

    // Preguntamos al servidor cada 2 segundos si ya se unió alguien
    do {
        await sleep(2000);

        const response = await axios.post("/api/games/check-match-status", {
            gameCode: matchCode.value,
            user: auth.user,
        });
        matchStatus = response.data.message; // 'waiting' o 'ready'
        contador++;

        // Límite de intentos (aprox. 80 segundos)
    } while (matchStatus == "waiting" && contador <= 40);

    // Si después de los intentos sigue en 'waiting', cancelamos
    if (matchStatus == "waiting") {
        await ensureMinimumLoadingTime(); // Aseguramos tiempo de carga visual
        backToHome(true, "Nadie se ha unido a la partida.");
    } else {
        // Si alguien se unió ('ready'), creamos el timestamp para empezar
        const response = await axios.post("/api/games/create-timestamp", {
            gameCode: matchCode.value,
            data: "start_date", // Le decimos que cree el timestamp 'start_date'
        });

        if (response.data.status == "success") {
            // Esperamos hasta la hora exacta del timestamp
            await waitForTimestamp(response.data.game.start_date);
            // Guardamos el código de partida en el store global
            gameStore.setMatchCode(matchCode.value);
            // Cambiamos la fase del juego a 'placement' (colocar barcos)
            gameStore.setGamePhase("placement");
        } else {
            // Si falla la creación del timestamp
            await ensureMinimumLoadingTime();
            backToHome(true, "Error al iniciar la partida.");
        }
    }
};

// Función para el invitado: espera a que el creador ponga el timestamp
const pollMatchStatusGuest = async () => {
    let contador = 0;
    let response = null;

    // Preguntamos cada 2 segundos si ya existe el timestamp 'start_date'
    do {
        await sleep(2000);

        response = await axios.post("/api/games/check-timestamp", {
            gameCode: matchCode.value,
            data: "start_date",
        });

        matchStatus = response.data.status; // 'success' si ya existe
        contador++;

        // Límite de intentos
    } while (matchStatus != "success" && contador <= 40);

    // Si encontramos el timestamp...
    if (matchStatus == "success") {
        console.log("Partida encontrada y unido.");
        // Esperamos hasta la hora exacta
        await waitForTimestamp(response.data.game.start_date);
        // Guardamos código y cambiamos fase
        gameStore.setMatchCode(matchCode.value);
        gameStore.setGamePhase("placement");
    } else {
        // Si no encontramos el timestamp después de los intentos
        await ensureMinimumLoadingTime();
        backToHome(true, "Error al unirse a la partida (timeout).");
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
    position: relative;
    width: 100%;
    overflow: hidden;
    z-index: 50;
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
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
    background-color: var(--background-primary);
}

.players-container {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
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
}

.player-card,
.player-side :deep(.profile) {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 300px !important;
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

.player-avatar {
    flex-shrink: 0;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: var(--neutral-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--primary-color);
    overflow: hidden;
}

.guest-player {
    border-color: var(--secondary-color) !important;
}

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
    font-size: 2.2rem;
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

.player-side :deep(.profile) {
    min-height: 100px !important;
    height: 100% !important;
    width: 100% !important;
    max-width: none !important;
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
    justify-content: center !important;
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

    .player-side :deep(.userImageContainer) {
        width: 55px !important;
        height: 55px !important;
        min-width: 55px !important;
    }

    .player-side :deep(.username) {
        font-size: 1.1rem !important;
        max-width: 200px !important;
    }

    .player-side :deep(.points) {
        font-size: 0.9rem !important;
    }
}

@media (max-width: 800px) and (min-width: 601px) {
    .players-container {
        grid-template-columns: 1fr auto 1fr;
        gap: 1rem;
    }

    .vs-separator {
        padding: 0 0.5rem !important;
        min-width: 30px;
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
        width: 45px !important;
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
        max-width: 200px !important;
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

.pulse {
    animation: pulse 2s infinite;
}

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
        max-width: 90% !important;
        width: 100% !important;
    }

    .player-side :deep(.profile-content) {
        flex-direction: row !important;
        align-items: center !important;
        text-align: left !important;
        gap: 0.5rem !important;
        justify-content: flex-start !important;
        padding-left: 0.25rem !important;
    }

    .player-side :deep(.left-side) {
        flex-direction: row !important;
        text-align: left !important;
        gap: 0.75rem !important;
        align-items: center !important;
        justify-content: flex-start !important;
    }

    .player-side :deep(.userImageContainer) {
        margin-right: 0.5rem !important;
    }

    .player-side :deep(.username-wrapper) {
        align-items: flex-start !important;
    }

    .player-avatar {
        width: 40px !important;
        height: 40px !important;
    }

    .ship-loading-animation.small {
        width: 40px !important;
        height: 40px !important;
    }

    .ship-loading-animation.small i {
        font-size: 1.4rem !important;
    }

    .is-loading .page-title {
        display: none;
    }
}
</style>
