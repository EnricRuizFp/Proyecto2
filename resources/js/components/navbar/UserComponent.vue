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
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <img
                    src="../../../../public/images/icons/user-icon-dark.svg"
                    alt="Default avatar"
                />
                <!-- Not logged user image -->
                Login now
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
                data-bs-toggle="dropdown"
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
                <div>
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
                <div>
                    <router-link
                        to="/admin/posts"
                        class="dropdown-item white-color neutral-hover"
                        >Post</router-link
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
import { onMounted, ref } from "vue";
import useAuth from "@/composables/auth";
import useUsers from "@/composables/users";
import { authStore } from "../../store/auth";
import useRankings from "@/composables/rankings.js";

/* -- VARIABLES -- */
const { processing, logout } = useAuth();
const { getRanking } = useRankings();
const { getUser } = useUsers();
const userPoints = ref(null);
const userAvatar = ref(null);

const props = defineProps({
    variant: {
        type: String,
        default: "sidebar", // 'sidebar' o 'profile'
    },
});

/* -- FUNCTIONS -- */
const updateUserData = async () => {
    if (authStore().user?.id) {
        try {
            const userData = await getUser(authStore().user?.id);
            if (userData) {
                userAvatar.value = userData.avatar;
                console.log("Avatar URL:", userData.avatar); // Debug
            }
        } catch (error) {
            console.error("Error fetching user data:", error);
        }
    }
};

const getAvatarUrl = () => {
    return userAvatar.value || "/images/icons/user-icon-dark.svg";
};

onMounted(async () => {
    if (authStore().user?.id) {
        // Obtener puntos
        const rankingData = await getRanking(authStore().user?.id);
        if (rankingData.points) {
            userPoints.value = rankingData.points;
        }

        // Actualizar datos del usuario
        await updateUserData();
    }
});
</script>

<style scoped>
/* Estilos base compartidos */
#userComponent {
    width: 100%;
    height: 100%;
    display: flex;
    padding: 0.5rem;
    border-radius: 8px;
    background-color: rgba(255, 255, 255, 0.05);
    transition: all 0.2s ease;
    justify-content: center;
    align-items: center; /* Añadido para centrar verticalmente */
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
    border: none;
    border-radius: 0;
    padding: 0.5rem;
}

.sidebar .userImageContainer {
    width: 40px;
    height: 40px;
}

/* Variante profile */
.profile {
    min-height: 150px;
    border: none;
    border-radius: 10px;
    padding: 1rem 1rem 0 1rem;
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
    background-color: rgba(255, 255, 255, 0.1);
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

.user-avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
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
        flex-direction: column;
        gap: 1rem;
    }

    .profile .left-side {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
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

    .profile .pointsContainer {
        padding: 0;
        justify-content: center;
    }
}
</style>
