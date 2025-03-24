<template>
    <div id="loginContainer">
        <div>
            <form @submit.prevent="handleLogin" id="loginForm">
                <!-- LOGIN title -->
                <div id="loginFormTitle">
                    <h2 class="h2 white-color uppercase">{{ $t("login") }}</h2>
                </div>

                <div id="loginFormContent">
                    <!-- Email -->
                    <div class="loginFormField">
                        <label for="email" class="loginFormLabels p4">{{
                            $t("email")
                        }}</label>
                        <input
                            v-model="loginForm.email"
                            id="email"
                            class="loginFormFields"
                            type="email"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <!-- Validation Errors -->
                        <div>
                            <div v-for="message in validationErrors?.email">
                                {{ message }}
                            </div>
                        </div>
                    </div>
                    <!-- Password -->
                    <div class="loginFormField">
                        <label for="password" class="loginFormLabels p4">
                            {{ $t("password") }}
                        </label>
                        <input
                            v-model="loginForm.password"
                            id="password"
                            class="loginFormFields"
                            type="password"
                            required
                            autocomplete="current-password"
                        />
                        <!-- Validation Errors -->
                        <div>
                            <div v-for="message in validationErrors?.password">
                                {{ message }}
                            </div>
                        </div>
                    </div>
                    <!-- Remember me -->
                    <div id="rememberMeField" class="color-white p5">
                        <input
                            type="checkbox"
                            name="remember"
                            v-model="loginForm.remember"
                            id="flexCheckIndeterminate"
                        />
                        <label for="flexCheckIndeterminate">
                            {{ $t("remember_me") }}
                        </label>
                    </div>

                    <!-- Ya tienes cuenta? -->
                    <div id="contenedorNoTienesCuenta">
                        <p class="p5 white-color">
                            ¿Todavía no tienes cuenta?
                            <a href="/register" class="linkText underline"
                                >Regístrate</a
                            >
                        </p>
                    </div>

                    <!-- Buttons -->
                    <button
                        :class="{ 'opacity-25': processing }"
                        :disabled="processing"
                        id="loginButton"
                        class="primary-button"
                    >
                        {{ $t("login") }}
                    </button>
                </div>

                <div id="forgotPasswordText">
                    <router-link
                        :to="{ name: 'auth.forgot-password' }"
                        class="white-color"
                        >{{ $t("forgot_password") }}</router-link
                    >
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import useAuth from "@/composables/auth";
import { useRouter } from "vue-router";

const router = useRouter();
const { loginForm, validationErrors, processing, submitLogin } = useAuth();

const handleLogin = async () => {
    try {
        await submitLogin();
        if (
            !validationErrors.value ||
            Object.keys(validationErrors.value).length === 0
        ) {
            await router.push({ name: "home" });
        }
    } catch (error) {
        console.error("Error during login:", error);
    }
};
</script>

<style scoped>
#loginContainer {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: var(--background-primary);
    min-height: 100vh;
    width: 100%;
    padding: 100px 20px;
}

#loginContainer > div {
    width: 100%;
    max-width: 500px; /* Reducido de 600px */
    display: flex;
    justify-content: center;
    align-items: center;
}

#loginForm {
    width: 100%;
    max-width: 500px; /* Reducido de 600px */
    margin: 0;
    padding: 2rem;
}

@media (max-width: 768px) {
    #loginContainer {
        padding-top: 80px;
    }
}

@media (max-width: 480px) {
    #loginContainer {
        padding: 60px 10px 10px;
    }
}
</style>
