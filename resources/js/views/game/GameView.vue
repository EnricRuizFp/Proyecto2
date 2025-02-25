<template>
    <div class="game-view">
        <GameLayout>
            <!-- Contenido principal del juego -->
            <div class="game-container">
                <!-- Aquí irá el contenido específico del juego -->
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
        </GameLayout>
    </div>
</template>

<script>
import GameLayout from "@/js/layouts/Game.vue";
import GameWin from "@/js/components/GameWin.vue";
import GameOver from "@/js/components/GameOver.vue";

export default {
    name: "GameView",
    components: {
        GameLayout,
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
    height: 100vh;
    background: #1a1a1a;
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
    border-radius: 5px;
    cursor: pointer;
    background: #333;
    color: white;
    transition: all 0.3s ease;
}

.debug-controls button:hover {
    background: #444;
}
</style>
