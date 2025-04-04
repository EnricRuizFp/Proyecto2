<template>
    <div class="rankings-page-container">
        <div class="container pt-4">
            <!-- Single Card for all Rankings Content -->
            <div class="row flex-grow-1">
                <div class="col-12 d-flex flex-column">
                    <div
                        class="card app-background-primary white-border ranking-card flex-grow-1"
                    >
                        <div class="card-body d-flex flex-column">
                            <!-- User Rankings Overview Section -->
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

                            <!-- Separator Line -->
                            <hr class="ranking-separator my-4" />

                            <!-- Global and National Rankings Lists -->
                            <div
                                v-if="loading"
                                class="loading-container flex-grow-1"
                            >
                                <div
                                    class="spinner-border text-light"
                                    role="status"
                                >
                                    <span class="visually-hidden"
                                        >Loading...</span
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
                                    Try Again
                                </button>
                            </div>
                            <div v-else class="rankings-container flex-grow-1">
                                <!-- Global Ranking (Left) -->
                                <div class="ranking-section global-ranking">
                                    <GlobalRankingComponent
                                        :ranking-data="globalRanking"
                                    />
                                </div>

                                <!-- National Ranking (Right) -->
                                <div class="ranking-section national-ranking">
                                    <NationalRankingComponent
                                        :ranking-data="nationalRanking || []"
                                        :nationality="
                                            userNationality || 'Unknown'
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

// Variables for rankings
const globalRanking = ref([]);
const nationalRanking = ref([]);
const userNationality = ref("");
const loading = ref(true);
const error = ref(null);

// Variables for career
const globalPosition = ref(null);
const nationalPosition = ref(null);
const userPoints = ref(null);
const userId = ref(null);

// Import rankings composable
const { getGlobalRanking, getNationalRanking } = useRankings();

// Obtener la posición global del usuario
const getGlobalPosition = async () => {
    try {
        const response = await axios.get("/api/rankings/global-position");
        globalPosition.value = response.data.position;
    } catch (error) {
        console.error(
            "Error obteniendo la posición global del usuario:",
            error
        );
        globalPosition.value = null;
    }
};

// Obtener la posición nacional del usuario
const getNationalPosition = async () => {
    try {
        const response = await axios.get("/api/rankings/national-position");
        nationalPosition.value = response.data.position;
    } catch (error) {
        console.error(
            "Error obteniendo la posición nacional del usuario:",
            error
        );
        nationalPosition.value = null;
    }
};

// Obtener la cantidad de puntos de un usuario
const getUserPoints = async () => {
    try {
        const response = await axios.get("/api/rankings/user-points");
        userPoints.value = response.data.points;
    } catch (error) {
        console.error("Error obteniendo los puntos del usuario:", error);
        userPoints.value = "NA";
    }
};

// Obtener el color del ranking
const getRankingColor = (position) => {
    switch (position) {
        case 1:
            return "rank-gold";
        case 2:
            return "rank-silver";
        case 3:
            return "rank-bronze";
        default:
            return "";
    }
};

// Functions for rankings
const loadRankings = async () => {
    try {
        loading.value = true;
        error.value = null;

        // Load global rankings
        const globalData = await getGlobalRanking(10);
        if (globalData) {
            globalRanking.value = globalData.data;
        }

        // Load national rankings
        try {
            const nationalData = await getNationalRanking(10);
            if (nationalData && nationalData.status === "success") {
                nationalRanking.value = nationalData.data;
                userNationality.value = nationalData.user_nationality;

                console.log("Loaded rankings:", {
                    global: globalRanking.value?.length || 0,
                    national: nationalRanking.value?.length || 0,
                    nationality: userNationality.value,
                });
            }
        } catch (nationalErr) {
            console.error("Error loading national rankings:", nationalErr);
            error.value =
                "Failed to load national rankings. You might need to log in or set your nationality.";
            // Still continue even if national rankings fail
        }
    } catch (err) {
        console.error("Error loading rankings:", err);
        error.value = "Failed to load rankings. Please try again later.";
    } finally {
        loading.value = false;
    }
};

// Lifecycle hooks
onMounted(async () => {
    // Load user data
    try {
        const user = await axios.get("/api/user");
        userId.value = user.data.id;
    } catch (error) {
        console.error("Error getting user data:", error);
    }

    // Load career data
    getGlobalPosition();
    getNationalPosition();
    getUserPoints();

    // Fetch ranking data when component mounts
    loadRankings();
});
</script>

<style scoped>
.rankings-page-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    padding-top: 100px; /* Use padding instead of margin */
    background-color: var(--background-primary); /* Ensure background matches */
}

.container {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* Remove the margin-top here since we're using padding-top on the container */
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
    padding: 0; /* Removed lateral padding */
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

/* User ranking section styles - with consistent sizing */
.user-ranking-container {
    width: 100%;
    padding-bottom: 0.5rem;
}

.section-title {
    margin-bottom: 1.5rem;
    color: var(--white-color);
    font-size: 1.8rem;
    text-align: left; /* Changed from center to left */
}

/* Equal-sized horizontal layout */
.ranking-overview-container {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 1rem;
    min-height: 120px;
    background-color: transparent; /* Ensure background is transparent */
    border: none; /* Remove any border */
    padding: 0; /* Remove padding that might be inherited */
}

/* All three elements have identical flex properties for equal sizing */
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
    background-color: rgba(
        255,
        255,
        255,
        0.05
    ); /* Match the ranking-box background */
    margin-top: 0; /* Override the margin-top for this component only */
}

.ranking-box {
    background-color: rgba(255, 255, 255, 0.05);
}

/* Create a special class just for the Rankings page */
.rankings-page-container .points-display {
    margin-top: 0 !important; /* Force override with !important */
}

/* Add hover effects */
.points-display:hover,
.ranking-box:hover {
    transform: translateY(-5px);
}

/* Consistent typography with increased font sizes */
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

/* New separator style */
.ranking-separator {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    width: 100%;
    margin: 1.5rem 0;
}

/* Styles to override component padding in Index page only */
:deep(#globalRankingMenuContainer),
:deep(#nationalRankingMenuContainer) {
    padding: 1rem 0.5rem; /* Reduced side padding */
}

:deep(#globalRankingMenuTitle),
:deep(#nationalRankingMenuTitle) {
    padding: 0 0.5rem; /* Add some padding to titles for alignment */
}

:deep(#globalRankingContainer),
:deep(#globalRankingInternContainer) {
    width: 100%;
    padding: 0;
}

/* Responsive adjustments */
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

/* Add this to ensure the entire page has proper background */
:deep(body) {
    background-color: var(--background-primary);
}
</style>
