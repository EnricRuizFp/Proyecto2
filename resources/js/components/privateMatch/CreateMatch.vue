<template>
    <div class="create-match">
        <h3 class="h3-dark page-title">Creando Partida Privada</h3>

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
                    <!-- Contenedor izquierdo -->
                    <div class="player-side">
                        <div
                            class="player-card host-player"
                            :class="{ 'player-ready': currentPlayer }"
                        >
                            <div class="player-content">
                                <div class="player-avatar">
                                    <img
                                        v-if="currentPlayer?.avatar"
                                        :src="currentPlayer.avatar"
                                        :alt="currentPlayer.username"
                                        @error="handleAvatarError"
                                    />
                                    <i v-else class="fas fa-user"></i>
                                </div>
                                <div class="player-info">
                                    <p class="p2-dark bold">
                                        {{
                                            currentPlayer?.username ||
                                            "Sin nombre de usuario"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- VS separator ahora es independiente -->
                    <div class="vs-separator">
                        <span class="h2-dark">VS</span>
                    </div>

                    <!-- Contenedor derecho -->
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

                <!-- Código de la partida -->
                <div class="match-code">
                    <p class="p3-dark">CÓDIGO DE LA PARTIDA</p>
                    <div class="code-display">
                        <div class="code-number">
                            <span class="code-text">{{
                                formattedMatchCode
                            }}</span>
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
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";
import useUsers from "../../composables/users";
import { authStore } from "../../store/auth"; // Añadir importación del auth store

const currentPlayer = ref(null);
const matchCode = ref("XXXX-XXXX");
const isLoading = ref(true);
const error = ref(null);
const { getUser, user } = useUsers();
const auth = authStore();

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

const handleAvatarError = (e) => {
    e.target.src = "/images/placeholder.jpg";
};

const retry = () => {
    error.value = null;
    getCurrentUser();
};

// Función para obtener el usuario actual
const formatNationality = (nationality) => {
    return nationality.charAt(0).toUpperCase() + nationality.slice(1);
};

const getCurrentUser = async () => {
    try {
        isLoading.value = true;
        // Primero verificamos si el usuario está autenticado
        if (!auth.authenticated) {
            throw new Error("No autenticado");
        }

        // Utilizamos el usuario del auth store
        if (auth.user) {
            console.log("Auth user:", auth.user);
            await getUser(auth.user.id);
            console.log("User from composable:", user.value);

            // Asignar directamente los valores que necesitamos
            currentPlayer.value = {
                username: user.value.username,
                name: `${user.value.name} ${user.value.surname1}`,
                avatar: user.value.avatar,
                nationality: user.value.nationality,
            };

            console.log("Current player set to:", currentPlayer.value);
            generateMatchCode();
        } else {
            throw new Error("Usuario no encontrado");
        }
    } catch (err) {
        console.error("Error completo:", err);
        error.value = "Debes iniciar sesión para crear una partida privada";

        // Mostrar notificación antes de redirigir
        showNotification("Sesión no iniciada. Redirigiendo...", "error");

        // Retrasar la redirección para que se vea la notificación
        setTimeout(() => {
            window.location.href = "/login";
        }, 2000);
    } finally {
        isLoading.value = false;
    }
};

// Observador para actualizar currentPlayer cuando cambie user.value
watch(
    () => user.value,
    (newValue) => {
        if (newValue) {
            currentPlayer.value = {
                username: newValue.username,
                name: `${newValue.name} ${newValue.surname1}`,
                avatar: newValue.avatar,
                nationality: newValue.nationality,
            };
        }
    },
    { immediate: true }
);

// Generar código aleatorio de 4 dígitos alfanuméricos
const generateMatchCode = () => {
    const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    let result = "";
    for (let i = 0; i < 4; i++) {
        result += characters.charAt(
            Math.floor(Math.random() * characters.length)
        );
    }
    matchCode.value = result;
};

// Computed property para el código formateado
const formattedMatchCode = computed(() => {
    return matchCode.value.toUpperCase();
});

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

onMounted(() => {
    getCurrentUser();
});
</script>

<style scoped>
.create-match {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center; /* Añadido para centrar verticalmente */
    gap: 2rem;
    background: var(--neutral-color);
    min-height: 100vh;
}

.match-setup {
    margin-top: -3rem; /* Aumentado el margen negativo para subir más el contenedor */
    background: var(--neutral-v2-color);
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
    width: 100%; /* Asegurar que la tarjeta ocupe todo el espacio disponible */
    min-width: 250px;
    max-width: 300px; /* Limitar el ancho máximo */
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background: var(
        --neutral-color-1
    ); /* Cambiado de neutral-color a neutral-color-1 */
    border-radius: 8px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
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
}

.code-number {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 1rem;
    border: 2px solid var(--primary-color);
    border-radius: 8px; /* Aumentado de 4px */
    background: var(--neutral-color);
    min-width: 240px; /* Aumentado de 200px */
}

.code-text {
    font-family: "Rubik", sans-serif;
    font-size: 24px;
    font-weight: 600;
    letter-spacing: 3px;
    color: var(--white-color);
    flex-grow: 1;
    text-align: center;
}

.copy-button {
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
    position: relative;
    min-height: 300px;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(19, 18, 23, 0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    border-radius: 8px;
}

.loading-overlay i {
    font-size: 3rem;
    color: var(--primary-color);
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
    margin-bottom: 3rem; /* Reducido de 5rem a 3rem */
}
</style>
