<template>
    <div class="lds">
        <div class="lds-ring">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
        <div class="text-center">Подождите, пожалуйста, идет загрузка...</div>
    </div>
</template>

<style lang="scss" scoped>
.lds {
    align-items: center;
    background-color: #181a1d73;
    color: var(--color-text);
    display: flex;
    flex-direction: column;
    font-size: 25px;
    height: 100%;
    justify-content: center;
    position: fixed;
    width: 100%;
    z-index: 16;
}

.lds-ring {
    display: inline-block;
    height: 80px;
    position: relative;
    width: 80px;
}

.lds-ring div {
    -webkit-animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border: 8px solid transparent;
    border-radius: 50%;
    border-top-color: #fff;
    box-sizing: border-box;
    display: block;
    height: 64px;
    margin: 8px;
    position: absolute;
    width: 64px;
}

.lds-ring div:first-child {
    -webkit-animation-delay: -0.45s;
    animation-delay: -0.45s;
}

.lds-ring div:nth-child(2) {
    -webkit-animation-delay: -0.3s;
    animation-delay: -0.3s;
}

.lds-ring div:nth-child(3) {
    -webkit-animation-delay: -0.15s;
    animation-delay: -0.15s;
}

@-webkit-keyframes lds-ring {
    0% {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(1turn);
    }
}

@keyframes lds-ring {
    0% {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(1turn);
    }
}
</style>
