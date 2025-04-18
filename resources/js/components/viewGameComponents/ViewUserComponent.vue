<template>
    <div class="view-user-component">
        <div class="profile-content">
            <div class="left-side">
                <div class="userImageContainer">
                    <img
                        :src="avatarUrl"
                        :alt="userData?.username"
                        @error="handleAvatarError"
                        class="user-avatar"
                    />
                </div>
                <div class="usernameContainer">
                    <div class="username-wrapper">
                        <span class="username">
                            {{ userData?.username || "Cargando usuario..." }}
                        </span>
                        <div class="pointsContainer">
                            <span class="points">
                                {{ userPoints !== null ? userPoints : "---" }}
                                <img
                                    src="/images/icons/trophy-icon-dark.svg"
                                    alt="Trophy icon"
                                />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import useUsers from "@/composables/users";
import useRankings from "@/composables/rankings";

const props = defineProps({
    userId: {
        type: Number,
        required: true,
    },
});

const { getUser } = useUsers();
const { getRanking } = useRankings();
const userData = ref(null);
const userPoints = ref(null);
const avatarUrl = ref("/images/icons/user-icon-dark.svg");

const handleAvatarError = (e) => {
    e.target.src = "/images/icons/user-icon-dark.svg";
};

async function loadUserData() {
    if (!props.userId) {
        userData.value = null;
        userPoints.value = null;
        avatarUrl.value = "/images/icons/user-icon-dark.svg";
        return;
    }

    userData.value = null;
    userPoints.value = null;
    avatarUrl.value = "/images/icons/user-icon-dark.svg";

    try {
        const user = await getUser(props.userId);

        if (!user) {
            console.warn(
                `User with ID ${props.userId} not found by composable.`
            );
        } else {
            userData.value = user;
            if (user.avatar) {
                avatarUrl.value = user.avatar;
            }
        }

        if (userData.value) {
            const rankingData = await getRanking(props.userId);
            userPoints.value = rankingData?.points ?? 0;
        } else {
            userPoints.value = null;
        }
    } catch (error) {
        console.error(`Error in loadUserData for ID ${props.userId}:`, error);
        userData.value = null;
        userPoints.value = null;
        avatarUrl.value = "/images/icons/user-icon-dark.svg";
    }
}

// console.log("Defining watcher for userId...");

watch(
    () => props.userId,
    (newUserId, oldUserId) => {
        if (newUserId) {
            loadUserData();
        } else {
            userData.value = null;
            userPoints.value = null;
            avatarUrl.value = "/images/icons/user-icon-dark.svg";
        }
    },
    { immediate: true }
);

</script>

<style scoped>
.view-user-component {
    width: 100%;
    display: flex;
    padding: 0.5rem;
    border-radius: 8px;
    border: 1px solid var(--primary-color);
    transition: all 0.2s ease;
}

.view-user-component:hover {
    background-color: var(--primary-color-secondary);
    transform: translateY(-2px);
}

.profile-content {
    height: 100%;
    display: flex;
    align-items: center;
    width: 100%;
    justify-content: flex-start;
    padding: 0.25rem;
}

.left-side {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.userImageContainer {
    width: 50px;
    height: 50px;
    margin: 0 0.75rem 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--white-color);
}

.user-avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.usernameContainer {
    flex: 1;
    padding: 0 0.5rem;
    display: flex;
    align-items: flex-start;
}

.username-wrapper {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
    width: 100%;
}

.username {
    color: var(--white-color);
    font-size: 1.1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: opacity 0.3s ease;
}

.pointsContainer {
    display: flex;
    align-items: center;
    min-width: fit-content;
}

.points {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    white-space: nowrap;
    color: var(--white-color);
    font-size: 0.9rem;
    transition: opacity 0.3s ease;
}

.points img {
    width: 20px;
    height: 20px;
    margin-left: 0.2rem;
}

.username:empty,
.points:empty {
    opacity: 0.6;
}

@media (max-width: 768px) {
    .userImageContainer {
        width: 40px;
        height: 40px;
    }

    .username {
        font-size: 0.9rem;
    }

    .points {
        font-size: 0.8rem;
    }

    .points img {
        width: 16px;
        height: 16px;
    }
}

/* Mejoras de responsividad específicas para ViewUserComponent */
@media (max-width: 800px) and (min-width: 601px) {
    .view-user-component {
        padding: 0.5rem;
    }

    .userImageContainer {
        width: 40px;
        height: 40px;
        margin: 0 0.5rem 0 0;
    }

    .username {
        font-size: 0.9rem;
        max-width: 110px;
    }

    .points {
        font-size: 0.8rem;
    }

    .points img {
        width: 16px;
        height: 16px;
        margin-left: 0.2rem;
    }
}
</style>
