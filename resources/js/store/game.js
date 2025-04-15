import { defineStore } from "pinia";

export const useGameStore = defineStore("game", {
    state: () => ({
        gameMode: null,
        gamePhase: "loading", // Cambiado el estado inicial a 'loading'
        matchCode: null,
        playerBoard: null,
        showWin: false,
        showDraw: false,
        showGameOver: false,
        points: 0
    }),

    actions: {

        // Setters de gameMode, matchCode y gamePhase
        setGameMode(mode) {
            this.gameMode = mode;
        },
        setMatchCode(code) {
            this.matchCode = code;
        },
        setGamePhase(phase) {
            this.gamePhase = phase;
        },

        // Reiniciar todas las variables de juego
        resetGame() {
            this.gameMode = null;
            this.gamePhase = "loading";
            this.matchCode = null;
            this.playerBoard = null;
            this.showWin = false;
            this.showDraw = false;
            this.showGameOver = false;
            this.points = 0;
            // console.log("Juego reiniciado.");
        },

        // Mostrar u ocultar pantallas finales
        setShowWin(value) {
            this.showWin = value;
        },
        setShowDraw(value) {
            this.showDraw = value;
        },
        setShowGameOver(value) {
            this.showGameOver = value;
        },

        // Settear los puntos a mostrar en pantallas finales
        setPoints(points) {
            this.points = points;
        }
    },
});
