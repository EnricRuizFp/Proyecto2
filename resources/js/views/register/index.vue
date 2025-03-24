<template>
    <div id="registerContainer">
        <div v-if="validationErrors?.general" class="alert alert-danger">
            <div v-for="message in validationErrors.general" :key="message">
                {{ message }}
            </div>
        </div>
        <div>
            <form @submit.prevent="handleRegister" id="registerForm">
                <!-- REGISTER title -->
                <div id="registerFormTitle">
                    <h2 class="h2 white-color uppercase">
                        {{ $t("register_title") }}
                    </h2>
                </div>

                <div id="registerFormContent">
                    <!-- Username -->
                    <div class="registerFormField">
                        <label for="username" class="registerFormLabels p4">{{
                            $t("username")
                        }}</label>
                        <input
                            v-model="registerForm.username"
                            id="username"
                            class="registerFormFields"
                            type="text"
                            required
                            autofocus
                        />
                        <div
                            v-for="message in validationErrors?.username"
                            class="white-color"
                        >
                            {{ message }}
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="registerFormField">
                        <label for="name" class="registerFormLabels p4">{{
                            $t("name")
                        }}</label>
                        <input
                            v-model="registerForm.name"
                            id="name"
                            class="registerFormFields"
                            type="text"
                            required
                            autofocus
                        />
                        <div
                            v-for="message in validationErrors?.name"
                            class="white-color"
                        >
                            {{ message }}
                        </div>
                    </div>
                    <!-- Surname 1 -->
                    <div class="registerFormField">
                        <label for="surname1" class="registerFormLabels p4">{{
                            $t("surname1")
                        }}</label>
                        <input
                            v-model="registerForm.surname1"
                            id="surname1"
                            class="registerFormFields"
                            type="text"
                            required
                            autofocus
                        />
                        <div
                            v-for="message in validationErrors?.surname1"
                            class="white-color"
                        >
                            {{ message }}
                        </div>
                    </div>
                    <!-- Surname 2 -->
                    <div class="registerFormField">
                        <label for="surname2" class="registerFormLabels p4">{{
                            $t("surname2")
                        }}</label>
                        <input
                            v-model="registerForm.surname2"
                            id="surname2"
                            class="registerFormFields"
                            type="text"
                            required
                            autofocus
                        />
                        <div
                            v-for="message in validationErrors?.surname2"
                            class="white-color"
                        >
                            {{ message }}
                        </div>
                    </div>

                    <!-- Nationality -->
                    <div class="registerFormField">
                        <label
                            for="nationality"
                            class="registerFormLabels p4"
                            >{{ $t("nationality") }}</label
                        >
                        <input
                            v-model="registerForm.nationality"
                            id="nationality"
                            class="registerFormFields"
                            type="text"
                            required
                            autofocus
                        />
                        <div
                            v-for="message in validationErrors?.nationality"
                            class="white-color"
                        >
                            {{ message }}
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="registerFormField">
                        <label for="email" class="registerFormLabels p4">{{
                            $t("email")
                        }}</label>
                        <input
                            v-model="registerForm.email"
                            id="email"
                            class="registerFormFields"
                            type="email"
                            required
                            autocomplete="username"
                        />
                        <div
                            v-for="message in validationErrors?.email"
                            class="white-color"
                        >
                            {{ message }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="registerFormField">
                        <label for="password" class="registerFormLabels p4">{{
                            $t("password")
                        }}</label>
                        <input
                            v-model="registerForm.password"
                            id="password"
                            class="registerFormFields"
                            type="password"
                            required
                            autocomplete="new-password"
                        />
                        <div
                            v-for="message in validationErrors?.password"
                            class="white-color"
                        >
                            {{ message }}
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div id="registerFormField">
                        <label
                            for="password_confirmation"
                            class="registerFormLabels p4"
                            >{{ $t("confirm_password") }}</label
                        >
                        <input
                            v-model="registerForm.password_confirmation"
                            id="password_confirmation"
                            class="registerFormFields"
                            type="password"
                            required
                            autocomplete="new-password"
                        />
                        <div
                            v-for="message in validationErrors?.password_confirmation"
                            class="white-color"
                        >
                            {{ message }}
                        </div>
                    </div>

                    <!-- ¿Ya tienes cuenta? -->
                    <div id="contenedorTienesCuenta">
                        <p class="p5 white-color">
                            ¿Ya tienes cuenta?
                            <a href="/login" class="linkText underline"
                                >Inicia sesión</a
                            >
                        </p>
                    </div>

                    <!-- Register Button -->
                    <button
                        :class="{ 'opacity-25': processing }"
                        :disabled="processing"
                        id="registerButton"
                        class="primary-button"
                    >
                        {{ $t("register") }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import useAuth from "@/composables/auth";
import { useRouter } from "vue-router";

const router = useRouter();
const { registerForm, validationErrors, processing, submitRegister } =
    useAuth();

const handleRegister = async () => {
    try {
        const success = await submitRegister();
        if (success) {
            await router.push({ name: "home" });
        }
    } catch (error) {
        console.error("Error in handleRegister:", error);
    }
};
</script>

<style scoped>
#registerContainer {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: var(--background-primary);
    min-height: 100vh;
    width: 100%;
    padding: 100px 20px;
}

#registerContainer > div {
    width: 100%;
    max-width: 500px; /* Reducido de 600px */
    display: flex;
    justify-content: center;
    align-items: center;
}

#registerForm {
    width: 100%;
    max-width: 500px; /* Reducido de 600px */
    margin: 0;
    padding: 2rem;
}

@media (max-width: 768px) {
    #registerContainer {
        padding-top: 80px;
    }
}

@media (max-width: 480px) {
    #registerContainer {
        padding: 60px 10px 10px;
    }
}
</style>
