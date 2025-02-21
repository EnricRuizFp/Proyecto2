<template>
    <div class="row justify-content-center my-5">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form @submit.prevent="submitForm">
                        <!-- Name field -->
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input
                                v-model="post.name"
                                type="text"
                                class="form-control"
                                id="name"
                            />
                            <div class="text-danger mt-1">
                                {{ errors.name }}
                            </div>
                            <div class="text-danger mt-1">
                                <div v-for="message in validationErrors?.name">
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Surname1 field -->
                        <div class="form-group">
                            <label for="surname1">Apellido 1</label>
                            <input
                                v-model="post.surname1"
                                type="text"
                                class="form-control"
                                id="surname1"
                            />
                            <div class="text-danger mt-1">
                                {{ errors.surname1 }}
                            </div>
                            <div class="text-danger mt-1">
                                <div
                                    v-for="message in validationErrors?.surname1"
                                >
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Surname2 field -->
                        <div class="form-group">
                            <label for="surname2">Apellido 2</label>
                            <input
                                v-model="post.surname2"
                                type="text"
                                class="form-control"
                                id="surname2"
                            />
                            <div class="text-danger mt-1">
                                {{ errors.surname2 }}
                            </div>
                            <div class="text-danger mt-1">
                                <div
                                    v-for="message in validationErrors?.surname2"
                                >
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Email field -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input
                                v-model="post.email"
                                id="email"
                                type="email"
                                class="form-control"
                            />
                            <div class="text-danger mt-1">
                                {{ errors.email }}
                            </div>
                            <div class="text-danger mt-1">
                                <div v-for="message in validationErrors?.email">
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Password field -->
                        <div class="mb-3">
                            <label for="password" class="form-label"
                                >Password</label
                            >
                            <input
                                v-model="post.password"
                                id="password"
                                type="password"
                                class="form-control"
                            />
                            <div class="text-danger mt-1">
                                {{ errors.password }}
                            </div>
                            <div class="text-danger mt-1">
                                <div
                                    v-for="message in validationErrors?.password"
                                >
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Role field -->
                        <div class="form-group">
                            <label for="roles">Roles</label>
                            <MultiSelect
                                id="roles"
                                v-model="post.role_id"
                                display="chip"
                                :options="roleList"
                                optionLabel="name"
                                dataKey="id"
                                placeholder="Seleciona los roles"
                                appendTo="self"
                                class="w-100"
                            />
                            <div class="text-danger mt-1">
                                {{ errors.role_id }}
                            </div>
                            <div class="text-danger mt-1">
                                <div
                                    v-for="message in validationErrors?.role_id"
                                >
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Submit button -->
                        <div class="mt-4">
                            <button
                                :disabled="isLoading"
                                class="btn btn-primary"
                            >
                                <div v-show="isLoading" class=""></div>
                                <span v-if="isLoading">Processing...</span>
                                <span v-else>Save</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive } from "vue";
import useRoles from "@/composables/roles";
import useUsers from "@/composables/users";

const { roleList, getRoleList } = useRoles();
const { storeUser, validationErrors, isLoading } = useUsers();

import { useForm, useField, defineRule } from "vee-validate";
import { required, min } from "@/validation/rules";
defineRule("required", required);
defineRule("min", min);

// Update validation schema to make surname1 and surname2 optional
const schema = {
    name: "required",
    email: "required",
    password: "required|min:8",
    role_id: "required",
};

// Update field definitions
const { validate, errors } = useForm({ validationSchema: schema });
const { value: name } = useField("name", null, { initialValue: "" });
const { value: email } = useField("email", null, { initialValue: "" });
const { value: password } = useField("password", null, { initialValue: "" });
const { value: role_id } = useField("role_id", null, {
    initialValue: "",
    label: "role",
});
const { value: surname1 } = useField("surname1", null, { initialValue: "" });
const { value: surname2 } = useField("surname2", null, { initialValue: "" });

// Asegurarse de que role_id se inicializa como array si es multiple
const post = reactive({
    name: "",
    email: "",
    password: "",
    role_id: [], // Inicializar como array vacío si es selección múltiple
    surname1: "",
    surname2: "",
});

function submitForm() {
    validate().then((form) => {
        if (form.valid) {
            // Asegurarse de que role_id es un array
            if (!Array.isArray(post.role_id)) {
                post.role_id = [post.role_id];
            }
            storeUser(post);
        }
    });
}

onMounted(() => {
    getRoleList();
});
</script>

<style>
.form-group {
    margin-bottom: 1rem;
}
label {
    margin-bottom: 0.3rem;
}
</style>
