<template>
    <div id="loginContainer">
        <div>

            <form @submit.prevent="submitLogin" id="loginForm">
                <div id="loginFormContent">
                    <!-- Email -->
                    <div id="emailField">
                        <label for="email" class="loginFormLabels">{{ $t('email') }}</label>
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
                        <label for="password"  class="loginFormLabels">
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
                    <div id="rememberMeField">
                        <input type="checkbox" name="remember" v-model="loginForm.remember" id="flexCheckIndeterminate">
                        <label for="flexCheckIndeterminate">
                            {{ $t('remember_me') }}
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div id="loginButton">
                        <button :class="{ 'opacity-25': processing }" :disabled="processing">
                            {{ $t('login') }}
                        </button>
                    </div>
                </div>
                <router-link :to="{name: 'auth.forgot-password'}">{{ $t('forgot_password')}}</router-link>
            </form>
        </div>
    </div>
</template>

<script setup>
import useAuth from '@/composables/auth'

const { loginForm, validationErrors, processing, submitLogin } = useAuth();
</script>
