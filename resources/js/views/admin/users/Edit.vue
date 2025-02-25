<template>
    <div class="grid">
        <div class="col-12 md:col-4 lg:col-4 xl:col-4">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="account-settings">
                        <div class="user-profile">
                            <div class="user-avatar">
                                <!-- FileUpload para el avatar -->
                                <FileUpload
                                    name="picture"
                                    url="/api/users/updateimg"
                                    @before-upload="onBeforeUpload"
                                    @upload="onTemplatedUpload($event)"
                                    accept="image/*"
                                    :maxFileSize="1500000"
                                    @select="onSelectedFiles"
                                    pt:content:class="fu-content"
                                    pt:buttonbar:class="fu-header"
                                    pt:root:class="fu"
                                    class="fu"
                                >
                                    <template
                                        #header="{
                                            chooseCallback,
                                            uploadCallback,
                                            clearCallback,
                                            files,
                                            uploadedFiles,
                                        }"
                                    >
                                        <!-- Contenedor vertical para el header -->
                                        <div class="header-container">
                                            <!-- Fila de botones -->
                                            <div class="button-row flex gap-2">
                                                <Button
                                                    @click="chooseCallback()"
                                                    icon="pi pi-images"
                                                    rounded
                                                    outlined
                                                ></Button>
                                                <Button
                                                    @click="
                                                        uploadEvent(
                                                            uploadCallback,
                                                            uploadedFiles
                                                        )
                                                    "
                                                    icon="pi pi-cloud-upload"
                                                    rounded
                                                    outlined
                                                    severity="success"
                                                    :disabled="
                                                        !files ||
                                                        files.length === 0
                                                    "
                                                ></Button>
                                                <Button
                                                    @click="clearCallback()"
                                                    icon="pi pi-times"
                                                    rounded
                                                    outlined
                                                    severity="danger"
                                                    :disabled="
                                                        !files ||
                                                        files.length === 0
                                                    "
                                                ></Button>
                                            </div>
                                            <!-- Texto en su propia fila -->
                                            <p class="upload-text mt-0 mb-0">
                                                Drag and drop files to here to
                                                upload.
                                            </p>
                                            <!-- Bloque horizontal de previews -->
                                            <div class="avatar-previews">
                                                <!-- Avatar personalizado primero -->
                                                <div
                                                    v-if="customAvatar"
                                                    class="preview-item cursor-pointer"
                                                    @click="
                                                        selectAvatarPreview(
                                                            customAvatar
                                                        )
                                                    "
                                                >
                                                    <img
                                                        :src="customAvatar.url"
                                                        alt="Avatar personalizado"
                                                    />
                                                    <span class="preview-label"
                                                        >Mi Avatar</span
                                                    >
                                                </div>
                                                <!-- Avatares predeterminados después -->
                                                <template
                                                    v-for="preset in defaultAvatars"
                                                    :key="preset.id"
                                                >
                                                    <div
                                                        class="preview-item cursor-pointer"
                                                        @click="
                                                            selectAvatarPreview(
                                                                preset
                                                            )
                                                        "
                                                    >
                                                        <img
                                                            :src="preset.url"
                                                            alt="Avatar predeterminado"
                                                        />
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <template
                                        #content="{ files, uploadedFiles }"
                                    >
                                        <div class="content-preview">
                                            <img
                                                :key="previewKey"
                                                :src="activePreview"
                                                :alt="`${user.name} avatar`"
                                                class="object-fit-cover w-100 h-100 img-profile"
                                                @error="
                                                    (e) =>
                                                        (e.target.src =
                                                            defaultAvatar)
                                                "
                                            />
                                        </div>
                                    </template>
                                </FileUpload>
                            </div>
                        </div>

                        <div class="about">
                            <!-- Eliminar el botón de aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 md:col-8 lg:col-8 xl:col-8">
            <div class="card mb-3">
                <div class="card-body">
                    <!-- Username field -->
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input
                            v-model="user.username"
                            type="text"
                            class="form-control"
                            id="username"
                        />
                        <div class="text-danger mt-1">
                            {{ errors.username }}
                        </div>
                        <div class="text-danger mt-1">
                            <div
                                v-for="message in validationErrors?.username"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input
                            v-model="user.name"
                            type="text"
                            class="form-control"
                            id="name"
                        />
                        <div class="text-danger mt-1">{{ errors.name }}</div>
                        <div class="text-danger mt-1">
                            <div
                                v-for="message in validationErrors?.name"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="surname1">Apellido 1</label>
                        <input
                            v-model="user.surname1"
                            type="text"
                            class="form-control"
                            id="surname1"
                        />
                        <div class="text-danger mt-1">
                            {{ errors.surname1 }}
                        </div>
                        <div class="text-danger mt-1">
                            <div
                                v-for="message in validationErrors?.surname1"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="surname2">Apellido 2</label>
                        <input
                            v-model="user.surname2"
                            type="text"
                            class="form-control"
                            id="surname2"
                        />
                        <div class="text-danger mt-1">
                            {{ errors.surname2 }}
                        </div>
                        <div class="text-danger mt-1">
                            <div
                                v-for="message in validationErrors?.surname2"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            v-model="user.email"
                            type="email"
                            class="form-control"
                            id="email"
                        />
                        <div class="text-danger mt-1">{{ errors.email }}</div>
                        <div class="text-danger mt-1">
                            <div
                                v-for="message in validationErrors?.email"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            v-model="user.password"
                            type="password"
                            class="form-control"
                            id="password"
                        />
                        <div class="text-danger mt-1">
                            {{ errors.password }}
                        </div>
                        <div class="text-danger mt-1">
                            <div
                                v-for="message in validationErrors?.password"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nationality">Nacionalidad</label>
                        <Select
                            v-model="selectedNationality"
                            :options="continents"
                            optionLabel="label"
                            placeholder="Selecciona un continente"
                            class="w-100"
                            @change="onNationalityChange"
                        />
                        <div class="text-danger mt-1">
                            {{ errors.nationality }}
                        </div>
                        <div class="text-danger mt-1">
                            <div
                                v-for="message in validationErrors?.nationality"
                                :key="message"
                            >
                                {{ message }}
                            </div>
                        </div>
                    </div>

                    <!-- Añadir el MultiSelect de Roles aquí, antes del botón submit -->
                    <div class="form-group">
                        <label for="roles">Roles</label>
                        <MultiSelect
                            id="roles"
                            v-model="user.role_id"
                            display="chip"
                            :options="roleList"
                            optionLabel="name"
                            dataKey="id"
                            placeholder="Seleciona los roles"
                            appendTo="self"
                            class="w-100"
                        />
                    </div>

                    <!-- Modificar el div del botón para que coincida con Create.vue -->
                    <div class="mt-4">
                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="btn btn-primary"
                            @click="submitForm"
                        >
                            <span v-if="isLoading">Processing...</span>
                            <span v-else>Save</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Toast />
