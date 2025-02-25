<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">Users</h5>
                </div>

                <DataTable
                    v-model:filters="filters"
                    :value="users.data"
                    paginator
                    :rows="10"
                    :globalFilterFields="[
                        'id',
                        'username',
                        'name',
                        'surname1',
                        'surname2',
                        'email',
                        'created_at',
                        'type.name',
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
                                    @click="getUsers()"
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

                    <template #empty> No customers found. </template>

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
                                />
                            </router-link>
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                v-if="can('user-delete')"
                                @click.prevent="
                                    deleteUser(
                                        slotProps.data.id,
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
import useUsers from "../../../composables/users";
import { useAbility } from "@casl/vue";
import { FilterMatchMode } from "@primevue/core/api";

const { users, getUsers, deleteUser } = useUsers();
const { can } = useAbility();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
};

const defaultAvatar = "https://bootdey.com/img/Content/avatar/avatar7.png";

// Agregar esta función para capitalizar la primera letra
const capitalizeFirstLetter = (string) => {
    if (!string) return "";
    return string.charAt(0).toUpperCase() + string.slice(1);
};

onMounted(() => {
    getUsers();
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
</style>
