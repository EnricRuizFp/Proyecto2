<template>
    <div class="neutral-background">
        <div v-if="route.name !== 'home'"><TopBar /></div>
        <LateralBar :defaultClosed="true" />
        <router-view></router-view>
    </div>
</template>

<script setup>
import { useRoute } from "vue-router";
import LateralBar from "./LateralBar.vue";
import TopBar from "./TopBar.vue";

const route = useRoute();
</script>
