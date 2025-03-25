<template>
    <!-- Menu List -->
    <div id="lateralMenu">
        <hr class="dropdown-divider" />
        <div class="tituloBarraLateral">
            <h2 class="bolder menu-item" title="Menú principal del juego">
                <i class="fas fa-home"></i>
                <span class="menu-text">INICIO</span>
            </h2>
        </div>
        <div class="subtituloBarraLateral">
            <div class="contenidoSubtituloBarraLateral">
                <router-link
                    class="menu-item"
                    to="/public_game"
                    title="Únete a partidas públicas con otros jugadores"
                >
                    <i class="fas fa-globe"></i>
                    <span class="menu-text">Partida pública</span>
                </router-link>
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <router-link
                    class="menu-item"
                    to="/create_game"
                    title="Crea tu propia partida personalizada"
                >
                    <i class="fas fa-plus"></i>
                    <span class="menu-text">Crear partida</span>
                </router-link>
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <router-link
                    class="menu-item"
                    to="/join_game"
                    title="Únete a una partida privada con amigos"
                >
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Unirse a partida</span>
                </router-link>
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <router-link
                    class="menu-item"
                    to="/view_game"
                    title="Observa partidas en curso"
                >
                    <i class="fas fa-eye"></i>
                    <span class="menu-text">Observar partida</span>
                </router-link>
            </div>
            <hr class="dropdown-divider" />
        </div>

        <div class="tituloBarraLateral">
            <router-link
                to="/rankings"
                class="bolder menu-item"
                title="Compite y alcanza la cima"
            >
                <i class="fas fa-trophy"></i>
                <span class="menu-text">RANKING</span>
            </router-link>
        </div>
        <div class="subtituloBarraLateral">
            <div class="contenidoSubtituloBarraLateral">
                <router-link
                    class="menu-item"
                    to="/global_ranking"
                    title="Consulta el ranking global de jugadores"
                >
                    <i class="fas fa-globe-americas"></i>
                    <span class="menu-text">Ranking global</span>
                </router-link>
            </div>
            <hr class="dropdown-divider" />
            <div class="contenidoSubtituloBarraLateral">
                <router-link
                    class="menu-item"
                    to="/national_ranking"
                    title="Consulta el ranking nacional de jugadores"
                >
                    <i class="fas fa-flag"></i>
                    <span class="menu-text">Ranking nacional</span>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
/* -- IMPORTS -- */

/* -- VARIABLES -- */

/* -- FUNCTIONS -- */
</script>

<style scoped>
.tituloBarraLateral {
    padding-top: 2rem;
    padding-left: 0.25rem;
    padding-bottom: 0.5rem;
}

.subtituloBarraLateral {
    padding: 0.5rem 0.25rem;
}

.contenidoSubtituloBarraLateral {
    padding: 0.25rem 0; /* Aumentado para más espacio entre items */
}

.dropdown-divider {
    opacity: 0.25; /* Más delgado */
    margin: 0.4rem 1rem; /* Aumentado margen */
}

#lateralMenu > .dropdown-divider:first-child {
    opacity: 0.75;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: var(--white-color);
    text-decoration: none;
    font-size: 1.4rem; /* Aumentado de 1.25rem a 1.4rem */
    white-space: nowrap;
    transition: all 0.3s ease;
    padding: 0.5rem;
}

.menu-item i {
    font-size: 1.5rem; /* Aumentado de 1.3rem a 1.5rem */
    min-width: 24px;
    text-align: center;
    transition: all 0.3s ease;
}

/* Estilo específico para los títulos */
h2.menu-item {
    margin: 0;
    font-size: 1.8rem; /* Tamaño más grande para los títulos */
}

.menu-item:hover {
    color: var(--primary-color);
}

/* Estilos para el menú cerrado */
:deep(#lateralBar.closed) .menu-text {
    display: none;
}

:deep(#lateralBar.closed) .menu-item {
    justify-content: center;
    padding: 0.5rem 0;
}

:deep(#lateralBar.closed) .menu-item i {
    margin: 0;
    font-size: 1.6rem;
}

:deep(#lateralBar.closed) h2.menu-item {
    font-size: 1.6rem;
}

:deep(#lateralBar.closed) .dropdown-divider {
    margin: 0.4rem 0.5rem;
}

:deep(#lateralBar.closed) .tituloBarraLateral {
    padding-left: 0;
    text-align: center;
}

:deep(#lateralBar.closed) .subtituloBarraLateral {
    padding: 0.5rem 0;
}

:deep(#lateralBar.closed) .contenidoSubtituloBarraLateral {
    padding: 0.25rem 0;
    text-align: center;
}

/* Estilos mejorados para tooltips */
:deep(#lateralBar.closed) .menu-item {
    position: relative;
}

:deep(#lateralBar.closed) .menu-item:hover::after {
    content: attr(title);
    position: absolute;
    left: calc(100% + 15px);
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(
        145deg,
        var(--primary-color),
        var(--neutral-color)
    );
    color: var(--white-color);
    padding: 0.8rem 1.2rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 0.3px;
    white-space: nowrap;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3),
        inset 0 1px 1px rgba(255, 255, 255, 0.1);
    z-index: 1000;
    opacity: 0;
    animation: tooltipFade 0.3s ease forwards;
}

:deep(#lateralBar.closed) .menu-item:hover::before {
    content: "";
    position: absolute;
    left: calc(100% + 7px);
    top: 50%;
    transform: translateY(-50%);
    border: 8px solid transparent;
    border-right-color: var(--primary-color);
    filter: drop-shadow(-2px 0 2px rgba(0, 0, 0, 0.2));
    z-index: 1001;
    opacity: 0;
    animation: tooltipFade 0.3s ease forwards;
}

@keyframes tooltipFade {
    from {
        opacity: 0;
        transform: translateY(-50%) translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(-50%) translateX(0);
    }
}
</style>
