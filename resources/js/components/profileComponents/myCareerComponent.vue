<template>
    <div class="career-container">
        <!-- Contenedor izquierdo -->
        <div class="left-section">
            <!-- Posiciones -->
            <h3 class="section-title h4 white-color">Ranking</h3>
            <div class="ranking-overview-container">
                <!-- Posición Global -->
                <div class="ranking-box">
                    <div class="h4">Global</div>
                    <div class="rank-number">
                        <template v-if="globalPosition">
                            <span class="rank-symbol" :class="getRankingColor(globalPosition)">#</span>
                            <span :class="getRankingColor(globalPosition)">{{ globalPosition }}</span>
                        </template>
                        <span v-else>NA</span>
                    </div>
                </div>

                <div class="ranking-divider"></div>

                <!-- Posición Nacional -->
                <div class="ranking-box">
                    <div class="h4">Nacional</div>
                    <div class="rank-number">
                        <template v-if="nationalPosition">
                            <span class="rank-symbol" :class="getRankingColor(nationalPosition)">#</span>
                            <span :class="getRankingColor(nationalPosition)">{{ nationalPosition }}</span>
                        </template>
                        <span v-else>NA</span>
                    </div>
                </div>

                <!-- Puntuación unificada -->
                <div class="points-display">
                    <span class="stats-value">{{ userPoints ?? "NA" }} pts</span>
                </div>
            </div>
        </div>

        <!-- Contenedor derecho - Historial -->
        <div class="right-section">
            <h3 class="section-title h4 white-color">Historial de partidas</h3>
            <div class="match-history-container">
                <div class="match-list">
                    <!-- Mostrar partidas siempre que haya al menos una -->
                    <template v-if="matchHistory.length > 0">
                        <table class="match-history-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Ganador</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="game in displayedGames" :key="game.id">
                                    <td class="match-date">{{ game.date }}</td>
                                    <td>{{ game.is_public }}</td>
                                    <td class="match-result">
                                        {{ isCurrentUser(game.winner_id) ? 'Tú' : game.winner }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Botón mostrar más -->
                        <div v-if="matchHistory.length > 10" class="show-more-container">
                            <button @click="toggleShowAll" class="show-more-btn">
                                {{ showAll ? 'Mostrar menos' : `Ver ${matchHistory.length - 10} más` }}
                            </button>
                        </div>

                        <!-- Mensaje motivacional después de la lista si hay menos de 10 partidas -->
                        <div v-if="matchHistory.length < 10" class="motivational-message">
                            ¡El mar es grande y tú apenas estás mojando los pies!
                        </div>
                    </template>
                    
                    <!-- Mostrar mensaje solo si no hay partidas -->
                    <div v-else class="motivational-message">
                        ¡Tu barco está tan nuevo que aún tiene el plástico protector!
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Importación de recursos
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { authStore } from "@/store/auth"

// Variables reactivas para los datos con funciones ASYNC
const globalPosition = ref(null);
const nationalPosition = ref(null);
const userPoints = ref(null);
const matchHistory = ref([]);
const motivationalMessage = ref(null);
const showAll = ref(false);
const userId = ref(null);

const displayedGames = computed(() => {
    return showAll.value ? matchHistory.value : matchHistory.value.slice(0, 10);
});

const toggleShowAll = () => {
    showAll.value = !showAll.value;
};

// Obtener la posición global del usuario
const getGlobalPosition = async () => {
    try {
        const response = await axios.get("/api/rankings/global-position");
        globalPosition.value = response.data.position;
    } catch (error) {
        console.error("Error obteniendo la posición global del usuario:", error);
        globalPosition.value = null;
    }
};

// Obtener la posición nacional del usuario
const getNationalPosition = async () => {
    try {
        const response = await axios.get("/api/rankings/national-position");
        nationalPosition.value = response.data.position;
    } catch (error) {
        console.error("Error obteniendo la posición nacional del usuario:", error);
        nationalPosition.value = null;
    }
};

// Obtener la cantidad de puntos de un usuario
const getUserPoints = async () => {
    try {
        const response = await axios.get("/api/rankings/user-points");
        userPoints.value = response.data.points;
    } catch (error) {
        console.error("Error obteniendo los puntos del usuario:", error);
        userPoints.value = "NA";
    }
};

// Obtener el historial de partidas del usuario
const getMatchHistory = async () => {
    try {

        console.log("Obteniendo match history.");

        const response = await axios.get("/api/games/user-match-history", { user: authStore().user });
        console.log("Historial de partidas:", response.data.data);
        matchHistory.value = response.data.data;
    } catch (error) {
        console.error("Error obteniendo el historial de partidas:", error);
        matchHistory.value = [];
    }
};

// Obtener el color del ranking
const getRankingColor = (position) => {
    switch (position) {
        case 1: return 'rank-gold';
        case 2: return 'rank-silver';
        case 3: return 'rank-bronze';
        default: return '';
    }
};

const isCurrentUser = (winnerId) => {
    return winnerId === userId.value;
};

// Al cargar el componente, ejecutar las 3 funciones del componente
onMounted(async () => {
    const user = await axios.get('/api/user');
    userId.value = user.data.id;
    
    getGlobalPosition();
    getNationalPosition();
    getUserPoints();
    getMatchHistory();
});
</script>