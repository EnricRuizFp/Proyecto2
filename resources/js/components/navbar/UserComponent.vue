<template>
    <!-- No session -->
    <div
        v-if="!authStore().user?.name"
        class="nav-item dropdown"
        id="userComponent"
    >
        <div id="userContent">
            <a
                id="userProfile"
                class="nav-link p1"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <img
                    src="../../../../public/images/icons/user-icon-dark.svg"
                    alt="Default avatar"
                />
                <!-- Not logged user image -->
                Login now
            </a>
            <div
                id="userProfileMenu"
                class="dropdown-menu dropdown-menu-start app-background-primary white-border"
            >
                <div>
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/login"
                        >Login</router-link
                    >
                </div>
                <div>
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/register"
                        >Register</router-link
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- Session already started -->
    <div v-if="authStore().user?.name" id="userComponent">
        <div id="userContent">
            <a
                id="userProfile"
                class="nav-link p1 white-color"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <div class="profile-content">
                    <div class="left-side">
                        <div class="userImageContainer">
                            <img
                                :src="getAvatarUrl()"
                                :alt="authStore().user?.name"
                                @error="
                                    (e) =>
                                        (e.target.src =
                                            '/images/icons/user-icon-dark.svg')
                                "
                                class="user-avatar"
                            />
                        </div>
                        <div class="usernameContainer">
                            <span class="username">{{
                                authStore().user?.name
                            }}</span>
                        </div>
                    </div>
                    <div class="pointsContainer">
                        <span class="points">
                            {{ userPoints }}
                            <img
                                src="../../../../public/images/icons/trophy-icon-dark.svg"
                                alt="Trophy icon"
                            />
                        </span>
                    </div>
                </div>
            </a>
            <div
                id="userProfileMenu"
                class="dropdown-menu dropdown-menu-start neutral-background white-border"
            >
                <div>
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/admin"
                        >Admin</router-link
                    >
                </div>
                <div>
                    <router-link
                        class="dropdown-item white-color neutral-hover"
                        to="/profile"
                        >Mi Perfil</router-link
                    >
                </div>
                <div>
                    <router-link
                        to="/admin/posts"
                        class="dropdown-item white-color neutral-hover"
                        >Post</router-link
                    >
                </div>
                <div><hr class="dropdown-divider white-background" /></div>
                <div>
                    <a
                        class="dropdown-item white-color neutral-hover"
                        href="javascript:void(0)"
                        @click="logout"
                        >Logout</a
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTS -- */
import { onMounted, ref } from "vue";
import useAuth from "@/composables/auth";
import useUsers from "@/composables/users";
import { authStore } from "../../store/auth";
import useRankings from "@/composables/rankings.js";

/* -- VARIABLES -- */
const { processing, logout } = useAuth();
const { getRanking } = useRankings();
const { getUser } = useUsers();
const userPoints = ref(null);
const userAvatar = ref(null);

/* -- FUNCTIONS -- */
const updateUserData = async () => {
    if (authStore().user?.id) {
        try {
            const userData = await getUser(authStore().user?.id);
            if (userData) {
                userAvatar.value = userData.avatar;
                console.log("Avatar URL:", userData.avatar); // Debug
            }
        } catch (error) {
            console.error("Error fetching user data:", error);
        }
    }
};

const getAvatarUrl = () => {
    return userAvatar.value || "/images/icons/user-icon-dark.svg";
};

onMounted(async () => {
    if (authStore().user?.id) {
        // Obtener puntos
        const rankingData = await getRanking(authStore().user?.id);
        if (rankingData.points) {
            userPoints.value = rankingData.points;
        }

        // Actualizar datos del usuario
        await updateUserData();
    }
});
</script>

<style scoped>
#userContent {
    width: 100%;
    min-width: 250px;
}

#userProfile {
    width: 100%;
}

.profile-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 0.5rem;
}

.left-side {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.userImageContainer {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    overflow: hidden;
}

.usernameContainer {
    padding-left: 0.5rem;
}

.pointsContainer {
    display: flex;
    align-items: center;
}

.points {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
}

.points img {
    height: 24px;
    width: 24px;
}

.user-avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}
</style>
