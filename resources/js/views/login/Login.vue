<template>
    <div id="loginContainer">
        <div>

            <form @submit.prevent="submitLogin" id="loginForm">

                <!-- LOGIN title -->
                <div id="loginFormTitle">
                    <h2 class="h2 white-color">INICIAR SESIÓN</h2>
                </div>

                <div id="loginFormContent">

                    <!-- Email -->
                    <div id="emailField">
                        <label for="email" class="loginFormLabels p4">{{ $t('email') }}</label>
                        <input v-model="loginForm.email" id="email" class="loginFormFields" type="email" required autofocus autocomplete="username">
                        <!-- Validation Errors -->
                        <div>
                            <div v-for="message in validationErrors?.email">
                                {{ message }}
                            </div>
                        </div>
                    </div>
                    <!-- Password -->
                    <div id="passwordField">
                        <label for="password"  class="loginFormLabels p4">
                            {{ $t('password') }}
                        </label>
                        <input v-model="loginForm.password" id="password" class="loginFormFields" type="password" required autocomplete="current-password">
                        <!-- Validation Errors -->
                        <div>
                            <div v-for="message in validationErrors?.password">
                                {{ message }}
                            </div>
                        </div>
                    </div>
                    <!-- Remember me -->
                    <div id="rememberMeField" class="color-white p5">
                        <input type="checkbox" name="remember" v-model="loginForm.remember" id="flexCheckIndeterminate">
                        <label for="flexCheckIndeterminate">
                            {{ $t('remember_me') }}
                        </label>
                    </div>

                    <!-- Ya tienes cuenta? -->
                    <div id="contenedorNoTienesCuenta">
                        <p class="p5 white-color">¿Todavía no tienes cuenta? <a href="/register" class="linkText underline">Regístrate</a></p>

                    </div>

                    <!-- Buttons -->
                    <div>
                        <button :class="{ 'opacity-25': processing }" :disabled="processing" id="loginButton" class="primary-button">
                            {{ $t('login') }}
                        </button>
                    </div>
                </div>

                <div id="forgotPasswordText">
                    <router-link :to="{name: 'auth.forgot-password'}" class="white-color">{{ $t('forgot_password')}}</router-link>
                </div>
                
            </form>
        </div>
    </div>
</template>

<script setup>
import useAuth from '@/composables/auth'

const { loginForm, validationErrors, processing, submitLogin } = useAuth();
</script>
