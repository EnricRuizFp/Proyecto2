<template>
    <div class="row justify-content-center my-5">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2>Create Ranking</h2>
                    <form @submit.prevent="submitForm">
                        <!-- Dropdown para seleccionar el usuario -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User</label>
                            <!-- Contenedor para que el Dropdown aparezca en una línea aparte -->
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
                        <!-- Otros campos para wins, losses, draws y points -->
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
                                <span v-else>Create Ranking</span>
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
import useRankings from "@/composables/rankings.js";
import useUsers from "@/composables/users.js";
import Dropdown from "primevue/dropdown";

const { storeRanking, validationErrors, isLoading } = useRankings();
const { users, getUsers } = useUsers();

// Cargar usuarios al montar el componente
onMounted(() => {
    getUsers();
});

// Extraemos la lista de usuarios desde la respuesta paginada
const userOptions = computed(() => {
    // Si la respuesta es paginada, extraemos el array de usuarios de users.value.data
    const data = Array.isArray(users.value) ? users.value : users.value.data;
    // Devuelve el array sin modificaciones (suponiendo que cada objeto tiene "username")
    return data || [];
});

const ranking = reactive({
    user_id: null,
    wins: "",
    losses: "",
    draws: "",
    points: "",
    updated_at: "", // Se asigna en el backend
});

function submitForm() {
    storeRanking(ranking);
}
</script>

<style scoped>
/* Ajusta estilos según necesites */
</style>
