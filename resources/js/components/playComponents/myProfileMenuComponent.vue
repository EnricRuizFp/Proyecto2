<template>
    <div id="myProfileMenuContainer">
        <!-- Titulo -->
        <div id="myProfileMenuTitle">
            <h4 class="bold white-color">MI PERFIL</h4>
        </div>
        <!-- Contenedor -->
        <div id="myProfileContainer">
            <div id="myProfileInternContainer" class="white-color">
                <UserComponent />
            </div>
        </div>
    </div>
</template>

<script setup>
import UserComponent from "../navbar/UserComponent.vue";
</script>