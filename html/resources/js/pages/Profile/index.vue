<template>
    <div class="page-profile">
        <h2 class="page-profile__title">Аккаунт</h2>
        <div class="player-info">
            <div class="player-info__card card">
                <img :src="$root.user.avatar" alt="" class="card__img" />
                <span class="card__name">{{ $root.user.username }}</span>
                <p class="card__desc">Зарегистрирован 23 января, 2024</p>

                <div class="auto-withdraw">
                    <label class="auto-withdraw__label">
                        <input type="checkbox" v-model="autoWithdraw" @change="toggleAutoWithdraw"
                            class="auto-withdraw__checkbox">
                        <span class="auto-withdraw__text">Автовыплаты</span>
                    </label>
                </div>
            </div>

            <div class="player-info__statistics">
                <div v-for="(statisticsItem, index) in statistics" :key="index"
                    class="player-info__statistics-item statistics-item">
                    <div class="statistics-item__icon" :style="{ backgroundColor: statisticsItem.color }">
                        <img :src="statisticsItem.icon" alt="" />
                    </div>

                    <p class="statistics-item__title">{{ statisticsItem.title }}</p>
                    <p class="statistics-item__value">{{ statisticsItem.value }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Settings from "@/components/modals/Settings.vue";
import IOdometer from "vue-odometer";
import "odometer/themes/odometer-theme-default.css";
import ICountUp from "vue-countup-v2";
import betSvg from "@img/profile/statistics/bet.svg";
import coinSvg from "@img/profile/statistics/coin.svg";
import coinsSvg from "@img/profile/statistics/coins.svg";
import flipSvg from "@img/profile/statistics/flip.svg";

export default {
    components: {
        IOdometer,
        ICountUp,
        Settings,
    },
    data() {
        return {
            options: {
                autoWithdraw: false,
                useEasing: true,
                useGrouping: true,
                separator: ",",
                decimal: ".",
                prefix: "",
                suffix: "",
                decimalPlaces: 2,
            },
            statistics: [
                {
                    color: "#8D24D5",
                    icon: betSvg,
                    title: "Всего ставок",
                    value: 23,
                },
                {
                    color: "#3ECC5E",
                    icon: coinsSvg,
                    title: "Сумма выигрышей",
                    value: 23,
                },
                {
                    color: "#E89211",
                    icon: coinsSvg,
                    title: "Макс. выигрышей",
                    value: 23,
                },
                {
                    color: "#232323",
                    icon: flipSvg,
                    title: "Макс. коэфф.",
                    value: 23,
                },
            ],
        };
    },
    methods: {
        toggleAutoWithdraw() {
            const response = axios.post('/withdraw/auto-withdraw', { enabled: this.autoWithdraw });
        }
    }
};
</script>

<style lang="scss" scoped>
.page-profile {
    &__title {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--color-text);
        font-family: Oswald !important;
    }

    .player-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 16px;

        &__card {
            height: 200px;
            border-radius: 8px;
            background-color: var(--color-content);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        &__statistics {
            display: flex;
            gap: 20px;
            background-color: var(--color-content);
            width: 100%;
            height: 200px;
            border-radius: 8px;
            align-items: center;
            justify-content: space-around;
        }

        .statistics-item {
            display: flex;
            flex-direction: column;
            align-items: center;

            &__icon {
                width: 80px;
                height: 80px;
                border-radius: 8px;
                border: 1px solid var(--color-text);
                padding: 20px;
                margin-bottom: 12px;

                img {
                    width: 100%;
                    height: 100%;
                    object-position: center;
                    object-fit: contain;
                }
            }

            &__title {
                font-size: 14px;
                font-weight: 300;
                margin-bottom: 12px;
                color: var(--color-text-secondary);
                white-space: nowrap;
                text-overflow: ellipsis;
                overflow: hidden;
            }

            &__value {
                font-size: 16px;
                font-weight: 700;
                color: var(--color-text);
                margin: 0;
                white-space: nowrap;
                text-overflow: ellipsis;
                overflow: hidden;
            }
        }

        .card {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 320px;
            flex-shrink: 0;

            &__img {
                width: 80px;
                height: 80px;
                object-position: center;
                object-fit: cover;
                border-radius: 8px;
            }

            &__name {
                font-size: 16px;
                font-weight: 700;
                margin-top: 12px;
                color: var(--color-text);
            }

            &__desc {
                font-size: 14px;
                font-weight: 300;
                text-align: center;
                color: var(--color-text-secondary);
                margin-top: 8px;
            }
        }
    }
}

@media (max-width: 1024px) {
    .page-profile {
        &__title {
            display: none;
        }

        .player-info {
            flex-direction: column;
            gap: 16px;

            &__statistics {
                display: grid;
                grid-template-columns: (repeat(2, 1fr));
                height: auto;
                padding: 15px;
            }

            .card {
                width: 100%;
            }

            .statistics-item {
                &__icon {
                    width: 42px;
                    height: 42px;
                    border: none;
                    padding: 7px;
                }
            }
        }
    }
}

.auto-withdraw {
    margin-top: 15px;
}

.auto-withdraw__label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.auto-withdraw__checkbox {
    margin-right: 8px;
}

.auto-withdraw__text {
    font-size: 14px;
    color: #e9e5e5;
}

.auto-withdraw__checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
</style>
