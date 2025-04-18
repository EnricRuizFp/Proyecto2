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

<script setup>
import { ref } from 'vue'
import GameWin from "./GameWin.vue"
import GameOver from "./GameOver.vue"

const showWin = ref(false)
const showGameOver = ref(false)

const handleNextLevel = () => {
    showWin.value = false
    // Lógica para siguiente nivel
}

const handleRestart = () => {
    showWin.value = false
    showGameOver.value = false
    // Lógica para reiniciar
}

const handleMenu = () => {
    showGameOver.value = false
    // Lógica para volver al menú
}
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
