<template>
    <transition name="fade">
        <div class="game-over-overlay" v-if="visible">
            <div class="over-container">
                <h1>Game Over</h1>
                <p>¡Inténtalo de nuevo!</p>
                <div class="buttons">
                    <button @click="$emit('restart')">Reintentar</button>
                    <button @click="$emit('menu')">Menú Principal</button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "GameOver",
    props: {
        visible: Boolean,
    },
};
</script>

<style scoped>
.game-over-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.over-container {
    background: #f44336;
    padding: 2rem;
    border-radius: 10px;
    text-align: center;
    color: white;
    transform: scale(1);
    transition: transform 0.3s ease;
}

.buttons {
    margin-top: 1rem;
}

button {
    margin: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 1.1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    background: white;
    color: #f44336;
    transition: all 0.3s ease;
}

button:hover {
    transform: scale(1.05);
    opacity: 0.9;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
