<template>
    <div id="topBar" class="app-background-primary">
        <div id="topBarContent">
            <!-- Si no estamos en rutas de juego, muestra el logo con enlace a inicio -->
            <a v-if="!isInGameRoute" href="/">
                <img
                    src="../../../public/images/logo-darkTheme.svg"
                    alt="Logo del juego"
                    height="50"
                />
            </a>
            <!-- Si estamos en rutas de juego, muestra el logo pero sin enlace -->
            <div v-else class="logo-container">
                <img
                    src="../../../public/images/logo-darkTheme.svg"
                    alt="Logo del juego"
                    height="50"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();

// Comprueba si la ruta actual corresponde a una pantalla de juego
const isInGameRoute = computed(() => {
    return route.path.startsWith("/game");
});
</script>

<style scoped>
#topBar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 70px;
    z-index: 1000;
}

#topBarContent {
    height: 100%;
    display: flex;
    align-items: center;
    padding: 0 2rem;
}

.logo-container {
    cursor: default;
}

@media (max-width: 768px) {
    #topBar {
        display: none;
    }
}
</style>
