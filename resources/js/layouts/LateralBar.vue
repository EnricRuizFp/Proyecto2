<template>
    <nav id="lateralBar" :class="{ closed: !dropdownOpen }">
        <div class="lateral-content">
            <div id="menuItems">
                <UserComponent
                    variant="sidebar"
                    :is-lateral-bar-closed="!dropdownOpen"
                />
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

const dropdownOpen = ref(false);
const isMobile = ref(window.innerWidth < 768); // Cambiado a 768px

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

onMounted(() => {
    window.addEventListener("resize", () => {
        isMobile.value = window.innerWidth < 768; // Cambiado a 768px
    });
});

defineExpose({ dropdownOpen, toggleDropdown });
</script>

<style scoped>
#lateralBar {
    width: 280px;
    height: 100vh;
    background-color: var(
        --background-secondary
    ); /* Cambiado de background-primary a background-secondary */
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
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.lateral-content::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

#menuItems {
    flex: 1;
    overflow-y: auto;
    padding-top: 4rem;
    padding-left: 2rem; /* Reducido de 2rem */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

#menuItems::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

#closeButton {
    position: absolute;
    top: 50%;
    right: -50px;
    transform: translateY(-50%);
    background-color: var(
        --background-secondary
    ); /* Cambiado de neutral-color */
    border: 1px solid var(--white-color);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.3s ease, right 0.3s ease-in-out;
}

/* Cambiar color al pasar el cursor */
#closeButton:hover {
    background-color: var(
        --background-primary
    ); /* Cambiado para mejor contraste */
}

#mobileMenuButton {
    position: fixed;
    top: 10px;
    left: 10px;
    background-color: var(--background-secondary);
    border: 1px solid var(--white-color);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

/* Cambiar color al pasar el cursor */
#mobileMenuButton:hover {
    background-color: var(--background-primary);
}
</style>
