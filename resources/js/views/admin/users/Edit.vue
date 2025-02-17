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
                                                <!-- Si existe avatar personalizado (diferente al default), se muestra primero -->
                                                <div
                                                    v-if="
                                                        user.avatar &&
                                                        user.avatar !==
                                                            defaultAvatar
                                                    "
                                                    class="preview-item cursor-pointer"
                                                    @click="
                                                        selectAvatarPreview({
                                                            url: user.avatar,
                                                        })
                                                    "
                                                >
                                                    <img
                                                        :src="user.avatar"
                                                        alt="Avatar personalizado"
                                                    />
                                                </div>
                                                <!-- Previews de avatares predeterminados -->
                                                <div
                                                    v-for="preset in presetAvatars"
                                                    :key="preset.id"
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
                                            </div>
                                        </div>
                                    </template>

                                    <template
                                        #content="{
                                            files,
                                            uploadedFiles,
                                            removeUploadedFileCallback,
                                            removeFileCallback,
                                        }"
                                    >
                                        <div class="content-preview">
                                            <img
                                                v-if="selectedAvatarPreview"
                                                :src="selectedAvatarPreview"
                                                alt="Avatar preview"
                                                class="object-fit-cover w-100 h-100 img-profile"
                                            />
                                            <div v-else>
                                                <img
                                                    v-if="files.length > 0"
                                                    v-for="(
                                                        file, index
                                                    ) in files"
                                                    :key="
                                                        file.name +
                                                        file.type +
                                                        file.size
                                                    "
                                                    role="presentation"
                                                    :alt="file.name"
                                                    :src="file.objectURL"
                                                    class="object-fit-cover w-100 h-100 img-profile"
                                                />
                                                <div v-else>
                                                    <img
                                                        v-if="
                                                            uploadedFiles.length >
                                                            0
                                                        "
                                                        :key="
                                                            uploadedFiles[
                                                                uploadedFiles.length -
                                                                    1
                                                            ].name +
                                                            uploadedFiles[
                                                                uploadedFiles.length -
                                                                    1
                                                            ].type +
                                                            uploadedFiles[
                                                                uploadedFiles.length -
                                                                    1
                                                            ].size
                                                        "
                                                        role="presentation"
                                                        :alt="
                                                            uploadedFiles[
                                                                uploadedFiles.length -
                                                                    1
                                                            ].name
                                                        "
                                                        :src="
                                                            uploadedFiles[
                                                                uploadedFiles.length -
                                                                    1
                                                            ].objectURL
                                                        "
                                                        class="object-fit-cover w-100 h-100 img-profile"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <template #empty>
                                        <img
                                            v-if="user.avatar"
                                            :src="user.avatar"
                                            alt="Avatar"
                                            class="object-fit-cover w-100 h-100 img-profile"
                                        />
                                        <img
                                            v-if="!user.avatar"
                                            src="https://bootdey.com/img/Content/avatar/avatar7.png"
                                            alt="Avatar Default"
                                            class="object-fit-cover w-100 h-100 img-profile"
                                        />
                                    </template>
                                </FileUpload>
                            </div>

                            <h5 class="user-name">{{ user.name }}</h5>
                            <h6 class="user-email">{{ user.email }}</h6>
                        </div>

                        <div class="about">
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

                            <div class="text-right">
                                <button
                                    :disabled="isLoading"
                                    class="btn btn-primary w-100"
                                    @click="submitForm"
                                >
                                    <div v-show="isLoading"></div>
                                    <span v-if="isLoading">Processing...</span>
                                    <span v-else>Guardar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 md:col-8 lg:col-8 xl:col-8">
            <div class="card mb-3">
                <div class="card-body">
                    {{ user.avatar }}
                    <h6 class="mb-2 text-primary">Personal Details</h6>

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
                </div>
            </div>
        </div>
    </div>

    <Toast />
</template>

<script setup>
import { onMounted, reactive, ref, watch, watchEffect } from "vue";
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
    name,
    email,
    surname1,
    surname2,
    password,
    role_id,
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

// Lógica para obtener avatares predeterminados
const presetAvatars = ref([]);
const getPresetAvatars = async () => {
    try {
        const response = await axios.get("/api/avatars");
        console.log("Response de /api/avatars:", response.data);
        // Mapear la propiedad 'data' del JSON devuelto
        presetAvatars.value = response.data.data.map((item) => ({
            id: item.id,
            url: item.image_route,
        }));
    } catch (error) {
        console.error("Error al obtener avatares predeterminados:", error);
    }
};

// Función para actualizar la previsualización y actualizar el backend si es un preset
const selectAvatarPreview = (avatar) => {
    selectedAvatarPreview.value = avatar.url;
    // Si el avatar tiene id, se asume que es un preset y se llama al endpoint para asignarlo
    if (avatar.id) {
        axios
            .post(`/api/users/assign-avatar/${user.id}`, {
                avatar_id: avatar.id,
            })
            .then((response) => {
                toast.add({
                    severity: "success",
                    summary: "Avatar asignado",
                    detail: response.data.message,
                    life: 3000,
                });
                // Actualiza el avatar del usuario según lo devuelto
                user.avatar = response.data.avatar.url;
            })
            .catch((error) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "No se pudo asignar el avatar",
                    life: 3000,
                });
                console.error(error);
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
    console.log("uploadEvent");
    totalSizePercent.value = totalSize.value / 10;
    await callback();
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
</style>
