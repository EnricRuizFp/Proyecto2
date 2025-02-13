<template>
    <div class="row justify-content-center my-5">
        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2>Edit ship</h2>
                    <form @submit.prevent="submitForm">

                        <!-- SHIP NAME -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Name
                            </label>
                            <div class="form-outline w-25">
                                <input v-model="ship.name" id="name" type="text" class="form-control">
                            </div>
                            <div class="text-danger mt-1">
                                {{ errors.name }}
                            </div>
                            <div class="text-danger mt-1">
                                <div v-for="message in validationErrors?.name">
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- SHIP SIZE -->
                        <div class="mb-3">
                            <label for="size" class="form-label">
                                Size
                            </label>
                            <div class="form-outline w-25">
                                <input v-model="ship.size" id="size" type="number" class="form-control">
                            </div>
                            <div class="text-danger mt-1">
                                {{ errors.size }}
                            </div>
                            <div class="text-danger mt-1">
                                <div v-for="message in validationErrors?.size">
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" :disabled="isLoading" class="btn btn-primary">
                                <span v-if="isLoading">Processing...</span>
                                <span v-else>Update</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<!--
<script async setup>
import {onMounted, reactive, watchEffect, ref} from "vue";
import {useRoute} from "vue-router";
import useShips from "@/composables/ships";
import {useForm, useField, defineRule} from "vee-validate";
import {required, min} from "@/validation/rules"

defineRule('required', required)
defineRule('min', min);

// Define a validation schema
const schema = {
    name: 'required|min:3',
    size: 'required|min:1'
}
// Create a form context with the validation schema
const {validate, errors, resetForm} = useForm({validationSchema: schema})
// Define actual fields for validation
const {value: name} = useField('name', null, {initialValue: ''});
const {ship: postData,
    getShip,
    updateShip,
    validationErrors,
    isLoading
} = useShips()
// const {allPermission, getAllPermissions} = usePermissions()
const ship = reactive({
    name,
    size
})
const route = useRoute()

let response = await axios.get('/api/permissions/')
let allPermission = response.data.data;

response = await axios.get('/api/ship-permissions/' + route.params.id)
let shipList = response.data.data;

let diffPermission = getDifference(allPermission, shipList);

let availablePermissions = ref(diffPermission)
let currentPermissions = ref(shipList)
const permisions = ref(  [diffPermission, shipList]);


function submitForm() {
    validate().then(form => {
        if (form.valid) {
            //let permissions = currentPermissions.value.map(a => a.id);
            let permissions = permisions.value[1].map(a => a.id);
            updateShip(ship, permissions)
            //  updateRole(role)
        }
    })
}

function onChangeList(data) {
    //console.log(data)
}

onMounted(() => {
    getShip(route.params.id)
})
// https://vuejs.org/api/reactivity-core.html#watcheffect
watchEffect(() => {
    ship.id = postData.value.id
    ship.name = postData.value.name
    ship.size = postData.value.size
})


function getDifference(array1, array2) {
    return array1.filter(object1 => {
        return !array2.some(object2 => {
            return object1.id === object2.id;
        });
    });
}
</script>
-->

<script setup>

    import { reactive, ref, onMounted, watchEffect } from 'vue';
    import { useRoute } from 'vue-router';
    import { useToast } from 'primevue/usetoast';
    import { useForm, useField, defineRule } from 'vee-validate';
    import { required, min } from "@/validation/rules";
    import useShips from "@/composables/ships";

    // Definir reglas de validación
    defineRule('required', required);
    defineRule('min', min);

    // Esquema de validación
    const schema = {
        name: 'required',
        size: 'required|min:1'
    };

    // Create a form context with the validation schema
    const { validate, errors, resetForm } = useForm({ validationSchema: schema });

    // Campos para el formulario
    const { value: name } = useField('name', null, {initialValue: ''});
    const { value: size } = useField('size', null, {initialValue: ''});
    const { ship: shipData, getShip, updateShip, validationErrors, isLoading } = useShips();
    // Estado reactivo para los datos del barco
    const ship = reactive({
        name: name,
        size: size
    });

    // Obtener el barco desde la API
    const route = useRoute();

    // Submit the form
    function submitForm() {
        validate().then(form => { if (form.valid) updateShip(ship)})
    }

    // Cargar detalles del barco cuando el componente se monta
    onMounted(() => {
        getShip(route.params.id);
    });

    // https://vuejs.org/api/reactivity-core.html#watcheffect
    watchEffect(() => {
        ship.id = shipData.value.id
        ship.name = shipData.value.name
        ship.size = shipData.value.size
    });

</script>

