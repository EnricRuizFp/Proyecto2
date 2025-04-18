<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent ps-0 pe-0">
                    <h5 class="float-start mb-0">Battleship Dashboard</h5>
                </div>
                
                <div class="grid mt-3">
                    <!-- Sección de Juegos -->
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="flex">
                                <!-- Datos en la izquierda -->
                                <div class="flex-none w-15rem">
                                    <div class="flex justify-content-between mb-3">
                                        <div>
                                            <span class="block text-500 font-medium mb-3">Juegos</span>
                                            <div class="text-900 font-medium text-xl">{{ totalGames }}</div>
                                        </div>
                                        <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 2.5rem; height: 2.5rem">
                                            <i class="pi pi-gamepad text-blue-500 text-xl"></i>
                                        </div>
                                    </div>
                                    <span class="text-green-500 font-medium">{{ activeGames }} activos </span>
                                    <span class="text-500">en este momento</span>
                                </div>
                                
                                <!-- Gráfico en la derecha -->
                                <div class="flex-grow-1">
                                    <Chart type="line" :data="chartData" :options="chartOptions" class="w-full h-8rem" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Usuarios -->
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="flex relative">
                                <!-- Icono en la esquina superior derecha -->
                                <div class="absolute" style="right: 0; top: 0;">
                                    <div class="flex align-items-center justify-content-center bg-orange-100 border-round" style="width: 2.5rem; height: 2.5rem">
                                        <i class="pi pi-users text-orange-500 text-xl"></i>
                                    </div>
                                </div>

                                <!-- Datos en la izquierda -->
                                <div class="flex-none w-15rem">
                                    <div class="mb-3">
                                        <span class="block text-500 font-medium mb-3">Usuarios</span>
                                        <div class="text-900 font-medium text-xl">{{ totalUsers }}</div>
                                        <span class="text-500">usuarios registrados</span>
                                    </div>
                                </div>
                                
                                <!-- Gráfico circular en el centro -->
                                <div class="flex-grow-1 px-3 relative" style="max-width: 400px;">
                                    <div class="absolute" style="right: 0; top: -10px;">
                                    </div>
                                    <Chart type="pie" :data="usersChartData" :options="usersChartOptions" class="w-full h-12rem" />
                                </div>

                                <!-- Datos por continente a la derecha -->
                                <div class="flex-none w-15rem">
                                    <span class="text-500 block mb-2">Por continente:</span>
                                    <div class="mt-2">
                                        <div v-for="(value, index) in usersChartData.datasets[0].data" :key="index" class="flex align-items-center mb-2">
                                            <div class="w-1rem h-1rem mr-2" :style="{ backgroundColor: usersChartData.datasets[0].backgroundColor[index] }"></div>
                                            <span class="text-500">{{ usersChartData.labels[index] }}: </span>
                                            <span class="text-900 ml-1">{{ value }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>

                    <!-- Sección de Rankings -->
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="flex">
                                <!-- Columna izquierda con gráficos -->
                                <div class="flex-grow-1 pr-3" style="max-width: 500px;">
                                    <!-- Título y contador -->
                                    <div class="mb-3">
                                        <span class="block text-500 font-medium mb-3">Rankings</span>
                                    </div>

                                    <!-- Gráfico de distribución de puntos -->
                                    <div class="mb-4">
                                        <span class="block text-600 mb-3 font-medium">Distribución de puntos de los jugadores</span>
                                        <Chart type="bar" 
                                            :data="{
                                                labels: rankingStats.pointDistribution.labels,
                                                datasets: [{
                                                    data: rankingStats.pointDistribution.data,
                                                    backgroundColor: ['#42A5F5', '#66BB6A', '#FFA726', '#26C6DA']
                                                }]
                                            }" 
                                            :options="rankingChartOptions" 
                                            class="w-full h-12rem" 
                                        />
                                    </div>

                                    <!-- Gráfico de ratio V/D/E -->
                                    <div class="mt-4">
                                        <div class="flex align-items-center mb-3">
                                            <span class="text-600 font-medium">Ratio V/D/E Global</span>
                                            <div class="ml-2 cursor-pointer" v-tooltip.top="'Victorias/Derrotas/Empates: Proporción global de resultados de todos los jugadores'">
                                                <i class="pi pi-info-circle text-500"></i>
                                            </div>
                                        </div>
                                        <Chart type="doughnut" 
                                            :data="{
                                                labels: rankingStats.winLossRatio.labels,
                                                datasets: [{
                                                    data: rankingStats.winLossRatio.data,
                                                    backgroundColor: rankingStats.winLossRatio.colors
                                                }]
                                            }" 
                                            :options="winLossChartOptions" 
                                            class="w-full h-12rem" 
                                        />
                                    </div>
                                </div>

                                <!-- Columna derecha con Top 10 -->
                                <div class="flex-none w-25rem pl-3">
                                    <div class="surface-0 p-4 border-round">
                                        <div class="text-xl text-900 font-medium mb-3">Top 10 Jugadores</div>
                                        <ul class="list-none p-0 m-0">
                                            <li v-for="(player, index) in rankingStats.topPlayers" 
                                                :key="index" 
                                                class="flex align-items-center py-2 border-bottom-1 surface-border"
                                                :class="{'border-none': index === rankingStats.topPlayers.length - 1}">
                                                <span class="text-900 font-medium w-2rem">{{ index + 1 }}</span>
                                                <div class="flex flex-column ml-2 flex-grow-1">
                                                    <span class="text-600">{{ player.user?.username || 'Unknown' }}</span>
                                                    <span class="text-500 text-sm">{{ capitalizeFirstLetter(player.user?.nationality) }}</span>
                                                </div>
                                                <span class="text-primary font-medium">{{ player.points }} pts</span>
                                            </li>
                                        </ul>
                                    </div>
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
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Chart from 'primevue/chart';

