import { createRouter, createWebHistory } from "vue-router";
import Guest from "../layouts/Guest.vue";
import Game from "../layouts/Game.vue";

const routes = [
    {
        path: "/",
        component: Guest,
        children: [
            {
                path: "",
                name: "home",
                component: () => import("../views/home/index.vue"),
            },
        ],
    },
    {
        path: "/game",
        component: Game,
        children: [
            {
                path: "",
                name: "game",
                component: () => import("../views/game/Index.vue"), // Asegúrate de que coincida exactamente con la ubicación del archivo
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory("/"), // Asegurarse de que la base URL está correcta
    routes,
});

router.beforeEach((to, from, next) => {
    console.log("Navegando a:", {
        path: to.path,
        matched: to.matched.length,
        exists: to.matched.length > 0,
        component: to.matched[0]?.components?.default, // Añadir log del componente
    });

    next();
});

export default router;
