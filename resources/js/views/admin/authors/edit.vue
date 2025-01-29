<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <div class="table-title">
                    {{ author }}
                    <div class="row">
                        <div class="col-sm-8"><h2>Editar Autores</h2></div>
                    </div>

                    <div class="mb-3">
                        <label for="id" class="form-label">ID</label>
                        <input
                            type="text"
                            class="form-control"
                            id="id"
                            readonly
                            v-model="author.id"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            v-model="author.name"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="surname" class="form-label">Apellido</label>
                        <input
                            type="text"
                            class="form-control"
                            id="surname"
                            v-model="author.surname"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            v-model="author.email"
                        />
                    </div>

                    <button type="submit" class="btn btn-primary" @click="">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
    <Toast />
</template>
<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useToast } from "primevue/useToast";

const toast = useToast();
const route = useRoute();
const author = ref({});

const schema = yup.object({
    id: yup.number().required(),
    name: yup.string().required().max(10),
    surname: yup.string().min(3),
    email: yup.string().email(),
});

onMounted(() => {
    console.log("route.params.id");
    axio.get("api/author/" + route.params.id).then((response) => {
        author.value = response.data.data;
    });
});

const updateAuthor = async () => {
    axio.put("api/author/" + route.params.id, author.value).then((response) => {
        author.value = response.data.data;
    });
};
</script>
