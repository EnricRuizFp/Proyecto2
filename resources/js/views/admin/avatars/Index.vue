<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">Avatars</h5>
                </div>

                <DataTable
                    v-model:filters="filters"
                    :value="avatars.data"
                    paginator
                    :rows="5"
                    :globalFilterFields="['id', 'name', 'image_route']"
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
                                    @click="getAvatars"
                                />
                            </template>

                            <template #end>
                                <Button
                                    v-if="can('avatar-create')"
                                    icon="pi pi-external-link"
                                    label="Create Avatar"
                                    @click="
                                        $router.push({ name: 'avatar.create' })
                                    "
                                    class="float-end"
                                />
                            </template>
                        </Toolbar>
                    </template>

                    <template #empty> No avatars were found. </template>

                    <Column field="id" header="ID" sortable />
                    <Column field="name" header="Name" sortable />
                    <Column header="Image">
                        <template #body="slotProps">
                            <img
                                :src="slotProps.data.image_route"
                                alt="Avatar image"
                                class="avatar-img"
                            />
                        </template>
                    </Column>

                    <Column field="image_route" header="File route" sortable />
                    <Column class="pe-0 me-0 icon-column-2">
                        <template #body="slotProps">
                            <router-link
                                v-if="can('avatar-edit')"
                                :to="{
                                    name: 'avatar.edit',
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
                                v-if="can('avatar-delete')"
                                @click.prevent="
                                    deleteAvatar(
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
import { FilterMatchMode } from "@primevue/core/api";
import useAvatars from "@/composables/avatars.js";

const { avatars, getAvatars, deleteAvatar } = useAvatars();
const { can } = useAbility();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
};

// Al montar el componente, cargamos la lista de avatares
onMounted(() => {
    getAvatars();
});
</script>

<style scoped>
.avatar-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}
</style>
