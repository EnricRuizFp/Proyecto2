import { ref, inject } from "vue";
import { useRouter } from "vue-router";

export default function useShips() {
    const ships = ref([]);
    const ship = ref({
        name: "",
        size: "",
    });

    const router = useRouter();
    const validationErrors = ref({});
    const isLoading = ref(false);
    const swal = inject("$swal");

    /**
     * Obtiene la lista de barcos de la API,
     * soportando paginación o filtros adicionales si los necesitas
     */
    const getShips = async (page = 1) => {
        isLoading.value = true;
        try {
            const response = await axios.get(`/api/ships?page=${page}`);
            ships.value = response.data;
        } catch (error) {
            console.error("Error at getting ships: ", error);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Obtiene datos de un solo barco por ID.
     */
    const getShip = async (id) => {
        return axios
            .get("/api/ships/" + id)
            .then((response) => {
                ship.value = response.data.data;
                return ship.value;
            })
            .catch((error) => {
                console.error("Error at getting the ship:", error);
                throw error;
            });
    };

    /**
     * Creation of a new ship using POST to the API
     */
    const storeShip = async (shipData) => {

        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};

        // Si necesitas enviar archivos (por ejemplo, la imagen),
        // conviertes shipData en FormData. Si no, puedes hacer un JSON normal.
        let serializedPost = new FormData();
        for (let item in shipData) {
            if (shipData.hasOwnProperty(item)) {
                serializedPost.append(item, shipData[item]);
            }
        }

        // API request to store a ship
        axios
            .post("/api/ships", serializedPost)
            .then((response) => {
                router.push({ name: "ship.index" });
                swal({
                    icon: "success",
                    title: "Ship successfuly created.",
                });
                getShips();
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
     * Actualiza un barco existente por ID.
     */
    const updateShip = async (shipData) => {
        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};

        // Si actualizas también archivo (imagen), utiliza FormData;
        // en caso contrario, un JSON es suficiente
        // Ejemplo con JSON:
        axios
            .put("/api/ships/" + shipData.id, shipData)
            .then((response) => {
                swal({
                    icon: "success",
                    title: "Ship updated successfuly.",
                });
                router.push({ name: 'ship.index' })
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
     * Elimina un barco existente por ID, con confirmación.
     */
    const deleteShip = async (id, index) => {
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
                    .delete("/api/ships/" + id)
                    .then((response) => {
                        // Si estás usando paginación y la respuesta está en ships.value.data
                        // y deseas quitar el registro de la lista directamente, haz:
                        if (ships.value.data) {
                            ships.value.data.splice(index, 1);
                        }
                        swal({
                            icon: "success",
                            title: "Ship successfuly deleted.",
                        });
                    })
                    .catch((error) => {
                        swal({
                            icon: "error",
                            title: "An error ocured while deleting the ship.",
                        });
                    });
            }
        });
    };

    return {
        ships,
        ship,
        getShips,
        getShip,
        storeShip,
        updateShip,
        deleteShip,
        validationErrors,
        isLoading,
    };
}
