<template>
    <div class="row justify-content-center my-5">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form @submit.prevent="submitForm">
                        <!-- Campo para el nombre -->
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

                        <!-- Campo para cambiar la imagen (opcional) -->
                        <div class="mb-3">
                            <label for="avatar-image" class="form-label"
                                >Image (optional)</label
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

                        <!-- Vista previa de la nueva imagen o mostrar la existente -->
                        <div class="mb-3">
                            <p>Image Preview:</p>
                            <img
                                :src="previewUrl || avatar.image_route"
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
                                <span v-else>Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import useAvatars from "@/composables/avatars.js";

const route = useRoute();
const router = useRouter();

const { getAvatar, updateAvatar, validationErrors, isLoading } = useAvatars();

// Creamos un objeto reactivo para el avatar
const avatar = reactive({
    id: null,
    name: "",
    image_route: "",
});

// Referencia para el archivo de imagen nuevo y la vista previa
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

// Cargar el avatar existente usando el id de la ruta
onMounted(() => {
    const id = route.params.id;
    getAvatar(id).then((data) => {
        // Suponiendo que getAvatar guarda los datos en avatar.value, copia los datos
        avatar.id = data.id;
        avatar.name = data.name;
        avatar.image_route = data.image_route;
    });
});

function submitForm() {
    // Crea un objeto que incluya el id para la actualización
    const avatarData = {
        id: avatar.id,
        name: avatar.name,
    };
    // Si se ha seleccionado un nuevo archivo, inclúyelo
    if (imageFile.value) {
        avatarData.image = imageFile.value;
    }
    updateAvatar(avatarData).then(() => {
        // Una vez actualizado, redirige al listado de avatares
        router.push({ name: "avatar.index" });
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
