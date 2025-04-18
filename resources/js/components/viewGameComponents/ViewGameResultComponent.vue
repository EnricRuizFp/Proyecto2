<template>
    <Transition name="modal">
        <div v-if="visible" class="game-result-overlay">
            <div class="game-result-modal neutral-background">
                <template v-if="isDraw">
                    <h2 class="white-color h2">¡EMPATE!</h2>
                    <div class="result-message white-color">
                        No hubo un ganador en esta partida
                    </div>
                </template>
                <template v-else>
                    <h2 class="white-color h2">¡GANADOR!</h2>
                    <div class="winner-name white-color">
                        {{ winnerName }}
                    </div>
                </template>
                
                <div class="buttons">
                    <button class="primary-button light" @click="watchAgain">
                        VOLVER A OBSERVAR
                        <svg class="button-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 5C7 5 2.73 8.11 1 12.5C2.73 16.89 7 20 12 20C17 20 21.27 16.89 23 12.5C21.27 8.11 17 5 12 5ZM12 17.5C9.24 17.5 7 15.26 7 12.5C7 9.74 9.24 7.5 12 7.5C14.76 7.5 17 9.74 17 12.5C17 15.26 14.76 17.5 12 17.5ZM12 9.5C10.34 9.5 9 10.84 9 12.5C9 14.16 10.34 15.5 12 15.5C13.66 15.5 15 14.16 15 12.5C15 10.84 13.66 9.5 12 9.5Z" 
                                fill="#ffffff"/>
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

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    winnerName: {
        type: String,
        default: '',
    },
    isDraw: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['cleanup']);
const router = useRouter();

const goToHome = () => {
    emit('cleanup');
    router.push("/");
};

const watchAgain = () => {
    window.location.reload();
};
</script>

<style scoped>
.game-result-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.game-result-modal {
    padding: 4rem;
    border-radius: 8px;
    text-align: center;
    border: 1px solid var(--white-color);
    transform-origin: center;
    animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.buttons {
    margin-top: 3rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    align-items: center;
}

.button-icon {
    margin-left: 10px;
    vertical-align: middle;
    width: 24px;
    height: 24px;
}

.winner-name {
    font-size: 2.5rem;
    margin: 2rem 0;
    font-weight: bold;
    color: var(--primary-color);
}

.result-message {
    font-size: 1.5rem;
    margin: 2rem 0;
}

/* Animaciones */
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from,
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

/* Estilos específicos para los botones */
.primary-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: uppercase;
    width: 100%;
}

/* El primer botón (Volver a observar) usa el color primario */
button:first-child {
    background: var(--primary-color);
}
button:first-child:hover {
    background: var(--primary-v2-color);
}

/* El segundo botón (Volver al inicio) usa el color secundario */
button:last-child {
    background: var(--secondary-color);
}
button:last-child:hover {
    background: var(--secondary-v2-color);
}
</style>
