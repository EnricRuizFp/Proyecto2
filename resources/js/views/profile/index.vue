<template>
    <div id="myProfilePageContent">
        <div id="myProfilePageContainer" class="col-8 mx-auto container-fluid">
            <h3 class="h3 white-color">MI CUENTA</h3>

            <div id="myProfileBox" class="row">
                <!-- Barra lateral -->
                <div id="myProfileLateralBar" class="col-3">
                    <div class="myProfileLateralBarButtons">
                        <button 
                            class="myProfileLateralBarButton white-color p2" 
                            @click="changePage('myProfile')"
                            :class="{ activeButton: isActive('myProfile') }"
                        >
                            Perfil
                        </button>
                    </div>
                    <div class="myProfileLateralBarButtons">
                        <button 
                            class="myProfileLateralBarButton white-color p2" 
                            @click="changePage('myCareer')"
                            :class="{ activeButton: isActive('myCareer') }"
                        >
                            Mi carrera
                        </button>
                    </div>
                    <div class="myProfileLateralBarButtons">
                        <button 
                            class="myProfileLateralBarButton white-color p2" 
                            @click="changePage('assistance')"
                            :class="{ activeButton: isActive('assistance') }"
                        >
                            Asistencia
                        </button>
                    </div>
                </div>

                <!-- Contenido dinámico -->
                <div id="myProfilePageMenuContainer" class="col-9">
                    <div v-if="actualPage === 'myProfile'">
                        <myProfileComponent />
                    </div>
                    <div v-if="actualPage === 'myCareer'">
                        <myCareerComponent />
                    </div>
                    <div v-if="actualPage === 'assistance'">
                        <assistanceComponent />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import myProfileComponent from "../../components/profileComponents/myProfileComponent.vue";
import myCareerComponent from "../../components/profileComponents/myCareerComponent.vue";
import assistanceComponent from "../../components/profileComponents/assistanceComponent.vue";

// Variable reactiva para el estado de la página actual
const actualPage = ref("myProfile");

// Función para cambiar de página
const changePage = (page) => {
    actualPage.value = page;
};

// Función para verificar si un botón debe estar resaltado
const isActive = (page) => {
    return actualPage.value === page;
};
</script>
