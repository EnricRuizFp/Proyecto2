<template>
    <div style="border: 1px solid white; padding: 40px;">
        <h3>Panel de pruebas</h3>
        <button style="padding: 10px; background-color: aqua;" @click="playPublic">Play public</button>
        <button style="padding: 10px; background-color: yellowgreen;" @click="createPrivateGame">Create private</button>
        <input id="gameCode" type="text" v-model="gameCode" maxlength="4" placeholder="Código">
        <button style="padding: 10px; background-color: yellow;" @click="joinPrivateGame">Join game</button>
    </div>
</template>

<script>
import axios from "axios";
import { authStore } from "@/store/auth";

export default {
    name: "PruebasComponent",
    data() {
        return {
            gameCode: "",
        };
    },
    methods: {
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
    },
};
</script>

<style scoped>
input {
    margin: 10px;
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
</style>
