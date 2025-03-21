<template>
    <div class="create-match app-background-primary">
        <h3 class="h3-dark page-title">{{loadingTitle}}</h3>

        <!-- Mensajes de error/éxito -->
        <transition name="fade">
            <div
                v-if="notification.show"
                :class="['notification', notification.type]"
            >
                {{ notification.message }}
            </div>
        </transition>

        <div class="match-setup" :class="{ 'loading-state': isLoading }">
            <div v-if="isLoading" class="loading-overlay">
                <i class="fas fa-spinner fa-spin"></i>
                <p class="p3-dark">Cargando...</p>
            </div>

            <div v-else-if="error" class="error-container">
                <i class="fas fa-exclamation-circle"></i>
                <p class="p2-dark">{{ error }}</p>
                <button @click="retry" class="primary-button">
                    Reintentar
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
                                <div class="player-info">
                                    <p class="p2-dark">Esperando oponente...</p>
                                </div>
                                <div class="player-avatar pulse">
                                    <i class="fas fa-spinner fa-spin"></i>
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
import { ref, onMounted, watch, computed, onUnmounted } from "vue";
import axios from "axios";
import useUsers from "../../composables/users";
import { authStore } from "../../store/auth"; // Añadir importación del auth store
import { useRoute } from 'vue-router';
import UserComponent from '../navbar/UserComponent.vue';

const matchCode = ref("XXXX-XXXX");
const isLoading = ref(true);
const error = ref(null);
const auth = authStore();
const route = useRoute();

const loadingTitle = computed(() => {
    const gameType = route.params.gameType;
    const gameCode = route.params.gameCode;

    if (gameType === 'public') {
        return 'Uniendo a una partida online...';
    } else if (gameType === 'private') {
        // Si hay código, nos estamos uniendo; si no, estamos creando
        return gameCode && gameCode !== 'null' ? 'Uniéndote a la partida...' : 'Creando partida privada...';
    }
    return 'Cargando...';
});

// Modificar el computed para mostrar el código solo cuando sea partida privada y no haya gameCode
const isPrivateGame = computed(() => {
    const isPrivate = route.params.gameType === 'private';
    const hasNoCode = !route.params.gameCode || route.params.gameCode === 'null';
    return isPrivate && hasNoCode;
});

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

const retry = () => {
    error.value = null;
    findMatch();
};

// Función para copiar el código de la partida al portapapeles
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

const MAX_WAIT_TIME = 120000; // 2 minutos
const POLLING_INTERVAL = 5000; // 5 segundos
let startTime = null;

// Función recursiva para verificar el estado de la partida
const pollMatchStatus = async () => {
    try {
        if (Date.now() - startTime >= MAX_WAIT_TIME) {
            console.log('Tiempo de espera agotado (2 minutos)');
            error.value = "Tiempo de espera agotado";
            return;
        }

        const response = await axios.post('/api/games/check-match-status', {
            gameCode: matchCode.value,
            user: auth.user
        });
        
        // Log detallado del estado de la partida
        console.log('Estado de la partida:', {
            timestamp: new Date().toLocaleTimeString(),
            message: response.data.message
        });

        // Si se ha unido otro usuario, obtener sus datos
        if (response.data.message === 'user joined') {

            console.log("Usuario actual: ", authStore().user?.id);
            console.log("Datos del juego: ", response.data.game.players);

            // Encontrar al otro jugador (el que no es el usuario actual)
            const otherPlayer = response.data.game.players.find(
                player => player.id !== authStore().user?.id
            );

            if (otherPlayer) {
                console.log("Oponente: ", otherPlayer.username);
                return; // Detener el polling una vez encontrado el oponente
            }
        }

        // Esperar 5 segundos antes de la siguiente verificación
        await new Promise(resolve => setTimeout(resolve, POLLING_INTERVAL));
        
        // Llamada recursiva
        await pollMatchStatus();

    } catch (err) {
        console.error('Error al verificar estado de la partida:', err);
        error.value = "Error al verificar el estado de la partida";
    }
};

// Modificar findMatch para iniciar el polling
const findMatch = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post('/api/games/find-match', {
            gameType: route.params.gameType,
            gameCode: route.params.gameCode,
            user: auth.user
        });
        
        console.log('Find Match Response:', response.data);
        
        if (response.data.status === 'success') {
            if (response.data.game && response.data.game.code) {
                matchCode.value = response.data.game.code;
                
                // Iniciar polling solo si es partida privada sin gameCode
                if (route.params.gameType === 'private' && route.params.gameCode == "null") {
                    startTime = Date.now();
                    pollMatchStatus();
                }
            }
            isLoading.value = false;
        } else {
            error.value = response.data.message;
            isLoading.value = false;
        }
    } catch (err) {
        console.error('Error finding match:', err);
        error.value = "Error al buscar partida";
        isLoading.value = false;
    }
};

// Limpiar el intervalo cuando el componente se desmonte
onUnmounted(() => {
    if (checkStatusInterval) {
        clearInterval(checkStatusInterval);
    }
});

// Función ONMOUNTED
onMounted(() => {

    // Buscar partida (con los datos de: gameType, gameCode y user)
    findMatch();

    
});
</script>

