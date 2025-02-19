<template>
    
    <div id="content">
        <div id="playMenu">
            <playComponent />
        </div>
        <div id="myProfileComponent">
            <myProfileMenuComponent />
        </div>
        <div id="globalRankingComponent">
            <globalRankingComponent />
        </div>
    </div>    

</template>

<script setup>

    import playComponent from "../../components/playComponents/playComponent.vue";
    import myProfileMenuComponent from "../../components/playComponents/myProfileMenuComponent.vue";
    import globalRankingComponent from "../../components/playComponents/globalRankingComponent.vue";

</script>