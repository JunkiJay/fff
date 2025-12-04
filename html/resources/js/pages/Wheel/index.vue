<template>
    <div class="page-wheel m-3 md:m-0 ">
        <div class="form_share page-wheel__content lg:gap-[20px] lg:p-4 rounded-[24px] bg-[var(--color-content)]">
            <div class="page-wheel__form form">
                <div class="form__group">
                    <label class="form__label">Сложность игры</label>
                    <div class="form__buttons chance">
                        <Button
                            variant="default"
                            class="form__button form__button-big"
                            :class="[level == 1 ? 'active' : '']"
                            @click.native="level = 1"
                            :isDisable="btnLoading"
                        >
                            Легкий
                        </Button>

                        <Button
                            variant="default"
                            class="form__button form__button-big"
                            :class="[level == 2 ? 'active' : '']"
                            @click.native="level = 2"
                            :isDisable="btnLoading"
                        >
                            Средний
                        </Button>

                        <Button
                            variant="default"
                            class="form__button form__button-big"
                            :class="[level == 3 ? 'active' : '']"
                            @click.native="level = 3"
                            :isDisable="btnLoading"
                        >
                            Сложный
                        </Button>
                    </div>
                </div>

                <div class="form__group">
                    <label class="form__label">Ставка</label>
                    <div class="form__content">
                        <div class="form__input">
                            <input
                                type="text"
                                v-model="bet"
                                v-on:focusout="validateBet"
                                @change="typeBet('default')"
                                :disabled="btnLoading"
                            />

                            <button v-on:click="bet = '1.00'" class="form__trash"><Trash /></button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('min')">MIN</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('max')">MAX</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('x2')">X2</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('/2')">/2</Button>
                        </div>
                        <div class="form__buttons bet">
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('min')">MIN</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('max')">MAX</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('x2')">X2</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('/2')">/2</Button>
                        </div>
                    </div>
                </div>

                <Button @click.native="play" :isDisable="btnLoading"> Начать игру </Button>

                <div
                    class="mt-3 h-[44px] rounded-[8px] text-white w-full flex items-center justify-center"
                    :class="[noty.type === 'success' ? 'bg-[#47D524]' : 'bg-[#D82828]', noty.mess ? '' : 'opacity-0']"
                >
                    {{ noty.mess }}
                </div>
            </div>

            <div class="page-wheel__circle" id="circle-container">
                <div class="legend-wheel">
                    <div class="legend-wheel-circle">
                        <img :src="'/assets/image/wheel/' + level + '.png'" class="legend-wheel-img" />
                        <div class="legend-wheel-inset"></div>
                        <div class="legend-wheel-arrow"></div>
                    </div>

                    <div class="legend-wheel-inner" v-if="!game.end">
                        <div class="legend-wheel-coefficient">
                            <div class="legend-wheel-coefficient-box" v-if="level == 1">
                                <div class="legend-wheel-coefficient-list">
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="21" height="21" viewBox="-1 -1 21 21">
                                            <circle fill="#273451" r="10" cx="10" cy="10"></circle>
                                        </svg>
                                        <span>x 0.0</span>
                                    </div>
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="21" height="21" viewBox="-1 -1 21 21">
                                            <circle fill="#5480f2" r="10" cx="10" cy="10"></circle>
                                        </svg>
                                        <span>x 1.2</span>
                                    </div>
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="21" height="21" viewBox="-1 -1 21 21">
                                            <circle fill="#f34102" r="10" cx="10" cy="10"></circle>
                                        </svg>
                                        <span>x 1.5</span>
                                    </div>
                                </div>
                            </div>

                            <div class="legend-wheel-coefficient-box" v-if="level == 2">
                                <div class="legend-wheel-coefficient-list">
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="16" height="16" viewBox="-1 -1 16 16">
                                            <circle fill="#273451" r="7" cx="7" cy="7"></circle>
                                        </svg>
                                        <span>x 0.0</span>
                                    </div>
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="16" height="16" viewBox="-1 -1 16 16">
                                            <circle fill="#5480f2" r="7" cx="7" cy="7"></circle>
                                        </svg>
                                        <span>x 1.2</span>
                                    </div>
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="16" height="16" viewBox="-1 -1 16 16">
                                            <circle fill="#f34102" r="7" cx="7" cy="7"></circle>
                                        </svg>
                                        <span>x 1.5</span>
                                    </div>
                                </div>
                                <div class="legend-wheel-coefficient-list">
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="16" height="16" viewBox="-1 -1 16 16" xmlns="http://www.w3.org/2000/svg">
                                            <circle fill="#91dc00" r="7" cx="7" cy="7"></circle>
                                        </svg>
                                        <span>x3.0</span>
                                    </div>
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="16" height="16" viewBox="-1 -1 16 16" xmlns="http://www.w3.org/2000/svg">
                                            <circle fill="#ed44cc" r="7" cx="7" cy="7"></circle>
                                        </svg>
                                        <span>x5.0</span>
                                    </div>
                                </div>
                            </div>

                            <div class="legend-wheel-coefficient-box" v-if="level == 3">
                                <div class="legend-wheel-coefficient-list">
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="16" height="16" viewBox="-1 -1 16 16" xmlns="http://www.w3.org/2000/svg">
                                            <circle fill="#24304a" r="7" cx="7" cy="7"></circle>
                                        </svg>
                                        <span>x0.0</span>
                                    </div>
                                    <div class="legend-wheel-coefficient-item">
                                        <svg width="16" height="16" viewBox="-1 -1 16 16" xmlns="http://www.w3.org/2000/svg">
                                            <circle fill="#ed44cb" r="7" cx="7" cy="7"></circle>
                                        </svg>
                                        <span>x49.5</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="legend-wheel-inner" v-else>
                        <div class="legend-wheel-winner">
                            <span class="legend-wheel-winner-prize">
                                <span class="legend-wheel-prize-integer">{{ game.win }}</span>
                            </span>
                            <div class="legend-wheel-winner-coefficient" style="background-color: rgb(255, 255, 255)">
                                x {{ game.coef }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <LiveDropList />
    </div>
</template>

<script>
import LiveDropList from "@/components/LiveDropList.vue";
import Button from "@/components/ui/Button.vue";
import Trash from "@/components/ui/TrashSVG.vue";
import axios from "axios";

export default {
    components: {
        LiveDropList,
        Button,
        Trash,
    },
    data() {
        return {
            level: 1,
            bet: "1.00",
            btnLoading: false,
            game: {
                end: false,
            },
            noty: {
                mess: null,
                type: null,
            },
            position: [
                {},
                {
                    blue: [444, 500, 507, 514, 535, 542, 550, 560, 570, 590, 606, 630],
                    red: [735, 736, 162],
                    lose: [24, 130, 135, 492, 496],
                },
                {
                    blue: [496, 668, 535, 520, 524, 534, 564, 594, 637],
                    red: [707, 710, 1500, 1503, 625],
                    green: [752, 755, 1472, 1475],
                    pink: [505, 506, 507, 508, 509, 510],
                    lose: [498, 530, 1477, 1479, 1505, 603, 629, 672, 515],
                },
                {
                    pink: [1540, 1535, 1539, 1537],
                    lose: [1000, 1050, 1532, 1543, 783, 283, 400, 900, 990, 1990, 1190, 590, 225, 825],
                },
            ],
            winDegree: 0,
        };
    },  
    methods: {
        async play() {
            // Блокируем повторные клики во время игры
            if (this.btnLoading) {
                return;
            }

            // Устанавливаем btnLoading сразу, чтобы заблокировать повторные клики
            this.btnLoading = true;

            const user = this.$store.state.user || {};
            const userBalance = parseFloat(user.balance || 0);
            const betAmount = parseFloat(this.bet);

            // Проверка на минимальную ставку
            if (betAmount < 1) {
                this.btnLoading = false; // Сбрасываем при ошибке
                this.noty = {
                    type: "error",
                    mess: "Минимальная ставка 1.00 ₽",
                };
                this.bet = "1.00";
                return;
            }

            // Проверка на недостаточность баланса
            if (betAmount > userBalance) {
                this.btnLoading = false; // Сбрасываем при ошибке
                this.noty = {
                    type: "error",
                    mess: "Недостаточно средств для ставки",
                };
                return;
            }

            // Проверка на максимальную ставку
            if (betAmount > 1000000) {
                this.btnLoading = false; // Сбрасываем при ошибке
                this.noty = {
                    type: "error",
                    mess: "Максимальная ставка 1,000,000.00 ₽",
                };
                this.bet = "1000000.00";
                return;
            }
            this.game.end = false;

            try {
                const response = await axios.post("/wheel/start", {
                    bet: betAmount,
                    level: this.level,
                });

                const { data } = response;

                if (data.error) {
                    this.btnLoading = false;
                    this.noty = {
                        type: "error",
                        mess: data.message,
                    };
                    return;
                }

                // Воспроизведение звука вращения
                if (this.isMute) {
                    const audio = new Audio("/assets/sounds/wheel.wav");
                    audio.play();
                }

                // Обновление баланса пользователя в Vuex
                this.$store.commit("setUser", {
                    ...this.$store.state.user,
                    balance: userBalance - betAmount,
                });

                const position = this.position[this.level][data.color];
                const random = this.getRandomInt(0, position.length - 1);
                const degree = position[random];

                this.startAnimation(degree);

                setTimeout(() => {
                    // Обновляем баланс пользователя после завершения игры
                    this.$store.commit("setUser", {
                        ...this.$store.state.user,
                        balance: data.balance,
                    });

                    this.btnLoading = false;

                    this.game = {
                        win: data.win,
                        coef: data.coef,
                        end: true,
                    };

                    // Воспроизведение звука результата
                    if (this.isMute) {
                        const audio = new Audio(
                            data.coef === 0
                                ? "/assets/sounds/loose.wav"
                                : "/assets/sounds/win_dice.wav"
                        );
                        audio.play();
                    }

                    setTimeout(() => (this.game.end = false), 4000);
                }, 4500);
            } catch (error) {
                console.error("Ошибка при выполнении запроса:", error);
                this.btnLoading = false;
                this.noty = {
                    type: "error",
                    mess: "Произошла ошибка при выполнении запроса.",
                };
            }
        },

        getRandomInt(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        },
        startAnimation(deg) {
            if (screen.width <= 760) {
                deg -= 90;
            }

            this.winDegree += deg + 360 * 2 - (this.winDegree % 360);

            const wheel = document.querySelector(".legend-wheel-img");
            wheel.style.transform = `rotate(${this.winDegree}deg)`;
        },
        validateBet() {
            const betValue = parseFloat(this.bet);
            const user = this.$store.state.user || {};
            const userBalance = parseFloat(user.balance || 0);

            if (isNaN(betValue) || betValue < 1) {
                this.bet = "1.00";
            } else if (betValue > 1000000) {
                this.bet = "1000000.00";
            } else if (betValue > userBalance && userBalance > 0) {
                this.bet = userBalance.toFixed(2);
            } else {
                this.bet = betValue.toFixed(2);
            }
        },
        typeBet(type) {
            const user = this.$store.state.user || {};
            const userBalance = parseFloat(user.balance || 0);
            let bet = parseFloat(this.bet);

            // Если bet невалидное, устанавливаем 1.00
            if (isNaN(bet) || bet < 1) {
                bet = 1;
            }

            const betTypes = {
                min: () => "1.00",
                max: () => (userBalance > 0 ? userBalance.toFixed(2) : "1.00"),
                "/2": () => Math.max(bet / 2, 1).toFixed(2),
                x2: () => {
                    const doubled = bet * 2;
                    return userBalance > 0 ? Math.min(doubled, userBalance).toFixed(2) : doubled.toFixed(2);
                },
                default: () => bet.toFixed(2),
            };

            const newBet = (betTypes[type] || betTypes.default)();
            this.bet = newBet;
        },
    },
    watch: {
        bet: function (newVal) {
            const betValue = parseFloat(newVal);
            // Только проверка границ, без форматирования (чтобы избежать бесконечных циклов)
            if (isNaN(betValue) || betValue < 1) {
                if (this.bet !== "1.00") {
                    this.bet = "1.00";
                }
            } else if (betValue > 1000000) {
                if (this.bet !== "1000000.00") {
                    this.bet = "1000000.00";
                }
            }
            // Проверка баланса убрана из watcher, чтобы избежать циклов
            // Она выполняется в validateBet (при потере фокуса) и в play (перед игрой)
        },
    },
    computed: {
        isMute() {
            return this.$store.state.isMute;
        },
    },
};
</script>

<style lang="scss" scoped>
.page-wheel {
    display: flex;
    flex-direction: column;
    gap: 32px;

    &__content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        overflow: hidden;
        padding: 32px;
    }

    &__form {
        width: 100%;
        max-width: 330px;
    }

    .form {
        &__button {
            font-family: Oswald !important;
        }
        &__group {
            &:first-child {
                margin-bottom: 32px;
            }
            &:nth-child(2) {
                margin-bottom: 92px;
            }
        }
        .active {
            background-color: var(--color-primary);
            color: #ffffff;
            border: 1px solid var(--color-primary);
        }
    }

    &__circle {
        display: flex;
        justify-content: center;
        margin-right: 50px;
    }
}

