<template>
    <h2>GAME COMPONENT</h2>
</template>

<script setup>

/* -- IMPORTS -- */
import { ref, computed, onMounted, onUnmounted } from "vue";
import { authStore } from "../../store/auth";
import { useRoute, useRouter } from 'vue-router';
import { useGameStore } from "../../store/game";

/* -- VARIABLES -- */
const route = useRoute();
const router = useRouter();
const gameStore = useGameStore(); // Utilizado para settear las fases del juego

/* -- FUNCTIONS -- */

// Función onMounted
onMounted(() => {
    console.log("GameComponent mounted");
    console.log("Game code:", gameStore.matchCode);
    console.log("User: ", authStore().user);

    loadShips();
    startGame();
});

// Función para volver a inicio
const backToHome = () => {
    router.push('/');
};

// Función loadShips (cargar los barcos del jugador)
const loadShips = async () => {

    // Verificación de usuario autenticado y código de partida
    if(authStore().user == null) {
        console.log("User not authenticated. Cannot load ships.");
        backToHome();
    }else if(gameStore.matchCode == "null"){
        console.log("No match code. Cannot load ships.");
        backToHome();
    }

    console.log("Obteniendo coordenadas de los barcos del jugador");

    // Obtener los barcos del jugador
    try {
        const response = await axios.post('/api/games/get-user-ship-placement', {
            gameCode: gameStore.matchCode,
            user: authStore().user
        });
        console.log("Ships loaded: ", response.data);
    } catch (error) {
        console.error("Error loading ships:", error);
    }
};


</script>