</template>

<script setup>
import { onMounted, reactive, ref, watch, watchEffect, nextTick } from "vue";
import { useRoute } from "vue-router";
import { usePrimeVue } from "primevue/config";
import useRoles from "@/composables/roles";
import useUsers from "@/composables/users";
import { useToast } from "primevue/usetoast";
import axios from "axios";

const $primevue = usePrimeVue();
const toast = useToast();
const { roleList, getRoleList } = useRoles();
const {
    updateUser,
    getUser,
    user: postData,
    createUserDB,
    deleteUserDB,
    changeUserPasswordDB,
    createUserProceduredDB,
    validationErrors,
    isLoading,
} = useUsers();

import { useForm, useField, defineRule } from "vee-validate";
import { required, min } from "@/validation/rules";
defineRule("required", required);
defineRule("min", min);

const schema = {
    name: "required",
    email: "required",
    password: "min:8",
};

const { validate, errors, resetForm } = useForm({ validationSchema: schema });
const { value: name } = useField("name", null, { initialValue: "" });
const { value: email } = useField("email", null, { initialValue: "" });
const { value: surname1 } = useField("surname1", null, { initialValue: "" });
const { value: surname2 } = useField("surname2", null, { initialValue: "" });
const { value: password } = useField("password", null, { initialValue: "" });
const { value: role_id } = useField("role_id", null, {
    initialValue: "",
    label: "role",
});

const user = reactive({
    username: "", // Añadir username
    name,
    email,
    surname1,
    surname2,
    password,
    role_id,
    nationality: "", // Añadir esta línea
});

const route = useRoute();
function submitForm() {
    validate().then((form) => {
        if (form.valid) updateUser(user);
    });
}

onMounted(() => {
    getRoleList();
    getUser(route.params.id);
    getPresetAvatars();
});
watchEffect(() => {
    user.id = postData.value.id;
    user.username = postData.value.username; // Añadir esta línea
    user.name = postData.value.name;
    user.email = postData.value.email;
    user.surname1 = postData.value.surname1;
    user.surname2 = postData.value.surname2;
    user.role_id = postData.value.role_id;
    user.avatar = postData.value.avatar;
});

