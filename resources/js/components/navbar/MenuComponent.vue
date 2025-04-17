<template>
    <!-- Menu List -->
    <div id="lateralMenu">
        <hr class="dropdown-divider" />
        <div class="tituloBarraLateral">
            <!-- Cambiado de router-link a 'a' -->
            <a
                href="#"
                class="bolder menu-item"
                title="Menú principal del juego"
                @click.prevent="handleInicio"
                :class="{ 'disabled-menu-item': isCurrentRouteBlocked }"
            >
                <i class="fas fa-home"></i>
                <span class="menu-text">INICIO</span>
            </a>
        </div>
        <div class="subtituloBarraLateral">
            <div class="contenidoSubtituloBarraLateral">
                <a
                    class="menu-item"
                    @click.prevent="handlePublicGame"
                    title="Únete a partidas públicas con otros jugadores"
                    :class="{ 'disabled-menu-item': isCurrentRouteBlocked }"
                >
                    <i class="fas fa-globe"></i>
                    <span class="menu-text">Partida pública</span>
                </a>
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <a
                    class="menu-item"
                    @click.prevent="handleCreateGame"
                    title="Crea tu propia partida personalizada"
                    :class="{ 'disabled-menu-item': isCurrentRouteBlocked }"
                >
                    <i class="fas fa-plus"></i>
                    <span class="menu-text">Crear partida</span>
                </a>
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <a
                    class="menu-item"
                    @click.prevent="showJoinMatchModal"
                    title="Únete a una partida privada con amigos"
                    :class="{ 'disabled-menu-item': isCurrentRouteBlocked }"
                >
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Unirse a partida</span>
                </a>
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <a
                    class="menu-item"
                    @click.prevent="handleViewGame"
                    title="Observa partidas en curso"
                    :class="{ 'disabled-menu-item': isCurrentRouteBlocked }"
                >
                    <i class="fas fa-eye"></i>
                    <span class="menu-text">Observar partida</span>
                </a>
            </div>
            <!-- Enlace a Mi Perfil (solo si está logueado) -->
            <template v-if="authStore().user?.id">
                <hr class="dropdown-divider" />
                <div class="contenidoSubtituloBarraLateral">
                    <!-- Cambiado de router-link a 'a' -->
                    <a
                        href="#"
                        class="menu-item"
                        title="Accede a tu perfil de usuario"
                        @click.prevent="handleProfile"
                        :class="{ 'disabled-menu-item': isCurrentRouteBlocked }"
                    >
                        <i class="fas fa-user"></i>
                        <!-- Icono de usuario -->
                        <span class="menu-text">MI PERFIL</span>
                    </a>
                </div>
            </template>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <!-- Cambiado de router-link a 'a' -->
                <a
                    href="#"
                    class="menu-item"
                    title="Compite y alcanza la cima"
                    @click.prevent="handleRanking"
                    :class="{ 'disabled-menu-item': isCurrentRouteBlocked }"
                >
                    <i class="fas fa-trophy"></i>
                    <span class="menu-text">RANKING</span>
                </a>
            </div>
            <hr class="dropdown-divider" />
        </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="error-alert">
        {{ errorMessage }}
        <button class="close-button" @click="errorMessage = ''">×</button>
    </div>

    <!-- Info Message -->
    <div v-if="infoMessage" class="info-alert">
        {{ infoMessage }}
        <button class="close-button" @click="infoMessage = ''">×</button>
    </div>

    <!-- Modal para unirse a partida -->
    <JoinMatchModal
        :visible="showJoinModal"
        @close="showJoinModal = false"
        @join="handleJoinMatch"
    />
</template>

<script setup>
/* -- IMPORTS -- */
// Importaciones necesarias: Vue (ref, computed), Vue Router (useRouter, useRoute),
// Stores (Pinia: useGameStore, authStore), Axios para llamadas API, y el componente del modal.
import { ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useGameStore } from "../../store/game";
import { authStore } from "../../store/auth";
import axios from "axios";
import JoinMatchModal from "../privateMatch/JoinMatchModal.vue";

/* -- VARIABLES -- */
// Instancias de router y route para navegación y acceso a la ruta actual.
const router = useRouter();
const route = useRoute();
// Instancia del store del juego para manejar su estado.
const gameStore = useGameStore();
// Refs para manejar mensajes de error/información y la visibilidad del modal.
const errorMessage = ref("");
const infoMessage = ref("");
const showJoinModal = ref(false);

