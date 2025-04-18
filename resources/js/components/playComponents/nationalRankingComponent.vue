<template>
    <div id="nationalRankingMenuContainer">
        <!-- Título -->
        <div id="nationalRankingMenuTitle">
            <h4 class="bold white-color">
                {{ nationality.toUpperCase() }} RANKING
            </h4>
        </div>

        <!-- Contenedor del ranking -->
        <div id="globalRankingContainer">
            <div id="globalRankingInternContainer">
                <!-- Si el usuario ha iniciado sesión -->
                <div v-if="authStore().user?.id">
                    <!-- Si hay datos de ranking disponibles -->
                    <div v-if="rankingData && rankingData.length">
                        <div
                            v-for="(user, index) in rankingData"
                            :key="user.id"
                        >
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
                                            alt="Icono de trofeo"
                                            height="70%"
                                    /></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Si no hay datos de ranking -->
                    <p
                        v-else
                        id="textoCargandoDatosRanking"
                        class="p3 white-color"
                    >
                        No hay datos de ranking nacional disponibles.
                    </p>
                </div>
                <!-- Si el usuario no ha iniciado sesión -->
                <div v-else id="peticionInicioSesion" class="white-color">
                    <p class="p3">
                        Para ver el ranking nacional, debes iniciar sesión y
                        establecer tu nacionalidad.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import useRankings from "@/composables/rankings.js";
import useUsers from "@/composables/users.js";
import { authStore } from "../../store/auth";

// Propiedades recibidas del componente padre
const props = defineProps({
    rankingData: {
        type: Array,
        default: () => [], // Por defecto, un array vacío
    },
    nationality: {
        type: String,
        default: "Global", // Por defecto, 'Global'
    },
});

/* -- VARIABLES -- */
const { getUser } = useUsers(); // Función para obtener datos de usuario

/* -- FUNCIONES -- */
// Construye la URL del avatar del usuario
const getAvatarUrl = (user) => {
    if (user.user && user.user.avatar) {
        // Si la URL ya es absoluta (empieza con http)
        if (user.user.avatar.startsWith("http")) {
            return user.user.avatar;
        }
        // Si es una ruta relativa, la completa con el origen
        return `${window.location.origin}${user.user.avatar}`;
    }
    // Si no hay avatar, devuelve la imagen por defecto
    return `${window.location.origin}/images/icons/user-icon-dark.svg`;
};

// Carga los datos del usuario asociado a una entrada del ranking
const loadUserData = async (rankingUser) => {
    try {
        const userData = await getUser(rankingUser.user_id);
        rankingUser.user = userData; // Asigna los datos cargados al objeto del ranking
        return userData;
    } catch (error) {
        console.error("Error al cargar datos del usuario:", error);
        return null; // Devuelve null si hay un error
    }
};

// Se ejecuta cuando el componente se monta en el DOM
onMounted(async () => {
    // Si el usuario está logueado y hay datos de ranking
    if (authStore().user?.id && props.rankingData.length) {
        // Itera sobre cada usuario en el ranking para cargar sus datos completos
        for (let rankUser of props.rankingData) {
            await loadUserData(rankUser);
        }
    }
});

// Maneja errores al cargar la imagen del avatar
const handleAvatarError = (e) => {
    // Si falla la carga, muestra la imagen por defecto
    e.target.src = "/images/icons/user-icon-dark.svg";
};
</script>

<style scoped>
#nationalRankingMenuContainer {
    width: 100%;
    padding: 1rem 0 0 2rem;
}

#nationalRankingMenuTitle {
    padding: 0;
    margin-bottom: 1rem;
}

#globalRankingContainer {
    display: flex;
    border: 1px solid var(--white-color);
    border-radius: 10px;
}

#globalRankingInternContainer {
    width: 100%;
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

#textoCargandoDatosRanking,
#peticionInicioSesion {
    padding: 20px;
}

@media (max-width: 1200px) {
    #nationalRankingMenuContainer {
        padding: 1rem;
    }
}

@media (max-width: 768px) {
    #nationalRankingMenuContainer {
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
    #nationalRankingMenuContainer {
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
