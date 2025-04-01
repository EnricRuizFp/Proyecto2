<template>
    <div>
        <!-- Botón móvil fuera de la barra lateral -->
        <button
            id="mobileMenuButton"
            v-if="isMobile"
            @click="toggleDropdown"
            :class="{ 'menu-opened': dropdownOpen }"
        >
            <!-- Reemplazamos la imagen por un icono de Font Awesome -->
            <i
                :class="dropdownOpen ? 'fas fa-times' : 'fas fa-bars'"
                style="font-size: 24px; color: var(--white-color)"
            ></i>
        </button>

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
                :class="{ moved: dropdownOpen, 'desktop-only': isMobile }"
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
        </nav>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, defineExpose } from "vue";
import UserComponent from "../components/navbar/UserComponent.vue";
import MenuComponent from "../components/navbar/MenuComponent.vue";

const dropdownOpen = ref(false);
const isMobile = ref(window.innerWidth < 768); // Cambiado a 768px

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
    // Controlar el scroll del body
    if (isMobile.value) {
        document.body.style.overflow = dropdownOpen.value ? "hidden" : "auto";
    }
};

onMounted(() => {
    window.addEventListener("resize", () => {
        isMobile.value = window.innerWidth < 768; // Cambiado a 768px
    });
});

// Limpiar el estilo al desmontar el componente
onUnmounted(() => {
    document.body.style.overflow = "auto";
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

/* Ocultar closeButton en móvil */
@media (max-width: 768px) {
    #closeButton {
        display: none !important;
    }
}

/* Estilos del botón móvil completamente reescritos */
#mobileMenuButton {
    all: unset;
    position: fixed;
    top: 1rem;
    left: 1rem;
    width: 40px;
    height: 40px;
    background-color: var(--background-primary);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100010;
    transition: all 0.3s ease;
}

#mobileMenuButton.menu-opened {
    background-color: var(--background-secondary);
}

#mobileMenuButton.menu-opened:hover {
    background-color: var(--background-primary);
}

/* Media queries */
@media (max-width: 768px) {
    #mobileMenuButton {
        display: flex !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    #lateralBar:not(.closed) ~ #mobileMenuButton {
        position: fixed;
    }
}

@media (min-width: 769px) {
    #mobileMenuButton {
        display: none;
    }
}

@media (max-width: 768px) {
    #lateralBar {
        width: 100vw;
        height: 100vh;
        transform: translateX(-100%);
        display: flex;
        align-items: center;
        padding: 0;
        overflow-y: auto; /* Permitir scroll en el menú */
        background-color: var(--background-secondary);
        transition: transform 0.3s ease;
        will-change: transform;
        opacity: 1;
        visibility: visible;
    }

    #lateralBar.closed {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    #lateralBar:not(.closed) {
        transform: translateX(0);
    }

    .lateral-content {
        width: 100%;
        height: 100vh; /* Altura completa */
        overflow-y: auto; /* Permitir scroll */
        display: flex;
        flex-direction: column;
        transition: opacity 0.3s ease;
        opacity: 1;
    }

    #menuItems {
        width: 100%;
        height: auto;
        min-height: 100vh; /* Asegurar altura mínima */
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 6rem 0 2rem 0; /* Aumentado el padding-top a 6rem */
        overflow-y: auto;
        transition: none;
    }

    #menuItems :deep(.menu-item) {
        justify-content: center;
        width: 100%;
        text-align: center;
        padding: 1rem 0;
    }

    #menuItems :deep(#userComponent) {
        padding: 2rem 0;
        width: 100%;
        display: flex;
        justify-content: center;
    }

    #menuItems :deep(#userContent) {
        width: auto;
        min-width: auto;
        padding: 0;
    }

    #menuItems :deep(.profile-content),
    #menuItems :deep(.left-side) {
        justify-content: center;
    }

    #menuItems :deep(hr.dropdown-divider) {
        width: 60%;
        margin: 0.5rem auto;
    }
}
</style>
