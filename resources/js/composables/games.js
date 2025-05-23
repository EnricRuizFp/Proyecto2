import { ref, inject } from "vue";
import { useRouter } from "vue-router";

export default function useGames() {
    // Store the whole pagination response object
    const games = ref({ data: [], total: 0, current_page: 1, per_page: 10 });
    const game = ref({});
    const router = useRouter();
    const validationErrors = ref({});
    const isLoading = ref(false);
    const swal = inject("$swal");

    // Obtener listado paginado
    const getGames = async (page = 1, rows = 10) => {
        // Accept rows per page
        isLoading.value = true;
        try {
            // Pass page and potentially rows/limit to API if needed
            const response = await axios.get(
                `/api/games?page=${page}&limit=${rows}`
            );
            games.value = response.data; // Assign the whole pagination object
            console.log("API Response in composable:", response.data); // Debug log
        } catch (error) {
            console.error("Error at getting games:", error);
            // Reset on error maybe?
            games.value = {
                data: [],
                total: 0,
                current_page: 1,
                per_page: rows,
            };
        } finally {
            isLoading.value = false;
        }
    };

    // Obtener un solo game (para editar)
    const getGame = async (id) => {
        return axios
            .get(`/api/games/${id}`)
            .then((response) => {
                game.value = response.data;
                return response.data;
            })
            .catch((error) => {
                console.error("Error at getting game:", error);
                throw error;
            });
    };

    // Crear un game
    const storeGame = async (gameData) => {
        if (isLoading.value) return;
        isLoading.value = true;
        validationErrors.value = {};
        axios
            .post("/api/games", gameData)
            .then((response) => {
                router.push({ name: "game.index" });
                // Opcional: mostrar alerta de éxito
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

    // Actualizar un game
    const updateGame = async (gameData) => {
        if (isLoading.value) return;
        isLoading.value = true;
        validationErrors.value = {};

        // Suponiendo que gameData es un objeto JSON (sin archivos)
        axios
            .put(`/api/games/${gameData.id}`, gameData)
            .then((response) => {
                swal({
                    icon: "success",
                    title: "Game updated successfully",
                });
                router.push({ name: "game.index" });
            })
            .catch((error) => {
                if (error.response?.data?.errors) {
                    validationErrors.value = error.response.data.errors;
                }
                console.error("Error at updating game:", error);
            })
            .finally(() => {
                isLoading.value = false;
            });
    };

    // Eliminar un game
    const deleteGame = async (id) => {
        // Remove index parameter, it's unreliable with pagination
        console.log("Intentando borrar game con id:", id);
        swal.fire({
            title: "Are you sure?",
            text: "This action cannot be reverted!",
            icon: "warning",
            showCancelButton: true,
            cancelButtonText: "Cancel",
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#ef4444",
            timer: 20000,
            timerProgressBar: true,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .delete(`/api/games/${id}`)
                    .then((response) => {
                        swal({
                            icon: "success",
                            title: "Game successfully deleted.",
                        });
                        // Refresh the current page after delete
                        getGames(
                            games.value.current_page,
                            games.value.per_page
                        );
                    })
                    .catch((error) => {
                        console.error("Error deleting game:", error);
                        swal.fire("Error deleting game", { icon: "error" });
                    });
            } else {
                console.log("Borrado cancelado");
            }
        });
    };

    return {
        games, // Return the whole object
        game,
        getGames,
        getGame,
        storeGame,
        updateGame,
        deleteGame,
        validationErrors,
        isLoading,
    };
}
