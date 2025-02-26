<template>
    <div v-if="visible" class="game-win-overlay">
        <div class="game-win-modal">
            <h2>¡Victoria!</h2>
            <div class="buttons">
                <button @click="$emit('next-level')">Siguiente Nivel</button>
                <button @click="$emit('restart')">Reiniciar</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "GameWin",
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style scoped>
.game-win-overlay {
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

.game-win-modal {
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
    background: #27ae60;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #219a52;
}
</style>
