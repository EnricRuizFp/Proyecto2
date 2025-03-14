<template>
    <div id="mainPageContainer" class="app-background-primary">
        <div id="mainPageContent" class="container-fluid">
            <div class="row container-fluid">
                <div class="col-sm-10 col-lg-8 mx-auto row">
                    <div class="col-sm-12 col-xl-6">
                        <div id="playMenu">
                            <playComponent />
                        </div>
                        <div>
                            <myProfileMenuComponent />
                        </div>
                    </div>

                    <div id="globalRankingComponent" class="col-sm-12 col-xl-6">
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
