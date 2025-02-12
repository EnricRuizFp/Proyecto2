<template>
    <div class="row justify-content-center my-5">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2>Edit Game</h2>
                    <form @submit.prevent="submitForm">
                        <!-- Campo para definir si la partida es pública -->
                        <div class="mb-3">
                            <label for="is_public" class="form-label"
                                >Public Game?</label
                            >
                            <div class="form-check form-switch">
                                <input
                                    id="is_public"
                                    type="checkbox"
                                    v-model="game.is_public"
                                    class="form-check-input bigger-switch"
                                />
                            </div>
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
                                <span v-else>Update Game</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import useGames from "@/composables/games.js";

const { game, getGame, updateGame, validationErrors, isLoading } = useGames();
const route = useRoute();
const router = useRouter();

onMounted(() => {
    const id = route.params.id;
    getGame(id).then((data) => {
        // Verifica en consola los datos recibidos:
        console.log("Game loaded for edit:", data);
    });
});

function submitForm() {
    console.log("Submitting update:", game.value);
    updateGame(game.value);
}
</script>

<style scoped>
.bigger-switch {
    transform: scale(1.5);
    margin-top: 0.5rem;
}
</style>
