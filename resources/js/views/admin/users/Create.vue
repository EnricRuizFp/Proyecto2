<template>
    <div class="grid">
        <!-- Columna del Avatar -->
        <div class="col-12 md:col-4 lg:col-4 xl:col-4">
            <div class="card">
                <div class="card-body">
                    <div class="account-settings">
                        <div class="user-profile">
                            <div class="user-avatar">
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
                                        }"
                                    >
                                        <div class="header-container">
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
                                                            files
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
                                            <p class="upload-text mt-0 mb-0">
                                                Drag and drop files to here to
                                                upload.
                                            </p>
                                            <div class="avatar-previews">
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

                                    <template #content="{ files }">
                                        <div class="content-preview">
                                            <img
                                                :key="previewKey"
                                                :src="activePreview"
                                                alt="Preview"
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna del Formulario - Ajustado para alinear con avatar -->
        <div class="col-12 md:col-8 lg:col-8 xl:col-8">
            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="onSubmit">
                        <!-- Username field -->
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input
                                v-model="formData.username"
                                type="text"
                                class="form-control"
                                :class="{
                                    'is-invalid': errors.username,
                                }"
                                id="username"
                                @blur="validateField('username')"
                            />
                            <div
                                v-if="errors.username"
                                class="invalid-feedback"
                            >
                                {{ errors.username }}
                            </div>
                        </div>

                        <!-- Other fields follow same pattern -->
                        <div
                            v-for="field in [
                                'name',
                                'surname1',
                                'surname2',
                                'email',
                                'password',
                            ]"
                            :key="field"
                            class="form-group"
                        >
                            <label :for="field">{{ labels[field] }}</label>
                            <input
                                v-model="formData[field]"
                                :type="getInputType(field)"
                                class="form-control"
                                :class="{ 'is-invalid': errors[field] }"
                                :id="field"
                                @blur="validateField(field)"
                            />
                            <div v-if="errors[field]" class="invalid-feedback">
                                {{ errors[field] }}
                            </div>
                        </div>

                        <!-- Nationality field -->
                        <div class="form-group">
                            <label for="nationality">Nacionalidad</label>
                            <Select
                                v-model="formData.nationality"
                                :options="continents"
                                optionLabel="label"
                                placeholder="Selecciona un continente"
                                class="w-100"
                                :class="{ 'is-invalid': errors.nationality }"
                                @blur="validateField('nationality')"
                                @change="validateField('nationality')"
                            ></Select>
                            <div
                                v-if="errors.nationality"
                                class="invalid-feedback d-block"
                            >
                                {{ errors.nationality }}
                            </div>
                        </div>

                        <!-- Role field -->
                        <div class="form-group">
                            <label for="roles">Roles</label>
                            <MultiSelect
                                v-model="formData.role_id"
                                :options="roleList"
                                optionLabel="name"
                                dataKey="id"
                                display="chip"
                                :class="{
                                    'is-invalid': errors.role_id,
                                }"
                                placeholder="Selecciona los roles"
                                class="w-100"
                                @blur="validateField('role_id')"
                                @change="validateField('role_id')"
                            />
                            <div
                                v-if="errors.role_id"
                                class="invalid-feedback d-block"
                            >
                                {{ errors.role_id }}
                            </div>
                        </div>

                        <!-- Submit button -->
                        <div class="mt-4">
                            <button
                                type="submit"
                                :disabled="isLoading"
                                class="btn btn-primary"
                            >
                                <span v-if="isLoading">Processing...</span>
                                <span v-else>Save</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <Toast position="top-right" />
    <!-- Cambiado a top-right -->
</template>

<script setup>
import { onMounted, reactive, ref, inject } from "vue";
import { useRouter } from "vue-router"; // Añadir esta importación
import useRoles from "@/composables/roles";
import useUsers from "@/composables/users";
import { useForm, defineRule } from "vee-validate";
import { required, min } from "@/validation/rules";
import { usePrimeVue } from "primevue/config";
import axios from "axios";

// Añadir estas importaciones
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";

// Añadir inject para swal
const swal = inject("$swal");

