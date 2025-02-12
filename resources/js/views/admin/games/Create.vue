<template>
    <div class="row justify-content-center my-5">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2>Create Game</h2>
                    <form @submit.prevent="submitForm">
                        <!-- Campo para definir si la partida es pública -->
                        <div class="mb-3">
                            <!-- Label en una línea superior -->
                            <label for="is_public" class="form-label"
                                >Public Game?</label
                            >
                            <!-- Switch debajo del label -->
                            <div class="form-check form-switch">
                                <input
                                    id="is_public"
                                    type="checkbox"
                                    v-model="game.is_public"
                                    class="form-check-input bigger-switch"
                                />
                            </div>
                            <!-- Mensajes de error, si existiesen -->
                            <div class="text-danger mt-1">
                                <div
                                    v-for="message in validationErrors?.is_public"
                                    :key="message"
                                >
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Botón para enviar el formulario -->
                        <div class="mt-4">
                            <button
                                type="submit"
                                :disabled="isLoading"
                                class="btn btn-primary"
                            >
                                <span v-if="isLoading">Processing...</span>
                                <span v-else>Create Game</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from "vue";
import useGames from "@/composables/games.js";

const { storeGame, validationErrors, isLoading } = useGames();

// Creamos un objeto reactivo para el game.
const game = reactive({
    is_public: false, // Por defecto, la partida es privada.
});

function submitForm() {
    storeGame(game);
}
</script>

<style scoped>
/* Clase para agrandar el switch */
.bigger-switch {
    transform: scale(1.5);
    /* Ajusta el margen superior o inferior si es necesario */
    margin-top: 0.5rem;
}
</style>
