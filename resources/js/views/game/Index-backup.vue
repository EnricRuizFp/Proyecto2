<template>
    <div class="game-view neutral-background">
        <div class="game-container">
            <h1>Game View</h1>
            <h2>Nivel {{ currentLevel }}</h2>
            <!-- Botones temporales para pruebas -->
            <div class="debug-controls">
                <button @click="testWin">Simular Victoria</button>
                <button @click="testGameOver">Simular Derrota</button>
            </div>
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
import GameWin from "../../components/gameComponents/GameWin.vue";
import GameOver from "../../components/gameComponents/GameOver.vue";

export default {
    name: "GameView",
    components: {
        GameWin,
        GameOver,
    },
    data() {
        return {
            currentLevel: 1,
            showWin: false,
            showGameOver: false,
        };
    },
    methods: {
        nextLevel() {
            this.currentLevel++;
            this.showWin = false;
            // Implementar lógica para cargar siguiente nivel
        },
        restartLevel() {
            this.showWin = false;
            this.showGameOver = false;
            // Implementar lógica para reiniciar nivel actual
        },
        goToMenu() {
            this.$router.push("/menu");
        },
        // Métodos de prueba
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
    height: calc(100vh - 101px);
    color: white;
}

.game-container {
    padding: 20px;
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
