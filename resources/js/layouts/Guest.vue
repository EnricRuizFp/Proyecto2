<template>
    <div class="neutral-background">
        <!-- <Navbar /> -->
        <LateralBar />
        <router-view></router-view>
    </div>
</template>
<script setup>
import Navbar from "./Nav.vue";
import LateralBar from "./LateralBar.vue";
</script>
