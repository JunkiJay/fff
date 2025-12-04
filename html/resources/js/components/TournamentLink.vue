<template>
    <div class="tournament-link">
        <img class="tournament-link__icon" :src="icon" alt="icon tournamnets" />
        <span class="tournament-link__title tournament-link__title-main">Турниры</span>
        <span class="tournament-link__title tournament-link__title-new">Новинка</span>
    </div>
</template>

<script>
export default {
    name: "TournamentLink",
    props: {
        icon: {
            type: String,
            required: true,
        },
    },
};
</script>

<style lang="scss" scoped>
.tournament-link {
    display: flex;
    justify-content: center;
    flex-direction: column;
    align-items: center;
    height: 62px;
    width: 62px;
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    filter: drop-shadow(0px 0px 15px rgba(255, 200, 0, 0.5));
     animation: animateLink 10s ease infinite;
    background: linear-gradient(90deg, rgba(249, 212, 35, 1) 0%, rgba(231, 102, 3, 1) 50%, rgba(249, 212, 35, 1) 100%);
    background-size: 200% 100%;
    background-position: 100% 50%;

    &__icon {
        width: 28px;
        height: 28px;
        position: absolute;
        animation: animateIcon 10s infinite;
    }

    &__title {
        font-size: 14px;
        line-height: 16px;
        color: #ffffff;
        text-transform: uppercase;
        font-family: Oswald;
        position: absolute;
        bottom: 5px;

        &-main {
            animation: animateTitleMain 10s infinite;
        }

        &-new {
            animation: animateTitleNew 10s infinite;
        }
    }
}

@media (max-width: 1024px) {
    .tournament-link{
        height: 48px;
        width: 48px;
        margin: auto;
        img{
            width: 22px;
        }
        span{
            font-size: 10px;
        }
    }
}

@keyframes animateLink {
    0% {
        background-position: 100% 50%;
    }
    20% {
        background-position: 100% 50%;
    }
    30% {
        background-position: 100% 50%;
    }
    50% {
        background-position: 0% 50%;
    }
    70% {
        background-position: 0% 50%;
    }
    80% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 100% 50%;
    }
}

@keyframes animateIcon {
    0% {
        transform: scale(1) translateY(-10px);
    }
    20% {
        transform: scale(1.8) translateY(0px);
    }
    30% {
        transform: scale(1.8) translateY(0px);
    }
    50% {
        transform: scale(1) translateY(-10px);
    }
    100% {
        transform: scale(1) translateY(-10px);
    }
}

@keyframes animateTitleMain {
    0% {
        opacity: 100%;
        transform: scale(1) translateY(0px);
    }
    20% {
        opacity: 100%;
        transform: scale(1.1) translateY(25px);
    }
    21% {
        opacity: 0%;
    }
    70% {
        opacity: 0%;
        transform: scale(1) translateY(0px);
    }
    80% {
        opacity: 100%;
    }
    100% {
        opacity: 100%;
    }
}

@keyframes animateTitleNew {
    0% {
        opacity: 0%;
    }
    30% {
        opacity: 0%;
    }
    45% {
        opacity: 100%;
    }
    70% {
        opacity: 100%;
    }
    80% {
        opacity: 0%;
    }
    100% {
        opacity: 0%;
    }
}
</style>
