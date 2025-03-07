<template>
    <div class="game-view neutral-background">
        <div class="game-container">
            <!-- Fase de colocación de barcos -->
            <ShipPlacement v-if="gamePhase === 'placement'" @placement-confirmed="startGame" />

            <!-- Resto del juego -->
            <div>
                <h1>Game View</h1>
                <h2>Nivel {{ currentLevel }}</h2>
            </div>

            <!-- Panel de pruebas (Siempre visible) -->
            <div style="border: 1px solid white; padding: 40px;">
                <h3>Panel de pruebas</h3>
                <button style="padding: 10px; background-color: aqua;" @click="playPublic">Play public</button>
                <button style="padding: 10px; background-color: yellowgreen;" @click="createPrivateGame">Create private</button>
                <input id="gameCode" type="text" v-model="gameCode" maxlength="4" placeholder="Código">
                <button style="padding: 10px; background-color: yellow;" @click="joinPrivateGame">Join game</button>
            </div>
        </div>

        <!-- Botones temporales para pruebas -->
        <div class="debug-controls">
            <button @click="testWin">Simular Victoria</button>
            <button @click="testGameOver">Simular Derrota</button>
        </div>

        <!-- Componentes de victoria y derrota -->
        <GameWin :visible="showWin" @next-level="nextLevel" @restart="restartLevel" />
        <GameOver :visible="showGameOver" @restart="restartLevel" @menu="goToMenu" />
    </div>
</template>

<script>
import axios from "axios";
import { authStore } from "@/store/auth";
import ShipPlacement from "../../components/gameComponents/ShipPlacement.vue";
import GameWin from "../../components/gameComponents/GameWin.vue";
import GameOver from "../../components/gameComponents/GameOver.vue";

export default {
    name: "GameView",
    components: {
        ShipPlacement,
        GameWin,
        GameOver,
    },
    data() {
        return {
            gamePhase: "placement", // 'placement', 'playing', 'finished'
            playerBoard: null,
            currentLevel: 1,
            showWin: false,
            showGameOver: false,
            gameCode: "", // Código del juego a unirse
        };
    },
    methods: {
        startGame(boardConfiguration) {
            this.playerBoard = boardConfiguration;
            this.gamePhase = "playing";
        },
        async playPublic() {
            if (!authStore().user) {
                console.error("Usuario no autenticado");
                return;
            }
            try {
                const response = await axios.post("http://127.0.0.1:8000/api/games/play-public", { user: authStore().user });
                console.log("Respuesta de la API: ", response.data);
            } catch (error) {
                console.error("Error al llamar a la API: ", error);
            }
        },
        async createPrivateGame() {
            if (!authStore().user) {
                console.error("Usuario no autenticado");
                return;
            }
            try {
                const response = await axios.post("http://127.0.0.1:8000/api/games/create-private", { user: authStore().user });
                console.log("Respuesta de la API: ", response.data);
            } catch (error) {
                console.error("Error al llamar a la API: ", error);
            }
        },
        async joinPrivateGame() {
            if (!authStore().user) {
                console.error("Usuario no autenticado");
                return;
            }
            if (this.gameCode.length !== 4) {
                console.error("El código debe tener 4 caracteres.");
                return;
            }
            try {
                const response = await axios.post("http://127.0.0.1:8000/api/games/join-private", { user: authStore().user, code: this.gameCode });
                console.log("Respuesta de la API: ", response.data);
            } catch (error) {
                console.error("Error al llamar a la API: ", error);
            }
        },
        nextLevel() {
            this.currentLevel++;
            this.showWin = false;
        },
        restartLevel() {
            this.showWin = false;
            this.showGameOver = false;
        },
        goToMenu() {
            this.$router.push("/menu");
        },
        testWin() {
            this.showWin = true;
        },
        testGameOver() {
            this.showGameOver = true;
        },
    },
};
</script>

<style scoped>
.game-view {
    width: 100%;
    height: calc(100vh - 101px);
    color: white;
}

.game-container {
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.debug-controls {
    position: fixed;
    bottom: 20px;
    right: 20px;
    display: flex;
    gap: 10px;
}

.debug-controls button {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: var(--primary-color);
    color: var(--white-color);
    font-weight: bold;
    transition: 0.3s;
}

.debug-controls button:hover {
    background: var(--primary-v2-color);
}
</style>