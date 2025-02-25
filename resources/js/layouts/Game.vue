<template>
    <div>
        <!-- Botones temporales para pruebas -->
        <div class="test-buttons">
            <button @click="showWin = true">Probar Victoria</button>
            <button @click="showGameOver = true">Probar Game Over</button>
        </div>

        <!-- Resto de tu juego -->
        // ...existing code...

        <!-- Componentes de Victoria y Derrota -->
        <GameWin
            :visible="showWin"
            @next-level="handleNextLevel"
            @restart="handleRestart"
        />

        <GameOver
            :visible="showGameOver"
            @restart="handleRestart"
            @menu="handleMenu"
        />
    </div>
</template>

<script>
import GameWin from "./GameWin.vue";
import GameOver from "./GameOver.vue";

export default {
    components: {
        GameWin,
        GameOver,
    },
    data() {
        return {
            showWin: false,
            showGameOver: false,
        };
    },
    methods: {
        handleNextLevel() {
            this.showWin = false;
            // Lógica para siguiente nivel
        },
        handleRestart() {
            this.showWin = false;
            this.showGameOver = false;
            // Lógica para reiniciar
        },
        handleMenu() {
            this.showGameOver = false;
            // Lógica para volver al menú
        },
    },
};
</script>

<style scoped>
.test-buttons {
    position: fixed;
    top: 10px;
    right: 10px;
    z-index: 1100;
}

.test-buttons button {
    margin: 5px;
    padding: 10px;
    background: #333;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
</style>
