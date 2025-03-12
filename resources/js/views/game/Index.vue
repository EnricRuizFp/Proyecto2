<template>
    <div class="game-view neutral-background">
        <div class="game-container">
            <!-- Fase de colocación de barcos -->
            <ShipPlacement
                v-if="gamePhase === 'placement'"
                @placement-confirmed="startGame"
            />

            <!-- Panel de pruebas convertido en componente -->
            <PruebasComponent />
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

<script>
import ShipPlacement from "../../components/gameComponents/ShipPlacement.vue";
import GameWin from "../../components/gameComponents/GameWin.vue";
import GameOver from "../../components/gameComponents/GameOver.vue";
import PruebasComponent from "../../components/PruebasComponent.vue";

export default {
    name: "GameView",
    components: {
        ShipPlacement,
        GameWin,
        GameOver,
        PruebasComponent,
    },
    data() {
        return {
            gamePhase: "placement", // 'placement', 'playing', 'finished'
            playerBoard: null,
            currentLevel: 1,
            showWin: false,
            showGameOver: false,
        };
    },
    methods: {
        startGame(boardConfiguration) {
            this.playerBoard = boardConfiguration;
            this.gamePhase = "playing";
        },
        nextLevel() {
            this.currentLevel++;
            this.showWin = false;
        },
        restartLevel() {
            this.showWin = false;
            this.showGameOver = false;
        },
        goToMenu() {
            this.$router.push("/menu");
        },
        testWin() {
            this.showWin = true;
        },
        testGameOver() {
            this.showGameOver = true;
        },
    },
};
</script>

<style scoped>
.game-view {
    width: 100%;
    min-height: 100vh;
    background-color: var(--neutral-color);
    color: var(--white-color);
    position: relative; /* Cambiado de fixed a relative */
    overflow-y: auto;
}

.game-container {
    padding: 20px;
    padding-top: 40px; /* Reducido de 121px a 111px */
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
