<template>
    <div id="mainPageContainer" class="app-background-primary">
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
</style>
