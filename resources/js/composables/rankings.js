import { ref, inject } from "vue";
import { useRouter } from "vue-router";

export default function useRankings() {
    const rankings = ref([]);
    const ranking = ref({
        user_id: "",
        wins: "",
        losses: "",
        draws: "",
        points: "",
        updated_at: "",
    });

    const router = useRouter();
    const validationErrors = ref({});
    const isLoading = ref(false);
    const swal = inject("$swal");

    /**
     * Obtiene la lista de rankings de la API,
     * soportando paginación o filtros adicionales si los necesitas
     */
    const getRankings = async (
        page = 1,
        search_global = "",
        order_column = "ranking_id",
        order_direction = "desc"
    ) => {
        try {
            const response = await axios.get(
                "/api/rankings/admin?" +
                    new URLSearchParams({
                        page,
                        search_global,
                        order_column,
                        order_direction,
                        per_page: 10,
                    })
            );
            rankings.value = response.data;
        } catch (error) {
            console.error("Error at getting rankings:", error);
            swal({
                icon: "error",
                title: "Error",
                text: "Failed to fetch rankings",
            });
        }
    };

    /**
     * Crea un ranking inicial para un nuevo usuario.
     */
    const createInitialRanking = async (userId) => {
        const initialRanking = {
            user_id: userId,
            wins: 0,
            losses: 0,
            draws: 0,
            points: 0,
        };

        return axios
            .post("/api/rankings", initialRanking)
            .then((response) => {
                ranking.value = response.data;
                return response.data;
            })
            .catch((error) => {
                console.error("Error creating initial ranking:", error);
                throw error;
            });
    };

    /**
     * Obtiene datos de un solo avatar por ID.
     */
    const getRanking = async (id) => {
        return axios
            .get("/api/rankings/" + id)
            .then((response) => {
                ranking.value = response.data;
                return response.data;
            })
            .catch((error) => {
                if (error.response?.status === 404) {
                    // If ranking not found, create a new one
                    return createInitialRanking(id);
                }
                console.error("Error at getting the ranking:", error);
                throw error;
            });
    };

    /**
     * Crea un nuevo avatar enviando un POST a la API.
     */
    const storeRanking = async (rankingData) => {
        if (isLoading.value) return;

        isLoading.value = true;
        validationErrors.value = {};

        let serializedPost = new FormData();
        for (let item in rankingData) {
            if (rankingData.hasOwnProperty(item)) {
                serializedPost.append(item, rankingData[item]);
            }
        }

        axios
            .post("/api/rankings", serializedPost)
            .then((response) => {
                router.push({ name: "ranking.index" });
                swal({
                    icon: "success",
                    title: "¡Ranking creado!",
                    text: "El ranking ha sido creado correctamente",
                    showConfirmButton: false,
                    timer: 1500,
                    background: "#1a1a1a",
                    customClass: {
                        title: "swal-title",
                        content: "swal-text",
                        popup: "swal-popup",
                    },
                });
            })
            .catch((error) => {
                if (error.response?.data?.errors) {
                    validationErrors.value = error.response.data.errors;
                    swal({
                        icon: "error",
                        title: "Error al crear el ranking",
                        text: "Ha ocurrido un problema al crear el ranking",
                        showConfirmButton: true,
                        background: "#1a1a1a",
                        customClass: {
                            title: "swal-title",
                            content: "swal-text",
                            popup: "swal-popup",
                            confirmButton: "swal-button",
                        },
                    });
                }
            })
            .finally(() => {
                isLoading.value = false;
            });
    };

    /**
     * Crea un nuevo ranking silenciosamente.
     */
    const storeRankingSilently = async (rankingData) => {
        if (isLoading.value) return;
        isLoading.value = true;
        validationErrors.value = {};

        try {
            const response = await axios.post("/api/rankings", rankingData);
            return response.data;
        } catch (error) {
            if (error.response?.data?.errors) {
                validationErrors.value = error.response.data.errors;
            }
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Actualiza un ranking existente por ID.
     */
    const updateRanking = async (rankingData) => {
        if (isLoading.value) return;
        isLoading.value = true;
        validationErrors.value = {};

        axios
            .put("/api/rankings/" + rankingData.ranking_id, rankingData)
            .then((response) => {
                swal.fire({
                    icon: "success",
                    title: "Ranking updated successfully",
                });
                router.push({ name: "ranking.index" });
            })
            .catch((error) => {
                if (error.response?.data?.errors) {
                    validationErrors.value = error.response.data.errors;
                }
                console.error("Error at updating ranking:", error);
            })
            .finally(() => {
                isLoading.value = false;
            });
    };

    /**
     * Elimina un ranking existente por ID, con confirmación.
     */
    const deleteRanking = async (id, index) => {
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
                    .delete("/api/rankings/" + id)
                    .then((response) => {
                        // Si estás usando paginación y la respuesta está en avatars.value.data
                        // y deseas quitar el registro de la lista directamente, haz:
                        if (rankings.value.data) {
                            rankings.value.data.splice(index, 1);
                        }
                        swal({
                            icon: "success",
                            title: "Ranking successfuly deleted.",
                        });
                    })
                    .catch((error) => {
                        swal({
                            icon: "error",
                            title: "An error ocured while deleteing the ranking.",
                        });
                    });
            }
        });
    };

    const getGlobalRanking = async (limit) => {
        try {
            const response = await axios.get(
                `/api/rankings?limit=${limit}&order_column=points&order_direction=desc`
            );
            console.log("Global Ranking Data:", {
                total: response.data.data.length,
                rankingData: response.data.data.map((user) => ({
                    id: user.user_id,
                    points: user.points,
                })),
            });
            return response.data;
        } catch (error) {
            console.error("Error al obtener el ranking global:", error);
            return null;
        }
    };

    const getNationalRanking = async (limit) => {
        try {
            const response = await axios.get(
                `/api/rankings/national?limit=${limit}`
            );
            console.log("National Ranking Data:", response.data);
            if (response.data.status === "success") {
                return response.data;
            } else {
                throw new Error(
                    response.data.message ||
                        "Unknown error fetching national ranking"
                );
            }
        } catch (error) {
            console.error("Error al obtener el ranking nacional:", error);
            if (error.response?.status === 401) {
                throw new Error("Please log in to view national rankings");
            } else if (error.response?.status === 404) {
                throw new Error("National ranking endpoint not found");
            }
            throw error;
        }
    };

    return {
        rankings,
        ranking,
        getRankings,
        getRanking,
        storeRanking,
        storeRankingSilently,
        updateRanking,
        deleteRanking,
        getGlobalRanking,
        getNationalRanking,
        validationErrors,
        isLoading,
    };
}
