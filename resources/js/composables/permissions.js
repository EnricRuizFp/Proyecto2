import { ref, inject } from "vue";
import { useRouter } from "vue-router";

export default function usePermissions() {
    const permissions = ref([]);
    const allPermission = ref([]);
    const permission = ref({
        name: "",
    });

    const router = useRouter();
    const validationErrors = ref({});
    const isLoading = ref(false);
    const isLoadingPermissions = ref(false);
    const swal = inject("$swal");

    const getPermissions = async (
        page = 1,
        search_id = "",
        search_title = "",
        search_global = "",
        order_column = "created_at",
        order_direction = "desc"
    ) => {
        isLoadingPermissions.value = true;

        try {
            const response = await axios.get("/api/permissions", {
                params: {
                    page,
                    per_page: 10, // Aseguramos que siempre se piden 10 elementos por página
                    search_id,
                    search_title,
                    search_global,
                    order_column,
                    order_direction,
                },
            });

            // Asegurarse de que permissions.value tiene los datos paginados correctos
            permissions.value = response.data;
        } catch (error) {
            console.error("Error fetching permissions:", error);
            if (error.response) {
                // The request was made and the server responded with a status code
                // that falls out of the range of 2xx
                console.error(
                    "Server responded with an error:",
                    error.response.data.message
                );
            } else if (error.request) {
                // The request was made but no response was received
                console.error("No response received from server");
            } else {
                // Something happened in setting up the request that triggered an Error
                console.error("Error setting up request:", error.message);
            }
        } finally {
            isLoadingPermissions.value = false;
        }
    };

    const getAllPermissions = async () => {
        axios.get("/api/permissions/").then((response) => {
            allPermission.value = response.data.data;
        });
    };
    const getPermission = async (id) => {
        axios.get("/api/permissions/" + id).then((response) => {
            permission.value = response.data.data;
        });
    };

    const storePermission = async (permission) => {
        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};

        axios
            .post("/api/permissions", permission)
            .then((response) => {
                router.push({ name: "permissions.index" });
                swal({
                    icon: "success",
                    title: "Permission saved successfully",
                });
            })
            .catch((error) => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => (isLoading.value = false));
    };

    const updatePermission = async (permission) => {
        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};

        axios
            .put("/api/permissions/" + permission.id, permission)
            .then((response) => {
                router.push({ name: "permissions.index" });
                swal({
                    icon: "success",
                    title: "Permission updated successfully",
                });
            })
            .catch((error) => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => (isLoading.value = false));
    };

    const deletePermission = async (id) => {
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
                    .delete("/api/permissions/" + id)
                    .then((response) => {
                        getPermissions(); // Cambié getRoles() a getPermissions()
                        router.push({ name: "permissions.index" });
                        swal({
                            icon: "success",
                            title: "Permission deleted successfully",
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

    return {
        permissions,
        allPermission,
        permission,
        getAllPermissions,
        getPermissions,
        getPermission,
        storePermission,
        updatePermission,
        deletePermission,
        validationErrors,
        isLoading,
        isLoadingPermissions,
    };
}
