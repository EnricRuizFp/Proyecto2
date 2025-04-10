<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">Permissions</h5>
                </div>

                <!-- Mostrar spinner mientras se carga -->
                <div v-if="isLoadingPermissions" class="text-center p-4">
                    <i
                        class="pi pi-spin pi-spinner"
                        style="font-size: 2rem"
                    ></i>
                    <p>Cargando permisos...</p>
                </div>

                <DataTable
                    v-else
                    v-model:filters="filters"
                    :value="permissions.data || []"
                    paginator
                    :rows="10"
                    :totalRecords="permissions.total || 0"
                    :rowsPerPageOptions="null"
                    stripedRows
                    dataKey="id"
                    size="small"
                    v-model:first="first"
                    :sortField="orderColumn"
                    :sortOrder="sortOrder"
                    @sort="onSort"
                    @page="onPageChange($event)"
                    :lazy="true"
                    @filter="onFilter"
                >
                    <template #header>
                        <Toolbar pt:root:class="toolbar-table">
                            <template #start>
                                <IconField>
                                    <InputIcon class="pi pi-search" />
                                    <InputText
                                        v-model="searchTerm"
                                        placeholder="Search"
                                        @keyup.enter="applySearch"
                                    />
                                </IconField>

                                <Button
                                    type="button"
                                    icon="pi pi-filter-slash"
                                    label="Clear"
                                    class="ml-1"
                                    outlined
                                    @click="clearFilters"
                                />
                                <Button
                                    type="button"
                                    icon="pi pi-search"
                                    class="ml-1"
                                    outlined
                                    @click="applySearch"
                                />
                                <Button
                                    type="button"
                                    icon="pi pi-refresh"
                                    class="h-100 ml-1"
                                    outlined
                                    @click="refreshData"
                                />
                            </template>

                            <template #end>
                                <Button
                                    v-if="can('permission-create')"
                                    icon="pi pi-plus"
                                    label="Create Permission"
                                    @click="
                                        $router.push({
                                            name: 'permissions.create',
                                        })
                                    "
                                    class="float-end"
                                />
                            </template>
                        </Toolbar>
                    </template>

                    <template #empty> No permissions were found. </template>

                    <Column field="id" header="ID" sortable />
                    <Column field="name" header="Title" sortable />
                    <Column field="created_at" header="Created at" sortable />
                    <Column class="pe-0 me-0 icon-column-2">
                        <template #body="slotProps">
                            <router-link
                                v-if="can('permission-edit')"
                                :to="{
                                    name: 'permissions.edit',
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
                                severity="danger"
                                v-if="can('permission-delete')"
                                @click.prevent="
                                    deletePermission(slotProps.data.id)
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
import { ref, onMounted, computed } from "vue";
import usePermissions from "@/composables/permissions";
import { useAbility } from "@casl/vue";
import { FilterMatchMode } from "@primevue/core/api";

// Variables para filtros
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Variable separada para el término de búsqueda
const searchTerm = ref("");
// Control de la página actual
const currentPage = ref(1);
const first = ref(0); // Posición del primer registro en la página actual

// Variables para ordenamiento - ya no hay valores predeterminados
const orderColumn = ref(null);
const orderDirection = ref(null);

// Computar sortOrder para PrimeVue (1 = asc, -1 = desc)
const sortOrder = computed(() => {
    return orderDirection.value === "asc" ? 1 : -1;
});

const { permissions, getPermissions, deletePermission, isLoadingPermissions } =
    usePermissions();
const { can } = useAbility();

// Inicializar filtros
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
    searchTerm.value = "";
    first.value = 0;
    currentPage.value = 1;

    // Reseteamos el ordenamiento sin valores predeterminados
    orderColumn.value = null;
    orderDirection.value = null;

    getPermissions(1);
};

// Aplicar búsqueda solo cuando se presione Enter o el botón de búsqueda
const applySearch = () => {
    filters.value.global.value = searchTerm.value;
    first.value = 0;
    currentPage.value = 1;
    getPermissions(
        1,
        "",
        "",
        searchTerm.value,
        orderColumn.value,
        orderDirection.value
    );
};

// Manejar cambio de página
const onPageChange = (event) => {
    first.value = event.first;
    currentPage.value = event.page + 1;

    getPermissions(
        currentPage.value,
        "",
        "",
        searchTerm.value,
        orderColumn.value,
        orderDirection.value
    );
};

// Manejar filtrado (deshabilitado para evitar búsqueda mientras escribe)
const onFilter = (event) => {
    // No hacer nada para evitar búsquedas mientras se escribe
};

// Función para ordenamiento
const onSort = (event) => {
    orderColumn.value = event.sortField;
    orderDirection.value = event.sortOrder === 1 ? "asc" : "desc";

    getPermissions(
        currentPage.value,
        "",
        "",
        searchTerm.value,
        orderColumn.value,
        orderDirection.value
    );
};

// Cargar datos al montar el componente - sin filtros predeterminados
onMounted(() => {
    // Verificar si hay un parámetro de página en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const pageParam = parseInt(urlParams.get("page")) || 1;

    if (pageParam > 1) {
        currentPage.value = pageParam;
        first.value = (pageParam - 1) * 10; // 10 es el número de filas por página
    }

    // Llamar a getPermissions sin valores de ordenamiento predeterminados
    getPermissions(currentPage.value, "", "", "", null, null);
});

// Función para refrescar datos
const refreshData = () => {
    getPermissions(currentPage.value);
};

// Función para limpiar filtros
const clearFilters = () => {
    initFilters();
};
</script>

<style scoped>
.icon-column-2 {
    width: 100px;
    text-align: center;
}
</style>
