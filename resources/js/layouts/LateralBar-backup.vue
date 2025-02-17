<template>
    <nav id="lateralBar" class="h-screen">
        <UserComponent />
        <div id="menuItems">
            <hr class="dropdown-divider">
            <MenuComponent /> 
        </div> 
    </nav>
</template>

<script setup>

    /* -- IMPORTS -- */
    import { ref } from "vue";

    import UserComponent from "../components/navbar/UserComponent.vue";
    import MenuComponent from "../components/navbar/MenuComponent.vue";

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
</style>
