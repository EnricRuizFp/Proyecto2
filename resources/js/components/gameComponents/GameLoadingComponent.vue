<template>
    <div class="create-match app-background-primary">
        <h3 class="h3-dark page-title">{{loadingTitle}}</h3>

        <div class="match-setup" :class="{ 'loading-state': isLoading }">
            <div v-if="isLoading" class="loading-overlay">
                <i class="fas fa-spinner fa-spin"></i>
                <p class="p3-dark">Cargando...</p>
            </div>

            <div v-else-if="error" class="error-container">
                <i class="fas fa-exclamation-circle"></i>
                <p class="p2-dark">{{ error }}</p>
                <button @click="backToHome" class="primary-button">
                    Volver a inicio
                </button>
            </div>

            <template v-else>
                <div class="players-container">
                    <!-- Contenedor izquierdo con UserComponent -->
                    <div class="player-side">
                        <UserComponent variant="profile" />
                    </div>

                    <!-- VS separator -->
                    <div class="vs-separator">
                        <span class="h2-dark">VS</span>
                    </div>

                    <!-- Contenedor derecho (esperando oponente) -->
                    <div class="player-side">
                        <div class="player-card guest-player waiting">
                            <div class="player-content">
                                <div class="player-info">
                                    <p v-if="opponentUsername=='Esperando oponente...'" class="p2-dark">{{ opponentUsername }}</p>
                                    <p v-else style="font-size: 26px;">{{ opponentUsername }}</p>
                                </div>
                                <div class="player-avatar pulse">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Código de la partida - Solo visible en partidas privadas -->
                <div v-if="isPrivateGame" class="match-code">
                    <p class="p3-dark">CÓDIGO DE LA PARTIDA</p>
                    <div class="code-display">
                        <div class="code-number">
                            <span class="code-text">{{ matchCode }}</span>
                            <button
                                class="copy-button"
                                @click="copyMatchCode"
                                title="Copiar código"
                            >
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTS -- */
import { ref, onMounted, watch, computed, onUnmounted } from "vue";
import axios from "axios";
import { authStore } from "../../store/auth"; // Añadir importación del auth store
import { useRoute, useRouter } from 'vue-router'; // Añadir useRouter a los imports
import UserComponent from '../navbar/UserComponent.vue';
import { useGameStore } from "../../store/game";

/* -- VARIABLES -- */

const matchCode = ref(null);
const isLoading = ref(true);
const isPrivateGame = ref(null);
const error = ref(null);
const auth = authStore();
const route = useRoute();
const router = useRouter();
const gameStore = useGameStore(); // Utilizado para settear las fases del juego
const opponentUsername = ref('Esperando oponente...');
let matchStatus = ref(null);
const loadingTitle = computed(() => { // Devuelve el título de la página dependiendo del tipo de juego y el código

    // Devuelve el título dependiendo del tipo de juego y el código
    if(route.params.gameType === 'public'){
        return "Uniéndote a una partida pública...";
    }else if(route.params.gameType === 'private' && route.params.gameCode != 'null'){
        return "Uniéndote a una partida privada...";
    }else{
        return "Creando partida...";
    }
});


/* -- FUNCIONES -- */

// Función para volver a inicio
const backToHome = () => {
    router.push('/');
};

// Función para copiar el código de la partida
const copyMatchCode = () => {
    navigator.clipboard
        .writeText(matchCode.value)
        .then(() => {
            showNotification("Código copiado al portapapeles", "success");
        })
        .catch((err) => {
            console.error("Error al copiar:", err);
            showNotification("No se pudo copiar el código", "error");
        });
};

// Función sleep que devuelve una promesa
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// Función esperar al timestamp
const waitForTimestamp = async (timestamp) => {
    const targetDate = new Date(timestamp);
    const now = new Date();
    
    if (now < targetDate) {
        const timeToWait = targetDate.getTime() - now.getTime();
        await new Promise(resolve => setTimeout(resolve, timeToWait));
    }
};

