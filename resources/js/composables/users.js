import { ref, inject } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";

export default function useUsers() {
    const users = ref([]);
    const user = ref({
        name: "",
    });

    const router = useRouter();
    const validationErrors = ref({});
    const isLoading = ref(false);
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
                console.log("Respuesta de usuarios:", response.data);
                users.value = response.data;
            })
            .catch((error) => {
                console.error("Error en getUsers:", error);
            });
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
        isLoading.value = true;
        validationErrors.value = {};

        try {
            console.log("Sending data:", data); // Debug log

            const formattedData = {
                ...data,
                role_id: Array.isArray(data.role_id)
                    ? data.role_id.map((role) =>
                          typeof role === "object" ? role.id : role
                      )
                    : [data.role_id],
                avatar_id: data.avatar_id || null,
            };

            console.log("Formatted data:", formattedData); // Debug log

            const response = await axios.post("/api/users", formattedData);
            console.log("Response:", response.data); // Debug log

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
            console.error("Full error:", error); // Debug log
            console.error("Error response:", error.response?.data); // Debug log

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
            isLoading.value = false;
        }
    };

    const updateUser = async (user) => {
        if (isLoading.value) return;
        isLoading.value = true;
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

            console.log("Sending update with data:", userData);

            const response = await axios.put(`/api/users/${user.id}`, userData);

            toast.add({
                severity: "success",
                summary: "Usuario actualizado",
                detail: "Los datos se han actualizado correctamente",
                life: 3000,
            });

            return response.data;
        } catch (error) {
            console.error("Update error:", {
                message: error.message,
                response: error.response?.data,
            });

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
            isLoading.value = false;
        }
    };

    const deleteUser = async (id, index) => {
        swal({
            title: "Are you sure?",
            text: "You won't be able to revert this action!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            confirmButtonColor: "#ef4444",
            timer: 20000,
            timerProgressBar: true,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .delete(`/api/users/${id}`)
                    .then((response) => {
                        users.value.data.splice(index, 1);
                        swal({
                            icon: "success",
                            title: "User deleted successfully",
                        });
                    })
                    .catch((error) => {
                        swal({
                            icon: "error",
                            title: "Something went wrong",
                        });
                    });
            }
        });
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
        isLoading,
    };
}
