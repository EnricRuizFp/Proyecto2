import { ref, reactive, inject } from 'vue'
import { useRouter } from "vue-router";
import { AbilityBuilder, createMongoAbility } from '@casl/ability';
import { ABILITY_TOKEN } from '@casl/vue';
//import store from '../store'
import { authStore } from "../store/auth";

// IMPORT RANKINGS
import useRankings from './rankings';

let user = reactive({
    name: '',
    email: '',
})

export default function useAuth() {
    const processing = ref(false)
    const validationErrors = ref({})
    const router = useRouter()
    const swal = inject('$swal')
    const ability = inject(ABILITY_TOKEN)
    const auth = authStore()

    const { storeRanking } = useRankings();

    const loginForm = reactive({
        email: '',
        password: '',
        remember: false
    })

    const forgotForm = reactive({
        email: '',
    })

    const resetForm = reactive({
        email: '',
        token: '',
        password: '',
        password_confirmation: ''
    })

    const registerForm = reactive({
        username: '',
        name: '',
        surname1: '',
        surname2: '',
        email: '',
        password: '',
        password_confirmation: '',
        nationality: ''
    })

    const submitLogin = async () => {
        if (processing.value) return

        processing.value = true
        validationErrors.value = {}

        await axios.post('/login', loginForm)
            .then(async response => {
                console.log('await auth.getUser()');
                await auth.getUser()
                console.log('uth.user.value');
                console.log(auth.user.value);
                //await store.dispatch('auth/getUser')
                await loginUser()
                swal({
                    icon: 'success',
                    title: 'Login correcto',
                    showConfirmButton: false,
                    timer: 1500
                })
                await router.push({ name: 'admin.index' })
            })
            .catch(error => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors
                }
            })
            .finally(() => processing.value = false)
    }

    // const submitRegister = async () => {

    //     if (processing.value) return

    //     processing.value = true
    //     validationErrors.value = {}

    //     await axios.post('/register', registerForm)
    //         .then(async response => {

    //             // Create empty ranking
    //             storeRanking(ranking);

    //             // Finalize with swal alert
    //             swal({
    //                 icon: 'success',
    //                 title: 'Registration successfully',
    //                 showConfirmButton: false,
    //                 timer: 1500
    //             })
    //             await router.push({ name: 'auth.login' })
    //         })
    //         .catch(error => {
    //             if (error.response?.data) {
    //                 validationErrors.value = error.response.data.errors
    //             }
    //         })
    //         .finally(() => processing.value = false)
    // }

    const submitRegister = async () => {

        if (processing.value) return;
    
        processing.value = true;
        validationErrors.value = {};
    
        await axios.post('/register', registerForm)
            .then(async response => {

                const newUser = response.data.data; // Suponiendo que la API devuelve el usuario registrado
                console.log(response.data.data);
    
                // Crear un ranking vacío asignado al nuevo usuario
                const rankingData = {
                    user_id: newUser.id,  // Asigna el ID del usuario recién creado
                    points: 0,
                    wins: 0,
                    losses: 0,
                    draws: 0
                };
    
                // Llamar a storeRanking con los datos del ranking
                await storeRanking(rankingData);

                // Mostrar mensaje de éxito
                swal({
                    icon: 'success',
                    title: 'Registration successfully',
                    showConfirmButton: false,
                    timer: 1500
                });
    
                await router.push({ name: 'auth.login' });
            })
            .catch(error => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => processing.value = false);

        console.log("User registred correctly.");
    };
    

    const submitForgotPassword = async () => {
        if (processing.value) return

        processing.value = true
        validationErrors.value = {}

        await axios.post('/api/forget-password', forgotForm)
            .then(async response => {
                swal({
                    icon: 'success',
                    title: 'We have emailed your password reset link! Please check your mail inbox.',
                    showConfirmButton: false,
                    timer: 1500
                })
                // await router.push({ name: 'admin.index' })
            })
            .catch(error => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors
                }
            })
            .finally(() => processing.value = false)
    }

    const submitResetPassword = async () => {
        if (processing.value) return

        processing.value = true
        validationErrors.value = {}

        await axios.post('/api/reset-password', resetForm)
            .then(async response => {
                swal({
                    icon: 'success',
                    title: 'Password successfully changed.',
                    showConfirmButton: false,
                    timer: 1500
                })
                await router.push({ name: 'auth.login' })
            })
            .catch(error => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors
                }
            })
            .finally(() => processing.value = false)
    }

    const loginUser = () => {
        //const auth = authStore(); //TODO test
        console.log('loginUser auth Compostable ' + auth.user)
        console.log(auth.user)
        user = auth.user
        //user = store.state.auth.user
        // Cookies.set('loggedIn', true)
        getAbilities()
    }

    const getUser = async () => {
        const auth = authStore();
        console.log('getUser')

        if (auth.authenticated) {
            await auth.getUser()
            console.log(auth.user.value)
            console.log(auth.authenticated)
            await loginUser()
        }
    }

    const logout = async () => {
        if (processing.value) return

        processing.value = true

        axios.post('/logout')
            .then(response => {
                user.name = ''
                user.email = ''
                auth.logout()
                //store.dispatch('auth/logout')
                router.push({ name: 'auth.login' })
            })
            .catch(error => {
                // swal({
                //     icon: 'error',
                //     title: error.response.status,
                //     text: error.response.statusText
                // })
            })
            .finally(() => {
                processing.value = false
                // Cookies.remove('loggedIn')
            })
    }

    const getAbilities = async () => {
        await axios.get('/api/abilities')
            .then(response => {
                const permissions = response.data
                const { can, rules } = new AbilityBuilder(createMongoAbility)

                can(permissions)

                ability.update(rules)
            })
    }

    return {
        loginForm,
        registerForm,
        forgotForm,
        resetForm,
        validationErrors,
        processing,
        submitLogin,
        submitRegister,
        submitForgotPassword,
        submitResetPassword,
        user,
        getUser,
        logout,
        getAbilities
    }
}
