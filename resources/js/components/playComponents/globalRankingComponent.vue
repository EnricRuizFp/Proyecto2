<template>
    <div id="globalRankingMenuContainer">
        <!-- Título -->
        <div id="globalRankingMenuTitle">
            <h4 class="bold white-color">GLOBAL RANKING</h4>
        </div>

        <!-- Contenedor del ranking -->
        <div id="globalRankingContainer">
            <div id="globalRankingInternContainer">
                <!-- Condición SESIÓN INICIADA -->
                <div v-if="authStore().user?.id">
                    <!-- Condición HAY RANKING -->
                    <div v-if="ranking.length">
                        <div v-for="(user, index) in ranking" :key="user.id">
                            <div class="rankingContainer white-color p3">
                                <div class="indexContainer">
                                    <span class="rank">{{ index + 1 }}</span>
                                </div>
                                <div class="userImageContainer">
                                    <img
                                        :src="getAvatarUrl(user)"
                                        :alt="user.user?.username"
                                        @error="handleAvatarError"
                                        class="user-avatar"
                                    />
                                </div>
                                <div class="usernameContainer">
                                    <span class="username">{{
                                        user.user?.username || "Desconocido"
                                    }}</span>
                                </div>
                                <div class="pointsContainer">
                                    <span class="points"
                                        >{{ user.points }}
                                        <img
                                            src="../../../../public/images/icons/trophy-icon-dark.svg"
                                            alt="Trophy icon"
                                            height="70%"
                                    /></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Condición SIN RANKING -->
                    <p
                        v-else
                        id="textoCargandoDatosRanking"
                        class="p3 white-color"
                    >
                        Cargando ranking...
                    </p>
                </div>
                <!-- Condición SIN SESIÓN -->
                <div v-else id="peticionInicioSesion" class="white-color">
                    <p class="p3">
                        Para ver el ranking global, debes iniciar sesión.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTS -- */
import { ref, onMounted } from "vue";
import useRankings from "@/composables/rankings.js";
import useUsers from "@/composables/users.js";
import { authStore } from "../../store/auth";

/* -- VARIABLES -- */
const { getGlobalRanking } = useRankings();
const { getUser } = useUsers();
const rankingLimit = 15;
const ranking = ref([]);

/* -- FUNCTIONS -- */
const getAvatarUrl = (user) => {
    // Depuración
    console.log("User data in getAvatarUrl:", user);
    console.log("User avatar URL:", user.user?.avatar);

    // Si el usuario tiene un objeto user y ese objeto tiene un avatar, lo usamos
    if (user.user && user.user.avatar) {
        // Asegurarnos de que la URL sea absoluta
        if (user.user.avatar.startsWith("http")) {
            return user.user.avatar;
        }
        // Si la URL es relativa, la convertimos en absoluta
        return `${window.location.origin}${user.user.avatar}`;
    }

    // URL por defecto
    return `${window.location.origin}/images/icons/user-icon-dark.svg`;
};

const loadUserData = async (rankingUser) => {
    try {
        const userData = await getUser(rankingUser.user_id);
        rankingUser.user = userData;
        return userData;
    } catch (error) {
        console.error("Error loading user data:", error);
        return null;
    }
};

onMounted(async () => {
    if (authStore().user?.id) {
        const data = await getGlobalRanking(rankingLimit);
        if (data && data.data) {
            // Cargar datos de usuarios
            const rankingData = data.data;
            for (let rankUser of rankingData) {
                await loadUserData(rankUser);
            }
            ranking.value = rankingData;
        } else {
            console.log("No hay ranking.");
        }
    } else {
        console.log("No hay usuario logueado.");
    }
});

const handleAvatarError = (e) => {
    e.target.src = "/images/icons/user-icon-dark.svg"; // Cambiado a ruta absoluta
};
</script>

<style scoped>
#globalRankingMenuContainer {
    width: 100%;
    padding: 1rem;
}

.rankingContainer {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border-radius: 8px;
    background-color: rgba(255, 255, 255, 0.05);
    transition: all 0.2s ease;
}

.rankingContainer:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.indexContainer {
    width: 40px;
    text-align: center;
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

.user-avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.usernameContainer {
    flex: 1;
    padding: 0 1rem;
}

.pointsContainer {
    display: flex;
    align-items: center;
    min-width: 100px;
    padding-left: 1rem;
    justify-content: flex-end;
}

.points {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
}

.pointsContainer img {
    margin-left: 0.5rem;
    height: 24px;
    width: 24px;
}

/* Estilos responsivos */
@media (max-width: 768px) {
    .rankingContainer {
        margin-bottom: 1rem;
        padding: 0.5rem;
    }
}
</style>
