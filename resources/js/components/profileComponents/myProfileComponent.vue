<template>
    <div id="image&settings">
        <!-- Vista de selección de avatar -->
        <div v-if="isSelectingAvatar">
            <h3 class="edit-title h4 white-color">Seleccionar avatar</h3>
            <div class="avatar-grid">
                <!-- Avatar personalizado -->
                <div v-if="customAvatar" class="avatar-option" @click="selectAvatar(customAvatar)">
                    <img :src="customAvatar.url" alt="Avatar personalizado" class="avatar-preview">
                    <span class="preview-label">Mi Avatar</span>
                </div>
                <!-- Avatares predeterminados -->
                <div v-for="avatar in defaultAvatars" :key="avatar.id" 
                     class="avatar-option" @click="selectAvatar(avatar)">
                    <img :src="avatar.url" :alt="'Avatar ' + avatar.id" class="avatar-preview">
                </div>
            </div>
            <div class="edit-form-buttons">
                <button type="button" class="cancel-button p4" @click="toggleAvatarSelection">Cancelar</button>
            </div>
        </div>

        <div v-else>
            <!-- Vista normal -->
            <div v-if="!isEditing && !isSelectingAvatar">
                <div class="profile-image-container">
                    <div id="userImageContainer">
                        <div v-if="isLoadingAvatar" class="loading-overlay">
                            <i class="pi pi-spin pi-spinner loading-icon"></i>
                        </div>
                        <img id="userImage" 
                             :src="userData?.avatar || defaultAvatar" 
                             @click="toggleAvatarSelection"
                             @load="isLoadingAvatar = false"
                             @error="handleImageError">
                    </div>
                    <div id="editUserIcon" @click="toggleEditMode">
                        <img src="../../../../public/images/icons/settings-icon-dark.svg">
                    </div>
                </div>
            </div>

            <!-- Vista de información -->
            <div v-if="!isEditing" class="user-info-section">
                <div class="info-field">
                    <div class="field-label p4">Nombre de usuario</div>
                    <div class="field-value p4">{{ userData?.username || 'No disponible' }}</div>
                </div>

                <div class="info-field">
                    <div class="field-label p4">Correo electrónico</div>
                    <div class="field-value p4">{{ userData?.email || 'No disponible' }}</div>
                </div>

                <div class="info-field">
                    <div class="field-label p4">Nombre y apellidos</div>
                    <div class="field-value p4">{{ `${userData?.name || ''} ${userData?.surname1 || ''} ${userData?.surname2 || ''}` }}</div>
                </div>

                <div class="info-field">
                    <div class="field-label p4">Nacionalidad</div>
                    <div class="field-value p4">{{ userData?.nationality || 'No especificada' }}</div>
                </div>

                <div class="info-field">
                    <div class="field-label p4">Fecha de registro</div>
                    <div class="field-value p4">{{ userData?.created_at || 'No disponible' }}</div>
                </div>
            </div>

            <!-- Formulario de edición -->
            <div v-else class="user-info-section">
                <h3 class="edit-title h4 white-color">Editar perfil</h3>
                <form @submit.prevent="handleSubmit" class="edit-form">
                    <div class="info-field">
                        <div class="field-label p4">Nombre de usuario</div>
                        <input v-model="editForm.name" type="text" class="field-value p4">
                    </div>

                    <div class="info-field">
                        <div class="field-label p4">Correo electrónico</div>
                        <input type="email" :value="userData?.email" class="field-value p4" disabled>
                    </div>

                    <div class="info-field">
                        <div class="field-label p4">Nombre</div>
                        <input v-model="editForm.name" type="text" class="field-value p4">
                    </div>

                    <div class="info-field">
                        <div class="field-label p4">Primer apellido</div>
                        <input v-model="editForm.surname1" type="text" class="field-value p4">
                    </div>

                    <div class="info-field">
                        <div class="field-label p4">Segundo apellido</div>
                        <input v-model="editForm.surname2" type="text" class="field-value p4">
                    </div>

                    <div class="info-field">
                        <div class="field-label p4">Nacionalidad</div>
                        <select v-model="editForm.nationality" class="field-value p4">
                            <option value="">Seleccione un continente</option>
                            <option value="africa">África</option>
                            <option value="america">América</option>
                            <option value="asia">Asia</option>
                            <option value="europa">Europa</option>
                            <option value="oceania">Oceanía</option>
                        </select>
                    </div>

                    <div class="info-field">
                        <div class="field-label p4">Nueva contraseña</div>
                        <input v-model="editForm.password" type="password" class="field-value p4">
                    </div>

                    <div class="edit-form-buttons">
                        <button type="button" class="cancel-button p4" @click="toggleEditMode">Cancelar</button>
                        <button type="submit" class="primary-button p4">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <Toast />
