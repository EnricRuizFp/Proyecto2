import { ref, reactive, inject } from "vue";
import { useRouter } from "vue-router";
import { AbilityBuilder, createMongoAbility } from "@casl/ability";
import { ABILITY_TOKEN } from "@casl/vue";
//import store from '../store'
import { authStore } from "../store/auth";

// IMPORT RANKINGS
import useRankings from "./rankings";

let user = reactive({
    name: "",
    email: "",
});

export default function useAuth() {
    const processing = ref(false);
    const validationErrors = ref({});
    const router = useRouter();
    const swal = inject("$swal");
    const ability = inject(ABILITY_TOKEN);
    const auth = authStore();

    const { storeRankingSilently } = useRankings();

    const loginForm = reactive({
        email: "",
        password: "",
        remember: false,
    });

    const forgotForm = reactive({
        email: "",
    });

    const resetForm = reactive({
        email: "",
        token: "",
        password: "",
        password_confirmation: "",
    });

    const registerForm = reactive({
        username: "",
        name: "",
        surname1: "",
        surname2: "",
        email: "",
        password: "",
        password_confirmation: "",
        nationality: "",
    });

    const submitLogin = async () => {
        if (processing.value) return;

        processing.value = true;
        validationErrors.value = {};

        try {
            const response = await axios.post("/login", loginForm);
            await auth.getUser();
            await loginUser();

            swal({
                icon: "success",
                title: "¡Bienvenido!",
                text: "Has iniciado sesión correctamente",
                showConfirmButton: false,
                timer: 1500,
                background: "#1a1a1a",
                customClass: {
                    title: "swal-title",
                    content: "swal-text",
                    popup: "swal-popup",
                },
            });

            return response; // Retornamos la respuesta para manejar la redirección en el componente
        } catch (error) {
            if (error.response?.data) {
                validationErrors.value = error.response.data.errors;
            }
            throw error; // Re-lanzamos el error para manejarlo en el componente
        } finally {
            processing.value = false;
        }
    };

    const submitRegister = async () => {
        if (processing.value) return false;
        processing.value = true;
        validationErrors.value = {};

        try {
            // 1. Register the user
            const registerResponse = await axios.post(
                "/register",
                registerForm
            );

            // Check if registration response is valid and contains user data
            if (
                !registerResponse.data ||
                !registerResponse.data.data ||
                !registerResponse.data.data.id
            ) {
                console.error(
                    "Registration response missing user data:",
                    registerResponse.data
                );
                throw new Error(
                    "Invalid response format from server during registration."
                );
            }

            const newUser = registerResponse.data.data;
            const newUserId = newUser.id;
            console.log("User registered successfully with ID:", newUserId); // Log user ID

            // 2. Create initial ranking for the new user
            try {
                console.log(
                    "Attempting to create initial ranking for user ID:",
                    newUserId
                );
                await storeRankingSilently({
                    user_id: newUserId,
                    points: 0,
                    wins: 0,
                    losses: 0,
                    draws: 0,
                });
                console.log(
                    "Initial ranking created successfully for user ID:",
                    newUserId
                );
            } catch (rankingError) {
                // Log the specific error during ranking creation but continue the process
                console.error(
                    `Failed to create initial ranking for user ${newUserId}:`,
                    rankingError.response?.data || rankingError.message
                );
                // Optionally notify the user or admin about the ranking issue, but don't block login
                swal({
                    icon: "warning",
                    title: "Registro parcial",
                    text: "Tu cuenta fue creada, pero hubo un problema al inicializar tu ranking. Por favor, contacta soporte si el problema persiste.",
                    showConfirmButton: true,
                    background: "#1a1a1a",
                    customClass: {
                        title: "swal-title",
                        content: "swal-text",
                        popup: "swal-popup",
                        confirmButton: "swal-button",
                    },
                });
            }

            // 3. Login the new user automatically
            console.log("Attempting automatic login for:", registerForm.email);
            await axios.post("/login", {
                email: registerForm.email,
                password: registerForm.password,
            });
            console.log("Automatic login successful");

            // 4. Fetch user data and abilities
            await auth.getUser();
            await loginUser(); // This updates local state and abilities

            // 5. Show success message
            swal({
                icon: "success",
                title: "¡Registro exitoso!",
                text: "Tu cuenta ha sido creada correctamente",
                showConfirmButton: false,
                timer: 1500,
                background: "#1a1a1a",
                customClass: {
                    title: "swal-title",
                    content: "swal-text",
                    popup: "swal-popup",
                },
            });

            return true; // Indicate overall success for redirection
        } catch (error) {
            console.error(
                "Registration process failed:",
                error.response?.data || error.message
            );

            if (error.response?.data?.errors) {
                validationErrors.value = error.response.data.errors;
            } else {
                // General error during registration or login steps
                validationErrors.value = {
                    general: [
                        error.message ||
                            "An unexpected error occurred during registration. Please try again.",
                    ],
                };
                swal({
                    icon: "error",
                    title: "Error de registro",
                    text: validationErrors.value.general[0],
                    showConfirmButton: true,
                    background: "#1a1a1a",
                    customClass: {
                        title: "swal-title",
                        content: "swal-text",
                        popup: "swal-popup",
                        confirmButton: "swal-button",
                    },
                });
            }
            return false; // Indicate failure
        } finally {
            processing.value = false;
        }
    };

    const submitForgotPassword = async () => {
        if (processing.value) return;

        processing.value = true;
        validationErrors.value = {};

        await axios
            .post("/api/forget-password", forgotForm)
            .then(async (response) => {
                swal({
                    icon: "success",
                    title: "We have emailed your password reset link! Please check your mail inbox.",
                    showConfirmButton: false,
                    timer: 1500,
                });
                // await router.push({ name: 'admin.index' })
            })
            .catch((error) => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => (processing.value = false));
    };

    const submitResetPassword = async () => {
        if (processing.value) return;

        processing.value = true;
        validationErrors.value = {};

        await axios
            .post("/api/reset-password", resetForm)
            .then(async (response) => {
                swal({
                    icon: "success",
                    title: "Password successfully changed.",
                    showConfirmButton: false,
                    timer: 1500,
                });
                await router.push({ name: "auth.login" });
            })
            .catch((error) => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors;
                }
            })
            .finally(() => (processing.value = false));
    };

    const loginUser = () => {
        console.log("loginUser auth Compostable " + auth.user);
        console.log(auth.user);
        user = auth.user;
        getAbilities();
    };

    const getUser = async () => {
        const auth = authStore();
        console.log("getUser");

        if (auth.authenticated) {
            await auth.getUser();
            console.log(auth.user.value);
            console.log(auth.authenticated);
            await loginUser();
        }
    };

    const logout = async () => {
        if (processing.value) return;

        processing.value = true;

        axios
            .post("/logout")
            .then((response) => {
                user.name = "";
                user.email = "";
                auth.logout();
                router.push({ name: "auth.login" });
            })
            .catch((error) => {
                // swal({
                //     icon: 'error',
                //     title: error.response.status,
                //     text: error.response.statusText
                // })
            })
            .finally(() => {
                processing.value = false;
            });
    };

    const getAbilities = async () => {
        await axios.get("/api/abilities").then((response) => {
            const permissions = response.data;
            const { can, rules } = new AbilityBuilder(createMongoAbility);

            can(permissions);

            ability.update(rules);
        });
    };

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
        getAbilities,
    };
}
