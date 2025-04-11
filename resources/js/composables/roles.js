import { ref, inject } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";

export default function useRoles() {
    const roles = ref([]);
    const allRoles = ref([]);
    const role = ref({
        name: "",
        permissions: [],
    });
    const roleList = ref([]);
    const rolePermissionList = ref([]);
    const router = useRouter();
    const validationErrors = ref({});
    const isLoading = ref(false);
    const isLoadingRoles = ref(false);
    const swal = inject("$swal");

    const getRoles = async (
        page = 1,
        search_id = "",
        search_title = "",
        search_global = "",
        order_column = "created_at",
        order_direction = "desc"
    ) => {
        isLoadingRoles.value = true;

        try {
            console.log(`Ordering by: ${order_column} ${order_direction}`); // Log para debugging
            const response = await axios.get("/api/roles", {
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

            // Asegurarse de que roles.value tiene los datos paginados correctos
            roles.value = response.data;
        } catch (error) {
            console.error("Error fetching roles:", error);
            if (error.response) {
                console.error(
                    "Server responded with an error:",
                    error.response.data.message
                );
            } else if (error.request) {
                console.error("No response received from server");
            } else {
                console.error("Error setting up request:", error.message);
            }
        } finally {
            isLoadingRoles.value = false;
        }
    };

    const getRole = async (id) => {
        axios.get("/api/roles/" + id).then((response) => {
            role.value = response.data.data;
        });
    };
    const getRolePermissions = async (id) => {
        axios.get("/api/role-permissions/" + id).then((response) => {
            rolePermissionList.value = response.data.data;
        });
    };
    const storeRole = async (role) => {
        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};

        axios
            .post("/api/roles", role)
            .then((response) => {
                router.push({ name: "roles.index" });
                swal({
                    icon: "success",
                    title: "Role saved successfully",
                });
            })
            .catch((error) => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => (isLoading.value = false));
    };

    const updateRole = async (role) => {
        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};

        axios
            .put("/api/roles/" + role.id, role)
            .then((response) => {
                router.push({ name: "roles.index" });
                swal({
                    icon: "success",
                    title: "Role updated successfully",
                });
            })
            .catch((error) => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => (isLoading.value = false));
    };

    const updateRolePermissions = async (role, permissions) => {
        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};
        axios
            .put("/api/role-permissions", {
                permissions: JSON.stringify(permissions),
                role_id: role.id,
            })
            .catch((error) => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => {
                isLoading.value = false;
                updateRole(role);
            });
    };

    const deleteRole = async (id) => {
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
                    .delete("/api/roles/" + id)
                    .then((response) => {
                        getRoles();
                        router.push({ name: "roles.index" });
                        swal({
                            icon: "success",
                            title: "Role deleted successfully",
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

    const getRoleList = async () => {
        isLoadingRoles.value = true;
        try {
            console.log("Solicitando lista de roles...");
            const response = await axios.get("/api/roles");
            console.log("Roles recibidos:", response.data);

            if (response.data.data) {
                roleList.value = response.data.data;
            } else {
                roleList.value = response.data;
            }

            return roleList.value;
        } catch (error) {
            console.error("Error al obtener la lista de roles:", error);
            return [];
        } finally {
            isLoadingRoles.value = false;
        }
    };

    return {
        roles,
        allRoles,
        role,
        roleList,
        getRoleList,
        getRoles,
        rolePermissionList,
        getRolePermissions,
        getRole,
        storeRole,
        updateRole,
        updateRolePermissions,
        deleteRole,
        validationErrors,
        isLoading,
        isLoadingRoles,
    };
}
