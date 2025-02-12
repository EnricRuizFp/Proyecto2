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
                    <Column
                        field="creation_date"
                        header="Creation Date"
                        sortable
                    />
                    <!-- Columna Public: muestra PUBLIC si true y PRIVATE si false -->
                    <Column header="Public" sortable>
                        <template #body="slotProps">
                            <span v-if="slotProps.data.is_public">PUBLIC</span>
                            <span v-else>PRIVATE</span>
                        </template>
                    </Column>
                    <Column header="Finished" sortable>
                        <template #body="slotProps">
                            <span v-if="slotProps.data.is_finished"
                                >FINISHED</span
                            >
                            <span v-else>NOT FINISHED</span>
                        </template>
                    </Column>

                    <Column field="end_date" header="End Date" sortable />
                    <!-- Columna Created By: muestra el alias del creador -->
                    <Column header="Created By" sortable>
                        <template #body="slotProps">
                            <span>
                                {{
                                    slotProps.data.creator &&
                                    slotProps.data.creator.alias
                                        ? slotProps.data.creator.alias
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
