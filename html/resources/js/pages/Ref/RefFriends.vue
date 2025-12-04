<template>
    <div class="ref-friends">
        <img :src="refFriendsImg" alt="icon" class="ref-friends__image" />
        <div
            class="ref-friends__info"
            @mouseenter="showTooltip"
            @mouseleave="hideTooltip"
            @click="toggleTooltip"
        >
            <img :src="iconPath" alt="icon" class="ref-friends__image" />
        </div>
        <div v-if="tooltipVisible" class="ref-friends__tooltip">
            Пригласив всего 10 своих друзей, которые пригласят 10 своих друзей, и каждый из них еще по 10 друзей,
            в сумме у Вас получится более 1000 рефералов с которых вы будете получать до 15% от пополнений.
        </div>
    </div>
</template>


<script>
import refFriendsImg from '@public/images/ref-friends.png';
import infoDarkSvg from '@public/images/info-dark.svg';
import infoSvg from '@public/images/info.svg';

export default {
    name: "RefFriends",
    data() {
        return {
            tooltipVisible: false,
            isHovered: false,
            refFriendsImg,
            infoDarkSvg,
            infoSvg,
        };
    },
    computed: {
        iconPath() {
            return this.isHovered
                ? this.infoDarkSvg
                : this.infoSvg;
        },
    },
    methods: {
        showTooltip() {
            this.tooltipVisible = true;
            this.isHovered = true;
        },
        hideTooltip() {
            this.tooltipVisible = false;
            this.isHovered = false;
        },
        toggleTooltip() {
            this.tooltipVisible = !this.tooltipVisible;
        },
    },
};
</script>

<style lang="scss" scoped>
.ref-friends {
    position: relative;
    padding: 66px 40px;
    backdrop-filter: blur(34.3px);
    background: #1F1B2940;
    border: 1px solid #FFFFFF40;
    border-radius: 32px;

    .ref-friends__info {
        position: absolute;
        top: 16px;
        right: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
        cursor: pointer;

        &:hover {
            background: #fff;
            border-radius: 100%;
        }

        img {
            width: 16px;
            height: 16px;
            transition: all 0.3s ease;
        }
    }
    .ref-friends__tooltip {
        position: absolute;
        top: 56px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 32px);
        padding: 12px;
        backdrop-filter: blur(32.4px);
        background: rgba(255, 255, 255, 0.05);
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 500;
        line-height: 16.94px;
        text-align: center;
        color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 10;
    }
}

@media (max-width: 1024px) {
    .ref-friends {
        display: flex;
        justify-content: center;
        padding: 56px 16px 60px;
        .ref-friends__info {
            .ref-friends__tooltip {
                position: absolute;
                top: 36px;
                right: 0;
                padding: 12px;
                backdrop-filter: blur(42.8px);
                background: rgba(255, 255, 255, 0.05);
                text-align: center;
                width: 320px;
                color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                z-index: 10;
                font-size: 12px;
                font-weight: 500;
                line-height: 14.52px;
            }
        }
    }
}
</style>
