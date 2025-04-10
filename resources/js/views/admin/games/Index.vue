<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">Games</h5>
                </div>

                <DataTable
                    ref="dt"
                    v-model:filters="filters"
                    v-model:sortField="sortField"
                    v-model:sortOrder="sortOrder"
                    :value="games.data"
                    paginator
                    :rows="10"
                    :globalFilterFields="[
                        'id',
                        'code',
                        'creation_date',
                        'is_public',
                        'is_finished',
                        'end_date',
                        'created_by',
                    ]"
                    stripedRows
                    dataKey="id"
                    size="small"
                >
                    <template #header>
                        <Toolbar pt:root:class="toolbar-table">
                            <template #start>
                                <IconField>
                                    <InputIcon class="pi pi-search" />
                                    <InputText
                                        v-model="filters['global'].value"
                                        placeholder="Buscar"
                                    />
                                </IconField>
                                <Button
                                    type="button"
                                    icon="pi pi-filter-slash"
                                    label="Clear"
                                    class="ml-1"
                                    outlined
                                    @click="initFilters()"
                                />
                                <Button
                                    type="button"
                                    icon="pi pi-refresh"
                                    class="h-100 ml-1"
                                    outlined
                                    @click="refreshGames()"
                                />
                            </template>
                            <template #end>
                                <Button
                                    label="Create Game"
                                    icon="pi pi-external-link"
                                    @click="
                                        $router.push({ name: 'game.create' })
                                    "
                                />
                            </template>
                        </Toolbar>
                    </template>

                    <Column field="id" header="ID" sortable />
                    <Column field="code" header="Code" sortable />
                    <Column
                        field="creation_date"
                        header="Creation Date"
                        sortable
                    />
                    <!-- Columna Public: muestra PUBLIC si true y PRIVATE si false -->
                    <Column field="is_public" header="Type" sortable>
                        <template #body="slotProps">
                            <span v-if="slotProps.data.is_public">PUBLIC</span>
                            <span v-else>PRIVATE</span>
                        </template>
                    </Column>
                    <Column field="is_finished" header="State" sortable>
                        <template #body="slotProps">
                            <span v-if="slotProps.data.is_finished"
                                >FINISHED</span
                            >
                            <span v-else>NOT FINISHED</span>
                        </template>
                    </Column>

                    <Column field="end_date" header="End Date" sortable />
                    <!-- Columna Created By: muestra el alias del creador -->
                    <Column
                        field="creator.username"
                        header="Created By"
                        sortable
                    >
                        <template #body="slotProps">
                            <span>
                                {{
                                    slotProps.data.creator &&
                                    slotProps.data.creator.username
                                        ? slotProps.data.creator.username
                                        : "N/A"
                                }}
                            </span>
                        </template>
                    </Column>
                    <!-- Columna de acciones -->
                    <Column header="Actions">
                        <template #body="slotProps">
                            <router-link
                                :to="{
                                    name: 'game.edit',
                                    params: { id: slotProps.data.id },
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
                                class="p-button-danger"
                                @click="
                                    deleteGame(
                                        slotProps.data.id,
                                        slotProps.index
                                    )
                                "
                                size="small"
                            />
                        </template>
                    </Column>
                    <template #empty>No games where found.</template>
                </DataTable>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted, watch } from "vue";
import useGames from "@/composables/games.js";
import { FilterMatchMode } from "@primevue/core/api";

const { games, getGames, deleteGame } = useGames();
const dt = ref(null); // Reference to DataTable component
const sortField = ref(null);
const sortOrder = ref(null);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Función para limpiar los filtros y ordenamiento
const initFilters = () => {
    // Reset filters
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };

    // Reset sorting manually
    sortField.value = null;
    sortOrder.value = null;

    // Reset pagination if DataTable reference exists
    if (dt.value) {
        dt.value.resetPage();
    }

    // Reload data to ensure everything is reset
    getGames();
};

// Función para refrescar la tabla
const refreshGames = () => {
    // Reiniciamos la paginación si es necesario
    getGames();
};

// Observar cambios en los filtros para aplicarlos automáticamente
watch(
    () => filters.value.global.value,
    () => {
        // Podríamos implementar filtrado en el servidor si es necesario
        // Por ahora el filtrado es local con PrimeVue
    }
);

onMounted(() => {
    getGames();
});
</script>

<style scoped>
/* Agrega estilos personalizados si es necesario */
</style>
