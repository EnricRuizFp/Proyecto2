<template>
    <div class="container mt-4">
        <h1 class="h2 text-center white-color mb-4">Rankings</h1>
        <div v-if="loading" class="text-center white-color">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div v-else-if="error" class="alert alert-danger text-center">
            {{ error }}
            <button @click="loadRankings" class="btn btn-outline-light mt-2">
                Try Again
            </button>
        </div>
        <div v-else class="row">
            <!-- Global Ranking -->
            <div class="col-12 col-sm-6 mb-4">
                <div class="card neutral-background white-border">
                    <div class="card-body">
                        <GlobalRankingComponent :ranking-data="globalRanking" />
                    </div>
                </div>
            </div>

            <!-- National Ranking -->
            <div class="col-12 col-sm-6 mb-4">
                <div class="card neutral-background white-border">
                    <div class="card-body">
                        <NationalRankingComponent
                            :ranking-data="nationalRanking || []"
                            :nationality="userNationality || 'Unknown'"
                        />
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

// Variables
const globalRanking = ref([]);
const nationalRanking = ref([]);
const userNationality = ref("");
const loading = ref(true);
const error = ref(null);

// Import rankings composable
const { getGlobalRanking, getNationalRanking } = useRankings();

// Lifecycle hooks
onMounted(() => {
    // Fetch ranking data when component mounts
    loadRankings();
});

// Functions
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
</script>

<style scoped>
.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-header {
    border-bottom: 1px solid var(--white-color);
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
