<template>
    <div class="row justify-content-center my-5">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form @submit.prevent="submitForm">
                        <div class="mb-3">
                            <label for="ship_name" class="form-label">Ship name</label>
                            <input v-model="post.ship_name" id="ship_name" type="text" class="form-control">
                            <div class="text-danger mt-1">
                                {{ errors.ship_name }}
                            </div>
                            <div class="text-danger mt-1">
                                <div v-for="message in validationErrors?.ship_name">
                                    {{ message }}
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ship_size" class="form-label">Size</label>
                            <input v-model="post.ship_size" id="ship_size" type="number" class="form-control">
                            <div class="text-danger mt-1">
                                {{ errors.ship_size }}
                            </div>
                            <div class="text-danger mt-1">
                                <div v-for="message in validationErrors?.ship_size">
                                    {{ message }}
                                </div>
                            </div>
                        </div>
                        <!-- Buttons -->
                        <div class="mt-4">
                            <button :disabled="isLoading" class="btn btn-primary">
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
    import useShips from "@/composables/ships";

    const { storeShip, validationErrors, isLoading } = useShips();

    import { useForm, useField, defineRule } from "vee-validate";
    import { required, min } from "@/validation/rules";
    defineRule('required', required);
    defineRule('min', min);

    // Define a validation schema
    const schema = {
        ship_name: 'required',
        ship_size: 'required|min:1|max:5',
    }
    // Create a form context with the validation schema
    const { validate, errors } = useForm({ validationSchema: schema })
    // Define actual fields for validation
    const { value: name } = useField('name', null, { initialValue: '' });
    const { value: email } = useField('email', null, { initialValue: '' });
    const { value: password } = useField('password', null, { initialValue: '' });
    const { value: role_id } = useField('role_id', null, { initialValue: '', label: 'role' });

    const post = reactive({
        name,
        email,
        password,
        role_id,
    })
    function submitForm() {
        validate().then(form => { if (form.valid) storeUser(post) })
    }
    onMounted(() => {
        getRoleList()
    })
</script>
