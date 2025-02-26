<template>
    <div v-if="visible" class="game-over-overlay">
        <div class="game-over-modal">
            <h2>Game Over</h2>
            <div class="buttons">
                <button @click="$emit('restart')">Intentar de nuevo</button>
                <button @click="$emit('menu')">Menú Principal</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "GameOver",
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style scoped>
.game-over-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

.game-over-modal {
    background: #2c3e50;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
}

.buttons {
    margin-top: 1rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

button {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    background: #e74c3c;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #c0392b;
}
</style>
