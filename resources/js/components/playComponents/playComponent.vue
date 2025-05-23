<template>
    <div id="playComponent">
        <!-- Alerta de error -->
        <div v-if="errorMessage" class="error-alert">
            {{ errorMessage }}
            <button class="close-button" @click="errorMessage = ''">×</button>
        </div>

        <!-- Alerta de información -->
        <div v-if="infoMessage" class="info-alert">
            {{ infoMessage }}
            <button class="close-button" @click="infoMessage = ''">×</button>
        </div>

        <div id="tituloPlayComponent">
            <h1 class="bold white-color">DE BATTLESHIP</h1>
        </div>
        <button
            id="botonJugarPartida"
            class="white-color h4 bold"
            @click="irAlJuego"
        >
            {{ $t("play") }}
            <svg
                width="35"
                height="35"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M19 12L12 5M19 12L12 19"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>
        <div id="partidasPrivadasComponent">
            <div id="tituloPartidaPrivada">
                <h2 class="bold white-color">{{ $t("private_match") }}</h2>
            </div>
            <div id="botonesPartidaPrivada">
                <button
                    class="botonPartida white-color h4 bold"
                    @click="crearPartidaPrivada"
                >
                    {{ $t("create_match") }}
                    <svg
                        width="35"
                        height="35"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M12 5V19M5 12H19"
                            stroke="currentColor"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
                <button
                    class="botonPartida white-color h4 bold"
                    @click="showJoinMatchModal"
                >
                    {{ $t("join_match") }}
                    <svg
                        width="35"
                        height="35"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal para unirse a partida -->
        <JoinMatchModal
            :visible="showJoinModal"
            @close="showJoinModal = false"
            @join="handleJoinMatch"
        />
    </div>
</template>

<script setup>
/* -- IMPORTS -- */
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useGameStore } from "../../store/game";
import { authStore } from "../../store/auth";
import JoinMatchModal from "../privateMatch/JoinMatchModal.vue";
import axios from "axios";

/* -- VARIABLES -- */
const router = useRouter();
const showJoinModal = ref(false);
const gameStore = useGameStore();
const errorMessage = ref("");
const infoMessage = ref("");

// Verificar requisitos del usuario y navegar a la página del juego
const checkAndNavigate = async (gameType, gameCode = null) => {
    try {
        const response = await axios.post(
            "/api/games/check-user-requirements",
            {
                gameType: gameType,
                gameCode: gameCode,
                user: authStore().user ?? null,
            }
        );

        if (response.data.status === "success") {
            console.log("OK: User ready to play.");
            router.push(`/game/${gameType}/${gameCode || "null"}`);
        } else {
            if (
                response.data.message ==
                "Your user is leaving the game. Wait a few seconds."
            ) {
                console.log("Failed without error: ", response.data.message);
                infoMessage.value = response.data.message;
            } else {
                console.log("Failed with error:", response.data.message);
                errorMessage.value = response.data.message;
            }
        }
    } catch (error) {
        errorMessage.value = "Error al verificar requisitos del juego";
    }
};

// Iniciar partida pública
const irAlJuego = async () => {
    gameStore.resetGame();
    await checkAndNavigate("public");
};

// Crear partida privada
const crearPartidaPrivada = async () => {
    gameStore.resetGame();
    await checkAndNavigate("private", null);
};

// Mostrar modal para unirse a partida privada
const handleJoinMatch = async (code) => {
    showJoinModal.value = false;
    gameStore.setGameMode("join");
    gameStore.setMatchCode(code);
    await checkAndNavigate("private", code);
};

// Función para verificar requisitos antes de mostrar el modal
const showJoinMatchModal = async () => {
    try {
        const response = await axios.post(
            "/api/games/check-user-requirements",
            {
                gameType: "private",
                gameCode: null,
                user: authStore().user ?? null,
            }
        );

        if (response.data.status === "success") {
            console.log("OK: User ready to play.");
            showJoinModal.value = true;
        } else {
            console.log("FAILED:", response.data.message);
            errorMessage.value = response.data.message;
        }
    } catch (error) {
        errorMessage.value = "Error al verificar requisitos del juego";
    }
};
</script>

