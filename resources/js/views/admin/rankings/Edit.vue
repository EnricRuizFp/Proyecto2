<template>
    <div class="row justify-content-center my-5">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2>Edit Ranking</h2>
                    <form @submit.prevent="submitForm">
                        <!-- Dropdown para seleccionar el usuario -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User</label>
                            <!-- El Dropdown queda en una línea separada -->
                            <div>
                                <Dropdown
                                    id="user_id"
                                    v-model="ranking.user_id"
                                    :options="userOptions"
                                    optionLabel="username"
                                    optionValue="id"
                                    placeholder="Select a user"
                                    filter
                                />
                            </div>
                            <div
                                class="text-danger mt-1"
                                v-for="message in validationErrors?.user_id"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                        <!-- Campos para wins, losses, draws y points -->
                        <div class="mb-3">
                            <label for="wins" class="form-label">Wins</label>
                            <input
                                v-model="ranking.wins"
                                id="wins"
                                type="number"
                                class="form-control"
                            />
                            <div
                                class="text-danger mt-1"
                                v-for="message in validationErrors?.wins"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="losses" class="form-label"
                                >Losses</label
                            >
                            <input
                                v-model="ranking.losses"
                                id="losses"
                                type="number"
                                class="form-control"
                            />
                            <div
                                class="text-danger mt-1"
                                v-for="message in validationErrors?.losses"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="draws" class="form-label">Draws</label>
                            <input
                                v-model="ranking.draws"
                                id="draws"
                                type="number"
                                class="form-control"
                            />
                            <div
                                class="text-danger mt-1"
                                v-for="message in validationErrors?.draws"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="points" class="form-label"
                                >Points</label
                            >
                            <input
                                v-model="ranking.points"
                                id="points"
                                type="number"
                                class="form-control"
                            />
                            <div
                                class="text-danger mt-1"
                                v-for="message in validationErrors?.points"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                        <div class="mt-4">
                            <button
                                type="submit"
                                :disabled="isLoading"
                                class="btn btn-primary"
                            >
                                <span v-if="isLoading">Processing...</span>
                                <span v-else>Update Ranking</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import useRankings from "@/composables/rankings.js";
import useUsers from "@/composables/users.js";
import Dropdown from "primevue/dropdown";

const route = useRoute();
const router = useRouter();
const { getRanking, updateRanking, ranking, validationErrors, isLoading } =
    useRankings();
const { users, getUsers } = useUsers();

// Cargar usuarios para el dropdown
onMounted(() => {
    getUsers();
    const id = route.params.id;
    getRanking(id).then((data) => {
        console.log("Ranking loaded for edit:", data);
        // Si el objeto retorna con clave "ranking_id" y updateRanking usa "id",
        // podemos asignar una propiedad "id" igual a "ranking_id"
        if (data.ranking_id) {
            ranking.value.id = data.ranking_id;
        }
    });
});

// Extraemos la lista de usuarios (suponiendo que la respuesta es paginada)
const userOptions = computed(() => {
    const data = Array.isArray(users.value) ? users.value : users.value.data;
    return data || [];
});

function submitForm() {
    console.log("Submitting update:", ranking.value);
    updateRanking(ranking.value);
}
</script>

<style scoped>
/* Puedes ajustar estilos según necesites */
</style>
