<template>
    <div>
        <!-- Botón móvil fuera de la barra lateral -->
        <button
            id="mobileMenuButton"
            v-if="isMobile"
            @click="toggleDropdown"
            :class="{ 'menu-opened': dropdownOpen }"
        >
            <!-- Icono de Font Awesome en lugar de imagen -->
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
                    <MenuComponent
                        @navigation="closeMenu"
                        :is-menu-blocked="menuBlocked"
                    />
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
                        alt="Flecha para abrir menú"
                    />
                    <img
                        v-else
                        src="/images/icons/arrow-right-dark.svg"
                        alt="Flecha para cerrar menú"
                    />
                </span>
            </div>
        </nav>

        <!-- Mensaje de Información -->
        <div v-if="infoMessage" class="info-alert">
            {{ infoMessage }}
            <button class="close-button" @click="infoMessage = ''">×</button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, defineExpose, watch, provide } from "vue";
import { useRouter, useRoute } from "vue-router";
import UserComponent from "../components/navbar/UserComponent.vue";
import MenuComponent from "../components/navbar/MenuComponent.vue";

const dropdownOpen = ref(false);
const isMobile = ref(window.innerWidth < 768);
const router = useRouter();
const route = useRoute();
const menuBlocked = ref(false); // Estado para bloquear interacción con el menú (ej: durante partida)
const infoMessage = ref(""); // Mensaje para intentos de navegación bloqueados

// Escucha eventos personalizados para bloquear/desbloquear el menú
const handleMenuBlock = (event) => {
    menuBlocked.value = event.detail;
};

// Escucha eventos personalizados para mostrar mensaje de navegación bloqueada
const handleMenuBlockedMessage = (event) => {
    infoMessage.value = event.detail;
    setTimeout(() => {
        infoMessage.value = ""; // Limpia el mensaje después de 3 segundos
    }, 3000);
};

// Cierra el menú al cambiar de ruta, especialmente en móvil
watch(
    () => route.fullPath,
    () => {
        if (isMobile.value) {
            closeMenu();
        }
    }
);

// Alterna el estado abierto/cerrado de la barra lateral
const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
    // Evita el scroll del body cuando el menú está abierto en móvil
    if (isMobile.value) {
        document.body.style.overflow = dropdownOpen.value ? "hidden" : "auto";
    }
};

// Cierra la barra lateral si está abierta
const closeMenu = () => {
    if (dropdownOpen.value) {
        dropdownOpen.value = false;
        // Restaura el scroll del body si es móvil
        if (isMobile.value) {
            document.body.style.overflow = "auto";
        }
    }
};

// Configura listeners al montar el componente
onMounted(() => {
    // Escucha eventos personalizados relacionados con el bloqueo del menú
    document.addEventListener("block-menu", handleMenuBlock);
    document.addEventListener(
        "show-menu-blocked-message",
        handleMenuBlockedMessage
    );

    // Actualiza el estado móvil al redimensionar la ventana
    const handleResize = () => {
        isMobile.value = window.innerWidth < 768;
    };
    window.addEventListener("resize", handleResize);
});

// Limpieza al desmontar el componente
onUnmounted(() => {
    document.body.style.overflow = "auto"; // Asegura que el scroll del body se restaure
    // Elimina los listeners de eventos
    document.removeEventListener("block-menu", handleMenuBlock);
    document.removeEventListener(
        "show-menu-blocked-message",
        handleMenuBlockedMessage
    );
    window.removeEventListener("resize", () => {
        isMobile.value = window.innerWidth < 768;
    });
});

// Provee el estado menuBlocked a los componentes hijos (como MenuComponent)
provide("menuBlocked", menuBlocked);

// Expone métodos si son necesarios para componentes padres (aunque probablemente no se usen aquí)
defineExpose({ dropdownOpen, toggleDropdown, closeMenu });
</script>

<style scoped>
#lateralBar {
    width: 280px;
    height: 100vh;
    background-color: var(--background-secondary);
    transition: width 0.3s ease;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.lateral-content::-webkit-scrollbar {
    display: none;
}

#menuItems {
    flex: 1;
    overflow-y: auto;
    padding-top: 4rem;
    padding-left: 2rem;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

#menuItems::-webkit-scrollbar {
    display: none;
}

#closeButton {
    position: absolute;
    top: 50%;
    right: -50px;
    transform: translateY(-50%);
    background-color: var(--background-secondary);
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

#closeButton:hover {
    background-color: var(--background-primary);
}

@media (max-width: 768px) {
    #closeButton {
        display: none !important;
    }
}

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
        overflow-y: auto;
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
        height: 100vh;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        transition: opacity 0.3s ease;
        opacity: 1;
    }

    #menuItems {
        width: 100%;
        height: auto;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 6rem 0 2rem 0;
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

.info-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 2rem;
    border-radius: 8px;
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    background-color: var(--neutral-color-2);
    color: white;
    border: 1px solid var(--white-color);
}

.close-button {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0 0.5rem;
}

.close-button:hover {
    opacity: 0.8;
}
</style>
