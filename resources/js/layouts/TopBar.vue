<template>
    <div id="topBar" class="app-background-primary">
        <div id="topBarContent">
            <a href="/">
                <img
                    src="../../../public/images/logo-darkTheme.svg"
                    alt="Game logo"
                    height="50"
                />
            </a>
        </div>
    </div>
</template>

<style scoped>
#topBar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 70px;
    z-index: 1000;
}

#topBarContent {
    height: 100%;
    display: flex;
    align-items: center;
    padding: 0 2rem;
}

@media (max-width: 768px) {
    #topBar {
        display: none;
    }
}
</style>
