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
        order_column = "updated_at",
        order_direction = "desc"
    ) => {
        axios
            .get(
                "/api/rankings?page=" +
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
                rankings.value = response.data;
            })
            .catch((error) => {
                console.error("Error at getting rankings:", error);
            });
    };

    /**
     * Obtiene datos de un solo avatar por ID.
     */
    const getRanking = async (id) => {
        return axios
            .get("/api/rankings/" + id)
            .then((response) => {
                ranking.value = response.data; // NO response.data.data
                return response.data;
            })
            .catch((error) => {

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

        // Si necesitas enviar archivos (por ejemplo, la imagen),
        // conviertes avatarData en FormData. Si no, puedes hacer un JSON normal.
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
                    title: "Ranking successfuly created",
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
            console.log(response.data.data);
            return response.data;
        } catch (error) {
            console.error("Error al obtener el ranking global:", error);
            return null;
        }
    };


    return {
        rankings,
        ranking,
        getRankings,
        getRanking,
        storeRanking,
        updateRanking,
        deleteRanking,
        getGlobalRanking,
        validationErrors,
        isLoading,
    };
}
