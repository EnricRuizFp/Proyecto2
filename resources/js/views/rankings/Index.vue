<template>
    <div class="container mt-4">
        <h1 class="h2 text-center white-color mb-4">Rankings</h1>
        <div class="row">
            <!-- Global Ranking -->
            <div class="col-md-6 mb-4">
                <div class="card neutral-background white-border">
                    <div class="card-header">
                        <h3 class="white-color">Global Ranking</h3>
                    </div>
                    <div class="card-body">
                        <!-- Global ranking content will go here -->
                    </div>
                </div>
            </div>

            <!-- National Ranking -->
            <div class="col-md-6 mb-4">
                <div class="card neutral-background white-border">
                    <div class="card-header">
                        <h3 class="white-color">National Ranking</h3>
                    </div>
                    <div class="card-body">
                        <!-- National ranking content will go here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

// Variables
const globalRanking = ref([]);
const nationalRanking = ref([]);

// Lifecycle hooks
onMounted(() => {
    // Fetch ranking data when component mounts
    loadRankings();
});

// Functions
const loadRankings = async () => {
    try {
        // TODO: Implement ranking data fetching
    } catch (error) {
        console.error("Error loading rankings:", error);
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
</style>