.legend-wheel-coefficient-list,
.legend-wheel-winner {
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
}

.legend-wheel {
    display: -webkit-inline-box;
    display: inline-flex;
    position: relative;
    vertical-align: middle;
    height: 418px;
    width: 418px;
}

.legend-wheel-circle {
    display: inherit;
    position: inherit;
    vertical-align: inherit;
    height: inherit;
    width: inherit;
}

.legend-wheel-img {
    height: 100%;
    width: auto;
    z-index: 1;
    -webkit-transform: rotate(0);
    transform: rotate(0);
    -webkit-transition: -webkit-transform 4.5s cubic-bezier(0.46, 0.07, 0.04, 0.98);
    transition: transform 4.5s cubic-bezier(0.46, 0.07, 0.04, 0.98), -webkit-transform 4.5s cubic-bezier(0.46, 0.07, 0.04, 0.98);
    will-change: opacity, transform;
}

.legend-wheel-img,
.legend-wheel-inset {
    display: inline-block;
    position: absolute;
}

.legend-wheel-inset {
    border-radius: 50%;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 2;
    border: 3px solid #3333338f;
}

.legend-wheel-arrow {
    background: url("/img/wheel/arrow.png") 50% no-repeat;
    position: absolute;
    right: -40px;
    top: 50%;
    -webkit-transform: translateY(-50%);
    transform: translateY(-50%);
    width: 72px;
    height: 100%;
    z-index: 5;
}

