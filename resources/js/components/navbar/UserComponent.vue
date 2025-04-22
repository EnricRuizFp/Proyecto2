<template>
    <!-- Sin sesión iniciada -->
    <div
        v-if="!authStore().user?.username"
        class="nav-item dropdown"
        :class="variant"
        id="userComponent"
    >
        <div id="userContent">
            <a
                id="userProfile"
                class="nav-link p1"
                href="#"
                role="button"
                :data-bs-toggle="
                    canOpenMenu && !isMenuBlocked ? 'dropdown' : ''
                "
                aria-expanded="false"
            >
                <div class="profile-content">
                    <div class="left-side">
                        <div class="userImageContainer">
                            <img
                                src="../../../../public/images/icons/user-icon-dark.svg"
                                alt="Avatar por defecto"
                                class="user-avatar"
                            />
                        </div>
                        <div class="usernameContainer">
                            <div class="username-wrapper">
                                <span class="username">{{ $t("login_message") }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <div
                id="userProfileMenu"
                class="dropdown-menu dropdown-menu-start app-background-primary white-border"
            >
                <div>
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/login"
                        :class="{ 'disabled-menu-item': isMenuBlocked }"
                        @click.prevent="handleNavigation('/login')"
                        >{{ $t("login") }}</router-link
                    >
                </div>
                <div>
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/register"
                        :class="{ 'disabled-menu-item': isMenuBlocked }"
                        @click.prevent="handleNavigation('/register')"
                        >{{$t("register")}}</router-link
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- Sesión ya iniciada -->
    <div v-if="authStore().user?.username" id="userComponent" :class="variant">
        <div id="userContent">
            <a
                id="userProfile"
                class="nav-link p1 white-color"
                href="#"
                role="button"
                :data-bs-toggle="
                    canOpenMenu && !isMenuBlocked ? 'dropdown' : ''
                "
                aria-expanded="false"
            >
                <div class="profile-content">
                    <div class="left-side">
                        <div class="userImageContainer">
                            <img
                                :src="getAvatarUrl()"
                                :alt="authStore().user?.name"
                                @error="
                                    (e) =>
                                        (e.target.src =
                                            '/images/icons/user-icon-dark.svg')
                                "
                                class="user-avatar"
                            />
                        </div>
                        <div class="usernameContainer">
                            <div class="username-wrapper">
                                <span class="username">{{
                                    authStore().user?.username
                                }}</span>
                                <div class="pointsContainer">
                                    <span class="points">
                                        {{ userPoints }}
                                        <img
                                            src="../../../../public/images/icons/trophy-icon-dark.svg"
                                            alt="Icono de trofeo"
                                        />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <div
                id="userProfileMenu"
                class="dropdown-menu dropdown-menu-start neutral-background white-border"
            >
                <!-- Mostrar enlace de Admin solo si el usuario tiene rol de admin -->
                <div v-if="isAdmin">
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/admin"
                        :class="{ 'disabled-menu-item': isMenuBlocked }"
                        @click.prevent="handleNavigation('/admin')"
                        >Admin</router-link
                    >
                </div>
                <div>
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/profile"
                        :class="{ 'disabled-menu-item': isMenuBlocked }"
                        @click.prevent="handleNavigation('/profile')"
                        >{{ $t("my_profile") }}</router-link
                    >
                </div>
                <div><hr class="dropdown-divider white-background" /></div>
                <div>
                    <a
                        class="dropdown-item white-color neutral-hover"
                        href="javascript:void(0)"
                        @click="handleLogout"
                        :class="{ 'disabled-menu-item': isMenuBlocked }"
                        >{{ $t("logout") }}</a
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTACIONES -- */
import { onMounted, ref, computed, watch, inject } from "vue";
import { useRouter } from "vue-router";
import useAuth from "@/composables/auth";
import useUsers from "@/composables/users";
import { authStore } from "../../store/auth";
import useRankings from "@/composables/rankings.js";

/* -- VARIABLES -- */
const router = useRouter();
const { processing, logout } = useAuth();
const { getRanking } = useRankings();
const { getUser } = useUsers();
const userPoints = ref(0);
const userAvatar = ref(null);
// Propiedad inyectada para saber si la interacción con el menú está bloqueada (ej: durante una partida)
const isMenuBlocked = inject("menuBlocked", ref(false));

const props = defineProps({
    variant: {
        type: String,
        default: "sidebar", // 'sidebar' o 'profile'
    },
    isLateralBarClosed: {
        type: Boolean,
        default: false,
    },
});

// Comprueba si el usuario actual tiene rol de administrador
const isAdmin = computed(() => {
    const user = authStore().user;
    if (!user || !user.roles) return false;
    return user.roles.some((role) => role.name.toLowerCase().includes("admin"));
});

// Determina si el menú desplegable se puede abrir según la variante y el estado
const canOpenMenu = computed(() => {
    // En la variante 'sidebar', el menú se abre solo si la barra no está cerrada
    // En la variante 'profile', siempre se puede abrir (a menos que esté bloqueado)
    return props.variant === "sidebar" ? !props.isLateralBarClosed : true;
});

/* -- FUNCIONES -- */
// Obtiene y actualiza la URL del avatar del usuario
const updateUserData = async () => {
    userAvatar.value = null; // Resetea el avatar antes de buscarlo
    if (authStore().user?.id) {
        try {
            const userData = await getUser(authStore().user.id);
            if (userData?.avatar) {
                userAvatar.value = userData.avatar;
            }
        } catch (error) {
            console.error("Error al obtener datos del usuario:", error);
            userAvatar.value = null; // Usa null como fallback si hay error
        }
    }
};

// Obtiene la URL del avatar del usuario, proporcionando una por defecto si no hay
const getAvatarUrl = () => {
    if (!authStore().user?.id) return "/images/icons/user-icon-dark.svg";
    // Usa el avatar obtenido o el por defecto si la obtención falló o no hay avatar
    return userAvatar.value || "/images/icons/user-icon-dark.svg";
};

// Maneja la navegación, previniéndola si el menú está bloqueado
const handleNavigation = (route) => {
    if (isMenuBlocked.value) {
        showNavigationBlockedMessage(); // Muestra un mensaje en lugar de navegar
        return;
    }
    router.push(route); // Procede con la navegación
};

// Muestra un mensaje indicando que la navegación está bloqueada
const showNavigationBlockedMessage = () => {
    // Despacha un evento personalizado que un listener (ej: en App.vue) puede capturar para mostrar una notificación
    document.dispatchEvent(
        new CustomEvent("show-menu-blocked-message", {
            detail: "No puedes navegar mientras estás en una partida o vista de juego",
        })
    );
};

// Maneja el cierre de sesión, previniéndolo si el menú está bloqueado
const handleLogout = () => {
    if (isMenuBlocked.value) {
        showNavigationBlockedMessage(); // Muestra mensaje en lugar de cerrar sesión
        return;
    }
    logout(); // Procede con el cierre de sesión
};

// Observa cambios en el ID del usuario logueado
watch(
    () => authStore().user?.id,
    async (newUserId) => {
        if (newUserId) {
            // Si un usuario inicia sesión, actualiza sus datos y puntos
            await updateUserData();
            const rankingData = await getRanking(newUserId);
            userPoints.value = rankingData?.points ?? 0; // Usa 0 si los puntos son null/undefined
        } else {
            // Si el usuario cierra sesión, resetea avatar y puntos
            userAvatar.value = null;
            userPoints.value = 0;
        }
    },
    { immediate: true } // Ejecuta el watcher inmediatamente al montar el componente
);

// Al montar el componente, obtiene datos iniciales si ya hay un usuario logueado
onMounted(async () => {
    if (authStore().user?.id) {
        // Obtiene puntos
        const rankingData = await getRanking(authStore().user?.id);
        userPoints.value = rankingData?.points ?? 0;

        // Obtiene datos del usuario (como el avatar)
        await updateUserData();
    }
});
</script>

<style scoped>
#userComponent {
    width: 100%;
    display: flex;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    justify-content: center;
    align-items: center;
}