// Variable para almacenar la URL del avatar seleccionado
const selectedAvatarPreview = ref(null);
// Cuando se selecciona un preview, se limpia el array de archivos
watch(selectedAvatarPreview, (newVal) => {
    if (newVal) files.value = [];
});

// Valor por defecto para el avatar placeholder
const defaultAvatar = "https://bootdey.com/img/Content/avatar/avatar7.png";

// Separar la lógica de avatares
const customAvatar = ref(null);
const defaultAvatars = ref([]);

// Modificar la función getPresetAvatars
const getPresetAvatars = async () => {
    try {
        const response = await axios.get("/api/avatars");
        console.log("Respuesta completa:", response.data);

        if (response.data.data) {
            const avatars = response.data.data;

            // Asegurarse que tenemos datos válidos
            if (!Array.isArray(avatars)) {
                console.error("Los avatares no son un array:", avatars);
                return;
            }

            // Filtrar avatares
            customAvatar.value = avatars.find(
                (avatar) => avatar.type === "custom"
            );
            defaultAvatars.value = avatars.filter(
                (avatar) => avatar.type === "default"
            );

            console.log("Avatar personalizado:", customAvatar.value);
            console.log("Avatares predeterminados:", defaultAvatars.value);

            // Solo establecer preview si no hay uno activo
            if (!selectedAvatarPreview.value) {
                if (user.avatar && user.avatar !== defaultAvatar) {
                    selectedAvatarPreview.value = user.avatar;
                } else if (customAvatar.value?.url) {
                    selectedAvatarPreview.value = customAvatar.value.url;
                } else if (defaultAvatars.value.length > 0) {
                    selectedAvatarPreview.value = defaultAvatars.value[0].url;
                }
            }
        }
    } catch (error) {
        console.error("Error completo:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "No se pudieron cargar los avatares: " + error.message,
            life: 3000,
        });
    }
};

// Asegurarse de que el preview se actualiza cuando cambian los avatares
watchEffect(() => {
    if (customAvatar.value && !selectedAvatarPreview.value) {
        selectedAvatarPreview.value = customAvatar.value.url;
    }
});

// Añadir ref para la URL actual del preview
const currentPreviewUrl = ref(null);

// Modificar la función selectAvatarPreview
const selectAvatarPreview = async (avatar) => {
    if (!avatar?.url || !user.id) return;

    const previousUrl = activePreview.value;

    try {
        // Actualizar inmediatamente el preview
        activePreview.value = avatar.url;
        previewKey.value++;

        const response = await axios.post(
            `/api/users/assign-avatar/${user.id}`,
            {
                avatar_id: avatar.id,
            }
        );

        // Actualizar el estado después de una respuesta exitosa
        user.avatar = avatar.url;
        files.value = [];

        toast.add({
            severity: "success",
            summary: "Avatar asignado",
            detail: response.data.message,
            life: 3000,
        });
    } catch (error) {
        // Revertir en caso de error
        activePreview.value = previousUrl;
        previewKey.value++;

        console.error("Error al asignar avatar:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "No se pudo asignar el avatar",
            life: 3000,
        });
    }
};

// Callbacks para FileUpload
const totalSize = ref(0);
const totalSizePercent = ref(0);
const files = ref([]);

const onBeforeUpload = (event) => {
    event.formData.append("id", user.id);
};
const onRemoveTemplatingFile = (file, removeFileCallback, index) => {
    removeFileCallback(index);
    totalSize.value -= parseInt(formatSize(file.size));
    totalSizePercent.value = totalSize.value / 10;
};
const onClearTemplatingUpload = (clear) => {
    clear();
    totalSize.value = 0;
    totalSizePercent.value = 0;
};
const onSelectedFiles = (event) => {
    console.log("onSelectedFiles");
    files.value = event.files;
    if (event.files.length > 1) {
        event.files = event.files.splice(0, event.files.length - 1);
    }
    files.value.forEach((file) => {
        totalSize.value += parseInt(formatSize(file.size));
    });
};
const uploadEvent = async (callback, uploadedFiles) => {
    try {
        totalSizePercent.value = totalSize.value / 10;
        await callback();

        if (uploadedFiles && uploadedFiles.length > 0) {
            const newUrl = uploadedFiles[uploadedFiles.length - 1].objectURL;
            activePreview.value = newUrl;
            user.avatar = newUrl;
            previewKey.value++;
        }

        toast.add({
            severity: "success",
            summary: "Success",
            detail: "Avatar updated successfully",
            life: 3000,
        });
    } catch (error) {
        console.error("Upload error:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: error.response?.data?.error || "Error uploading avatar",
            life: 3000,
        });
    }
};
const onTemplatedUpload = (event) => {
    // Callback para depuración, si es necesario
};
const formatSize = (bytes) => {
    const k = 1024;
    const dm = 3;
    const sizes = $primevue.config.locale.fileSizeTypes;
    if (bytes === 0) {
        return `0 ${sizes[0]}`;
    }
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    const formattedSize = parseFloat((bytes / Math.pow(k, i)).toFixed(dm));
    return `${formattedSize} ${sizes[i]}`;
};

