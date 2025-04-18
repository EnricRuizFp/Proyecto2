<template>
    <div class="rankings-page-container">
        <div class="container pt-4">
            <!-- Tarjeta única para todo el contenido de los rankings -->
            <div class="row flex-grow-1">
                <div class="col-12 d-flex flex-column">
                    <div
                        class="card app-background-primary white-border ranking-card flex-grow-1"
                    >
                        <div class="card-body d-flex flex-column">
                            <!-- Sección de resumen del ranking del usuario -->
                            <div class="user-ranking-container mb-4">
                                <h3 class="section-title h4 white-color">
                                    Ranking
                                </h3>
                                <div class="ranking-overview-container">
                                    <!-- Puntuación unificada (primero) -->
                                    <div class="points-display">
                                        <span class="stats-value"
                                            >{{ userPoints ?? "NA" }} pts</span
                                        >
                                    </div>

                                    <!-- Posición Global -->
                                    <div class="ranking-box">
                                        <div class="h4">Global</div>
                                        <div class="rank-number">
                                            <template v-if="globalPosition">
                                                <span
                                                    class="rank-symbol"
                                                    :class="
                                                        getRankingColor(
                                                            globalPosition
                                                        )
                                                    "
                                                    >#</span
                                                >
                                                <span
                                                    :class="
                                                        getRankingColor(
                                                            globalPosition
                                                        )
                                                    "
                                                    >{{ globalPosition }}</span
                                                >
                                            </template>
                                            <span v-else>NA</span>
                                        </div>
                                    </div>

                                    <!-- Posición Nacional -->
                                    <div class="ranking-box">
                                        <div class="h4">Nacional</div>
                                        <div class="rank-number">
                                            <template v-if="nationalPosition">
                                                <span
                                                    class="rank-symbol"
                                                    :class="
                                                        getRankingColor(
                                                            nationalPosition
                                                        )
                                                    "
                                                    >#</span
                                                >
                                                <span
                                                    :class="
                                                        getRankingColor(
                                                            nationalPosition
                                                        )
                                                    "
                                                    >{{
                                                        nationalPosition
                                                    }}</span
                                                >
                                            </template>
                                            <span v-else>NA</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Línea separadora -->
                            <hr class="ranking-separator my-4" />

                            <!-- Listas de Rankings Global y Nacional -->
                            <div
                                v-if="loading"
                                class="loading-container flex-grow-1"
                            >
                                <div
                                    class="spinner-border text-light"
                                    role="status"
                                >
                                    <span class="visually-hidden"
                                        >Cargando...</span
                                    >
                                </div>
                            </div>
                            <div
                                v-else-if="error"
                                class="alert alert-danger text-center flex-grow-1 d-flex flex-column justify-content-center"
                            >
                                {{ error }}
                                <button
                                    @click="loadRankings"
                                    class="btn btn-outline-light mt-2"
                                >
                                    Intentar de nuevo
                                </button>
                            </div>
                            <div v-else class="rankings-container flex-grow-1">
                                <!-- Ranking Global (Izquierda) -->
                                <div class="ranking-section global-ranking">
                                    <GlobalRankingComponent
                                        :ranking-data="globalRanking"
                                    />
                                </div>

                                <!-- Ranking Nacional (Derecha) -->
                                <div class="ranking-section national-ranking">
                                    <NationalRankingComponent
                                        :ranking-data="nationalRanking || []"
                                        :nationality="
                                            userNationality || 'Desconocida'
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import GlobalRankingComponent from "../../components/playComponents/globalRankingComponent.vue";
import NationalRankingComponent from "../../components/playComponents/nationalRankingComponent.vue";
import useRankings from "../../composables/rankings";
import axios from "axios";

// Variables reactivas para los rankings
const globalRanking = ref([]);
const nationalRanking = ref([]);
const userNationality = ref("");
const loading = ref(true); // Indica si se están cargando los datos
const error = ref(null); // Almacena mensajes de error

// Variables reactivas para los datos del usuario
const globalPosition = ref(null);
const nationalPosition = ref(null);
const userPoints = ref(null);
const userId = ref(null);

// Importamos el composable de rankings para reutilizar lógica
const { getGlobalRanking, getNationalRanking } = useRankings();

// Función para obtener la posición global del usuario desde la API
const getGlobalPosition = async () => {
    try {
        const response = await axios.get("/api/rankings/global-position");
        globalPosition.value = response.data.position;
    } catch (error) {
        console.error(
            "Error al obtener la posición global del usuario:",
            error
        );
        globalPosition.value = null; // En caso de error, dejamos la posición como nula
    }
};

// Función para obtener la posición nacional del usuario desde la API
const getNationalPosition = async () => {
    try {
        const response = await axios.get("/api/rankings/national-position");
        nationalPosition.value = response.data.position;
    } catch (error) {
        console.error(
            "Error al obtener la posición nacional del usuario:",
            error
        );
        nationalPosition.value = null; // En caso de error, dejamos la posición como nula
    }
};

// Función para obtener los puntos del usuario desde la API
const getUserPoints = async () => {
    try {
        const response = await axios.get("/api/rankings/user-points");
        userPoints.value = response.data.points;
    } catch (error) {
        console.error("Error al obtener los puntos del usuario:", error);
        userPoints.value = "NA"; // Mostramos 'NA' si hay un error
    }
};

// Función para determinar el color según la posición en el ranking
const getRankingColor = (position) => {
    switch (position) {
        case 1:
            return "rank-gold"; // Oro para el primero
        case 2:
            return "rank-silver"; // Plata para el segundo
        case 3:
            return "rank-bronze"; // Bronce para el tercero
        default:
            return ""; // Sin color especial para el resto
    }
};