.legend-wheel-fade-enter-active,
.legend-wheel-fade-leave-active {
    -webkit-transition: opacity 0.15s ease-in-out;
    transition: opacity 0.15s ease-in-out;
}

.legend-wheel-coefficient-enter,
.legend-wheel-coefficient-leave-to,
.legend-wheel-fade-enter,
.legend-wheel-fade-leave-to,
.legend-wheel-winner-enter,
.legend-wheel-winner-leave-to {
    opacity: 0;
}

.legend-wheel-inner {
    -webkit-box-align: center;
    align-items: center;
    background-color: #fff;
    border: 6px solid #E1B5FF;
    box-shadow: 0 0 0 10px rgba(0, 0, 0, 0.2), 0 0 32px 0 rgba(0, 0, 0, 0.65);
    border-radius: 50%;
    display: -webkit-box;
    display: flex;
    -webkit-box-pack: center;
    justify-content: center;
    position: absolute;
    left: 50%;
    top: 50%;
    -webkit-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
    width: 238px;
    height: 238px;
    z-index: 3;
}

.legend-wheel-coefficient,
.legend-wheel-winner {
    border-radius: 50%;
    position: absolute;
    will-change: opacity;
}

.legend-wheel-coefficient {
    -webkit-box-align: center;
    align-items: center;
    background-color: #fff;
    min-height: 50%;
    min-width: 50%;
    max-width: 50%;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    color: #000;
}