// Añadir toast para mensajes
const toast = useToast();

// Añadir antes de las otras constantes
const router = useRouter();

const { roleList, getRoleList } = useRoles();
const { storeUser, validationErrors, isLoading, uploadCustomAvatar } =
    useUsers();
const formSubmitted = ref(false);

// Define labels before they're used in the template
const labels = {
    name: "Nombre",
    surname1: "Apellido 1",
    surname2: "Apellido 2",
    email: "Email",
    password: "Password",
    nationality: "Nacionalidad",
};

const formData = reactive({
    username: "",
    name: "",
    email: "",
    password: "",
    role_id: [],
    surname1: "",
    surname2: "",
    nationality: "",
});

// Añadir los continentes como constante
const continents = [
    { label: "África", value: "africa" },
    { label: "América", value: "america" },
    { label: "Asia", value: "asia" },
    { label: "Europa", value: "europe" },
    { label: "Oceanía", value: "oceania" },
];

// Validation rules
defineRule("required", required);
defineRule("min", min);

// Modificar la configuración del formulario para validación más interactiva
const { errors, handleSubmit, validate } = useForm({
    initialValues: formData,
    validateOnBlur: true, // Validar cuando el campo pierde el foco
    validateOnChange: true, // Validar cuando cambia el valor
    validateOnInput: false, // No validar mientras se escribe
    validateOnModelUpdate: true, // Validar cuando se actualiza el v-model
});

const validationSchema = {
    username: (value) => {
        if (!value?.trim()) return "El username es requerido";
        return true;
    },
    name: (value) => {
        if (!value?.trim()) return "El nombre es requerido";
        return true;
    },
    email: (value) => {
        if (!value?.trim()) return "El email es requerido";
        const emailRegex = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
        if (!emailRegex.test(value)) return "Email inválido";
        return true;
    },
    password: (value) => {
        if (!value?.trim()) return "La contraseña es requerida";
        if (value.length < 8)
            return "La contraseña debe tener al menos 8 caracteres";
        return true;
    },
    surname1: (value) => {
        if (!value?.trim()) return "El apellido es requerido";
        return true;
    },
    role_id: (value) => {
        if (!value || value.length === 0)
            return "Debe seleccionar al menos un rol";
        return true;
    },
    nationality: (value) => {
        if (!value) return "La nacionalidad es requerida";
        return true;
    },
};

// Función para validar un campo específico
const validateField = async (field) => {
    const validator = validationSchema[field];
    if (validator) {
        const result = await validator(formData[field]);
        if (result !== true) {
            errors.value[field] = result;
        } else {
            delete errors.value[field];
        }
    }
};

function getInputType(field) {
    if (field === "password") return "password";
    if (field === "email") return "email";
    return "text";
}

const onSubmit = async () => {
    formSubmitted.value = true;

    // Validación
    const validationResult = {};
    let isValid = true;

    for (const [field, validator] of Object.entries(validationSchema)) {
        const result = await validator(formData[field]);
        if (result !== true) {
            validationResult[field] = result;
            isValid = false;
        }
    }

    if (!isValid) {
        errors.value = validationResult;
        return;
    }

    try {
        const dataToSubmit = {
            ...formData,
            role_id: formData.role_id.map((role) => role.id || role),
            avatar_id: selectedAvatarId.value,
            nationality: formData.nationality.value, // Modificar esta línea para enviar solo el valor
        };

        const response = await storeUser(dataToSubmit);

        if (response?.data?.user?.id) {
            let avatarMessage = "";

            // Manejar avatar personalizado
            if (files.value && files.value.length > 0) {
                try {
                    await uploadCustomAvatar(
                        response.data.user.id,
                        files.value[0]
                    );
                    avatarMessage = "y el avatar personalizado ha sido subido";
                } catch (avatarError) {
                    console.error(
                        "Error subiendo avatar personalizado:",
                        avatarError
                    );
                    avatarMessage = "pero hubo un error al subir el avatar";
                }
            }
            // Manejar avatar predeterminado
            else if (selectedAvatarId.value) {
                try {
                    await axios.post(
                        `/api/users/assign-avatar/${response.data.user.id}`,
                        {
                            avatar_id: selectedAvatarId.value,
                        }
                    );
                    avatarMessage =
                        "y el avatar predeterminado ha sido asignado";
                } catch (avatarError) {
                    console.error("Error asignando avatar:", avatarError);
                    avatarMessage = "pero hubo un error al asignar el avatar";
                }
            }

            // Mostrar mensaje de éxito con información del avatar
            await swal({
                icon: "success",
                title: "¡Usuario creado exitosamente!",
                text: `El usuario ha sido creado ${avatarMessage}`,
                confirmButtonText: "Aceptar",
            });

            await router.push({ name: "users.index" });
        }
    } catch (error) {
        console.error("Error creating user:", error);
        await swal({
            icon: "error",
            title: "Error al crear el usuario",
            text:
                error.response?.data?.message ||
                "Ha ocurrido un error inesperado",
        });
    }
};

