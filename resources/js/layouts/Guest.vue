<template>
    <div class="neutral-background">
        <!-- <Navbar /> -->
        <LateralBar :defaultClosed="true" />
        <router-view></router-view>
    </div>
</template>

<script setup>
import LateralBar from "./LateralBar.vue";
</script>