/* -- PROPS -- */
// Define una propiedad 'isMenuBlocked' que el componente padre puede pasar
// para forzar el bloqueo del menú externamente (ej: si otra acción está en curso).
const props = defineProps({
    isMenuBlocked: {
        type: Boolean,
        default: false, // Por defecto, el menú no está bloqueado por el padre.
    },
});

/* -- COMPUTED PROPERTIES -- */
// Rutas específicas donde la navegación desde el menú debe estar deshabilitada (ej: dentro de una partida activa).
const blockedRoutes = ["/game"]; // Solo bloquea si la ruta empieza con /game

// Propiedad computada que determina si el menú debe estar bloqueado.
// Devuelve true si la ruta actual empieza con alguna de las 'blockedRoutes'
// O si la prop 'isMenuBlocked' es true. Se usa para deshabilitar los items del menú.
const isCurrentRouteBlocked = computed(() => {
    return (
        blockedRoutes.some((blockedRoute) =>
            route.path.startsWith(blockedRoute)
        ) || props.isMenuBlocked
    );
});

/* -- EMITS -- */
// Define un evento 'navigation' que se emitirá cuando se haga clic en un ítem del menú
// (y la navegación no esté bloqueada), usualmente para que el componente padre cierre el menú.
const emit = defineEmits(["navigation"]);

/* -- FUNCTIONS -- */

/**
 * Verifica si el menú está bloqueado. Si lo está, muestra un mensaje.
 * Si no lo está, emite el evento 'navigation'.
 * @returns {boolean} - True si la navegación puede proceder, false si está bloqueada.
 */
const emitNavigation = () => {
    if (isCurrentRouteBlocked.value) {
        showNavigationBlockedMessage(); // Muestra mensaje de bloqueo
        return false; // Indica que la navegación NO debe proceder
    }
    emit("navigation"); // Emite el evento para notificar al padre (ej: cerrar menú)
    return true; // Indica que la navegación PUEDE proceder
};

/**
 * Muestra un mensaje temporal informando que la navegación está bloqueada.
 */
const showNavigationBlockedMessage = () => {
    // Mensaje actualizado para ser más preciso
    infoMessage.value = "No puedes navegar mientras estás en una partida";
    setTimeout(() => {
        infoMessage.value = ""; // Oculta el mensaje después de 3 segundos
    }, 3000);
};

/**
 * Verifica los requisitos del usuario en el backend antes de navegar a una partida.
 * Realiza la navegación usando router.push() si los requisitos se cumplen.
 * @param {string} gameType - Tipo de juego ('public', 'private').
 * @param {string|null} gameCode - Código de la partida (para unirse a privada, null para crear).
 */
const checkAndNavigate = async (gameType, gameCode = null) => {
    // La comprobación de bloqueo (isCurrentRouteBlocked) se hace ANTES de llamar a esta función.
    // Se añade una comprobación redundante por seguridad, aunque no debería ser necesaria si los handlers llaman a emitNavigation() primero.
    if (isCurrentRouteBlocked.value) {
        showNavigationBlockedMessage();
        return;
    }

    try {
        // Llamada API para verificar si el usuario cumple requisitos para el juego.
        const response = await axios.post(
            "/api/games/check-user-requirements",
            {
                gameType: gameType,
                gameCode: gameCode,
                user: authStore().user ?? null, // Envía datos del usuario si está logueado.
            }
        );

        // Si el backend responde con éxito...
        if (response.data.status === "success") {
            console.log("OK: User ready to play.");
            // Navega a la ruta correspondiente según el tipo y código.
            if (gameType === "public") {
                router.push(`/game/public/null`); // Ruta para partida pública.
            } else if (gameType === "private" && gameCode) {
                router.push(`/game/private/${gameCode}`); // Ruta para unirse a privada con código.
            } else if (gameType === "private" && !gameCode) {
                router.push(`/game/private/null`); // Ruta para crear partida privada.
            }
        } else {
            // Si el backend indica un problema (ej: usuario saliendo de otra partida, error).
            if (
                response.data.message ==
                "Your user is leaving the game. Wait a few seconds."
            ) {
                // Mensaje informativo específico.
                console.log("Failed without error: ", response.data.message);
                infoMessage.value = response.data.message;
            } else {
                // Otro tipo de error devuelto por el backend.
                console.log("Failed with error:", response.data.message);
                errorMessage.value = response.data.message;
            }
        }
    } catch (error) {
        // Error durante la llamada Axios (ej: problema de red).
        errorMessage.value = "Error al verificar requisitos del juego";
    }
};

