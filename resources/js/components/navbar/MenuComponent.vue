<template>

    <!-- Menu List -->
    <div id="lateralMenu">
        <div class="tituloBarraLateral">
            <router-link class="color-white h3 bolder" to="/">INICIO</router-link>
        </div>
        <div class="subtituloBarraLateral">
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/public_game">Partida pública</router-link>
            </div>
            <hr class="dropdown-divider">
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/create_game">Crear partida</router-link>
            </div>
            <hr class="dropdown-divider">
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/join_game">Unirse a partida</router-link>
            </div>
            <hr class="dropdown-divider">
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/view_game">Observar partida</router-link>
            </div>
            <hr class="dropdown-divider">
        </div>

        <div class="tituloBarraLateral">
            <h2 class="bolder">RANKING</h2>
        </div>
        <div class="subtituloBarraLateral">

            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/global_ranking">Ranking global</router-link>
            </div>
            <hr class="dropdown-divider">
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/national_ranking">Ranking nacional</router-link>
            </div>
            <hr class="dropdown-divider">
        </div>
    </div>

</template>

<script setup>

    /* -- IMPORTS -- */

    /* -- VARIABLES -- */

    /* -- FUNCTIONS -- */

</script>