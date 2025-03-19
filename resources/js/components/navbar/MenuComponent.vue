<template>
    <!-- Menu List -->
    <div id="lateralMenu">
        <hr class="dropdown-divider" />
        <div class="tituloBarraLateral">
            <router-link class="color-white h3 bolder" to="/"
                >INICIO</router-link
            >
        </div>
        <div class="subtituloBarraLateral">
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/public_game"
                    >Partida pública</router-link
                >
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/create_game"
                    >Crear partida</router-link
                >
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/join_game"
                    >Unirse a partida</router-link
                >
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/view_game"
                    >Observar partida</router-link
                >
            </div>
            <hr class="dropdown-divider" />
        </div>

        <div class="tituloBarraLateral">
            <h2 class="bolder">RANKING</h2>
        </div>
        <div class="subtituloBarraLateral">
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/global_ranking"
                    >Ranking global</router-link
                >
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <router-link class="color-white p3" to="/national_ranking"
                    >Ranking nacional</router-link
                >
            </div>
            <hr class="dropdown-divider" />
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTS -- */

/* -- VARIABLES -- */

/* -- FUNCTIONS -- */
</script>

<style scoped>
.tituloBarraLateral {
    padding-top: 2rem;
    padding-left: 0.25rem;
    padding-bottom: 0.5rem;
}

.subtituloBarraLateral {
    padding: 0.5rem 0.25rem;
}

.contenidoSubtituloBarraLateral {
    padding: 0.25rem 0; /* Aumentado para más espacio entre items */
}

.dropdown-divider {
    opacity: 0.25; /* Más delgado */
    margin: 0.4rem 0; /* Aumentado margen */
}

#lateralMenu > .dropdown-divider:first-child {
    opacity: 0.75;
}
</style>
