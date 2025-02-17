<template>
    <!-- No session -->
    <div v-if="!authStore().user?.name" class="nav-item dropdown" id="userComponent">
        <div id="userContent">
            <a id="userProfile" class="nav-link p1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="../../../../public/images/icons/user-icon-dark.svg" alt="Default avatar"> <!-- Not logged user image -->
                Login now
            </a>
            <ul id="userProfileMenu" class="dropdown-menu dropdown-menu-start neutral-background white-border">
                <li><router-link class="dropdown-item white-color neutral-hover" to="/login">Login</router-link></li>
                <li><router-link class="dropdown-item white-color neutral-hover" to="/register">Register</router-link></li>
            </ul>
        </div>
        
    </div>

    <!-- Session already started -->
    <div v-if="authStore().user?.name" id="userComponent">
        <div id="userContent">
            <a id="userProfile" class="nav-link p1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="../../../../public/images/icons/user-icon-dark.svg" alt="User avatar"> <!-- Reemplazar por imagen real cuando estén relacionadas con el usuario -->
                {{ authStore().user?.name }}
                <p class="p4">{{ userPoints }} <img src="../../../../public/images/icons/trophy-icon-dark.svg" alt="Trophy icon"></p>
            </a>
            <ul id="userProfileMenu" class="dropdown-menu dropdown-menu-start neutral-background white-border">
                <li><router-link class="dropdown-item white-color neutral-hover" to="/admin">Admin</router-link></li>
                <li><router-link class="dropdown-item white-color neutral-hover" to="/profile">Perfil</router-link></li>
                <li><router-link to="/admin/posts" class="dropdown-item white-color neutral-hover">Post</router-link></li>
                <li><hr class="dropdown-divider white-background"></li>
                <li><a class="dropdown-item white-color neutral-hover" href="javascript:void(0)" @click="logout">Logout</a></li>
            </ul>
        </div>
        
    </div>
</template>

<script setup>
    /* -- IMPORTS -- */
    import { onMounted, ref } from "vue";
    import useAuth from "@/composables/auth";
    import { authStore } from '../../store/auth';
    import useRankings from "@/composables/rankings.js";

    /* -- VARIABLES -- */
    const { processing, logout } = useAuth();
    const { getRanking } = useRankings();
    const userPoints = ref(null);  // Declaramos userPoints como una variable reactiva

    /* -- FUNCTIONS -- */
    onMounted(() => {
        console.log("Obteniendo rankings [para trofeos]");
        console.log("Usuario actual: ", authStore().user?.id);
        getRanking(authStore().user?.id).then((data) => {
            console.log("User ranking: ", data);

            if (data.points) {
                userPoints.value = data.points;  // Asignamos el valor a la variable reactiva
                console.log("User points: ", userPoints.value);
            }
        });
        console.log("Trofeos obtenidos");
    });
</script>
