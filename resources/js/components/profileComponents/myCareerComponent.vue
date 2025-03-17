<template>
    <div class="career-container">
        <!-- Contenedor izquierdo -->
        <div class="left-section">
            <!-- Posiciones -->
            <h3 class="section-title h4 white-color">Ranking</h3>
            <div class="ranking-overview-container">
                <!-- Posición Global -->
                <div class="ranking-box">
                    <div class="ranking-title">Global</div>
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
                    <div class="ranking-title">Nacional</div>
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
            <h3 class="section-title h4 white-color">Historial de Partidas</h3>
            <div class="match-history-container">
                <div v-if="motivationalMessage" class="motivational-message">
                    {{ motivationalMessage }}
                </div>
                <div class="match-list" v-else>
                    <div v-for="(game, index) in matchHistory" :key="index" 
                         :class="['match-item', game.result]">
                        <div class="match-opponent">vs {{ game.opponent }}</div>
                        <div class="match-date">{{ game.date }}</div>
                        <div class="match-result">
                            <span v-if="game.result === 'victory'">Victoria</span>
                            <span v-else-if="game.result === 'defeat'">Derrota</span>
                            <span v-else>Empate</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Importación de recursos
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Variables reactivas para los datos con funciones ASYNC
const globalPosition = ref(null);
const nationalPosition = ref(null);
const userPoints = ref(null);
const matchHistory = ref([]);
const motivationalMessage = ref(null);

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
        const response = await axios.get("/api/games/user-match-history");
        matchHistory.value = response.data.data;
        motivationalMessage.value = response.data.message;
    } catch (error) {
        console.error("Error obteniendo el historial de partidas:", error);
        matchHistory.value = [];
        motivationalMessage.value = null;
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

// Al cargar el componente, ejecutar las 3 funciones del componente
onMounted(() => {
    getGlobalPosition();
    getNationalPosition();
    getUserPoints();
    getMatchHistory();
});
</script>

<style scoped>
.match-history-container {
    max-height: 400px;
    overflow-y: auto;
    padding: 10px;
}

.match-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    margin-bottom: 5px;
    border-radius: 5px;
    background: rgba(255, 255, 255, 0.1);
}

.match-item.victory {
    border-left: 3px solid #4CAF50;
}

.match-item.defeat {
    border-left: 3px solid #f44336;
}

.match-item.draw {
    border-left: 3px solid #FFC107;
}

.motivational-message {
    text-align: center;
    padding: 20px;
    font-style: italic;
    color: #ffffff80;
}

.match-opponent {
    font-weight: bold;
}

.match-date {
    color: #ffffff80;
    font-size: 0.9em;
}

.match-result {
    font-weight: bold;
}

.victory .match-result {
    color: #4CAF50;
}

.defeat .match-result {
    color: #f44336;
}

.draw .match-result {
    color: #FFC107;
}
</style>