</template>

<script setup>

/* -- IMPORTS -- */
import { onMounted, ref, watchEffect } from "vue";
import useAuth from "@/composables/auth";
import { authStore } from "../../store/auth";
import useUsers from "@/composables/users.js";
import useUserAvatars from "@/composables/userAvatars.js";
import axios from 'axios';
import { useToast } from "primevue/usetoast";

/* -- VARIABLES -- */
const { getUser, user: postData } = useUsers();
const { getUserAvatar } = useUserAvatars();
const userImage = ref();
const userData = ref(null);
const defaultAvatar = "https://bootdey.com/img/Content/avatar/avatar7.png";
const isEditing = ref(false);
const editForm = ref({
    name: '',
    surname1: '',
    surname2: '',
    nationality: '',
    password: ''
});
const isSelectingAvatar = ref(false);
const customAvatar = ref(null);
const defaultAvatars = ref([]);
const toast = useToast();
const isLoadingAvatar = ref(true);

/* -- FUNCTIONS -- */
const getPresetAvatars = async () => {
    try {
        const response = await axios.get("/api/avatars");
        if (response.data.data) {
            const avatars = response.data.data;
            customAvatar.value = avatars.find(avatar => avatar.type === 'custom');
            defaultAvatars.value = avatars.filter(avatar => avatar.type === 'default');
        }
    } catch (error) {
        console.error("Error al cargar avatares:", error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los avatares',
            life: 3000
        });
    }
};

const toggleAvatarSelection = () => {
    isSelectingAvatar.value = !isSelectingAvatar.value;
    if (isSelectingAvatar.value) {
        getPresetAvatars();
    }
};

const selectAvatar = async (avatar) => {
    if (!avatar?.url || !userData.value?.id) return;

    try {
        const response = await axios.post(`/api/users/assign-avatar/${userData.value.id}`, {
            avatar_id: avatar.id
        });

        userData.value.avatar = avatar.url;
        
        toast.add({
            severity: 'success',
            summary: 'Avatar actualizado',
            detail: 'El avatar se ha cambiado correctamente',
            life: 3000
        });

        toggleAvatarSelection();
    } catch (error) {
        console.error("Error al asignar avatar:", error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo asignar el avatar',
            life: 3000
        });
    }
};

const handleImageError = (e) => {
    isLoadingAvatar.value = false;
    e.target.src = defaultAvatar;
};

onMounted(() => {
    if(authStore().user?.id) {
        getUser(authStore().user?.id);
        getPresetAvatars();
    } else {
        console.log("No estás autenticado");
    }
});

// Observar cambios en postData
watchEffect(() => {
    if (postData.value) {
        isLoadingAvatar.value = true;  // Resetear el estado de carga
        userData.value = {
            id: postData.value.id,
            username: postData.value.username,
            email: postData.value.email,
            name: postData.value.name,
            surname1: postData.value.surname1,
            surname2: postData.value.surname2,
            role_id: postData.value.role_id,
            avatar: postData.value.avatar,
            nationality: postData.value.nationality ? postData.value.nationality.charAt(0).toUpperCase() + postData.value.nationality.slice(1) : '',
            created_at: postData.value.created_at ? new Date(postData.value.created_at).toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            }) : ''
        };
    }
});

const toggleEditMode = () => {
    if (!isEditing.value) {
        // Llenar el formulario con los datos actuales
        editForm.value = {
            name: userData.value?.name || '',
            surname1: userData.value?.surname1 || '',
            surname2: userData.value?.surname2 || '',
            nationality: userData.value?.nationality || '',
            password: ''
        };
    }
    isEditing.value = !isEditing.value;
};

const handleSubmit = async () => {
    try {
        const updateData = {
            name: editForm.value.name,
            surname1: editForm.value.surname1,
            surname2: editForm.value.surname2,
            nationality: editForm.value.nationality,
        };

        if (editForm.value.password.trim() !== '') {
            updateData.password = editForm.value.password;
        }

        // Aquí iría tu llamada a la API para actualizar el usuario
        // await updateUser(updateData);

        toast.add({
            severity: 'success',
            summary: 'Perfil actualizado',
            detail: 'Los cambios se han guardado correctamente',
            life: 3000
        });
        
        toggleEditMode();
    } catch (error) {
        console.error("Error al actualizar perfil:", error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se han podido guardar los cambios',
            life: 3000
        });
    }
};
</script>