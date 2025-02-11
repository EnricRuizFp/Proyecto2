import { ref, inject } from "vue";
import { useRouter } from "vue-router";

export default function useAvatars() {
    const avatars = ref([]);
    const avatar = ref({
        nombre: "",
        ruta_imagen: "",
    });

    const router = useRouter();
    const validationErrors = ref({});
    const isLoading = ref(false);
    const swal = inject("$swal");

    /**
     * Obtiene la lista de avatares de la API,
     * soportando paginación o filtros adicionales si los necesitas
     */
    const getAvatars = async (
        page = 1,
        search_global = "",
        order_column = "created_at",
        order_direction = "desc"
    ) => {
        axios
            .get(
                "/api/avatars?page=" +
                    page +
                    "&search_global=" +
                    search_global +
                    "&order_column=" +
                    order_column +
                    "&order_direction=" +
                    order_direction
            )
            .then((response) => {
                // asumiendo que tu endpoint devuelve la lista con meta de paginación
                // en un atributo "data" (ajusta a tu respuesta real)
                avatars.value = response.data;
            })
            .catch((error) => {
                console.error("Error at getting avatars:", error);
            });
    };

    /**
     * Obtiene datos de un solo avatar por ID.
     */
    const getAvatar = async (id) => {
        axios
            .get("/api/avatars/" + id)
            .then((response) => {
                avatar.value = response.data.data;
            })
            .catch((error) => {
                console.error("Error at getting the avatar:", error);
            });
    };

    /**
     * Crea un nuevo avatar enviando un POST a la API.
     */
    const storeAvatar = async (avatarData) => {
        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};

        let serializedPost = new FormData();
        for (let item in avatarData) {
            if (avatarData.hasOwnProperty(item)) {
                serializedPost.append(item, avatarData[item]);
            }
        }

        axios
            .post("/api/avatars", serializedPost)
            .then((response) => {
                router.push({ name: "avatar.index" });
                swal({
                    icon: "success",
                    title: "Avatar successfuly created",
                });
            })
            .catch((error) => {
                if (error.response?.data?.errors) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => {
                isLoading.value = false;
            });
    };

    /**
     * Actualiza un avatar existente por ID.
     */
    const updateAvatar = async (avatarData) => {
        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};

        // Si actualizas también archivo (imagen), utiliza FormData;
        // en caso contrario, un JSON es suficiente
        // Ejemplo con JSON:
        axios
            .put("/api/avatars/" + avatarData.id, avatarData)
            .then((response) => {
                // router.push({ name: 'avatars.index' })
                swal({
                    icon: "success",
                    title: "Avatar updated successfuly",
                });
            })
            .catch((error) => {
                if (error.response?.data?.errors) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => {
                isLoading.value = false;
            });
    };

    /**
     * Elimina un avatar existente por ID, con confirmación.
     */
    const deleteAvatar = async (id, index) => {
        swal({
            title: "Are you sure?",
            text: "This action cannot be reverted!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#ef4444",
            timer: 20000,
            timerProgressBar: true,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .delete("/api/avatars/" + id)
                    .then((response) => {
                        // Si estás usando paginación y la respuesta está en avatars.value.data
                        // y deseas quitar el registro de la lista directamente, haz:
                        if (avatars.value.data) {
                            avatars.value.data.splice(index, 1);
                        }
                        swal({
                            icon: "success",
                            title: "Avatar successfuly deleted.",
                        });
                    })
                    .catch((error) => {
                        swal({
                            icon: "error",
                            title: "An error ocured while deleteing the avatar.",
                        });
                    });
            }
        });
    };

    return {
        avatars,
        avatar,
        getAvatars,
        getAvatar,
        storeAvatar,
        updateAvatar,
        deleteAvatar,
        validationErrors,
        isLoading,
    };
}
