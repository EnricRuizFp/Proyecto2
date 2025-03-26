<template>
    <div class="view-games-container">
        <h2>Partidas Disponibles para Observar</h2>
        <div class="games-table">
            <table v-if="games.length > 0">
                <thead>
                    <tr>
                        <th>Código de Partida</th>
                        <th>Jugadores</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="game in displayedGames" :key="game.id">
                        <td>{{ game.code }}</td>
                        <td>{{ game.player1 }} vs {{ game.player2 }}</td>
                        <td>{{ game.status }}</td>
                        <td>
                            <button
                                @click="watchGame(game.code)"
                                class="watch-btn"
                            >
                                Observar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Botón mostrar más -->
            <div v-if="games.length > 10" class="show-more-container">
                <button @click="toggleShowAll" class="show-more-btn">
                    {{
                        showAll
                            ? "Mostrar menos"
                            : `Ver ${games.length - 10} más`
                    }}
                </button>
            </div>

            <div v-else-if="games.length === 0" class="no-games">
                No hay partidas disponibles para observar
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const games = ref([]);
const showAll = ref(false);

const fetchGames = async () => {
    try {
        const response = await axios.get("/api/games/available");
        if (response.data) {
            games.value = response.data;
        }
    } catch (error) {
        console.error("Error fetching games:", error);
        // Opcionalmente mostrar un mensaje de error al usuario
        games.value = []; // Asegurar que games esté vacío en caso de error
    }
};

const watchGame = async (gameCode) => {
    router.push(`/game/private/${gameCode}`);
};

const displayedGames = computed(() => {
    return showAll.value ? games.value : games.value.slice(0, 10);
});

const toggleShowAll = () => {
    showAll.value = !showAll.value;
};

onMounted(() => {
    fetchGames();
    const interval = setInterval(fetchGames, 10000);
    onUnmounted(() => {
        clearInterval(interval);
    });
});
</script>

<style scoped>
.view-games-container {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    background-color: var(--background-primary);
    min-height: 100vh;
}

.view-games-container h2 {
    color: var(--white-color);
    font-size: 2.5rem;
    margin-bottom: 2rem;
    text-align: center;
    text-shadow: 0 0 10px rgba(112, 72, 236, 0.5);
}

.games-table {
    background: var(--background-secondary);
    padding: 2.5rem;
    padding-top: 3rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: transparent;
    margin-bottom: 1rem;
}

th,
td {
    padding: 1.2rem 1.5rem;
    text-align: left;
    color: var(--white-color);
    border-bottom: 1px solid var(--neutral-color-2);
    font-size: 1rem;
}

td {
    padding: 1.5rem;
    font-size: 1.1rem;
}

th {
    background: var(--primary-color);
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.9rem;
    color: var(--white-color);
    border-bottom: 2px solid var(--primary-v2-color);
}

th:first-child {
    border-top-left-radius: 8px;
}

th:last-child {
    border-top-right-radius: 8px;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover td {
    background-color: var(--neutral-color-1);
    transition: background-color 0.2s ease;
}

.watch-btn {
    padding: 0.5rem 1.5rem;
    background: var(--primary-color);
    color: var(--white-color);
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
}

.watch-btn:hover {
    background: var(--primary-v2-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(112, 72, 236, 0.2);
}

.no-games {
    text-align: center;
    padding: 3rem;
    color: var(--white-color);
    font-size: 1.2rem;
    background: var(--background-secondary);
    border-radius: 8px;
    margin: 1rem 0;
}

.show-more-container {
    text-align: center;
    margin-top: 2rem;
}

.show-more-btn {
    background-color: var(--neutral-color-1);
    color: var(--white-color);
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.show-more-btn:hover {
    background-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(112, 72, 236, 0.2);
}

@media (max-width: 768px) {
    .view-games-container {
        padding: 1rem;
    }

    .view-games-container h2 {
        font-size: 2rem;
    }

    th,
    td {
        padding: 0.75rem 1rem;
    }

    .watch-btn {
        padding: 0.4rem 1rem;
        font-size: 0.8rem;
    }
}
</style>
