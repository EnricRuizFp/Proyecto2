<template>
    <nav id="lateralBar" :class="{ closed: !dropdownOpen }">
        <UserComponent variant="sidebar" />
        <div id="menuItems">
            <hr class="dropdown-divider" />
            <MenuComponent />
        </div>
        <!-- Botón de abrir/cerrar en desktop -->
        <div
            id="closeButton"
            :class="{ moved: dropdownOpen, mobile: isMobile }"
            @click="toggleDropdown"
        >
            <span class="arrow">
                <img
                    v-if="dropdownOpen"
                    src="/images/icons/arrow-left-dark.svg"
                    alt="Open menu arrow"
                />
                <img
                    v-else
                    src="/images/icons/arrow-right-dark.svg"
                    alt="Close menu arrow"
                />
            </span>
        </div>
        <!-- Botón de abrir el menú en móvil -->
        <div
            id="mobileMenuButton"
            @click="toggleDropdown"
            v-if="isMobile && !dropdownOpen"
        >
            <span class="arrow">
                <img
                    v-if="!dropdownOpen"
                    src="/images/icons/menu-dark.svg"
                    alt="Open menu arrow"
                    height="30px"
                />
            </span>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted, defineExpose } from "vue";
import UserComponent from "../components/navbar/UserComponent.vue";
import MenuComponent from "../components/navbar/MenuComponent.vue";

const dropdownOpen = ref(false); // Menú cerrado por defecto
const isMobile = ref(window.innerWidth < 1000); // Detectar si es dispositivo móvil

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

onMounted(() => {
    window.addEventListener("resize", () => {
        isMobile.value = window.innerWidth < 1000;
    });
});

defineExpose({ dropdownOpen, toggleDropdown });
</script>

<style scoped>
#mobileMenuButton {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 1000;
    padding: 0.5rem;
    border-radius: 8px;
    background-color: var(--background-primary);
    cursor: pointer;
    transition: all 0.3s ease;
}

#mobileMenuButton:hover {
    background-color: var(--background-secondary);
}
</style>