<style scoped>
.create-match {
    padding-bottom: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center; /* Añadido para centrar verticalmente */
    gap: 2rem;
    min-height: 100vh;
}

.match-setup {
    margin-top: -3rem; /* Aumentado el margen negativo para subir más el contenedor */
    background: var(--background-primary);
    padding: 2rem;
    border-radius: 8px;
    min-width: 300px;
    display: flex;
    flex-direction: column;
    gap: 3rem;
    align-items: center;
}

.players-container {
    display: grid;
    grid-template-columns: minmax(250px, 1fr) auto minmax(250px, 1fr); /* Asegurar ancho mínimo igual */
    gap: 2rem;
    padding: 1rem;
    width: 100%;
    align-items: center;
    justify-content: space-between; /* Distribuir espacio uniformemente */
}

.player-side {
    display: flex;
    justify-content: center; /* Centra el contenido en su espacio */
    width: 100%;
}

.player-card {
    width: 100%;
    min-width: 250px;
    max-width: 300px;
    height: 120px; /* Altura fija para ambas tarjetas */
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background: var(--neutral-color-1);
    border-radius: 8px;
    border: 2px solid var(--primary-color);
    transition: all 0.3s ease;
}

.guest-player {
    border-color: var(--secondary-color); /* Borde distintivo para el guest */
}

.player-ready {
    border-color: var(--primary-color);
}

.player-avatar {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--neutral-color); /* Asegurar fondo oscuro para el avatar */
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
    border-radius: 12px; /* Aumentado de 8px */
    background: var(--neutral-color-1);
    min-width: 300px; /* Añadido para garantizar un ancho mínimo */
}

.code-display {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 60px; /* Añadido para asegurar altura consistente */
}

.code-number {
    display: flex;
    align-items: center;
    justify-content: center; /* Cambiado para centrar contenido */
    padding: 0.75rem 1.5rem;
    border: 2px solid var(--primary-color);
    border-radius: 12px;
    background: var(--neutral-color);
    width: 100%;
    max-width: 400px; /* Ajustado el ancho máximo */
    position: relative; /* Para posicionar el botón de copia */
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

@media (max-width: 600px) {
    .players-container {
        grid-template-columns: 1fr; /* Una sola columna en móvil */
        gap: 1rem;
        justify-items: center; /* Centrar elementos en móvil */
    }

    .page-title {
        margin-bottom: 3rem; /* Reducir el margen en móvil */
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
        max-width: 250px; /* Ajustar máximo en móvil */
    }
}

/* Añadir estilos para estados de carga y error */
.error-message {
    color: var(--secondary-color);
    text-align: center;
    padding: 1rem;
}

.loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Estilos para el sistema de notificaciones */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 2rem;
    border-radius: 4px;
    z-index: 1000;
    animation: slideIn 0.3s ease;
}

.notification.success {
    background-color: #4caf50;
    color: white;
}

.notification.error {
    background-color: var(--secondary-color);
    color: white;
}

.notification.info {
    background-color: var(--primary-color);
    color: white;
}

/* Estado de carga */
.loading-state {
    position: fixed; /* Cambiado de relative a fixed */
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.loading-overlay {
    position: relative; /* Cambiado de absolute a relative */
    width: 300px; /* Añadido ancho fijo */
    padding: 2rem;
    background: var(--background-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    border-radius: 8px;
}

/* Efecto pulse para el avatar de espera */
.pulse {
    animation: pulse 2s infinite;
}

/* Animaciones */
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

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.7;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Error container */
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

/* Avatar imagen */
.player-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.player-info {
    flex-grow: 1;
    display: block;
}

.player-ready {
    border-color: var(--primary-color);
}

.nationality {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.flag-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: cover;
}

/* Ajuste para el hover del jugador actual */
.player-ready:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(112, 72, 236, 0.2);
}

.player-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    width: 100%;
}

.host-player .player-content {
    flex-direction: row;
    justify-content: flex-start;
    background: transparent; /* Asegurar que no haya fondo en el contenedor */
}

.guest-player .player-content {
    flex-direction: row-reverse;
    justify-content: flex-start;
    background: transparent; /* Asegurar que no haya fondo en el contenedor */
}

/* Ajustar el color del texto del guest player */
.guest-player .player-info p {
    color: var(--white-color);
    font-size: 1.1rem;
    font-weight: bold;
}

/* Ajustar los media queries para la nueva disposición */
@media (max-width: 600px) {
    .player-content {
        flex-direction: column !important;
        gap: 1rem;
    }

    .player-info {
        text-align: center !important;
    }

    .player-card {
        min-width: 200px;
    }
}

.player-info p {
    color: var(--white-color);
}

.page-title {
    margin-bottom: 3rem; /* Reducido de 5rem a
3rem */
}

/* Ajustes para el UserComponent */
.player-side :deep(.profile) {
    min-height: unset;
    width: 100%;
    padding: 1rem;
    margin: 0;
    background-color: var(--neutral-color-1);
    border: 2px solid var(--primary-color);
}

/* Asegurar que el UserComponent tenga el mismo tamaño que la card del oponente */
.player-side :deep(.profile-content) {
    height: auto;
    padding: 0;
}
</style>
