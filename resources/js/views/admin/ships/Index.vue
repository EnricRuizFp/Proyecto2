<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">Ships</h5>
                </div>

                <DataTable
                    v-model:filters="filters"
                    :value="ships.data"
                    paginator
                    :rows="5"
                    :globalFilterFields="[
                        'id',
                        'name',
                        'size',
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
                                    @click="getShips"
                                />
                            </template>

                            <template #end>
                                <Button
                                    v-if="can('ship-create')"
                                    icon="pi pi-external-link"
                                    label="Create Ship"
                                    @click="
                                        $router.push({ name: 'ship.create' })
                                    "
                                    class="float-end"
                                />
                            </template>
                        </Toolbar>
                    </template>

                    <template #empty> No ships where found. </template>

                    <Column field="id" header="ID" sortable />
                    <Column field="name" header="Name" sortable />
                    <Column field="size" header="Ship size" sortable />

                    <Column class="pe-0 me-0 icon-column-2">
                        <template #body="slotProps">
                            <router-link
                                v-if="can('ship-edit')"
                                :to="{
                                    name: 'ship.edit',
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
                                v-if="can('ship-delete')"
                                @click.prevent="
                                    deleteShip(
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
import { useAbility } from "@casl/vue";
import { FilterMatchMode, FilterService } from "@primevue/core/api";

// Importa tu composable para avatares (ajusta la ruta si es necesario)
import useShips from "@/composables/ships.js";

// Desestructura las funciones que necesitas de tu composable
const { ships, getShips, deleteShip } = useShips();

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
    getShips();
});
</script>

<style scoped>
/* Añade estilos personalizados si lo requieres */
</style>
