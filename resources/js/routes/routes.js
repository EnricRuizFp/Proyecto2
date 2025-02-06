import { authStore } from "../store/auth";

const AuthenticatedLayout = () => import("../layouts/Authenticated.vue");
const AuthenticatedUserLayout = () =>
    import("../layouts/AuthenticatedUser.vue");
const GuestLayout = () => import("../layouts/Guest.vue");
const PostsIndex = () => import("../views/admin/posts/Index.vue");
const PostsCreate = () => import("../views/admin/posts/Create.vue");
const PostsEdit = () => import("../views/admin/posts/Edit.vue");

async function requireLogin(to, from, next) {
    const auth = authStore();
    let isLogin = !!auth.authenticated;

    if (isLogin) {
        next();
    } else {
        next("/login");
    }
}

function hasAdmin(roles) {
    for (let rol of roles) {
        if (rol.name && rol.name.toLowerCase().includes("admin")) {
            return true;
        }
    }
    return false;
}
async function guest(to, from, next) {
    const auth = authStore();

    let isLogin = !!auth.authenticated;

    if (isLogin) {
        next("/");
    } else {
        next();
    }
}

async function requireAdmin(to, from, next) {
    const auth = authStore();
    let isLogin = !!auth.authenticated;
    let user = auth.user;

    if (isLogin) {
        if (hasAdmin(user.roles)) {
            next();
        } else {
            next("/app");
        }
    } else {
        next("/login");
    }
}

