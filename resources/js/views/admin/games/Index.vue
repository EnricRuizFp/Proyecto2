<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">Games</h5>
                </div>

                <DataTable
                    :value="games.data"
                    paginator
                    :rows="10"
                    :globalFilterFields="[
                        'id',
                        'code', // <-- Campo agregado
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
                                <InputText
                                    v-model="globalFilter"
                                    placeholder="Search..."
                                />
                            </template>
                            <template #end>
                                <Button
                                    label="Create Game"
                                    icon="pi pi-plus"
                                    @click="
                                        $router.push({ name: 'game.create' })
                                    "
                                />
                            </template>
                        </Toolbar>
                    </template>

                    <Column field="id" header="ID" sortable />
                    <Column field="code" header="Code" sortable />
                    <!-- Nueva columna -->
                    <Column
                        field="creation_date"
                        header="Creation Date"
                        sortable
                    />
                    <Column field="is_public" header="Public" sortable />
                    <Column field="is_finished" header="Finished" sortable />
                    <Column field="end_date" header="End Date" sortable />
                    <Column field="created_by" header="Created By" sortable />

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
                                severity="danger"
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
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import useGames from "@/composables/game.js";

const { games, getGames, deleteGame } = useGames();
const globalFilter = ref("");

onMounted(() => {
    getGames();
});
</script>

<style scoped>
/* Agrega estilos personalizados si es necesario */
</style>
