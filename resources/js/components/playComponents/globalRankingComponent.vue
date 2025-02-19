<template>
    <div id="globalRankingMenuContainer">
        <!-- Título -->
        <div id="globalRankingMenuTitle">
            <h4 class="bold white-color">GLOBAL RANKING</h4>
        </div>

        <!-- Contenedor del ranking -->
        <div id="globalRankingContainer">
            <div id="globalRankingInternContainer">

                <ul v-if="ranking.length">
                    <li v-for="(user, index) in ranking" :key="user.id">

                        <div class="rankingContainer white-color p4">
                            <div class="indexContainer">
                                <span class="rank">{{ index + 1 }}</span>
                            </div>
                            <div class="userImageContainer">
                                <!-- <img src={{  }} alt="User avatar" height="70%"> -->
                                <img src="../../../../public/images/icons/user-icon-dark.svg" alt="User avatar" height="70%">
                            </div>
                            <div class="usernameContainer">
                                <span class="username">{{ user.user?.username || "Desconocido" }}</span>
                            </div>
                            <div class="pointsContainer">
                                <span class="points">{{ user.points }} <img src="../../../../public/images/icons/trophy-icon-dark.svg" alt="Trophy icon" height="70%"></span>
                            </div>
                        </div>
                    </li>
                </ul>
                <p v-else>Cargando ranking...</p>

            </div>
        </div>
    </div>
</template>

<script setup>
    /* -- IMPORTS -- */
    import { ref, onMounted } from 'vue';
    import useRankings from "@/composables/rankings.js";

    /* -- VARIABLES -- */
    const { getGlobalRanking } = useRankings();
    const rankingLimit = 15;
    const ranking = ref([]);

    /* -- FUNCTIONS -- */
    onMounted(() => {
        console.log("Obteniendo ranking global");

        getGlobalRanking(rankingLimit).then((data) => {
            if (data && data.data) {
                ranking.value = data.data;
            } else {
                console.log("No hay ranking.");
            }
        });
    });

</script>
