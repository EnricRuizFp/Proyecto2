import { defineStore } from "pinia";

export const useGameStore = defineStore("game", {
    state: () => ({
        gameMode: null,
        gamePhase: "loading", // Cambiado el estado inicial a 'loading'
        matchCode: null,
        playerBoard: null,
        showWin: false,
        showDraw: false,
        showGameOver: false
    }),

    actions: {
        setGameMode(mode) {
            this.gameMode = mode;
        },
        setMatchCode(code) {
            this.matchCode = code;
        },
        setGamePhase(phase) {
            this.gamePhase = phase;
        },
        resetGame() {
            this.gameMode = null;
            this.gamePhase = "placement";
            this.matchCode = null;
            this.playerBoard = null;
        },
        setShowWin(value) {
            this.showWin = value;
        },
        setShowDraw(value) {
            this.showDraw = value;
        },
        setShowGameOver(value) {
            this.showGameOver = value;
        }
    },
});
