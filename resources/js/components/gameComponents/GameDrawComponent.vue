<template>
    <Transition name="modal">
        <div v-if="visible" class="game-win-overlay">
            <div class="game-win-modal neutral-background">
                <h2 class="white-color h2">EMPATE!</h2>
                <div class="points-container">
                    <span class="points white-color h3">+0</span>
                    <svg
                        width="40"
                        height="40"
                        viewBox="0 0 24 25"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M12 12.1053V18.1053"
                            stroke="#ffffff"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                        <path
                            d="M12 18.1053C10.3264 18.1053 8.86971 19.1173 8.11766 20.6103C7.75846 21.3233 8.27389 22.1053 8.95877 22.1053H15.0412C15.7261 22.1053 16.2415 21.3233 15.8823 20.6103C15.1303 19.1173 13.6736 18.1053 12 18.1053Z"
                            stroke="#ffffff"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                        <path
                            d="M5 5.10526H3.98471C2.99819 5.10526 2.50493 5.10526 2.20017 5.47579C1.89541 5.84632 1.98478 6.26122 2.16352 7.09105C2.50494 8.67611 3.24548 10.0687 4.2489 11.1053"
                            stroke="#ffffff"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                        <path
                            d="M19 5.10526H20.0153C21.0018 5.10526 21.4951 5.10526 21.7998 5.47579C22.1046 5.84632 22.0152 6.26122 21.8365 7.09105C21.4951 8.67611 20.7545 10.0687 19.7511 11.1053"
                            stroke="#ffffff"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                        <path
                            d="M12 12.1053C15.866 12.1053 19 8.98836 19 5.14347C19 5.04265 18.9978 4.94233 18.9936 4.84255C18.9508 3.84332 18.9295 3.34371 18.2523 2.72448C17.5751 2.10526 16.8247 2.10526 15.324 2.10526H8.67596C7.17526 2.10526 6.42492 2.10526 5.74772 2.72448C5.07051 3.3437 5.04915 3.84332 5.00642 4.84255C5.00215 4.94233 5 5.04265 5 5.14347C5 8.98836 8.13401 12.1053 12 12.1053Z"
                            stroke="#ffffff"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>
                <div class="buttons">
                    <button
                        class="primary-button light"
                        @click="handleNextLevel"
                    >
                        JUGAR DE NUEVO
                        <svg
                            class="button-icon"
                            width="24"
                            height="25"
                            viewBox="0 0 24 25"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M9 4.48685C9 4.07264 9.33579 3.73685 9.75 3.73685H14.25C14.6642 3.73685 15 4.07264 15 4.48685V6.73685H18.75C19.1642 6.73685 19.5 7.07264 19.5 7.48685V12.7694L21.2982 13.5487C21.4925 13.6328 21.6422 13.7952 21.7101 13.9956C21.7782 14.1961 21.7584 14.416 21.6557 14.6011L19.9385 17.6921C19.7054 17.2667 19.4118 16.826 18.821 16.6148L19.9425 14.596L12.8946 11.5419C12.3239 11.2946 11.6762 11.2946 11.1054 11.5419L4.0575 14.596L5.17905 16.6148C4.58822 16.826 4.29464 17.2667 4.0616 17.6921L2.34438 14.6011C2.24157 14.416 2.22176 14.1961 2.28984 13.9956C2.35793 13.7952 2.50755 13.6328 2.7018 13.5487L4.5 12.7694V7.48685C4.5 7.07264 4.83579 6.73685 5.25 6.73685H9V4.48685ZM6 12.1194L10.509 10.1656C11.4603 9.75336 12.5397 9.75336 13.491 10.1656L18 12.1194V8.23685H6V12.1194ZM13.5 5.23685H10.5V6.73685H13.5V5.23685Z"
                                fill="#ffffff"
                                stroke="#ffffff"
                                stroke-width="0.5"
                            />
                            <path
                                d="M5.40246 18.2833C5.38423 18.3073 5.36736 18.3326 5.35197 18.359C5.31683 18.4193 5.28945 18.484 5.26204 18.5488C5.23686 18.6083 5.21165 18.6679 5.18033 18.7243C5.09004 18.8867 4.94247 19.1087 4.71968 19.3316C4.28835 19.7629 3.53808 20.2369 2.25 20.2369C1.83579 20.2369 1.5 20.5727 1.5 20.9869C1.5 21.4012 1.83579 21.7369 2.25 21.7369C3.96192 21.7369 5.08665 21.0859 5.78032 20.3923C5.8456 20.3269 5.90674 20.2616 5.96394 20.1968C6.00084 20.245 6.03948 20.2936 6.07992 20.342C6.64501 21.0202 7.58901 21.7369 8.99984 21.7369C10.4107 21.7369 11.3547 21.0202 11.9198 20.342C11.9473 20.309 11.974 20.276 11.9998 20.2432C12.0257 20.276 12.0524 20.309 12.0799 20.342C12.645 21.0202 13.589 21.7369 14.9998 21.7369C16.4106 21.7369 17.3547 21.0202 17.9198 20.342C17.9602 20.2934 17.999 20.245 18.0359 20.1967C18.0931 20.2615 18.1544 20.3269 18.2196 20.3923C18.9134 21.0859 20.038 21.7369 21.75 21.7369C22.1642 21.7369 22.5 21.4012 22.5 20.9869C22.5 20.5727 22.1642 20.2369 21.75 20.2369C20.462 20.2369 19.7116 19.7629 19.2804 19.3316C18.8914 18.9427 18.7288 18.5449 18.7107 18.4972C18.6616 18.3655 18.5876 18.2494 18.4785 18.1592C18.3451 18.049 18.1756 17.9866 17.9997 17.9867C17.8883 17.9867 17.7796 18.0116 17.6813 18.058C17.624 18.0847 17.5715 18.1181 17.5242 18.1568C17.4192 18.2428 17.3362 18.3583 17.2896 18.4961C17.2579 18.584 17.2185 18.6695 17.1767 18.7531C17.0901 18.9263 16.956 19.1555 16.7675 19.3817C16.395 19.8286 15.839 20.2369 14.9998 20.2369C14.1607 20.2369 13.6047 19.8286 13.2323 19.3817C13.0437 19.1555 12.9096 18.9263 12.823 18.7531C12.7587 18.6245 12.723 18.5314 12.713 18.5039C12.672 18.3902 12.6111 18.287 12.53 18.2062C12.459 18.1351 12.3727 18.0775 12.2741 18.0388C12.1877 18.0049 12.0947 17.9867 11.9998 17.9867C11.9049 17.9867 11.812 18.0049 11.7256 18.0388C11.6268 18.0775 11.5404 18.1354 11.4694 18.2065C11.3884 18.2873 11.3276 18.3904 11.2867 18.5039C11.2766 18.5314 11.2409 18.6245 11.1767 18.7531C11.0901 18.9263 10.9559 19.1555 10.7674 19.3817C10.395 19.8286 9.83901 20.2369 8.99984 20.2369C8.16066 20.2369 7.60467 19.8286 7.23225 19.3817C7.04373 19.1555 6.9096 18.9263 6.823 18.7531C6.7954 18.6979 6.77241 18.64 6.7494 18.5819C6.71923 18.506 6.68907 18.43 6.6485 18.3601C6.63222 18.332 6.61428 18.3053 6.59484 18.28C6.50682 18.1652 6.38472 18.0746 6.23717 18.0254L6.22953 18.0229C6.08278 17.9755 5.92017 17.9729 5.76267 18.0254C5.61369 18.0751 5.49066 18.1669 5.40246 18.2833Z"
                                fill="#ffffff"
                            />
                        </svg>
                    </button>
                    <button class="primary-button light" @click="goToHome">
                        VOLVER AL INICIO
                        <svg
                            class="button-icon"
                            width="24"
                            height="25"
                            viewBox="0 0 24 25"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M8.99932 22.7368L8.74868 19.2279C8.61394 17.3414 10.108 15.7368 11.9993 15.7368C13.8906 15.7368 15.3847 17.3414 15.25 19.2279L14.9993 22.7368"
                                stroke="#ffffff"
                                stroke-width="2"
                            />
                            <path
                                d="M2.35139 13.9503C1.99837 11.653 1.82186 10.5045 2.25617 9.48623C2.69047 8.46797 3.65403 7.77128 5.58114 6.37791L7.02099 5.33685C9.41829 3.60352 10.6169 2.73685 12 2.73685C13.3831 2.73685 14.5817 3.60352 16.979 5.33685L18.4189 6.37791C20.346 7.77128 21.3095 8.46797 21.7438 9.48623C22.1781 10.5045 22.0016 11.653 21.6486 13.9503L21.3476 15.9092C20.8471 19.1657 20.5969 20.794 19.429 21.7654C18.2611 22.7368 16.5537 22.7368 13.1388 22.7368H10.8612C7.44633 22.7368 5.73891 22.7368 4.571 21.7654C3.40309 20.794 3.15287 19.1657 2.65243 15.9092L2.35139 13.9503Z"
                                stroke="#ffffff"
                                stroke-width="2"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { useRouter } from "vue-router";