// Función principal para cargar los datos de los rankings
const loadRankings = async () => {
    try {
        loading.value = true; // Activamos el indicador de carga
        error.value = null; // Reseteamos cualquier error previo

        // Cargamos el ranking global (top 10)
        const globalData = await getGlobalRanking(10);
        if (globalData) {
            globalRanking.value = globalData.data;
        }

        // Intentamos cargar el ranking nacional (top 10)
        try {
            const nationalData = await getNationalRanking(10);
            // Verificamos que la respuesta sea exitosa y contenga datos
            if (nationalData && nationalData.status === "success") {
                nationalRanking.value = nationalData.data;
                userNationality.value = nationalData.user_nationality; // Guardamos la nacionalidad

                // Log para depuración
                console.log("Rankings cargados:", {
                    global: globalRanking.value?.length || 0,
                    national: nationalRanking.value?.length || 0,
                    nationality: userNationality.value,
                });
            }
        } catch (nationalErr) {
            console.error("Error al cargar el ranking nacional:", nationalErr);
            // Mensaje de error específico si falla el ranking nacional
            error.value =
                "No se pudo cargar el ranking nacional. Puede que necesites iniciar sesión o configurar tu nacionalidad.";
            // Continuamos aunque falle el nacional, para mostrar el global
        }
    } catch (err) {
        console.error("Error general al cargar los rankings:", err);
        error.value =
            "No se pudieron cargar los rankings. Inténtalo más tarde.";
    } finally {
        loading.value = false; // Desactivamos el indicador de carga, haya éxito o error
    }
};

// Hook del ciclo de vida: se ejecuta cuando el componente se monta en el DOM
onMounted(async () => {
    // Intentamos obtener los datos básicos del usuario (como el ID)
    try {
        const user = await axios.get("/api/user");
        userId.value = user.data.id;
    } catch (error) {
        console.error("Error al obtener los datos del usuario:", error);
        // No es crítico si esto falla, pero lo registramos
    }

    // Cargamos los datos específicos del usuario (posición, puntos)
    // Estas llamadas no necesitan `await` aquí si no dependen entre sí
    // y queremos que se inicien en paralelo.
    getGlobalPosition();
    getNationalPosition();
    getUserPoints();

    // Finalmente, cargamos las listas de rankings
    loadRankings();
});
</script>

<style scoped>
.rankings-page-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    padding-top: 100px;
    background-color: var(--background-primary);
}

.container {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.rankings-page-container .points-display {
    margin-top: 0 !important;
}

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 15px;
}

.card.ranking-card {
    min-height: 400px;
    display: flex;
    flex-direction: column;
}

.rankings-container {
    display: flex;
    flex-direction: row;
    width: 100%;
    gap: 20px;
}

.ranking-section {
    flex: 1;
    padding: 0;
    display: flex;
    flex-direction: column;
}

.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 350px;
    width: 100%;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

.alert {
    border-radius: 10px;
}

.flex-grow-1 {
    flex-grow: 1;
}

.user-ranking-container {
    width: 100%;
    padding-bottom: 0.5rem;
}

.section-title {
    margin-bottom: 1.5rem;
    color: var(--white-color);
    font-size: 1.8rem;
    text-align: left;
}

.ranking-overview-container {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 1rem;
    min-height: 120px;
    background-color: transparent;
    border: none;
    padding: 0;
}

.points-display,
.ranking-box {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 1.5rem;
    border-radius: 10px;
    transition: transform 0.2s ease-in-out;
}

.points-display {
    background-color: rgba(255, 255, 255, 0.05);
    margin-top: 0;
    border-top: none !important;
}

.ranking-box {
    background-color: rgba(255, 255, 255, 0.05);
}

.rankings-page-container .points-display {
    margin-top: 0 !important;
}

.points-display:hover,
.ranking-box:hover {
    transform: translateY(-5px);
}

.ranking-box .h4 {
    color: var(--white-color);
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.rank-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--white-color);
}

.stats-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--white-color);
}

.ranking-separator {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    width: 100%;
    margin: 1.5rem 0;
}

:deep(#globalRankingMenuContainer),
:deep(#nationalRankingMenuContainer) {
    padding: 1rem 0.5rem;
}

:deep(#globalRankingMenuTitle),
:deep(#nationalRankingMenuTitle) {
    padding: 0 0.5rem;
}

:deep(#globalRankingContainer),
:deep(#globalRankingInternContainer) {
    width: 100%;
    padding: 0;
}

@media (max-width: 768px) {
    .rankings-container {
        flex-direction: column;
    }

    .ranking-section {
        margin-bottom: 20px;
    }

    .ranking-overview-container {
        flex-direction: column;
        min-height: auto;
    }

    .points-display,
    .ranking-box {
        width: 100%;
        margin-bottom: 1rem;
        padding: 1.25rem;
    }

    .ranking-divider {
        display: none;
    }

    .stats-value,
    .rank-number {
        font-size: 2.2rem;
    }

    .ranking-box .h4 {
        font-size: 1.5rem;
    }

    :deep(#globalRankingMenuContainer),
    :deep(#nationalRankingMenuContainer) {
        padding: 0.5rem;
    }
}

@media (max-width: 480px) {
    :deep(#globalRankingMenuContainer),
    :deep(#nationalRankingMenuContainer) {
        padding: 0.25rem;
    }
}

:deep(body) {
    background-color: var(--background-primary);
}
</style>