// Datos básicos del dashboard
const totalGames = ref(0);
const activeGames = ref(0);
const totalUsers = ref(0);

// Configuración del gráfico de usuarios por continente
const usersChartData = ref({
    labels: ['África', 'América', 'Asia', 'Europa', 'Oceanía'],
    datasets: [{
        data: [],
        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
    }]
});

const usersChartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    return `${context.label}: ${context.raw} usuarios (${Math.round(context.raw/totalUsers.value*100)}%)`;
                }
            }
        }
    }
});

const chartData = ref({
    labels: [],
    datasets: [{
        label: '',
        data: [],
        fill: false,
        borderColor: '#42A5F5',
        tension: 0.4
    }]
});

const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            mode: 'index',
            intersect: false
        }
    },
    scales: {
        x: {
            display: true,
            ticks: {
                color: '#495057'
            },
            grid: {
                display: false
            }
        },
        y: {
            display: true,
            beginAtZero: true,
            suggestedMax: function(context) {
                const max = Math.max(...context.chart.data.datasets[0].data);
                return max + (max * 0.2);
            },
            ticks: {
                color: '#495057',
                stepSize: 1
            },
            grid: {
                display: false
            }
        }
    }
});

// Estructura de datos para las estadísticas de rankings
const rankingStats = ref({
    totalPlayers: 0,
    topPlayers: [],
    pointDistribution: {
        labels: ['0-100', '101-500', '501-1000', '1000+'],
        data: [0, 0, 0, 0]
    },
    winLossRatio: {
        labels: ['Victorias', 'Derrotas', 'Empates'],
        data: [0, 0, 0],
        colors: ['#36A2EB', '#FF6384', '#FFCE56']
    }
});

const rankingChartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    return `${context.raw} jugadores`;
                }
            }
        }
    }
});

const winLossChartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '60%',
    plugins: {
        legend: {
            display: true,
            position: 'bottom',
            labels: {
                color: '#495057'
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    return `${context.label}: ${context.raw}`;
                }
            }
        }
    }
});

// Helper para capitalizar la primera letra de un string
const capitalizeFirstLetter = (string) => {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
};