export default [
    {
        path: "/",
        // redirect: { name: 'login' },
        component: GuestLayout,
        children: [
            {
                path: "/",
                name: "home",
                component: () => import("../views/home/index.vue"),
            },
            {
                path: "posts",
                name: "public-posts.index",
                component: () => import("../views/posts/index.vue"),
            },
            {
                path: "posts/:id",
                name: "public-posts.details",
                component: () => import("../views/posts/details.vue"),
            },
            {
                path: "category/:id",
                name: "category-posts.index",
                component: () => import("../views/category/posts.vue"),
            },
            {
                path: "login",
                name: "auth.login",
                component: () => import("../views/login/Login.vue"),
                beforeEnter: guest,
            },
            {
                path: "register",
                name: "auth.register",
                component: () => import("../views/register/index.vue"),
                beforeEnter: guest,
            },
            {
                path: "forgot-password",
                name: "auth.forgot-password",
                component: () => import("../views/auth/passwords/Email.vue"),
                beforeEnter: guest,
            },
            {
                path: "reset-password/:token",
                name: "auth.reset-password",
                component: () => import("../views/auth/passwords/Reset.vue"),
                beforeEnter: guest,
            },
        ],
    },

    {
        path: "/app",
        component: AuthenticatedUserLayout,
        // redirect: {
        //     name: 'admin.index'
        // },
        name: "app",
        beforeEnter: requireLogin,
        meta: { breadCrumb: "Dashboard" },
    },

    {
        path: "/admin",
        component: AuthenticatedLayout,
        // redirect: {
        //     name: 'admin.index'
        // },
        beforeEnter: requireAdmin,
        meta: { breadCrumb: "Dashboard" },
        children: [
            {
                name: "admin.index",
                path: "",
                component: () => import("../views/admin/index.vue"),
                meta: { breadCrumb: "Admin" },
            },
            {
                name: "profile.index",
                path: "profile",
                component: () => import("../views/admin/profile/index.vue"),
                meta: { breadCrumb: "Profile" },
            },
            {
                name: "posts.index",
                path: "posts",
                component: PostsIndex,
                meta: { breadCrumb: "Posts" },
            },
            {
                name: "posts.create",
                path: "posts/create",
                component: PostsCreate,
                meta: { breadCrumb: "Add new post" },
            },
            {
                name: "posts.edit",
                path: "posts/edit/:id",
                component: PostsEdit,
                meta: { breadCrumb: "Edit post" },
            },
            {
                name: "categories",
                path: "categories",
                meta: { breadCrumb: "Categories" },
                children: [
                    {
                        name: "categories.index",
                        path: "",
                        component: () =>
                            import("../views/admin/categories/Index.vue"),
                        meta: { breadCrumb: "View category" },
                    },
                    {
                        name: "categories.create",
                        path: "create",
                        component: () =>
                            import("../views/admin/categories/Create.vue"),
                        meta: {
                            breadCrumb: "Add new category",
                            linked: false,
                        },
                    },
                    {
                        name: "categories.edit",
                        path: "edit/:id",
                        component: () =>
                            import("../views/admin/categories/Edit.vue"),
                        meta: {
                            breadCrumb: "Edit category",
                            linked: false,
                        },
                    },
                ],
            },
            {
                name: "permissions",
                path: "permissions",
                meta: { breadCrumb: "Permisos" },
                children: [
                    {
                        name: "permissions.index",
                        path: "",
                        component: () =>
                            import("../views/admin/permissions/Index.vue"),
                        meta: { breadCrumb: "Permissions" },
                    },
                    {
                        name: "permissions.create",
                        path: "create",
                        component: () =>
                            import("../views/admin/permissions/Create.vue"),
                        meta: {
                            breadCrumb: "Create Permission",
                            linked: false,
                        },
                    },
                    {
                        name: "permissions.edit",
                        path: "edit/:id",
                        component: () =>
                            import("../views/admin/permissions/Edit.vue"),
                        meta: {
                            breadCrumb: "Permission Edit",
                            linked: false,
                        },
                    },
                ],
            },
            {
                name: "users",
                path: "users",
                meta: { breadCrumb: "Usuarios" },
                children: [
                    {
                        name: "users.index",
                        path: "",
                        component: () =>
                            import("../views/admin/users/Index.vue"),
                        meta: { breadCrumb: "Usuarios" },
                    },
                    {
                        name: "users.create",
                        path: "create",
                        component: () =>
                            import("../views/admin/users/Create.vue"),
                        meta: {
                            breadCrumb: "Crear Usuario",
                            linked: false,
                        },
                    },
                    {
                        name: "users.edit",
                        path: "edit/:id",
                        component: () =>
                            import("../views/admin/users/Edit.vue"),
                        meta: {
                            breadCrumb: "Editar Usuario",
                            linked: false,
                        },
                    },
                ],
            },
            {
                name: "authors",
                path: "authors",
                meta: { breadCrumb: "Autores" },
                children: [
                    {
                        name: "authors.index",
                        path: "",
                        component: () =>
                            import("../views/admin/authors/Index.vue"),
                        meta: { breadCrumb: "Listado Autores" },
                    },
                    // {
                    //     name: "authors.create",
                    //     path: "create",
                    //     component: () =>
                    //         import("../views/admin/authors/Create.vue"),
                    //     meta: {
                    //         breadCrumb: "Create Autor",
                    //         linked: false,
                    //     },
                    // },
                    {
                        name: "authors.edit",
                        path: "edit/:id",
                        component: () =>
                            import("../views/admin/authors/edit.vue"),
                        meta: {
                            breadCrumb: "Autor Edit",
                            linked: false,
                        },
                    },
                ],
            },



            /* ----- ROUTES FOR DATABASE TAGS ----- */

            // Avatars
            {
                name: "avatars",
                path: "avatars",
                meta: { breadCrumb: "Avatars" },
                children: [
                    {
                        name: "avatar.index",
                        path: "",
                        component: () =>
                            import("../views/admin/avatars/Index.vue"),
                        meta: { breadCrumb: "Avatars" },
                    },
                    {
                        name: "avatar.create",
                        path: "create",
                        component: () =>
                            import("../views/admin/avatars/Create.vue"),
                        meta: {
                            breadCrumb: "Create Avatar",
                            linked: false,
                        },
                    },
                    {
                        name: "avatar.edit",
                        path: "edit/:id",
                        component: () =>
                            import("../views/admin/avatars/Edit.vue"),
                        meta: {
                            breadCrumb: "Edit Avatar",
                            linked: false,
                        },
                    },

                ],
            },
            // Ships
            {
                name: "ships",
                path: "ships",
                meta: { breadCrumb: "Ships" },
                children: [
                    {
                        name: "ship.index",
                        path: "",
                        component: () =>
                            import("../views/admin/ships/Index.vue"),
                        meta: { breadCrumb: "Ships" },
                    },
                    {
                        name: "ship.create",
                        path: "create",
                        component: () =>
                            import("../views/admin/ships/Create.vue"),
                        meta: {
                            breadCrumb: "Create Ship",
                            linked: false,
                        },
                    },
                    {
                        name: "ship.edit",
                        path: "edit/:id",
                        component: () =>
                            import("../views/admin/ships/Edit.vue"),
                        meta: {
                            breadCrumb: "Edit Ship",
                            linked: false,
                        },
                    },
                ],
            },
            // Games
            {
                name: "games",
                path: "games",
                meta: { breadCrumb: "Games" },
                children: [
                    {
                        name: "game.index",
                        path: "",
                        component: () =>
                            import("../views/admin/games/Index.vue"),
                        meta: { breadCrumb: "Games" },
                    },
                    {
                        name: "game.create",
                        path: "create",
                        component: () =>
                            import("../views/admin/games/Create.vue"),
                        meta: {
                            breadCrumb: "Create Game",
                            linked: false,
                        },
                    },
                    {
                        name: "game.edit",
                        path: "edit/:id",
                        component: () =>
                            import("../views/admin/games/Edit.vue"),
                        meta: {
                            breadCrumb: "Edit Game",
                            linked: false,
                        },
                    },
                ],
            },
            // Rankings
            {
                name: "rankings",
                path: "rankings",
                meta: { breadCrumb: "Rankings" },
                children: [
                    {
                        name: "ranking.index",
                        path: "",
                        component: () =>
                            import("../views/admin/rankings/Index.vue"),
                        meta: { breadCrumb: "Rankings" },
                    },
                    {
                        name: "ranking.create",
                        path: "create",
                        component: () =>
                            import("../views/admin/rankings/Create.vue"),
                        meta: {
                            breadCrumb: "Create Ranking",
                            linked: false,
                        },
                    },
                    {
                        name: "ranking.edit",
                        path: "edit/:id",
                        component: () =>
                            import("../views/admin/rankings/Edit.vue"),
                        meta: {
                            breadCrumb: "Edit Ranking",
                            linked: false,
                        },
                    },
                ],
            },





            //TODO Organizar rutas
            {
                name: "roles.index",
                path: "roles",
                component: () => import("../views/admin/roles/Index.vue"),
                meta: { breadCrumb: "Roles" },
            },
            {
                name: "roles.create",
                path: "roles/create",
                component: () => import("../views/admin/roles/Create.vue"),
                meta: { breadCrumb: "Create Role" },
            },
            {
                name: "roles.edit",
                path: "roles/edit/:id",
                component: () => import("../views/admin/roles/Edit.vue"),
                meta: { breadCrumb: "Role Edit" },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        name: "NotFound",
        component: () => import("../views/errors/404.vue"),
    },
];
