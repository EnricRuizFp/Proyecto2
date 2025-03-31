<template>
    <!-- No session -->
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
                :data-bs-toggle="canOpenMenu ? 'dropdown' : ''"
                aria-expanded="false"
            >
                <div class="profile-content">
                    <div class="left-side">
                        <div class="userImageContainer">
                            <img
                                src="../../../../public/images/icons/user-icon-dark.svg"
                                alt="Default avatar"
                                class="user-avatar"
                            />
                        </div>
                        <div class="usernameContainer">
                            <div class="username-wrapper">
                                <span class="username">Login now</span>
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
                        >Login</router-link
                    >
                </div>
                <div>
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/register"
                        >Register</router-link
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- Session already started -->
    <div v-if="authStore().user?.username" id="userComponent" :class="variant">
        <div id="userContent">
            <a
                id="userProfile"
                class="nav-link p1 white-color"
                href="#"
                role="button"
                :data-bs-toggle="canOpenMenu ? 'dropdown' : ''"
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
                                            alt="Trophy icon"
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
                <!-- Solo mostrar el enlace Admin si el usuario tiene rol de admin -->
                <div v-if="isAdmin">
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/admin"
                        >Admin</router-link
                    >
                </div>
                <div>
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/profile"
                        >Mi Perfil</router-link
                    >
                </div>
                <div><hr class="dropdown-divider white-background" /></div>
                <div>
                    <a
                        class="dropdown-item white-color neutral-hover"
                        href="javascript:void(0)"
                        @click="logout"
                        >Logout</a
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTS -- */
import { onMounted, ref, computed, watch } from "vue";
import useAuth from "@/composables/auth";
import useUsers from "@/composables/users";
import { authStore } from "../../store/auth";
import useRankings from "@/composables/rankings.js";

/* -- VARIABLES -- */
const { processing, logout } = useAuth();
const { getRanking } = useRankings();
const { getUser } = useUsers();
const userPoints = ref(0); // Initialize with 0 instead of null
const userAvatar = ref(null);

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

// Función para comprobar si el usuario es admin
const isAdmin = computed(() => {
    const user = authStore().user;
    if (!user || !user.roles) return false;
    return user.roles.some((role) => role.name.toLowerCase().includes("admin"));
});

// Computed para controlar cuándo se puede abrir el menú
const canOpenMenu = computed(() => {
    return props.variant === "sidebar" ? !props.isLateralBarClosed : true;
});

/* -- FUNCTIONS -- */
const updateUserData = async () => {
    userAvatar.value = null; // Resetear el avatar antes de actualizar
    if (authStore().user?.id) {
        try {
            const userData = await getUser(authStore().user.id);
            if (userData?.avatar) {
                userAvatar.value = userData.avatar;
            }
        } catch (error) {
            console.error("Error fetching user data:", error);
            userAvatar.value = null;
        }
    }
};

const getAvatarUrl = () => {
    if (!authStore().user?.id) return "/images/icons/user-icon-dark.svg";
    return userAvatar.value || "/images/icons/user-icon-dark.svg";
};

// Observador para detectar cambios en el usuario
watch(
    () => authStore().user?.id,
    async (newUserId) => {
        if (newUserId) {
            await updateUserData();
            // Actualizar también los puntos
            const rankingData = await getRanking(newUserId);
            userPoints.value = rankingData?.points ?? 0; // Use nullish coalescing
        } else {
            // Resetear datos cuando no hay usuario
            userAvatar.value = null;
            userPoints.value = 0; // Reset to 0 instead of null
        }
    },
    { immediate: true }
);

onMounted(async () => {
    if (authStore().user?.id) {
        // Obtener puntos
        const rankingData = await getRanking(authStore().user?.id);
        userPoints.value = rankingData?.points ?? 0; // Use nullish coalescing

        // Actualizar datos del usuario
        await updateUserData();
    }
});
</script>

<style scoped>
/* Estilos base compartidos */
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
    max-width: 800px; /* Limitar el ancho máximo */
    margin: 0 auto; /* Centrar horizontalmente */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center; /* Centrar contenido interno */
    position: relative; /* Añadido para establecer contexto de posicionamiento */
}

#userProfile {
    width: 100%;
    height: 100%;
}

#userContent {
    width: 100%;
    min-width: 300px;
}

/* Variante sidebar */
.sidebar {
    height: 90px; /* Aumentado de 80px */
    min-height: unset; /* Eliminar altura mínima */
    flex-shrink: 0; /* Evitar que se encoja */
    border: none;
    border-radius: 0;
    margin-bottom: 0.5rem;
    padding: 0 1rem;
    transform: none !important; /* Prevenir cualquier transformación */
    position: relative; /* Añadido para posicionamiento del menú */
}

.sidebar:hover {
    background-color: transparent !important;
    transform: none !important;
}

.sidebar #userContent {
    min-width: unset; /* Eliminar el ancho mínimo */
}