import { useGameStore } from "../../store/game";
import { authStore } from "../../store/auth";
import axios from "axios";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['next-level', 'cleanup']);

const router = useRouter();
const gameStore = useGameStore();

const goToHome = () => {
    // Limpiar el estado actual
    emit('cleanup');
    gameStore.resetGame();
    
    // Asegurarse de que todas las variables del juego se reinicien
    gameStore.$reset(); // Reinicia todo el store a su estado inicial
    
    // Navegar al inicio
    router.push("/");
};

const handleNextLevel = async () => {
    emit('cleanup');
    gameStore.resetGame();

    try {
        const response = await axios.post('/api/games/check-user-requirements', {
            gameType: gameStore.gameType,
            gameCode: gameStore.matchCode,
            user: authStore().user ?? null
        });

        if (response.data.status === 'success') {
            router.push(`/game/${gameStore.gameType}/${gameStore.matchCode || 'null'}`);
        } else {
            console.error("Failed:", response.data.message);
            goToHome();
        }
    } catch (error) {
        console.error("Error checking requirements:", error);
        goToHome();
    }
};
</script>

<style scoped>
.game-win-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

.game-win-modal {
    padding: 4rem; /* Aumentar padding general */
    border-radius: 8px;
    text-align: center;
    border: 1px solid var(--white-color); /* Cambiar de 2px a 1px */
    transform-origin: center;
    animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.buttons {
    margin-top: 3rem; /* Aumentar margen superior de botones */
    display: flex;
    flex-direction: column;
    gap: 2rem; /* Aumentar espacio entre botones */
    align-items: center;
}

/* Eliminar los estilos personalizados de botones ya que usamos primary-button */

.button-icon {
    margin-left: 10px;
    vertical-align: middle;
    width: 24px; /* Reducir de 32px a 24px */
    height: 24px; /* Mantener proporción cuadrada */
}

.primary-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: uppercase;
}

/* Botón de jugar de nuevo mantiene el color primario por defecto */
button:first-child {
    background: var(--primary-color);
}
button:first-child:hover {
    background: var(--primary-v2-color);
}

/* Botón de volver al inicio usa el color secundario */
button:last-child {
    background: var(--secondary-color);
}
button:last-child:hover {
    background: var(--secondary-v2-color);
}

.points-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 25px; /* Aumentar espacio entre número y trofeo */
    margin: 3rem 0; /* Aumentar margen vertical */
}

.points {
    color: var(--white-color);
    font-size: 50px; /* Aumentar tamaño de fuente */
    line-height: 1; /* Ajustar line-height para mejor alineación */
    margin-top: 8px; /* Ajuste fino de alineación vertical */
}

.h2 {
    margin-top: 2rem; /* Aumentar margen superior del título */
}

svg {
    transform: translateY(
        -2px
    ); /* Ajuste fino de alineación vertical del SVG */
}

/* Animaciones de popup */
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from {
    opacity: 0;
    transform: scale(0.8);
}

.modal-leave-to {
    opacity: 0;
    transform: scale(0.8);
}

@keyframes popIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
