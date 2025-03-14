<template>
    <Transition name="modal">
        <div v-if="visible" class="join-match-overlay">
            <div class="join-match-modal app-background-primary">
                <button class="close-button" @click="$emit('close')">
                    <i class="fas fa-times"></i>
                </button>
                <h2 class="h4-dark">ÚNETE A UNA PARTIDA PRIVADA</h2>
                <div class="match-form">
                    <input
                        type="text"
                        placeholder="Ej: 1A2B"
                        class="match-input"
                        v-model="matchCode"
                        maxlength="4"
                    />
                    <p class="p4-dark help-text">
                        Introduce el código de 4 dígitos que aparece en la
                        pantalla de tu oponente para unirte
                    </p>
                    <button
                        class="primary-button"
                        :disabled="!matchCode"
                        @click="joinMatch"
                    >
                        UNIRSE
                        <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M14.4301 5.92993L20.5001 11.9999L14.4301 18.0699"
                                stroke="white"
                                stroke-width="2.5"
                                stroke-miterlimit="10"
                                stroke-linecap="round"
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
import { ref } from "vue";

const props = defineProps({
    visible: Boolean,
});

const emit = defineEmits(["close", "join"]);
const matchCode = ref("");

const joinMatch = () => {
    emit("join", matchCode.value);
    matchCode.value = "";
};
</script>

<style scoped>
.join-match-overlay {
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

.join-match-modal {
    padding: 4rem;
    border-radius: 8px;
    border: 1px solid var(--white-color);
    text-align: center;
    min-width: 350px;
    max-width: 400px;
    position: relative; /* Añadido para posicionamiento absoluto del botón */
    transform-origin: center;
    animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3rem;
}

.close-button {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    color: var(--white-color);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    transition: transform 0.3s ease;
}

.close-button:hover {
    transform: scale(1.1);
}

.h2-dark {
    margin-bottom: 2rem;
}

.h3-dark {
    margin-bottom: 2rem;
    text-transform: uppercase;
}

.h4-dark {
    margin-bottom: 2rem;
    text-transform: uppercase;
}

.match-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    margin-top: 2rem;
    width: 100%;
}

.match-input {
    padding: 0.7rem; /* Reducido de 1rem */
    border-radius: 8px;
    border: 2px solid var(--white-color);
    background: var(--neutral-v2-color);
    color: var(--white-color);
    font-size: 1.1rem; /* Reducido de 1.2rem */
    text-align: center;
    width: 50%; /* Reducido de 60% */
    margin: 0 auto;
}

.primary-button {
    margin-top: 1rem;
    width: 100%;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px; /* Aumentado de 10px */
}

.primary-button svg {
    transform: translateY(-1px); /* Ajuste fino de alineación vertical */
}

.match-input:focus {
    outline: none;
    border-color: var(--primary-v2-color);
}

.help-text {
    color: var(--white-color-1);
    text-align: center;
    margin-top: -1rem;
    font-size: 0.9rem;
    line-height: 1.4;
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