.sidebar .profile-content {
    padding: 0;
    max-width: unset;
    justify-content: flex-start; /* Alinear contenido a la izquierda */
}

.sidebar .userImageContainer {
    width: 65px;
    height: 65px;
    margin: 0 0.75rem 0 0; /* Eliminado margen izquierdo */
}

.sidebar .username {
    font-size: 1.2rem; /* Aumentado de 1.1rem */
}

.sidebar .points {
    font-size: 1.1rem; /* Aumentado de 1rem */
}

.sidebar .points img {
    width: 24px; /* Aumentado de 20px */
    height: 24px; /* Aumentado de 20px */
    margin-left: 0.3rem; /* Ajustado el margen */
}

.sidebar .left-side {
    justify-content: flex-start; /* Alinear a la izquierda */
}

/* Estilos para el menú desplegable - versión sidebar */
.sidebar #userProfileMenu {
    position: absolute;
    top: 85px;
    left: 40px; /* Alineado con el padding del contenedor padre */
    transform: none;
    min-width: 160px; /* Reducido de 200px */
    width: auto;
    background-color: var(--background-secondary) !important;
    border: 1px solid var(--white-color);
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 99999;
    padding: 0.25rem 0; /* Reducido de 0.5rem */
}

.sidebar #userProfileMenu .dropdown-item {
    padding: 0.5rem 1rem; /* Reducido de 0.75rem 1.5rem */
    font-size: 0.9rem; /* Reducido de 1rem */
    white-space: nowrap;
}

.sidebar #userProfileMenu .dropdown-divider {
    margin: 0.25rem 0; /* Reducido de 0.5rem */
    opacity: 0.2;
    border-top: 1px solid var(--white-color);
}

/* Variante profile */
.profile {
    min-height: 150px;
    border: none;
    border-radius: 10px;
    padding: 1rem 1rem 0 1rem;
    background-color: rgba(
        255,
        255,
        255,
        0.05
    ); /* Añadido para coincidir con rankingContainer */
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
    align-items: flex-start; /* Alinear al inicio del contenedor */
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

/* Estilos base para user-avatar */
.user-avatar {
    object-fit: cover;
    border-radius: 50%;
}

/* Estilos específicos para la versión profile */
.profile .user-avatar {
    width: 100%;
    height: 100%;
}

/* Estilos específicos para la versión sidebar */
.sidebar .user-avatar {
    width: 50px;
    height: 50px;
}

.username-wrapper {
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* Alinear al inicio */
    gap: 0.5rem;
    width: 100%;
}

@media (max-width: 768px) {
    #userComponent {
        padding: 0.5rem;
    }

    .profile .profile-content {
        flex-direction: row; /* Cambiado de column a row */
        gap: 0;
    }

    .profile .left-side {
        flex-direction: row;
        justify-content: flex-start; /* Cambiado de center a flex-start */
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

/* Limpiar estilos anteriores y usar solo estos para el menú cerrado */
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

/* Eliminar todos los demás estilos relacionados con #lateralBar.closed */

/* Estilos específicos para el menú cerrado */
:deep(#lateralBar.closed) .sidebar {
    height: 80px; /* Altura fija para mantener simetría */
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
    width: 50px; /* Reducido para mantener proporción */
    height: 50px; /* Igual que el width */
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    padding: 0;
}

:deep(#lateralBar.closed) .sidebar .left-side {
    width: 50px; /* Igual que el contenedor padre */
    height: 50px; /* Igual que el width */
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    padding: 0;
}

:deep(#lateralBar.closed) .sidebar .userImageContainer {
    width: 50px; /* Mismo tamaño que los contenedores */
    height: 50px; /* Mismo tamaño que el width */
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
    width: 46px; /* 50px - 4px (border) */
    height: 46px; /* Igual que el width */
    object-fit: cover;
    border-radius: 50%;
}

/* Aplicar los mismos estilos para usuario no logueado */
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

/* Estilos para el menú desplegable */
#userProfileMenu {
    background-color: var(--background-secondary) !important;
    z-index: 99999; /* Aumentado para asegurar que esté por encima */
    position: absolute;
    top: 100%;
    left: 0;
    width: auto; /* Cambiado de 100% a auto */
    min-width: 180px; /* Reducido de 200px */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Añadido sombra */
}

.dropdown-menu {
    border: 1px solid var(--white-color);
    border-radius: 8px;
    position: relative;
    margin-top: 0.5rem;
    padding: 0.5rem 0; /* Añadido padding vertical */
}

.dropdown-item {
    padding: 0.5rem 1rem; /* Ajustado el padding */
    font-size: 0.9rem; /* Reducido el tamaño de fuente */
}

/* Estilo para el separador del menú */
.dropdown-divider {
    width: 100% !important;
    margin: 0.5rem 0;
    opacity: 0.2;
}

/* Estilos específicos para el menú cerrado */
:deep(#lateralBar.closed) .sidebar #userProfileMenu {
    left: 80px; /* Ancho del menú lateral cerrado */
    transform: none;
}
</style>
