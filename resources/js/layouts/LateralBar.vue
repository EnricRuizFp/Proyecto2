<template>
    <nav id="lateralBar" :class="{ closed: !dropdownOpen }">
        <div class="lateral-content">
            <div id="menuItems">
                <UserComponent variant="sidebar" />
                <MenuComponent />
            </div>
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
#lateralBar {
    width: 280px;
    height: 100vh;
    background-color: var(--background-primary);
    transition: width 0.3s ease;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999; /* Aseguramos que esté por encima de todo */
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Opcional: añade sombra para mejor separación visual */
}

#lateralBar.closed {
    width: 70px;
}

#lateralBar.closed :deep(.menu-text) {
    display: none;
}

#lateralBar.closed :deep(.menu-item) {
    justify-content: center;
}

#lateralBar.closed :deep(.tituloBarraLateral),
#lateralBar.closed :deep(.subtituloBarraLateral) {
    padding-left: 0;
    text-align: center;
}

.lateral-content {
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

#menuItems {
    flex: 1;
    overflow-y: auto;
    padding-top: 4rem;
    padding-left: 2rem; /* Reducido de 2rem */
}

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
