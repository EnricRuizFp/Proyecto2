import { ref, inject } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import axios from "axios";

export default function useUsers() {
    const users = ref([]);
    const user = ref({
        name: "",
    });

    const router = useRouter();
    const validationErrors = ref({});
    const isLoadingUsers = ref(false);
    const swal = inject("$swal");
    const toast = useToast();

    const getUsers = async (
        page = 1,
        search_id = "",
        search_title = "",
        search_global = "",
        order_column = "created_at",
        order_direction = "desc"
    ) => {
        isLoadingUsers.value = true;
        axios
            .get(
                "/api/users?page=" +
                    page +
                    "&search_id=" +
                    search_id +
                    "&search_title=" +
                    search_title +
                    "&search_global=" +
                    search_global +
                    "&order_column=" +
                    order_column +
                    "&order_direction=" +
                    order_direction
            )
            .then((response) => {
                users.value = response.data;

                // Añadir propiedades de paginación directamente al objeto users.value
                if (response.data.meta) {
                    users.value.total = response.data.meta.total;
                    users.value.current_page = response.data.meta.current_page;
                    users.value.last_page = response.data.meta.last_page;
                }
            })
            .catch((error) => {
                console.error("Error en getUsers:", error);
            })
            .finally(() => {
                isLoadingUsers.value = false;
            });
    };

    const getAllUsers = async () => {
        isLoadingUsers.value = true;
        try {
            const response = await axios.get('/api/users/all');
            users.value = response.data;
        } catch (e) {
            console.error('Error loading users:', e);
        } finally {
            isLoadingUsers.value = false;
        }
    };

    const getUsersWithTasks = async () => {
        axios.get("/api/userswithtasks").then((response) => {
            users.value = response.data;
        });
    };

    const getUser = async (id) => {
        try {
            const response = await axios.get("/api/users/" + id);
            user.value = response.data.data;
            return response.data.data;
        } catch (error) {
            console.error("Error fetching user:", error);
            throw error;
        }
    };

    const createUserDB = async (id) => {
        return axios.put("/api/users/db/create/" + id);
    };

    const deleteUserDB = async (id) => {
        return axios.put("/api/users/db/delete/" + id);
    };

    const changeUserPasswordDB = async (id) => {
        return axios.put("/api/users/db/password/" + id);
    };

    const createUserProceduredDB = async (id) => {
        return axios.put("/api/users/db/procedure/" + id);
    };

    const storeUser = async (data) => {
        isLoadingUsers.value = true;
        validationErrors.value = {};

        try {
            const formattedData = {
                ...data,
                role_id: Array.isArray(data.role_id)
                    ? data.role_id.map((role) =>
                          typeof role === "object" ? role.id : role
                      )
                    : [data.role_id],
                avatar_id: data.avatar_id || null,
            };

            const response = await axios.post("/api/users", formattedData);

            toast.add({
                severity: "success",
                summary: "Usuario creado",
                detail: "El usuario se ha creado correctamente",
                life: 3000,
            });

            if (response.data) {
                return response;
            }
        } catch (error) {
            console.error("Error al crear usuario:", error);

            if (error.response?.data?.errors) {
                validationErrors.value = error.response.data.errors;

                // Mostrar mensaje específico para errores de duplicados
                if (error.response.data.errors.username) {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "El nombre de usuario ya está en uso",
                        life: 5000,
                    });
                }
                if (error.response.data.errors.email) {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "El correo electrónico ya está registrado",
                        life: 5000,
                    });
                }
            }
            throw error;
        } finally {
            isLoadingUsers.value = false;
        }
    };

    const updateUser = async (user) => {
        if (isLoadingUsers.value) return;
        isLoadingUsers.value = true;
        validationErrors.value = {};

        try {
            // Preparar los datos
            const userData = {
                ...user,
                nationality:
                    typeof user.nationality === "object"
                        ? user.nationality.value
                        : user.nationality,
                role_id: Array.isArray(user.role_id)
                    ? user.role_id.map((role) =>
                          typeof role === "object" ? role.id : role
                      )
                    : [user.role_id],
            };

            const response = await axios.put(`/api/users/${user.id}`, userData);

            toast.add({
                severity: "success",
                summary: "Usuario actualizado",
                detail: "Los datos se han actualizado correctamente",
                life: 3000,
            });

            return response.data;
        } catch (error) {
            console.error("Error al actualizar usuario:", error);

            if (error.response?.data?.errors) {
                validationErrors.value = error.response.data.errors;

                // Mostrar errores específicos
                Object.entries(error.response.data.errors).forEach(
                    ([field, messages]) => {
                        toast.add({
                            severity: "error",
                            summary: "Error de validación",
                            detail: messages[0],
                            life: 5000,
                        });
                    }
                );
            } else {
                // Error general
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Error al actualizar el usuario",
                    life: 5000,
                });
            }

            throw error;
        } finally {
            isLoadingUsers.value = false;
        }
    };

    const deleteUser = async (id, index) => {
        try {
            const result = await swal({
                title: "Are you sure?",
                text: "You won't be able to revert this action!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                confirmButtonColor: "#ef4444",
                timer: 20000,
                timerProgressBar: true,
                reverseButtons: true,
            });

            if (result.isConfirmed) {
                const response = await axios.delete(`/api/users/${id}`);
                users.value.data.splice(index, 1);

                await swal({
                    icon: "success",
                    title: "User deleted successfully",
                });

                return response;
            }
        } catch (error) {
            console.error("Error deleting user:", error);
            await swal({
                icon: "error",
                title: "Something went wrong",
            });
            throw error;
        }
    };

    const assignAvatar = async (userId, data) => {
        try {
            const response = await axios.post(
                `/api/users/${userId}/assign-avatar`,
                data
            );
            return response.data;
        } catch (error) {
            console.error("Error assigning avatar:", error);
            throw error;
        }
    };

    const uploadCustomAvatar = async (userId, fileData) => {
        try {
            const formData = new FormData();
            formData.append("picture", fileData);
            formData.append("id", userId);

            const response = await axios.post(
                "/api/users/updateimg",
                formData,
                {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                }
            );
            return response.data;
        } catch (error) {
            console.error("Error uploading custom avatar:", error);
            throw error;
        }
    };

    return {
        users,
        user,
        getUsers,
        getAllUsers,
        getUsersWithTasks,
        getUser,
        createUserDB,
        deleteUserDB,
        changeUserPasswordDB,
        createUserProceduredDB,
        storeUser,
        updateUser,
        deleteUser,
        assignAvatar,
        uploadCustomAvatar,
        validationErrors,
        isLoadingUsers,
    };
}
