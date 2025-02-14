<template>
    <nav id="lateralBar">
        <UserComponent />
        <hr>

        

    </nav>
</template>

<script setup>

    /* -- IMPORTS -- */
    import { ref } from "vue";

    import UserComponent from "../components/navbar/UserComponent.vue";

    /* -- VARIABLES -- */
    const dropdownOpen = ref(false);

    /* -- FUNCTIONS -- */
    const toggleDropdown = () => {
        dropdownOpen.value = !dropdownOpen.value;
    };
</script>

<style scoped>
    /* Asegura que la barra lateral tenga una altura completa */
    html, body, #app {
        height: 100%;
        background-color: var(--neutral-color);
    }

    #lateralBar{
        width: 20%;
        background-color: aquamarine;
        height: 100%;
    }
</style>
