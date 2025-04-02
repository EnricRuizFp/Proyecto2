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
                    :lazy="true"
                    :totalRecords="rankings.total"
                    :rows="10"
                    :loading="isLoading"
                    @page="onPage($event)"
                    @sort="onSort($event)"
                    :first="first"
                    paginator
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
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
                                    @click="loadRankings"
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
import { ref, onMounted, watch } from "vue";
import { useAbility } from "@casl/vue";
import { FilterMatchMode } from "@primevue/core/api";
import useRankings from "@/composables/rankings.js";

const { rankings, getRankings, deleteRanking, isLoading } = useRankings();

const { can } = useAbility();

// Estado para la paginación
const first = ref(0);
const currentPage = ref(1);
const orderColumn = ref("ranking_id");
const orderDirection = ref("desc");

// Configuración de filtros
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Manejador de cambio de página
const onPage = (event) => {
    first.value = event.first;
    currentPage.value = event.page + 1;
    loadRankings();
};

// Manejador de ordenamiento
const onSort = (event) => {
    orderColumn.value = event.sortField;
    orderDirection.value = event.sortOrder === 1 ? "asc" : "desc";
    loadRankings();
};

// Función para cargar rankings
const loadRankings = async () => {
    await getRankings(
        currentPage.value,
        filters.value.global.value || "",
        orderColumn.value,
        orderDirection.value
    );
};

// Función para reiniciar los filtros
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
    loadRankings();
};

// Watch para los filtros globales
watch(
    () => filters.value.global.value,
    (newValue) => {
        currentPage.value = 1;
        first.value = 0;
        loadRankings();
    }
);

onMounted(() => {
    loadRankings();
});
</script>

<style scoped>
/* Añade estilos personalizados si lo requieres */
</style>