// --- Handlers para navegación directa (usados en @click de los enlaces 'a') ---

/**
 * Maneja el clic en 'INICIO'. Navega a la ruta raíz '/' si el menú no está bloqueado.
 */
const handleInicio = () => {
    if (emitNavigation()) {
        // Primero verifica bloqueo y emite evento.
        router.push("/"); // Si no está bloqueado, navega.
    }
};

/**
 * Maneja el clic en 'MI PERFIL'. Navega a '/profile' si el menú no está bloqueado.
 */
const handleProfile = () => {
    if (emitNavigation()) {
        // Verifica bloqueo y emite evento.
        router.push("/profile"); // Navega a la ruta del perfil.
    }
};

/**
 * Maneja el clic en 'RANKING'. Navega a '/rankings' si el menú no está bloqueado.
 */
const handleRanking = () => {
    if (emitNavigation()) {
        // Verifica bloqueo y emite evento.
        router.push("/rankings"); // Navega a la ruta de rankings.
    }
};

// --- Handlers para acciones que pueden implicar navegación ---

/**
 * Maneja el clic en 'Partida pública'. Si no está bloqueado, resetea el store
 * del juego y llama a checkAndNavigate para iniciar el proceso de partida pública.
 */
const handlePublicGame = async () => {
    if (emitNavigation()) {
        // Verifica bloqueo y emite evento.
        gameStore.resetGame(); // Limpia datos de partida anterior en el store.
        await checkAndNavigate("public"); // Inicia flujo para partida pública.
    }
};

/**
 * Maneja el clic en 'Crear partida'. Si no está bloqueado, resetea el store
 * y llama a checkAndNavigate para iniciar el proceso de creación de partida privada.
 */
const handleCreateGame = async () => {
    if (emitNavigation()) {
        // Verifica bloqueo y emite evento.
        gameStore.resetGame(); // Limpia datos de partida anterior.
        await checkAndNavigate("private", null); // Inicia flujo para crear privada (sin código).
    }
};

/**
 * Maneja el clic en 'Unirse a partida'. Si no está bloqueado, verifica requisitos básicos
 * y, si se cumplen, muestra el modal para que el usuario introduzca el código.
 */
const showJoinMatchModal = async () => {
    if (emitNavigation()) {
        // Verifica bloqueo y emite evento.
        try {
            // Verificación inicial en backend antes de mostrar el modal.
            const response = await axios.post(
                "/api/games/check-user-requirements",
                {
                    gameType: "private", // Tipo genérico para esta verificación.
                    gameCode: null,
                    user: authStore().user ?? null,
                }
            );

            if (response.data.status === "success") {
                console.log("OK: User can potentially join a game.");
                showJoinModal.value = true; // Muestra el modal si pasa la verificación.
            } else {
                // Manejo de mensajes específicos o errores generales del backend.
                if (
                    response.data.message ==
                    "Your user is leaving the game. Wait a few seconds."
                ) {
                    console.log(
                        "Failed without error: ",
                        response.data.message
                    );
                    infoMessage.value = response.data.message;
                } else {
                    console.log("FAILED:", response.data.message);
                    errorMessage.value = response.data.message;
                }
            }
        } catch (error) {
            errorMessage.value = "Error al verificar requisitos del juego";
        }
    }
};

/**
 * Se ejecuta cuando el modal 'JoinMatchModal' emite el evento 'join' (con el código).
 * Oculta el modal, configura el store y llama a checkAndNavigate con el código para unirse.
 * @param {string} code - El código de partida introducido por el usuario en el modal.
 */
const handleJoinMatch = async (code) => {
    // No se necesita emitNavigation aquí porque ya se verificó al mostrar el modal.
    showJoinModal.value = false; // Oculta el modal.
    gameStore.setGameMode("join"); // Configura el modo 'unirse' en el store.
    gameStore.setMatchCode(code); // Guarda el código en el store.
    await checkAndNavigate("private", code); // Inicia flujo para unirse con el código.
};

/**
 * Maneja el clic en 'Observar partida'. Navega a '/view-games' si el menú no está bloqueado.
 */
const handleViewGame = () => {
    if (emitNavigation()) {
        // Verifica bloqueo y emite evento.
        router.push("/view-games"); // Navega a la ruta para observar partidas.
    }
};
</script>

<style scoped>
.tituloBarraLateral {
    padding-top: 2rem;
    padding-left: 0.25rem;
    padding-bottom: 0.5rem;
}

