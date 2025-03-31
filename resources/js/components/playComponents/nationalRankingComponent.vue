<template>
    <div id="globalRankingMenuContainer">
        <!-- Título -->
        <div id="globalRankingMenuTitle">
            <h4 class="bold white-color">NATIONAL RANKING</h4>
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
    if (user.user && user.user.avatar) {
        if (user.user.avatar.startsWith("http")) {
            return user.user.avatar;
        }
        return `${window.location.origin}${user.user.avatar}`;
    }
    return `${window.location.origin}/images/icons/user-icon-dark.svg`;
};

const loadUserData = async (rankingUser) => {
    try {
        const userData = await getUser(rankingUser.user_id);
        rankingUser.user = userData;
        return userData;
    } catch (error) {
        return null;
    }
};

onMounted(async () => {
    if (authStore().user?.id) {
        const data = await getGlobalRanking(rankingLimit);

        if (data && data.data) {
            const rankingData = data.data;
            for (let rankUser of rankingData) {
                await loadUserData(rankUser);
            }
            ranking.value = rankingData;
        }
    }
});

const handleAvatarError = (e) => {
    e.target.src = "/images/icons/user-icon-dark.svg"; // Cambiado a ruta absoluta
};
</script>

<style scoped>
#globalRankingMenuContainer {
    width: 100%;
    padding: 1rem 0 0 2rem;
}

#globalRankingMenuTitle {
    padding: 0;
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
    margin-right: 0.5rem;
}

.pointsContainer img {
    margin-left: 0.5rem;
    height: 24px;
    width: 24px;
}

/* Estilos responsivos */
@media (max-width: 1200px) {
    #globalRankingMenuContainer {
        padding: 1rem;
    }
}

@media (max-width: 768px) {
    #globalRankingMenuContainer {
        padding: 1rem;
    }

    .rankingContainer {
        padding: 0.75rem;
        margin-bottom: 0;
    }

    .userImageContainer {
        width: 35px;
        height: 35px;
        margin: 0 0.5rem;
    }

    .usernameContainer {
        padding: 0 0.5rem;
    }

    .pointsContainer {
        min-width: 80px;
        padding-left: 0.5rem;
    }

    .points img {
        height: 20px;
        width: 20px;
    }

    .indexContainer {
        width: 30px;
    }
}

@media (max-width: 480px) {
    #globalRankingMenuContainer {
        padding: 0.5rem;
    }

    .rankingContainer {
        padding: 0.5rem;
        font-size: 0.9rem;
    }

    .userImageContainer {
        width: 30px;
        height: 30px;
    }

    .pointsContainer {
        min-width: 70px;
    }

    .points img {
        height: 18px;
        width: 18px;
    }
}
</style>