// Avatar handling methods
const $primevue = usePrimeVue();
const defaultAvatar = "https://bootdey.com/img/Content/avatar/avatar7.png";
const previewKey = ref(0);
const activePreview = ref(defaultAvatar);
const defaultAvatars = ref([]);
const files = ref([]);
const selectedAvatarId = ref(null);

const getPresetAvatars = async () => {
    try {
        const response = await axios.get("/api/avatars");
        if (response.data.data) {
            defaultAvatars.value = response.data.data.filter(
                (avatar) => avatar.type === "default"
            );
            // Establecer el primer avatar por defecto si existe
            if (defaultAvatars.value.length > 0) {
                selectAvatarPreview(defaultAvatars.value[0]);
            }
        }
    } catch (error) {
        console.error("Error loading avatars:", error);
        swal({
            icon: "error",
            title: "Error",
            text: "No se pudieron cargar los avatares predeterminados",
        });
    }
};

const selectAvatarPreview = (avatar) => {
    if (!avatar?.url) return;

    activePreview.value = avatar.url;
    selectedAvatarId.value = avatar.id;
    previewKey.value++;
    files.value = [];

    // Añadir mensaje de éxito
    toast.add({
        severity: "success",
        summary: "Avatar seleccionado",
        detail: "El avatar predeterminado ha sido seleccionado correctamente",
        life: 3000,
    });
};

// FileUpload methods
const onBeforeUpload = (event) => {
    if (formData.id) {
        event.formData.append("id", formData.id);
    }
};

const onSelectedFiles = (event) => {
    files.value = event.files;
    if (event.files.length > 0) {
        const fileReader = new FileReader();
        fileReader.onload = (e) => {
            activePreview.value = e.target.result;
            previewKey.value++;
            // Limpiar el avatar predeterminado seleccionado
            selectedAvatarId.value = null;

            // Añadir mensaje de éxito
            toast.add({
                severity: "success",
                summary: "Avatar seleccionado",
                detail: "El avatar ha sido seleccionado correctamente",
                life: 3000,
            });
        };
        fileReader.readAsDataURL(event.files[0]);
    }
};

const uploadEvent = async (callback, uploadedFiles) => {
    try {
        if (!formData.id) {
            swal({
                icon: "error",
                title: "Error",
                text: "Primero debe crear el usuario para poder asignar un avatar",
            });
            return;
        }
        await callback();
    } catch (error) {
        console.error("Upload error:", error);
        swal({
            icon: "error",
            title: "Error",
            text: "Error al subir el avatar",
        });
    }
};

// Initialize data
onMounted(() => {
    getRoleList();
    getPresetAvatars();
});
</script>

<style scoped>
.form-group {
    margin-bottom: 1rem;
}
label {
    margin-bottom: 0.3rem;
}
.invalid-feedback {
    display: block;
}

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
.header-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-width: 100%;
    overflow: hidden;
}
.avatar-previews {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    align-items: center;
    max-width: 100%;
    padding-bottom: 0.5rem;
}
.preview-item {
    flex-shrink: 0;
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

/* Añadir estos estilos para mejorar el alineamiento */
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
