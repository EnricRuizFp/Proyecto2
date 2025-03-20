import { createRouter, createWebHistory } from "vue-router";
import routes from "../routes/routes";

const router = createRouter({
    history: createWebHistory("/"),
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
