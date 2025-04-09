<template>
    <div class="view-games-container">
        <template v-if="!selectedGame">
            <h2>Partidas Disponibles para Observar</h2>
            <AvailableGames 
                :games="games"
                @watch-game="watchGame"
            />
        </template>
        <ViewGameComponent 
            v-else
            :game-code="selectedGame"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import AvailableGames from '../../components/viewGameComponents/AvailableGames.vue';
import ViewGameComponent from '../../components/viewGameComponents/ViewGameComponent.vue';

const games = ref([]);
const selectedGame = ref(null);

const fetchGames = async () => {
    try {
        const response = await axios.get("/api/games/available");
        if (response.data) {
            games.value = response.data;
        }
    } catch (error) {
        console.error("Error fetching games:", error);
        games.value = [];
    }
};

const watchGame = (gameCode) => {
    selectedGame.value = gameCode;
};

onMounted(() => {
    fetchGames();
    const interval = setInterval(fetchGames, 10000);
    onUnmounted(() => {
        clearInterval(interval);
    });
});
</script>

<style scoped>
.view-games-container {
    padding: 2rem 0 2rem 2rem;
    background-color: var(--background-primary);
    min-height: 100vh;
}

.view-games-container h2 {
    color: var(--white-color);
    font-size: 2.5rem;
    margin-bottom: 2rem;
    text-align: center;
    text-shadow: 0 0 10px rgba(112, 72, 236, 0.5);
}
</style>
