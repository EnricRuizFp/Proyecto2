<template>
    <div class="game-view app-background-primary">
        <div class="game-container">
            <!-- Fase de carga de la partida -->
            <GameLoadingComponent v-if="gamePhase === 'loading'" />

            <!-- Fase de colocación de barcos -->
            <ShipPlacement
                v-if="gamePhase === 'placement' && !gameMode"
                @placement-confirmed="startGame"
            />

            <!-- Componente de partida privada -->
            <CreateMatch v-if="gameMode === 'create'" />

            <!-- Panel de pruebas convertido en componente -->
            <PruebasComponent v-if="false" />
        </div>

        <!-- Botones temporales para pruebas -->
        <div class="debug-controls">
            <button @click="testWin">Simular Victoria</button>
            <button @click="testGameOver">Simular Derrota</button>
        </div>

        <!-- Componentes de victoria y derrota -->
        <GameWin
            :visible="showWin"
            @next-level="nextLevel"
            @restart="restartLevel"
        />
        <GameOver
            :visible="showGameOver"
            @restart="restartLevel"
            @menu="goToMenu"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useGameStore } from "../../store/game";
import { authStore } from "../../store/auth";
import ShipPlacement from "../../components/gameComponents/ShipPlacement.vue";
import GameWin from "../../components/gameComponents/GameWin.vue";
import GameOver from "../../components/gameComponents/GameOver.vue";
import PruebasComponent from "../../components/PruebasComponent.vue";
import CreateMatch from "../../components/privateMatch/CreateMatch.vue";
import GameLoadingComponent from "../../components/gameComponents/GameLoadingComponent.vue";

const route = useRoute();
const router = useRouter();
const gameStore = useGameStore();
const showWin = ref(false);
const showGameOver = ref(false);

// Computed properties
const gamePhase = computed(() => gameStore.gamePhase);
const gameMode = computed(() => gameStore.gameMode);

// Game methods
const startGame = (boardConfiguration) => {
    gameStore.playerBoard = boardConfiguration;
    gameStore.setGamePhase("playing");
};

const nextLevel = () => {
    currentLevel.value++;
    showWin.value = false;
};

const restartLevel = () => {
    showWin.value = false;
    showGameOver.value = false;
};

const goToMenu = () => {
    router.push("/menu");
};

const testWin = () => {
    showWin.value = true;
};

const testGameOver = () => {
    showGameOver.value = true;
};

// ON MOUNTED
onMounted(() => {
    // Verificación de usuario autenticado y tipo de juego
    if (!authStore().user || !route.params.gameType) {
        gameStore.setGameMode(null);
        gameStore.setMatchCode(null);
        router.push("/");
        return;
    }

    console.log("Game Type:", route.params.gameType);
    console.log("Game Code:", route.params.gameCode);
    gameStore.setGamePhase("loading");
});
</script>

<style scoped>
.game-view {
    width: 100%;
    min-height: 100vh;
    color: var(--white-color);
    position: relative;
    overflow-y: auto;
}

.game-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.debug-controls {
    position: fixed;
    bottom: 20px;
    right: 20px;
    display: flex;
    gap: 10px;
}

.debug-controls button {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: var(--primary-color);
    color: var(--white-color);
    font-weight: bold;
    transition: 0.3s;
}

.debug-controls button:hover {
    background: var(--primary-v2-color);
}
</style>