.subtituloBarraLateral {
    padding: 0.5rem 0.25rem;
}

.contenidoSubtituloBarraLateral {
    padding: 0.25rem 0; /* Aumentado para más espacio entre items */
}

.dropdown-divider {
    opacity: 0.25; /* Más delgado */
    margin: 0.4rem 1rem; /* Aumentado margen */
}

#lateralMenu > .dropdown-divider:first-child {
    opacity: 0.75;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: var(--white-color);
    text-decoration: none;
    font-size: 1.4rem; /* Aumentado de 1.25rem a 1.4rem */
    white-space: nowrap;
    transition: all 0.3s ease;
    padding: 0.5rem;
    cursor: pointer; /* Explicitly add pointer cursor */
}

.menu-item i {
    font-size: 1.5rem; /* Aumentado de 1.3rem a 1.5rem */
    min-width: 24px;
    text-align: center;
    transition: all 0.3s ease;
}

/* Estilo específico para los títulos */
h2.menu-item {
    margin: 0;
    font-size: 1.8rem; /* Tamaño más grande para los títulos */
}

.menu-item:hover {
    color: var(--primary-color);
}

/* Estilos para el menú cerrado */
:deep(#lateralBar.closed) .menu-item {
    justify-content: center;
    padding: 0.5rem 0;
}

:deep(#lateralBar.closed) .menu-text {
    display: none;
}

:deep(#lateralBar.closed) .menu-item i {
    margin: 0;
    font-size: 1.6rem;
}

:deep(#lateralBar.closed) h2.menu-item {
    font-size: 1.6rem;
}

:deep(#lateralBar.closed) .dropdown-divider {
    margin: 0.4rem 0.5rem;
}

:deep(#lateralBar.closed) .tituloBarraLateral {
    padding-left: 0;
    text-align: center;
}

:deep(#lateralBar.closed) .subtituloBarraLateral {
    padding: 0.5rem 0;
}

:deep(#lateralBar.closed) .contenidoSubtituloBarraLateral {
    padding: 0.25rem 0;
    text-align: center;
}

/* Estilos mejorados para tooltips */
:deep(#lateralBar.closed) .menu-item {
    position: relative;
}

:deep(#lateralBar.closed) .menu-item:hover::after {
    content: attr(title);
    position: absolute;
    left: calc(100% + 15px);
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(
        145deg,
        var(--primary-color),
        var(--neutral-color)
    );
    color: var(--white-color);
    padding: 0.8rem 1.2rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 0.3px;
    white-space: nowrap;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3),
        inset 0 1px 1px rgba(255, 255, 255, 0.1);
    z-index: 1000;
    opacity: 0;
    animation: tooltipFade 0.3s ease forwards;
}

:deep(#lateralBar.closed) .menu-item:hover::before {
    content: "";
    position: absolute;
    left: calc(100% + 7px);
    top: 50%;
    transform: translateY(-50%);
    border: 8px solid transparent;
    border-right-color: var(--primary-color);
    filter: drop-shadow(-2px 0 2px rgba(0, 0, 0, 0.2));
    z-index: 1001;
    opacity: 0;
    animation: tooltipFade 0.3s ease forwards;
}

@keyframes tooltipFade {
    from {
        opacity: 0;
        transform: translateY(-50%) translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(-50%) translateX(0);
    }
}

/* Añadir estilos para la alerta de error */
.error-alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    padding: 1rem 2rem;
    border-radius: 8px;
    background-color: var(--secondary-color);
    color: white;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.close-button {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0 0.5rem;
}

.close-button:hover {
    opacity: 0.8;
}

.info-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 2rem;
    border-radius: 8px;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    background-color: var(--neutral-color-2);
    color: white;
    border: 1px solid var(--white-color);
}

/* Estilos para los enlaces deshabilitados */
.disabled-link {
    opacity: 0.5;
    cursor: not-allowed !important;
    pointer-events: none;
}

/* Nuevo estilo para ítems deshabilitados */
.disabled-menu-item {
    opacity: 0.5;
    cursor: not-allowed !important;
    pointer-events: none;
    color: var(--neutral-color-3) !important;
    transition: all 0.3s ease;
}

.disabled-menu-item i {
    color: var(--neutral-color-3) !important;
}

/* Eliminar el estilo de oscurecimiento para el contenedor completo */
.menu-blocked {
    position: relative;
}

/* Eliminar el pseudo-elemento que oscurece todo el contenedor */
.menu-blocked::after {
    content: none;
}
</style>