// Función onMounted
onMounted(() => {

    console.log("GameLoadingComponent mounted.");
    console.log("User: ", authStore().user);
    console.log("Game type: ", route.params.gameType);
    console.log("Game code: ", route.params.gameCode);

    // Verificación de usuario autenticado, modo de juego y código
    if(!authStore().user || !route.params.gameType){
        backToHome();
    }

    // Encontrar partida
    findMatch();

});

// Función FindMatch
const findMatch = async () => {
    console.log("Finding match...");

    // Mostrar pantalla de cargando...
    isLoading.value = true;

    try{

        // Llamar a la API para encontrar partida
        const response = await axios.post('/api/games/find-match', {
            gameType: route.params.gameType,
            gameCode: route.params.gameCode,
            user: authStore().user
        });

        // Mostrar respuesta por consola
        console.log("Match found:", response.data);

        // Verificar si se ha encontrado partida
        if(response.data.status == 'success'){

            // Mostrar pantalla de carga con el oponente y settear matchCode
            isLoading.value = false;
            
            // Obtener si el usuario actual es el creador de la partida
            if(response.data.game.created_by == authStore().user.id){
                
                console.log("Creador de la partida");
                matchCode.value = response.data.game.code;

                // Mostrar por pantalla el código de la partida si es privada
                if(!response.data.game.is_public){
                    isPrivateGame.value = true;
                }

                // Crear un timestamp a 10 segundos vista cuando un usuario se una
                setTimestampMatchCreator();

            }else{
                console.log("Invitado a la partida.");
                matchCode.value = response.data.game.data.code;

                // Buscar en la base de datos el timestamp de inicio de la partida
                pollMatchStatusGuest();
            }
        }else{

            // Volver a la página de inicio
            console.log("No se ha podido unir a la partida.");
            backToHome();
        }

    }catch(err){

        // Mostrar error
        console.error("Error finding match:", err);
        error.value = "Error al encontrar partida. Inténtalo de nuevo más tarde.";
        isLoading.value = false;
    }
};

// Función de polling para creador de la partida
const setTimestampMatchCreator = async () => {

    let contador = 0;

    // Esperar a que se una un usuario
    do{
        // Esperar 2.5 segundos entre pollings
        await sleep(2500);

        const response = await axios.post('/api/games/check-match-status', {
            gameCode: matchCode.value,
            user: auth.user
        });
        console.log("Match status:", response.data);

        matchStatus = response.data.message;
        contador++;

    }while(matchStatus == "waiting" && contador <= 50);

    console.log("Setting match timestamp as creator...");

    // Subir el timestamp a la DB
    const response = await axios.post('/api/games/create-timestamp', {
        gameCode: matchCode.value,
        data: "start_date"
    });

    // Si se ha subido correctamente, redirigir a la página de ShipPlacement
    if(response.data.status == "success"){
        console.log("Timestamp uploaded: ", response.data.game.start_date);

        // Redirigir a la página de ShipPlacement
        await waitForTimestamp(response.data.game.start_date);
        gameStore.setGamePhase('placement');
    }else{
        console.log("Error al subir el timestamp.");
        backToHome();
    }

};

// Función de polling para invitado de la partida
const pollMatchStatusGuest = async () => {

    console.log("Polling match status as guest...");
    let contador = 0;
    let response = null;

    do{
        // Esperar 2.5 segundos entre pollings
        await sleep(2500);

        // Obtener el timestamp de la DB
        response = await axios.post('/api/games/check-timestamp', {
            gameCode: matchCode.value,
            data: "start_date"
        });
        console.log('Timestamp obtenido:', response.data);

        console.log("Status: ", response.data.status);

        matchStatus =  response.data.status;
        contador++;

    }while(matchStatus != 'success' && contador <= 50);

    // Si encuentra el timestamp, entra a la partida
    if(matchStatus == "success"){
        console.log("Partida encontrada y unido");

        // Redirigir a la página de ShipPlacement
        await waitForTimestamp(response.data.game.start_date);
        gameStore.setGamePhase('placement');

    }else{
        console.log("Error al unirse a la partida");
        backToHome();
    }

};


</script>