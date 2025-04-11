<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">Users</h5>
                </div>

                <!-- Mostrar spinner mientras se carga -->
                <div v-if="isLoadingUsers" class="text-center p-4">
                    <i
                        class="pi pi-spin pi-spinner"
                        style="font-size: 2rem"
                    ></i>
                    <p>Cargando usuarios...</p>
                </div>

                <DataTable
                    v-else
                    v-model:filters="filters"
                    :value="users.data"
                    paginator
                    :rows="10"
                    :totalRecords="users.meta?.total || 0"
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
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                    :pageLinkSize="5"
                >
                    <template #header>
                        <Toolbar pt:root:class="toolbar-table">
                            <template #start>
                                <IconField>
                                    <InputIcon class="pi pi-search" />
                                    <InputText
                                        v-model="searchTerm"
                                        placeholder="Buscar"
                                        @keyup.enter="applySearch"
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
                                    v-if="can('user-create')"
                                    icon="pi pi-external-link"
                                    label="Crear Usuario"
                                    @click="$router.push('users/create')"
                                    class="float-end"
                                />
                            </template>
                        </Toolbar>
                    </template>

                    <template #empty> No users found. </template>

                    <Column field="id" header="ID" sortable />
                    <Column header="Avatar">
                        <template #body="slotProps">
                            <div class="avatar-container">
                                <img
                                    :src="slotProps.data.avatar"
                                    :alt="slotProps.data.name + ' avatar'"
                                    class="avatar-img"
                                    @error="
                                        (e) => (e.target.src = defaultAvatar)
                                    "
                                />
                            </div>
                        </template>
                    </Column>
                    <Column field="username" header="Username" sortable />
                    <Column field="name" header="Nombre" sortable />
                    <Column field="surname1" header="Apellido1" sortable />
                    <Column field="surname2" header="Apellido2" sortable />
                    <Column field="email" header="Email" sortable />
                    <Column field="nationality" header="Nacionalidad" sortable>
                        <template #body="slotProps">
                            {{
                                capitalizeFirstLetter(
                                    slotProps.data.nationality
                                )
                            }}
                        </template>
                    </Column>
                    <Column field="created_at" header="Creado el" sortable />
                    <Column class="pe-0 me-0 icon-column-2">
                        <template #body="slotProps">
                            <router-link
                                v-if="can('user-edit')"
                                :to="{
                                    name: 'users.edit',
                                    params: { id: slotProps.data.id },
                                }"
                            >
                                <Button
                                    icon="pi pi-pencil"
                                    severity="info"
                                    size="small"
                                    class="mr-1"
                                    :loading="
                                        isLoadingAction ===
                                        `edit-${slotProps.data.id}`
                                    "
                                />
                            </router-link>
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                v-if="can('user-delete')"
                                @click.prevent="
                                    handleDelete(
                                        slotProps.data.id,
                                        slotProps.index
                                    )
                                "
                                size="small"
                                :loading="
                                    isLoadingAction ===
                                    `delete-${slotProps.data.id}`
                                "
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
import useUsers from "../../../composables/users";
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

// Variables para ordenamiento
const orderColumn = ref(null);
const orderDirection = ref(null);

// Variable para controlar qué acción específica está cargando
const isLoadingAction = ref(null);

// Computar sortOrder para PrimeVue (1 = asc, -1 = desc)
const sortOrder = computed(() => {
    return orderDirection.value === "asc" ? 1 : -1;
});

const { users, getUsers, deleteUser, isLoadingUsers } = useUsers();
const { can } = useAbility();

const defaultAvatar = "https://bootdey.com/img/Content/avatar/avatar7.png";

// Inicializar filtros
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
    searchTerm.value = "";
    first.value = 0;
    currentPage.value = 1;

    // Reseteamos el ordenamiento
    orderColumn.value = null;
    orderDirection.value = null;

    getUsers(1);
};

// Aplicar búsqueda solo cuando se presione Enter o el botón de búsqueda
const applySearch = () => {
    filters.value.global.value = searchTerm.value;
    first.value = 0;
    currentPage.value = 1;
    getUsers(
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

    getUsers(
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

    getUsers(
        currentPage.value,
        "",
        "",
        searchTerm.value,
        orderColumn.value,
        orderDirection.value
    );
};

// Agregar esta función para capitalizar la primera letra
const capitalizeFirstLetter = (string) => {
    if (!string) return "";
    return string.charAt(0).toUpperCase() + string.slice(1);
};

// Función para refrescar datos
const refreshData = () => {
    getUsers(currentPage.value);
};

// Modificar la función deleteUser para mostrar el estado de carga
const handleDelete = async (id, index) => {
    try {
        isLoadingAction.value = `delete-${id}`;
        await deleteUser(id, index);
    } finally {
        isLoadingAction.value = null;
    }
};

onMounted(() => {
    // Verificar si hay un parámetro de página en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const pageParam = parseInt(urlParams.get("page")) || 1;

    if (pageParam > 1) {
        currentPage.value = pageParam;
        first.value = (pageParam - 1) * 10; // 10 es el número de filas por página
    }

    // Llamar a getUsers sin valores de ordenamiento predeterminados
    getUsers(currentPage.value, "", "", "", null, null);
});
</script>

<style scoped>
.avatar-container {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.icon-column-2 {
    width: 100px;
    text-align: center;
}
</style>
