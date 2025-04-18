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
                    :value="games.data"
                    paginator
                    :rows="10"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                    currentPageReportTemplate="&nbsp;"
                    :rowsPerPageOptions="[10, 20, 50]"
                    lazy
                    :totalRecords="games.total"
                    @page="onPage($event)"
                    :sortField="lazyParams.sortField"
                    :sortOrder="lazyParams.sortOrder"
                    @sort="onSort($event)"
                    dataKey="id"
                    size="small"
                    stripedRows
                    :globalFilterFields="[
                        'id',
                        'code',
                        'creation_date',
                        'is_public',
                        'is_finished',
                        'end_date',
                        'created_by',
                    ]"
                >
                    <template #header>
                        <Toolbar pt:root:class="toolbar-table">
                            <template #start>
                                <IconField>
                                    <InputIcon class="pi pi-search" />
                                    <InputText
                                        v-model="filters['global'].value"
                                        placeholder="Buscar"
                                        @keydown.enter="onFilter"
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
                                @click="deleteGame(slotProps.data.id)"
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
import { ref, onMounted } from "vue";
import useGames from "@/composables/games.js";
import { FilterMatchMode } from "@primevue/core/api";

const { games, getGames, deleteGame, isLoading } = useGames();
const dt = ref(null);

const lazyParams = ref({
    first: 0,
    rows: 10,
    page: 1,
    sortField: null,
    sortOrder: null,
    filters: {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    },
});

const filters = ref(lazyParams.value.filters);

const loadLazyData = () => {
    getGames(lazyParams.value);
};

const onPage = (event) => {
    lazyParams.value.page = event.page + 1;
    lazyParams.value.rows = event.rows;
    lazyParams.value.first = event.first;
    loadLazyData();
};

const onSort = (event) => {
    lazyParams.value.sortField = event.sortField;
    lazyParams.value.sortOrder = event.sortOrder;
    loadLazyData();
};

const onFilter = () => {
    lazyParams.value.page = 1;
    lazyParams.value.first = 0;
    loadLazyData();
};

onMounted(() => {
    loadLazyData();
});

const initFilters = () => {
    lazyParams.value.filters.global.value = null;
    lazyParams.value.sortField = null;
    lazyParams.value.sortOrder = null;
    lazyParams.value.page = 1;
    lazyParams.value.first = 0;
    if (dt.value) {
        dt.value.state.first = 0;
        dt.value.state.page = 0;
    }
    loadLazyData();
};

const refreshGames = () => {
    loadLazyData();
};
</script>
