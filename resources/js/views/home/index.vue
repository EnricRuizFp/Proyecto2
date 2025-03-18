<template>
    <div id="mainPageContainer" class="app-background-primary">
        <TopBar />
        <div id="mainPageContent" class="container-fluid">
            <div class="row container-fluid">
                <div class="col-sm-10 col-lg-8 mx-auto row">
                    <div
                        class="col-sm-12 col-xl-6 d-flex flex-column justify-content-between"
                    >
                        <div id="playMenu" class="w-100">
                            <playComponent />
                        </div>
                        <div class="w-100">
                            <myProfileMenuComponent />
                        </div>
                    </div>

                    <div class="col-sm-12 col-xl-6">
                        <globalRankingComponent />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import TopBar from "../../layouts/TopBar.vue";
import playComponent from "../../components/playComponents/playComponent.vue";
import myProfileMenuComponent from "../../components/playComponents/myProfileMenuComponent.vue";
import globalRankingComponent from "../../components/playComponents/globalRankingComponent.vue";
</script>

<style scoped>
.col-xl-6 {
    padding: 2rem 0;
}

.col-sm-12.col-xl-6 {
    display: flex;
    flex-direction: column;
}

#mainPageContent {
    min-height: calc(100vh - 70px);
    margin-top: 70px;
    padding: 1rem 2rem; /* Añadir padding horizontal simétrico */
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .col-xl-6 {
        padding: 1rem 0;
    }

    #mainPageContent {
        margin-top: 0; /* Eliminar el margen superior en móvil */
        min-height: 100vh; /* Ajustar la altura al viewport completo */
        padding: 2.75rem 1rem; /* Aumentado el padding-top de 1rem a 2rem */
    }

    #playMenu {
        margin-bottom: 1rem;
    }
}
</style>