.legend-wheel-coefficient,
.legend-wheel-coefficient-box {
    flex: none;
    display: -webkit-box;
    display: flex;
    -webkit-box-pack: center;
    justify-content: center;
    max-height: 50%;
}

.legend-wheel-coefficient-box,
.legend-wheel-coefficient-list {
    -webkit-box-align: start;
    align-items: flex-start;
}

.legend-wheel-coefficient-list {
    display: -webkit-box;
    display: flex;
    flex-direction: column;
    -webkit-box-pack: start;
    justify-content: flex-start;
    margin-left: 24px;
}

.legend-wheel-coefficient-list:first-child {
    margin-left: 0;
}

.legend-wheel-coefficient-item {
    -webkit-box-align: center;
    align-items: center;
    display: -webkit-box;
    display: flex;
    -webkit-box-pack: center;
    justify-content: center;
    margin-bottom: 12px;
}

.legend-wheel-coefficient-item:last-child {
    margin-bottom: 0;
}

.legend-wheel-coefficient-item > span {
    color: #000;
    font-weight: 500;
    font-stretch: normal;
    font-style: normal;
    font-size: 18px;
    margin-left: 7px;
    letter-spacing: normal;
    text-transform: uppercase;
}

.legend-wheel-coefficient-enter-active,
.legend-wheel-coefficient-leave-active,
.legend-wheel-winner-enter-active {
    -webkit-transition: opacity 0.3s ease-in-out;
    transition: opacity 0.3s ease-in-out;
}

