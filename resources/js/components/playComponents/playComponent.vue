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
            <img
                src="../../../../public/images/icons/arrow-right-dark.svg"
                alt="Arrow right"
                height="70%"
            />
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
                    <img
                        src="../../../../public/images/icons/add-dark.svg"
                        style="margin-left: 10px"
                        alt="Add icon"
                        height="70%"
                    />
                </button>
                <div id="espacio4"></div>
                <button
                    class="botonPartida white-color h4 bold"
                    @click="showJoinModal = true"
                >
                    UNIRSE
                    <img
                        src="../../../../public/images/icons/user-multiple-dark.svg"
                        style="margin-left: 10px"
                        alt="User multiple icon"
                        height="70%"
                    />
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
import JoinMatchModal from "../privateMatch/JoinMatchModal.vue";

export default {
    components: {
        JoinMatchModal,
    },
    setup() {
        const router = useRouter();
        const showJoinModal = ref(false);

        const irAlJuego = () => {
            router.push({ name: "game" });
        };

        const crearPartidaPrivada = () => {
            router.push({ name: "private-match.create" });
        };

        const handleJoinMatch = (code) => {
            console.log("Uniendo a partida con código:", code);
            showJoinModal.value = false;
            // Aquí irá la lógica para unirse a la partida
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
