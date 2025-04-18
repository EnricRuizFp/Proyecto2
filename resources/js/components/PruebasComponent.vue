<template>
    <div style="border: 1px solid white; padding: 40px;">
        <h3>Panel de pruebas</h3>
        <button style="padding: 10px; background-color: aqua;" @click="playPublic">Play public</button>
        <button style="padding: 10px; background-color: yellowgreen;" @click="createPrivateGame">Create private</button>
        <input id="gameCode" type="text" v-model="gameCode" maxlength="4" placeholder="Código">
        <button style="padding: 10px; background-color: yellow;" @click="joinPrivateGame">Join game</button>

        <h2>PLAY FUNCTION</h2>
        <button style="padding: 10px; background-color: yellow;" @click="checkUserRequirements('public', null)">Play game</button>
        <button style="padding: 10px; background-color: yellow;" @click="checkUserRequirements('private', gameCode)">Private game</button>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from "axios"
import { authStore } from "@/store/auth"

const gameCode = ref("")
const gameType = ref("")

const playPublic = async () => {
    if (!authStore().user) {
        console.error("Usuario no autenticado")
        return
    }
    try {
        const response = await axios.post("/api/games/play-public", { user: authStore().user })
        console.log("Respuesta de la API: ", response.data)
    } catch (error) {
        console.error("Error al llamar a la API: ", error)
    }
}

const createPrivateGame = async () => {
    if (!authStore().user) {
        console.error("Usuario no autenticado")
        return
    }
    try {
        const response = await axios.post("/api/games/create-private", { user: authStore().user })
        console.log("Respuesta de la API: ", response.data)
    } catch (error) {
        console.error("Error al llamar a la API: ", error)
    }
}

const joinPrivateGame = async () => {
    if (!authStore().user) {
        console.error("Usuario no autenticado")
        return
    }
    if (gameCode.value.length !== 4) {
        console.error("El código debe tener 4 caracteres.")
        return
    }
    try {
        const response = await axios.post("/api/games/join-private", { 
            user: authStore().user, 
            code: gameCode.value 
        })
        console.log("Respuesta de la API: ", response.data)
    } catch (error) {
        console.error("Error al llamar a la API: ", error)
    }
}

const checkUserRequirements = async (gameType, gameCode) => {
    try {
        const response = await axios.post("/api/games/check-user-requirements", { 
            gameType: gameType, 
            gameCode: gameCode, 
            user: authStore().user 
        })
        if(response.data.status == 'success'){
            console.log("Usuario preparado para unirse a una partida")
            // Llevar al usuario a la pantalla de matchmaking pasando los datos
        }else{
            console.log("No se cumple con los requisitos para empezar una partida")
        }
        console.log("Respuesta de la API: ", response.data)
    } catch (error) {
        console.error("Error al llamar a la API: ", error)
    }
}
</script>

<style scoped>
input {
    margin: 10px;
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
</style>
