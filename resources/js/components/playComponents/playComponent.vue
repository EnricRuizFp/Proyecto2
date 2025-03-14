<template>
    <div id="playComponent">
        <div id="tituloPlayComponent">
            <h1 class="boldest white-color">DE BATTLESHIP</h1>
        </div>
        <button
            id="botonJugarPartida"
            class="white-color h4 bold"
            @click="irAlJuego"
        >
            JUGAR
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
                <h2 class="boldest white-color">PARTIDA PRIVADA</h2>
            </div>
            <div id="botonesPartidaPrivada">
                <button
                    class="botonPartida white-color h4 bold"
                    @click="crearPartidaPrivada"
                >
                    CREAR
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
                    @click="showJoinModal = true"
                >
                    UNIRSE
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

<script>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useGameStore } from "../../store/game";
import JoinMatchModal from "../privateMatch/JoinMatchModal.vue";

export default {
    components: {
        JoinMatchModal,
    },
    setup() {
        const router = useRouter();
        const showJoinModal = ref(false);
        const gameStore = useGameStore();

        const irAlJuego = () => {
            gameStore.resetGame();
            router.push({ name: "game" });
        };

        const crearPartidaPrivada = () => {
            gameStore.setGameMode("create");
            router.push({ name: "game" });
        };

        const handleJoinMatch = (code) => {
            showJoinModal.value = false;
            gameStore.setGameMode("join");
            gameStore.setMatchCode(code);
            router.push({ name: "game" });
        };

        return {
            irAlJuego,
            crearPartidaPrivada,
            showJoinModal,
            handleJoinMatch,
        };
    },
};
</script>

<style scoped>
#tituloPlayComponent {
    width: 100%;
    text-align: center;
}

#tituloPlayComponent h1 {
    font-size: min(7vw, 50px);
    text-shadow: 0 0 10px rgba(112, 72, 236, 0.5);
    letter-spacing: min(0.5vw, 4px);
    margin: 0;
    white-space: nowrap;
}

#botonJugarPartida {
    background-color: var(--primary-color);
    border: none;
    border-radius: 10px;
    padding: 20px 35px; /* Aumentado el padding vertical */
    display: flex;
    align-items: center;
    justify-content: center; /* Centrar contenido */
    gap: 15px; /* Aumentar espacio entre texto e icono */
    margin: 2rem 0 4rem 0; /* Aumentado el margin-bottom a 4rem */
    min-width: 250px; /* Asegurar ancho mínimo */
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
    margin-left: 0 !important; /* Eliminar margen inline */
}

.botonPartida {
    background-color: var(--secondary-color);
    border: none;
    border-radius: 10px;
    padding: 20px 35px; /* Aumentado el padding vertical */
    display: flex;
    align-items: center;
    justify-content: center; /* Centrar contenido */
    gap: 15px; /* Aumentar espacio entre texto e icono */
    min-width: 200px; /* Asegurar ancho mínimo */
    transition: all 0.3s ease;
    flex: 1;
    max-width: 250px; /* Limitar el ancho máximo de cada botón */
}

.botonPartida {
    width: 220px; /* Aumentado de 180px/200px a 220px */
    min-width: 220px; /* Ajustado para mantener consistencia */
    padding: 0 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
}

.botonPartida img {
    height: 32px; /* Aumentado el tamaño del icono */
    width: 32px; /* Aseguramos que el ancho sea igual */
    filter: brightness(0) invert(1) opacity(0.9); /* Hacer el icono más grueso */
    margin-left: 0 !important; /* Eliminar margen inline */
}

.botonPartida:hover {
    transform: translateY(-2px);
    background-color: var(--secondary-v2-color);
    box-shadow: 0 4px 12px rgba(244, 60, 107, 0.2);
}

/* Ajustar la altura de los botones */
#botonJugarPartida,
.botonPartida {
    height: 65px; /* Reducido de 80px a 65px */
    padding: 0 35px; /* Mantener el padding horizontal pero quitar el vertical */
}

#tituloPartidaPrivada h2 {
    font-size: min(5vw, 25px);
    margin-bottom: 1rem;
    text-align: center;
    white-space: nowrap;
}

/* Responsive layout */
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

.botonPartida {
    background-color: var(--secondary-color);
    border: none;
    border-radius: 10px;
    height: 65px;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    flex: 1;
    width: calc(
        50% - 0.5rem
    ); /* Cada botón ocupa la mitad del espacio menos el gap */
    min-width: 0; /* Eliminar min-width para permitir encogimiento */
    max-width: none; /* Eliminar max-width para permitir crecimiento */
}

/* Eliminar referencias al tamaño de fuente en los media queries */
@media (max-width: 768px) {
    #botonJugarPartida {
        min-width: 200px;
        padding: 0 25px;
    }

    .botonPartida {
        width: 100%;
        height: 55px;
    }

    #botonesPartidaPrivada {
        flex-direction: column;
        gap: 1rem;
    }

    #espacio4 {
        display: none;
    }
}

@media (max-width: 480px) {
    #botonJugarPartida,
    .botonPartida {
        height: 55px;
        font-size: 24px;
    }

    #botonJugarPartida svg {
        height: 28px;
        width: 28px;
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
        font-size: 20px;
    }
}
</style>