<style scoped>
#playComponent {
    width: 100%;
    margin-top: 0.5rem;
}

#tituloPlayComponent {
    width: 100%;
    text-align: center;
}

#tituloPlayComponent h1 {
    font-size: 3rem;
    text-shadow: 0 0 10px rgba(112, 72, 236, 0.5);
    letter-spacing: min(0.5vw, 4px);
    margin: 0;
    white-space: nowrap;
}

#botonJugarPartida {
    background-color: var(--primary-color);
    border: none;
    border-radius: 10px;
    padding: 20px 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin: 2rem 0 4rem 0;
    min-width: 250px;
    transition: all 0.3s ease;
}

#botonJugarPartida:hover {
    transform: translateY(-3px);
    background-color: var(--primary-v2-color);
    box-shadow: 0 4px 12px rgba(112, 72, 236, 0.2);
}

#botonJugarPartida svg,
.botonPartida svg {
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.botonPartida svg {
    margin-left: 0 !important;
}

.botonPartida {
    background-color: var(--secondary-color);
    border: none;
    border-radius: 10px;
    height: 65px;
    padding: 0 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    transition: all 0.3s ease;
    flex: 1;
    width: calc(50% - 0.5rem);
    min-width: 0;
    max-width: none;
}

.botonPartida img {
    height: 32px;
    width: 32px;
    filter: brightness(0) invert(1) opacity(0.9);
    margin-left: 0 !important;
}

.botonPartida:hover {
    transform: translateY(-2px);
    background-color: var(--secondary-v2-color);
    box-shadow: 0 4px 12px rgba(244, 60, 107, 0.2);
}

/* Ajustar la altura de los botones */
#botonJugarPartida,
.botonPartida {
    height: 65px;
    padding: 0 35px;
}

#tituloPartidaPrivada h2 {
    font-size: min(5vw, 25px);
    margin-bottom: 1rem;
    text-align: center;
    white-space: nowrap;
}

#partidasPrivadasComponent {
    width: 100%;
    padding: 0;
}

#botonesPartidaPrivada {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    width: 100%;
}

@media (max-width: 1200px) {
    #playComponent {
        padding: 1rem;
    }
}

@media (max-width: 768px) {
    #playComponent {
        padding: 1rem;
    }

    #botonJugarPartida {
        min-width: 200px;
        padding: 0 25px;
        margin: 2rem 0 3rem 0;
        height: 60px;
        font-size: 22px;
    }

    #tituloPlayComponent h1 {
        font-size: 2.5rem;
    }

    .botonPartida {
        width: 100%;
        height: 60px !important;
        min-height: 60px;
        font-size: 22px;
        gap: 10px;
    }

    .botonPartida svg {
        height: 24px;
        width: 24px;
    }

    #botonesPartidaPrivada {
        flex-direction: column;
        gap: 1rem;
    }

    #tituloPartidaPrivada h2 {
        margin-bottom: 1rem;
    }

    #espacio4 {
        display: none;
    }
}

@media (max-width: 480px) {
    #playComponent {
        padding: 0.5rem;
    }

    #botonJugarPartida,
    .botonPartida {
        height: 60px;
        font-size: 20px;
    }

    #tituloPlayComponent h1 {
        font-size: 2.75rem;
    }

    #botonJugarPartida svg {
        height: 24px;
        width: 24px;
    }

    .botonPartida {
        height: 60px !important;
        font-size: 20px;
    }

    .botonPartida svg {
        height: 24px;
        width: 24px;
    }
}

@media (max-width: 360px) {
    #botonJugarPartida,
    .botonPartida {
        min-width: 180px;
        padding: 0 20px;
        font-size: 22px;
    }
}

.error-alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    padding: 1rem 2rem;
    border-radius: 8px;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    background-color: var(--error-color, #ff4444);
    color: white;
}

.info-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 2rem;
    border-radius: 8px;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    background-color: var(--neutral-color-2);
    color: white;
    border: 1px solid var(--white-color);
}

.close-button {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0 0.5rem;
}

.close-button:hover {
    opacity: 0.8;
}
</style>
