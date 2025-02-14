<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">User Avatars</h5>
                </div>

                <DataTable
                    :value="userAvatars.data"
                    paginator
                    :rows="10"
                    :globalFilterFields="['id', 'user.name', 'avatar.name']"
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
                                    label="Create Assignment"
                                    icon="pi pi-plus"
                                    @click="
                                        $router.push({
                                            name: 'userAvatar.create',
                                        })
                                    "
                                />
                            </template>
                        </Toolbar>
                    </template>

                    <Column field="id" header="ID" sortable />
                    <Column header="User">
                        <template #body="slotProps">
                            <span>{{
                                slotProps.data.user
                                    ? slotProps.data.user.username
                                    : "N/A"
                            }}</span>
                        </template>
                    </Column>
                    <Column header="Avatar">
                        <template #body="slotProps">
                            <img
                                :src="slotProps.data.avatar.image_route"
                                alt="Avatar"
                                class="avatar-img"
                            />
                        </template>
                    </Column>
                    <template #empty> No user avatars where found. </template>
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import useUserAvatars from "@/composables/userAvatars.js";

const { userAvatars, getUserAvatars } = useUserAvatars();
const globalFilter = ref("");

onMounted(() => {
    getUserAvatars();
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