// Procesa los datos de juegos para mostrar la actividad por día
const processGamesData = (games) => {
    const gamesArray = Array.isArray(games) ? games : (games.data || []);
    const gamesByDate = {};
    
    gamesArray.forEach(game => {
        if (game.creation_date) {
            const date = new Date(game.creation_date).toISOString().split('T')[0];
            gamesByDate[date] = (gamesByDate[date] || 0) + 1;
        }
    });

    const sortedDates = Object.keys(gamesByDate).sort();
    
    chartData.value = {
        labels: sortedDates.map(date => {
            const [_, month, day] = date.split('-');
            return `${day}/${month}`;
        }),
        datasets: [{
            label: '',
            data: sortedDates.map(date => gamesByDate[date]),
            fill: false,
            borderColor: '#42A5F5',
            tension: 0.4
        }]
    };
};

// Función principal que carga todos los datos del dashboard
onMounted(async () => {
    try {
        // Cargar datos de juegos y usuarios activos
        const response = await axios.get('/api/games/all');
        if (response.data) {
            totalGames.value = response.data.length;
            activeGames.value = response.data.filter(game => !game.is_finished).length;
            processGamesData(response.data);
        }

        // Cargar y procesar datos de usuarios por continente
        const usersResponse = await axios.get('/api/users/all');
        if (usersResponse.data) {
            const users = usersResponse.data;
            totalUsers.value = users.length;
            
            const usersByContinent = {
                africa: users.filter(u => u.nationality === 'africa').length,
                america: users.filter(u => u.nationality === 'america').length,
                asia: users.filter(u => u.nationality === 'asia').length,
                europe: users.filter(u => u.nationality === 'europe').length,
                oceania: users.filter(u => u.nationality === 'oceania').length
            };

            usersChartData.value.datasets[0].data = [
                usersByContinent.africa,
                usersByContinent.america,
                usersByContinent.asia,
                usersByContinent.europe,
                usersByContinent.oceania
            ];
        }

        // Cargar y procesar estadísticas de rankings
        const rankingsResponse = await axios.get('/api/rankings/all');
        if (rankingsResponse.data) {
            const rankings = rankingsResponse.data;
            
            // Obtener el top 10 de jugadores
            rankingStats.value.topPlayers = rankings
                .sort((a, b) => b.points - a.points)
                .slice(0, 10);

            // Calcular rangos equitativos basados en la puntuación máxima
            const maxPoints = Math.max(...rankings.map(r => r.points));
            const sectionRange = Math.ceil(maxPoints / 5);
            
            // Crear etiquetas para los rangos de puntuación
            rankingStats.value.pointDistribution.labels = [
                `0-${sectionRange}`,
                `${sectionRange + 1}-${sectionRange * 2}`,
                `${sectionRange * 2 + 1}-${sectionRange * 3}`,
                `${sectionRange * 3 + 1}-${sectionRange * 4}`,
                `${sectionRange * 4 + 1}-${maxPoints}`
            ];

            // Distribuir jugadores en los rangos correspondientes
            rankingStats.value.pointDistribution.data = Array(5).fill(0);
            rankings.forEach(ranking => {
                const points = ranking.points;
                if (points <= sectionRange) rankingStats.value.pointDistribution.data[0]++;
                else if (points <= sectionRange * 2) rankingStats.value.pointDistribution.data[1]++;
                else if (points <= sectionRange * 3) rankingStats.value.pointDistribution.data[2]++;
                else if (points <= sectionRange * 4) rankingStats.value.pointDistribution.data[3]++;
                else rankingStats.value.pointDistribution.data[4]++;
            });

            // Calcular estadísticas globales de V/D/E
            const totalStats = rankings.reduce((acc, curr) => {
                acc.wins += curr.wins;
                acc.losses += curr.losses;
                acc.draws += curr.draws;
                return acc;
            }, { wins: 0, losses: 0, draws: 0 });

            rankingStats.value.winLossRatio.data = [
                totalStats.wins,
                totalStats.losses,
                totalStats.draws
            ];
        }
    } catch (error) {
        console.error('Error al cargar los datos:', error);
    }
});
</script>

<style scoped>
.h-30rem {
    height: 30rem;
}
.h-10rem {
    height: 10rem;
}
</style>
