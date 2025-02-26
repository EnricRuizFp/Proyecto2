<template>
    <div id="forgotContainer">
        <div>
            <form @submit.prevent="submitForgotPassword" id="forgotForm">

                <!-- FORGOT title -->
                <div id="forgotFormTitle">
                    <h2 class="h2 white-color uppercase">{{ $t('forgot_password_1')}}</h2>
                </div>

                <div class="forgonFormContent">

                    <!-- Email -->
                    <div class="forgotFormField">
                        <label for="email" class="forgotFormLabels p4">{{ $t('email') }}</label>
                        <input v-model="forgotForm.email" id="email" type="email" class="forgotFormFields" required autofocus autocomplete="username">
                        <!-- Validation Errors -->
                        <div>
                            <div v-for="message in validationErrors?.email">
                                {{ message }}
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <button :class="{ 'opacity-25': processing }" :disabled="processing" id="forgotButton" class="primary-button">
                        {{ $t('send_password_reset_link') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import useAuth from '@/composables/auth'

const { forgotForm, validationErrors, processing, submitForgotPassword } = useAuth();
</script>

<style scoped>

</style>
