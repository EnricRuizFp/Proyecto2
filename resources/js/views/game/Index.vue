<template>
    <div class="game-view app-background-primary">
        <div class="game-container">
            <!-- Fase de carga de la partida -->
            <GameLoadingComponent v-if="gamePhase === 'loading'" />

            <!-- Fase de colocación de barcos -->
            <ShipPlacement
                v-if="gamePhase === 'placement'"
                @placement-confirmed="startGame"
            />

            <!-- Componente de partida -->
            <GameComponent v-if="gamePhase === 'playing'" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, provide } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useGameStore } from "../../store/game";
import { authStore } from "../../store/auth";
import GameLoadingComponent from "../../components/gameComponents/GameLoadingComponent.vue";
import ShipPlacement from "../../components/gameComponents/ShipPlacement.vue";
import GameComponent from "../../components/gameComponents/GameComponent.vue";
import GameWin from "../../components/gameComponents/GameWinComponent.vue";
import GameDraw from "../../components/gameComponents/GameDrawComponent.vue";
import GameOver from "../../components/gameComponents/GameOverComponent.vue";
import PruebasComponent from "../../components/PruebasComponent.vue";

const route = useRoute();
const router = useRouter();
const gameStore = useGameStore();

// Computed properties
const gamePhase = computed(() => gameStore.gamePhase);
const showWin = computed(() => gameStore.showWin);
const showDraw = computed(() => gameStore.showDraw);
const showGameOver = computed(() => gameStore.showGameOver);

// Proporcionar el estado de bloqueo del menú para componentes hijos
provide("menuBlocked", true);

// Game methods
const startGame = (boardConfiguration) => {
    gameStore.playerBoard = boardConfiguration;
    gameStore.setGamePhase("playing");
};

const goToMenu = () => {
    router.push("/menu");
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

    // Bloquear el menú cuando se monta el componente
    document.dispatchEvent(new CustomEvent("block-menu", { detail: true }));

    console.log("Game Type:", route.params.gameType);
    console.log("Game Code:", route.params.gameCode);
    gameStore.setGamePhase("loading");
});

// Desbloquear el menú cuando se desmonta el componente
onUnmounted(() => {
    document.dispatchEvent(new CustomEvent("block-menu", { detail: false }));
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
</style>
