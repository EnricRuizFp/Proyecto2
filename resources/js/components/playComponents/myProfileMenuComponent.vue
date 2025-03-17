<template>
    <div id="myProfileMenuContainer">
        <!-- Titulo -->
        <div id="myProfileMenuTitle">
            <h4 class="bold white-color">MI PERFIL</h4>
        </div>
        <!-- Contenedor -->
        <div>
            <div id="myProfileInternContainer" class="white-color">
                <UserComponent variant="profile" />
            </div>
        </div>
    </div>
</template>

<script setup>
import UserComponent from "../navbar/UserComponent.vue";
</script>

<style scoped>
#myProfileInternContainer {
    width: 100%;
    height: 100%;
}

#myProfileMenuContainer > div {
    width: 100%;
}

@media (max-width: 1200px) {
    #myProfileMenuContainer {
        padding: 1rem;
    }
}

@media (max-width: 768px) {
    #myProfileMenuContainer {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    #myProfileMenuContainer {
        padding: 0.5rem;
    }
}
</style>