#userComponent:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

#userContent {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
}

#userProfile {
    width: 100%;
    height: 100%;
}

#userContent {
    width: 100%;
    min-width: 300px;
}

.sidebar {
    height: 90px;
    min-height: unset;
    flex-shrink: 0;
    border: none;
    border-radius: 0;
    margin-bottom: 0.5rem;
    padding: 0 1rem;
    transform: none !important;
    position: relative;
}

.sidebar:hover {
    background-color: transparent !important;
    transform: none !important;
}

.sidebar #userContent {
    min-width: unset;
}

.sidebar .profile-content {
    padding: 0;
    max-width: unset;
    justify-content: flex-start;
}

.sidebar .userImageContainer {
    width: 65px;
    height: 65px;
    margin: 0 0.75rem 0 0;
}

.sidebar .username {
    font-size: 1.2rem;
}

.sidebar .points {
    font-size: 1.1rem;
}

.sidebar .points img {
    width: 24px;
    height: 24px;
    margin-left: 0.3rem;
}

.sidebar .left-side {
    justify-content: flex-start;
}

.sidebar #userProfileMenu {
    position: absolute;
    top: 85px;
    left: 40px;
    transform: none;
    min-width: 160px;
    width: auto;
    background-color: var(--background-secondary) !important;
    border: 1px solid var(--white-color);
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 99999;
    padding: 0.25rem 0;
}

