<template>
    <div class="row justify-content-center my-5">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form @submit.prevent="submitForm">
                        <!-- Campo para el nombre del avatar -->
                        <div class="mb-3">
                            <label for="avatar-name" class="form-label"
                                >Name</label
                            >
                            <input
                                v-model="avatar.name"
                                id="avatar-name"
                                type="text"
                                class="form-control"
                            />
                            <div class="text-danger mt-1">
                                <div
                                    v-for="message in validationErrors?.name"
                                    :key="message"
                                >
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Campo para seleccionar la imagen -->
                        <div class="mb-3">
                            <label for="avatar-image" class="form-label"
                                >Image</label
                            >
                            <input
                                id="avatar-image"
                                type="file"
                                class="form-control"
                                @change="handleFileChange"
                                accept="image/*"
                            />
                            <div class="text-danger mt-1">
                                <div
                                    v-for="message in validationErrors?.image"
                                    :key="message"
                                >
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Vista previa de la imagen -->
                        <div class="mb-3" v-if="previewUrl">
                            <p>Image Preview:</p>
                            <img
                                :src="previewUrl"
                                alt="Avatar preview"
                                class="avatar-img"
                            />
                        </div>

                        <!-- Botón para enviar el formulario -->
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
</template>

<script setup>
import { reactive, ref } from "vue";
import { useForm, useField, defineRule } from "vee-validate";
import { required } from "@/validation/rules";
defineRule("required", required);

// Definimos un esquema de validación simple para 'name' y 'image'
const schema = {
    name: "required",
};

// Creamos el contexto del formulario con vee-validate
const { validate, errors } = useForm({ validationSchema: schema });

// Usamos useField para el campo name (la imagen la manejaremos manualmente)
const { value: name } = useField("name", "required", { initialValue: "" });

// Creamos un objeto reactivo para el avatar
const avatar = reactive({
    name: name,
});

// Referencia para el archivo de imagen y vista previa
const imageFile = ref(null);
const previewUrl = ref("");

// Función para capturar el archivo seleccionado
function handleFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        previewUrl.value = URL.createObjectURL(file);
    } else {
        imageFile.value = null;
        previewUrl.value = "";
    }
}

// Importamos storeAvatar, validationErrors y isLoading desde el composable de avatares
import useAvatars from "@/composables/avatars.js";
const { storeAvatar, validationErrors, isLoading } = useAvatars();

function submitForm() {
    console.log("submitForm triggered");
    validate().then((result) => {
        if (result.valid && imageFile.value) {
            const avatarData = {
                name: avatar.name,
                image: imageFile.value,
            };
            console.log("Submitting avatar:", avatarData);
            storeAvatar(avatarData);
        } else {
            console.log("Validation failed or no image selected");
        }
    });
}
</script>

<style scoped>
.avatar-img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: 10px;
}
</style>
