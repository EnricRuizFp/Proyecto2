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
        axios.get("/api/users/" + id).then((response) => {
            user.value = response.data.data;
            console.log(user.value);
        });
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

            if (response.data) {
                return response;
            }
        } catch (error) {
            console.error("Full error:", error); // Debug log
            console.error("Error response:", error.response?.data); // Debug log

            if (error.response?.data?.errors) {
                validationErrors.value = error.response.data.errors;
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

        axios
            .put("/api/users/" + user.id, user)
            .then((response) => {
                //router.push({name: 'users.index'})

                swal({
                    icon: "success",
                    title: "User updated successfully",
                });
            })
            .catch((error) => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => (isLoading.value = false));
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
                    .delete("/api/users/" + id)
                    .then((response) => {
                        users.value.data.splice(index, 1);

                        //getUsers()
                        //router.push({name: 'users.index'})
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