.legend-wheel-winner {
    background-color: #5669a1;
    flex-direction: column;
    min-height: 50%;
    min-width: 50%;
    max-width: 50%;
    max-height: 50%;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}

.legend-wheel-winner,
.legend-wheel-winner-coefficient {
    -webkit-box-align: center;
    align-items: center;
    display: -webkit-box;
    display: flex;
    -webkit-box-pack: center;
    justify-content: center;
}

.legend-wheel-winner-coefficient {
    background-color: #fde907;
    border: 1px solid rgba(58, 42, 86, 0.3);
    border-radius: 16px;
    color: #3f2652;
    margin-top: 7px;
    width: 87px;
    text-transform: uppercase;
}

.legend-wheel-prize-decimal,
.legend-wheel-prize-integer,
.legend-wheel-winner-box-shadow-orange .legend-wheel-winner-coefficient,
.legend-wheel-winner-box-shadow-purple .legend-wheel-winner-coefficient,
.legend-wheel-winner-box-shadow-red .legend-wheel-winner-coefficient {
    color: var(--color-text);
}

.legend-wheel-prize-integer {
    font-size: 26px;
    line-height: 1.5;
}

.legend-wheel-prize-decimal {
    font-size: 26px;
    line-height: 1.23;
    white-space: nowrap;
}

.legend-wheel-winner-leave-active {
    -webkit-transition: opacity 0 ease-in-out;
    transition: opacity 0 ease-in-out;
}

@media (max-width: 1024px) {
    .page-wheel {
        &__content {
            flex-direction: column;
            padding: 16px;
        }

        &__form {
            max-width: 100%;
            order: 2;
        }
        .form__group {
            &:nth-child(1) {
                order: 1;
            }
            &:nth-child(2) {
                order: 2;
                margin-bottom: 24px;
            }
        }
        .button_primary {
            order: 3;
        }
        &__circle {
            margin: 0;
            order: 1;
        }
    }

    .legend-wheel {
        width: 265px;
        height: 265px;
    }

    .legend-wheel-inner {
        width: 160px;
        height: 160px;
    }

    .legend-wheel-coefficient-list {
        margin-left: 10px;
    }
    .legend-wheel-coefficient-item {
        margin-bottom: 8px;
    }
    .legend-wheel-coefficient-item > svg {
        width: 12px;
    }
    .legend-wheel-winner-coefficient {
        font-size: 14px;
        width: 61px;
    }
}

@media (max-width: 768px) {
    .legend-wheel-arrow {
        background-size: 70%;
        right: auto;
        top: 4px;
        left: 50%;
        height: 100px;
        -webkit-transform: translate(-50%) rotate(-90deg);
        transform: translate(-50%) rotate(90deg);
    }

    .legend-wheel-winner-coefficient {
        margin-top: 2px;
    }
    .legend-wheel-prize-decimal,
    .legend-wheel-prize-integer {
        font-size: 20px;
    }
}

@media (max-width: 414px) {
    .legend-wheel-coefficient {
        min-height: 56%;
        min-width: 56%;
        max-width: 56%;
        max-height: 56%;
    }
    .legend-wheel-winner {
        min-height: 54%;
        min-width: 54%;
        max-width: 54%;
        max-height: 54%;
    }
}

@media (max-width: 330px) {
    .legend-wheel-coefficient,
    .legend-wheel-winner {
        min-height: 60%;
        min-width: 60%;
        max-width: 60%;
        max-height: 60%;
    }
}
@media (max-width: 900px) {
  .form__buttons.bet {
    display: none !important;
  }
}
</style>
