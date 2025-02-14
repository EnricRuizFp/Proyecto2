import { ref, inject } from "vue";
import { useRouter } from "vue-router";

export default function useUserAvatars() {
    const userAvatars = ref([]);
    const userAvatar = ref({});
    const router = useRouter();
    const validationErrors = ref({});
    const isLoading = ref(false);
    const swal = inject("$swal");

    /**
     * Obtiene el listado paginado de asignaciones.
     */
    const getUserAvatars = async (page = 1) => {
        isLoading.value = true;
        try {
            const response = await axios.get(`/api/user-avatars?page=${page}`);
            userAvatars.value = response.data;
        } catch (error) {
            console.error("Error getting user avatars:", error);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Obtiene una asignación específica.
     */
    const getUserAvatar = async (id) => {
        return axios
            .get(`/api/user-avatars/${id}`)
            .then((response) => {
                userAvatar.value = response.data;
                return response.data;
            })
            .catch((error) => {
                console.error("Error getting user avatar:", error);
                throw error;
            });
    };

    /**
     * Crea una nueva asignación de avatar a usuario.
     */
    const storeUserAvatar = async (data) => {
        if (isLoading.value) return;
        isLoading.value = true;
        validationErrors.value = {};
        axios
            .post("/api/user-avatars", data)
            .then((response) => {
                router.push({ name: "userAvatar.index" });
                swal.fire({
                    icon: "success",
                    title: "User avatar assigned successfully",
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
     * Actualiza una asignación de avatar.
     */
    const updateUserAvatar = async (data) => {
        if (isLoading.value) return;
        isLoading.value = true;
        validationErrors.value = {};

        axios
            .put("/api/user-avatars/" + data.id, data)
            .then((response) => {
                swal.fire({
                    icon: "success",
                    title: "User avatar assignment updated successfully",
                });
                router.push({ name: "userAvatar.index" });
            })
            .catch((error) => {
                if (error.response?.data?.errors) {
                    validationErrors.value = error.response.data.errors;
                }
                console.error("Error updating user avatar:", error);
            })
            .finally(() => {
                isLoading.value = false;
            });
    };

    /**
     * Elimina una asignación de avatar.
     */
    const deleteUserAvatar = async (id, index) => {
        swal.fire({
            title: "Are you sure?",
            text: "This action cannot be reverted!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#ef4444",
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .delete(`/api/user-avatars/${id}`)
                    .then((response) => {
                        // Si estás usando paginación, recarga la lista
                        getUserAvatars();
                        swal.fire({
                            icon: "success",
                            title: "User avatar assignment deleted successfully",
                        });
                    })
                    .catch((error) => {
                        console.error("Error deleting user avatar:", error);
                        swal.fire({
                            icon: "error",
                            title: "Error deleting user avatar",
                        });
                    });
            }
        });
    };

    return {
        userAvatars,
        userAvatar,
        getUserAvatars,
        getUserAvatar,
        storeUserAvatar,
        updateUserAvatar,
        deleteUserAvatar,
        validationErrors,
        isLoading,
    };
}
