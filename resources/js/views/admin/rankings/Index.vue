<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">Rankings</h5>
                </div>

                <DataTable
                    v-model:filters="filters"
                    :value="rankings.data"
                    paginator
                    :rows="5"
                    :globalFilterFields="[
                        'ranking_id',
                        'user_id',
                        'wins',
                        'losses',
                        'draws',
                        'points',
                        'updated_at',
                    ]"
                    stripedRows
                    dataKey="ranking_id"
                    size="small"
                >
                    <template #header>
                        <Toolbar pt:root:class="toolbar-table">
                            <template #start>
                                <IconField>
                                    <InputIcon class="pi pi-search" />
                                    <InputText
                                        v-model="filters['global'].value"
                                        placeholder="Search"
                                    />
                                </IconField>

                                <Button
                                    type="button"
                                    icon="pi pi-filter-slash"
                                    label="Clear"
                                    class="ml-1"
                                    outlined
                                    @click="initFilters"
                                />
                                <Button
                                    type="button"
                                    icon="pi pi-refresh"
                                    class="h-100 ml-1"
                                    outlined
                                    @click="getRankings"
                                />
                            </template>

                            <template #end>
                                <Button
                                    v-if="can('ranking-create')"
                                    icon="pi pi-external-link"
                                    label="Create ranking"
                                    @click="
                                        $router.push({ name: 'ranking.create' })
                                    "
                                    class="float-end"
                                />
                            </template>
                        </Toolbar>
                    </template>

                    <template #empty> No rankings where found. </template>

                    <Column field="ranking_id" header="ID" sortable />
                    <Column header="User" sortable>
                        <template #body="slotProps">
                            <span>{{
                                slotProps.data.user
                                    ? slotProps.data.user.username
                                    : "N/A"
                            }}</span>
                        </template>
                    </Column>
                    <Column field="wins" header="Wins" sortable />
                    <Column field="losses" header="Losses" sortable />
                    <Column field="draws" header="Draws" sortable />
                    <Column field="points" header="Points" sortable />
                    <Column field="updated_at" header="Updated at" sortable />

                    <Column class="pe-0 me-0 icon-column-2">
                        <template #body="slotProps">
                            <router-link
                                v-if="can('ranking-edit')"
                                :to="{
                                    name: 'ranking.edit',
                                    params: { id: slotProps.data.ranking_id },
                                }"
                            >
                                <Button
                                    icon="pi pi-pencil"
                                    severity="info"
                                    size="small"
                                    class="mr-1"
                                />
                            </router-link>

                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                v-if="can('ranking-delete')"
                                @click.prevent="
                                    deleteRanking(
                                        slotProps.data.ranking_id,
                                        slotProps.index
                                    )
                                "
                                size="small"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useAbility } from "@casl/vue";
import { FilterMatchMode, FilterService } from "@primevue/core/api";

// Importa tu composable para avatares (ajusta la ruta si es necesario)
import useRankings from "@/composables/rankings.js";

// Desestructura las funciones que necesitas de tu composable
const { rankings, getRankings, deleteRanking } = useRankings();

const { can } = useAbility();

// Configuración de filtros
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Función para reiniciar los filtros
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
};

// Al montar el componente, cargamos la lista de avatares
onMounted(() => {
    getRankings();
});
</script>

<style scoped>
/* Añade estilos personalizados si lo requieres */
</style>
