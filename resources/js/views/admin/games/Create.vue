<template>
    <div class="row justify-content-center my-5">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2>Create Game</h2>
                    <form @submit.prevent="submitForm">
                        <!-- Campo para definir si la partida es pública -->
                        <div class="mb-3">
                            <label for="is_public" class="form-label"
                                >Public Game?</label
                            >
                            <input
                                id="is_public"
                                type="checkbox"
                                v-model="game.is_public"
                                class="form-check-input"
                            />
                            <!-- Puedes mostrar un mensaje de error si fuese necesario -->
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
import useGames from "@/composables/game.js";

const { storeGame, validationErrors, isLoading } = useGames();

// Creamos un objeto reactivo para el game. Establecemos valores por defecto.
const game = reactive({
    is_public: false, // por defecto, la partida es privada (false). Cambia según tus necesidades.
    // Los demás campos (creation_date, is_finished, end_date, created_by) se asignarán en el backend.
});

function submitForm() {
    // En este caso, no es necesario convertir a FormData porque solo se envía un dato booleano.
    // Si se agregan otros campos (por ejemplo, archivos), habría que convertirlo a FormData.
    storeGame(game);
}
</script>

<style scoped>
/* Puedes ajustar los estilos según tu proyecto */
</style>
