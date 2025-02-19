<template>
    <nav id="lateralBar" :class="{ 'closed': dropdownOpen }">
        <UserComponent />
        <div id="menuItems">
            <hr class="dropdown-divider">
            <MenuComponent /> 
        </div> 
        <!-- Botón de abrir/cerrar -->
        <div id="closeButton" :class="{ 'moved': dropdownOpen }" @click="toggleDropdown">
            <span class="arrow">
                <img v-if="dropdownOpen" src="/images/icons/arrow-right-dark.svg" alt="Open menu arrow">
                <img v-else src="/images/icons/arrow-left-dark.svg" alt="Close menu arrow">
            </span>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted, defineExpose } from "vue";
import UserComponent from "../components/navbar/UserComponent.vue";
import MenuComponent from "../components/navbar/MenuComponent.vue";

const dropdownOpen = ref(true); // Menú oculto por defecto

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

// Asegurar que el estado se mantiene cerrado al cargar
onMounted(() => {
    dropdownOpen.value = true;
});

defineExpose({ dropdownOpen, toggleDropdown });
</script>
