import { ref } from "vue";
import { useRouter } from "vue-router";

export default function useGames() {
    const games = ref([]);
    const game = ref({});
    const router = useRouter();
    const validationErrors = ref({});
    const isLoading = ref(false);
    // Si utilizas alertas, puedes inyectar $swal (similar a lo que haces en otros composables)
    // const swal = inject("$swal");

    const getGames = async (page = 1) => {
        isLoading.value = true;
        try {
            const response = await axios.get(`/api/games?page=${page}`);
            games.value = response.data;
        } catch (error) {
            console.error("Error at getting games:", error);
        } finally {
            isLoading.value = false;
        }
    };

    const storeGame = async (gameData) => {
        if (isLoading.value) return;
        isLoading.value = true;
        validationErrors.value = {};

        axios
            .post("/api/games", gameData)
            .then((response) => {
                // Redirige al listado de partidas al crear exitosamente
                router.push({ name: "game.index" });
                // Opcional: mostrar una alerta de éxito
                // swal({ icon: "success", title: "Game successfully created" });
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

    // Aquí podrías agregar funciones adicionales (getGame, updateGame, deleteGame, etc.)

    return {
        games,
        game,
        getGames,
        storeGame,
        validationErrors,
        isLoading,
    };
}