.sidebar #userProfileMenu .dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    white-space: nowrap;
}

.sidebar #userProfileMenu .dropdown-divider {
    margin: 0.25rem 0;
    opacity: 0.2;
    border-top: 1px solid var(--white-color);
}

.profile {
    min-height: 150px;
    border: none;
    border-radius: 10px;
    padding: 1rem 1rem 0 1rem;
    background-color: rgba(255, 255, 255, 0.05);
}

.profile:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.profile .userImageContainer {
    width: 90px;
    height: 90px;
}

.profile .points {
    margin-right: 0.5rem;
}

.profile-content {
    height: 100%;
    display: flex;
    align-items: center;
    width: 100%;
    justify-content: center;
    max-width: 800px;
    margin: 0 auto;
    padding: 0.5rem;
}

.left-side {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.userImageContainer {
    width: 40px;
    height: 40px;
    margin: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    overflow: hidden;
}

.usernameContainer {
    flex: 1;
    padding: 0 1rem;
    display: flex;
    align-items: flex-start;
}

.pointsContainer {
    display: flex;
    align-items: center;
    min-width: fit-content;
    padding: 0;
    justify-content: flex-start;
}

.points {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
}

.points img {
    margin-left: 0.5rem;
    height: 24px;
    width: 24px;
}

.user-avatar {
    object-fit: cover;
    border-radius: 50%;
}

.profile .user-avatar {
    width: 100%;
    height: 100%;
}

.sidebar .user-avatar {
    width: 50px;
    height: 50px;
}

.username-wrapper {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
    width: 100%;
}

@media (max-width: 768px) {
    #userComponent {
        padding: 0.5rem;
    }

    .profile .profile-content {
        flex-direction: row;
        gap: 0;
    }

    .profile .left-side {
        flex-direction: row;
        justify-content: flex-start;
    }

    .profile .usernameContainer {
        text-align: left;
        padding: 0.5rem;
    }

    .profile .pointsContainer {
        padding: 0;
        justify-content: flex-start;
    }

    .profile .username-wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

:deep(#lateralBar.closed) .sidebar {
    height: auto;
    padding: 0;
    width: 100%;
}

:deep(#lateralBar.closed) .sidebar #userContent {
    min-width: 0;
}

:deep(#lateralBar.closed) .sidebar .profile-content {
    justify-content: center;
}

:deep(#lateralBar.closed) .sidebar .left-side {
    width: auto;
    justify-content: center;
}

:deep(#lateralBar.closed) .sidebar .userImageContainer {
    width: 45px;
    height: 45px;
    margin: 0;
    padding: 0;
    border: 2px solid var(--white-color);
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
}

:deep(#lateralBar.closed) .sidebar .user-avatar {
    width: 41px;
    height: 41px;
    object-fit: cover;
}

:deep(#lateralBar.closed) .sidebar .usernameContainer,
:deep(#lateralBar.closed) .sidebar .pointsContainer,
:deep(#lateralBar.closed) .sidebar .username-wrapper {
    display: none !important;
}

:deep(#lateralBar.closed) .sidebar {
    height: 80px;
    width: 100%;
    padding: 0;
    display: flex;
    align-items: center;
}

:deep(#lateralBar.closed) .sidebar #userContent {
    min-width: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    padding: 10px 0;
}

:deep(#lateralBar.closed) .sidebar .profile-content {
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    padding: 0;
}

:deep(#lateralBar.closed) .sidebar .left-side {
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    padding: 0;
}

:deep(#lateralBar.closed) .sidebar .userImageContainer {
    width: 50px;
    height: 50px;
    margin: 0;
    padding: 0;
    border: 2px solid var(--white-color);
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
}

:deep(#lateralBar.closed) .sidebar .user-avatar {
    width: 46px;
    height: 46px;
    object-fit: cover;
    border-radius: 50%;
}

:deep(#lateralBar.closed) .sidebar:not(.logged-in) .userImageContainer {
    width: 50px;
    height: 50px;
    margin: 0;
    padding: 0;
    border: 2px solid var(--white-color);
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
}

:deep(#lateralBar.closed) .sidebar:not(.logged-in) .user-avatar {
    width: 46px;
    height: 46px;
    object-fit: cover;
    border-radius: 50%;
}

:deep(#lateralBar.closed) .sidebar:not(.logged-in) .usernameContainer {
    display: none !important;
}

#userProfileMenu {
    background-color: var(--background-secondary) !important;
    z-index: 99999;
    position: absolute;
    top: 100%;
    left: 0;
    width: auto;
    min-width: 180px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.dropdown-menu {
    border: 1px solid var(--white-color);
    border-radius: 8px;
    position: relative;
    margin-top: 0.5rem;
    padding: 0.5rem 0;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.dropdown-divider {
    width: 100% !important;
    margin: 0.5rem 0;
    opacity: 0.2;
}

:deep(#lateralBar.closed) .sidebar #userProfileMenu {
    left: 80px;
    transform: none;
}

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

.player-side :deep(.profile) {
    min-height: auto !important;
    width: 100% !important;
    padding: 1rem !important;
    border: 2px solid var(--primary-color) !important;
}

.player-side :deep(.profile-content) {
    padding: 0.25rem !important;
}

.player-side :deep(.userImageContainer) {
    min-width: 50px !important;
    width: 50px !important;
    height: 50px !important;
    margin: 0 0.75rem 0 0 !important;
}

.player-side :deep(.usernameContainer) {
    overflow: hidden !important;
    flex: 1 !important;
}

.player-side :deep(.username-wrapper) {
    width: 100% !important;
}

.player-side :deep(.username) {
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

@media (max-width: 800px) and (min-width: 601px) {
    .player-side :deep(.profile) {
        padding: 0.5rem !important;
    }

    .player-side :deep(.userImageContainer) {
        width: 40px !important;
        height: 40px !important;
        margin: 0 0.5rem 0 0 !important;
    }

    .player-side :deep(.username) {
        font-size: 0.9rem !important;
        max-width: 110px !important;
    }

    .player-side :deep(.points) {
        font-size: 0.8rem !important;
    }

    .player-side :deep(.points img) {
        width: 16px !important;
        height: 16px !important;
        margin-left: 0.2rem !important;
    }
}
</style>
