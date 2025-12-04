<template>
    <div class="preloader-overlay">
        <div class="preloader">
            <img src="/assets/image/logo.png" alt="Preloader" class="preloader-img" />
        </div>
    </div>
</template>

<script>
export default {
    name: "Preloader",
};
</script>

<style lang="scss" scoped>
.preloader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 1);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.preloader {
    animation: pulse 0.6s ease-in-out infinite; /* Анимация пульсации */
}

@keyframes pulse {
    0% {
        transform: scale(1); /* Начальный размер */
    }
    50% {
        transform: scale(1.1); /* Максимальный размер */
    }
    100% {
        transform: scale(1); /* Возвращение к начальному размеру */
    }
}

@-webkit-keyframes pulse {
    0% {
        -webkit-box-shadow: 0 0 0 0 rgba(10, 203, 144, 0.6);
    }

    70% {
        -webkit-box-shadow: 0 0 0 10px transparent;
    }

    to {
        -webkit-box-shadow: 0 0 0 0 transparent;
    }
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgb(106 32 243);
    }

    70% {
        box-shadow: 0 0 0 9px transparent;
        transform: rotateY(0deg);
    }

    to {
        box-shadow: 0 0 0 0 transparent;
    }
}

.preloader-img {
    width: 150px;
}
</style>