// Modificar watchEffect para establecer el avatar actual al cargar
watchEffect(() => {
    if (postData.value) {
        user.id = postData.value.id;
        user.name = postData.value.name;
        user.email = postData.value.email;
        user.surname1 = postData.value.surname1;
        user.surname2 = postData.value.surname2;
        user.role_id = postData.value.role_id;
        user.avatar = postData.value.avatar;

        // Establecer el avatar actual como preview inicial
        if (user.avatar && user.avatar !== defaultAvatar) {
            selectedAvatarPreview.value = user.avatar;
        }
    }
});

const previewKey = ref(0); // Añadir ref para forzar re-render

// Nuevo ref para el preview activo
const activePreview = ref(null);

// Modificar el watchEffect inicial para establecer el preview
watchEffect(() => {
    if (postData.value?.avatar) {
        activePreview.value = postData.value.avatar;
    }
});

// Añadir un watch para manejar cambios en el avatar del usuario
watch(
    () => user.avatar,
    (newAvatar) => {
        if (newAvatar) {
            activePreview.value = newAvatar;
            previewKey.value++;
        }
    }
);

// Añadir los continentes como constante
const continents = [
    { label: "África", value: "africa" },
    { label: "América", value: "america" },
    { label: "Asia", value: "asia" },
    { label: "Europa", value: "europe" },
    { label: "Oceanía", value: "oceania" },
];

// Simplificar completamente el manejo de nacionalidad
const selectedNationality = ref(null);

watch(
    () => postData.value?.nationality,
    (newValue) => {
        if (newValue) {
            const matchingContinent = continents.find(
                (c) => c.value === newValue
            );
            if (matchingContinent) {
                selectedNationality.value = matchingContinent;
                user.nationality = matchingContinent.value;
            }
        }
    },
    { immediate: true }
);

const onNationalityChange = (e) => {
    if (!e?.value) return;
    user.nationality = e.value;
};

// Remover los watchEffect anteriores relacionados con nacionalidad

// Modificar el watchEffect de datos generales para excluir explícitamente nationality
watchEffect(() => {
    if (postData.value) {
        // Desestructurar para excluir nationality
        const { nationality: _, ...rest } = postData.value;
        Object.assign(user, rest);
    }
});
</script>

<style>
.fu-content {
    padding: 0px !important;
    border: 0px !important;
    border-radius: 6px;
}
.fu-header {
    border: 0px !important;
    border-radius: 6px;
}
.fu {
    display: flex;
    flex-direction: column-reverse;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}
.img-profile {
    border-top-right-radius: 6px;
    border-top-left-radius: 6px;
    aspect-ratio: 1/1;
}
.form-group {
    margin-bottom: 1rem;
}
label {
    margin-bottom: 0.3rem;
}
/* Contenedor vertical en el header */
.header-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-width: 100%;
    overflow: hidden;
}
/* Bloque horizontal de previews */
.avatar-previews {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    align-items: center;
    max-width: 100%;
    padding-bottom: 0.5rem;
}
.avatar-previews .preview-item {
    flex-shrink: 0;
}
.avatar-previews .preview-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 1px solid #ccc;
    border-radius: 50%;
}
.content-preview {
    max-width: 100%;
    overflow: hidden;
}
.preview-label {
    font-size: 0.8rem;
    color: #666;
    text-align: center;
    margin-top: 0.25rem;
}
.preview-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.preview-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 2px solid #ccc;
    border-radius: 50%;
    transition: border-color 0.3s ease;
}

.preview-item:hover img {
    border-color: #4caf50;
}

.preview-item.active img {
    border-color: #4caf50;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.3);
}

/* Añadir estos estilos para igualar alturas */
.card {
    height: 100%;
    margin: 0;
}

.card-body {
    padding: 1.5rem;
}

/* Ajustar el espaciado del formulario */
form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Opcional: hacer que el formulario ocupe todo el espacio disponible */
.form-group:last-child {
    margin-bottom: 0;
}
</style>
