<template>
    <div class="page-mines m-3 md:m-0 ">
        <div class="page-mines__content lg:p-4 rounded-[24px] lg:bg-[var(--color-content)]">
            <div class="page-mines__form form gap-[16px] lg:gap-[32px]">
                <div class="form__group chance">
                    <label class="form__label">Ставка</label>
                    <div class="form__content">
                        <div class="form__input">
                            <input type="text" v-model="bet" :disabled="game.state == 2"
                                @focusout="onBetFocusOut" @change="typeBet('default')" />
                            <button v-on:click="bet = '1.00'" class="form__trash">
                                <Trash />
                            </button>
                            <Button variant="default" :disabled="game.state == 2" class="form__button"
                                :isDisabled="false"
                                @click.native="typeBet('min')">MIN</Button>
                            <Button variant="default" :disabled="game.state == 2" class="form__button"
                                :isDisabled="false"
                                @click.native="typeBet('max')">MAX</Button>
                            <Button variant="default" :disabled="game.state == 2" class="form__button"
                                :isDisabled="false"
                                @click.native="typeBet('x2')">X2</Button>
                            <Button variant="default" :disabled="game.state == 2" class="form__button"
                                :isDisabled="false"
                                @click.native="typeBet('/2')">/2</Button>
                        </div>
                        <div class="form__buttons">
                            <Button variant="default" :isDisable="game.state == 2" class="form__button"
                                @click.native="typeBet('min')">MIN</Button>
                            <Button variant="default" :isDisable="game.state == 2" class="form__button"
                                @click.native="typeBet('max')">MAX</Button>
                            <Button variant="default" :isDisable="game.state == 2" class="form__button"
                                @click.native="typeBet('x2')">X2</Button>
                            <Button variant="default" :isDisable="game.state == 2" class="form__button"
                                @click.native="typeBet('/2')">/2</Button>
                        </div>
                    </div>
                </div>
                <div class="form__group">
                    <label class="form__label">Количество мин</label>

                    <div class="form__content">
                        <div class="form__input changes">
                            <Mines />
                            <input type="string" step="1" v-model="bomb" v-on:focusout="bomb = +bomb <= 2 ? 2 : bomb"
                                @change="typeBet('default')" :disabled="game.state == 2" />
                        </div>

                        <div class="form__buttons">
                            <Button variant="default" :isDisable="game.state == 2"
                                :class="['form__button amount-number', { active: bomb == 3 }]" @click.native="bomb = 3">
                                3
                            </Button>
                            <Button variant="default" :isDisable="game.state == 2"
                                :class="['form__button amount-number', { active: bomb == 5 }]" @click.native="bomb = 5">
                                5
                            </Button>
                            <Button variant="default" :isDisable="game.state == 2"
                                :class="['form__button amount-number', { active: bomb == 10 }]"
                                @click.native="bomb = 10">
                                10
                            </Button>
                            <Button variant="default" :isDisable="game.state == 2"
                                :class="['form__button amount-number', { active: bomb == 24 }]"
                                @click.native="bomb = 24">
                                24
                            </Button>
                        </div>
                    </div>
                </div>

                <template v-if="game.state !== 2">
                    <Button :isDisabled="false" class="mt-auto start-game-btn" @click.native="play" v-if="game.state != 2">Начать игру</Button>
                </template>

                <template v-else>
                    <div class="button_primary mt-auto flex gap-2 items-center">
                        <Button @click.native="take" :isDisabled="false">
                            Забрать
                            <ICountUp class="ml-1 font-weight-500" :endVal="game.total" :options="options" />
                        </Button>

                        <Button :isDisabled="false" @keydown.enter.prevent @click.native="autoSelect">Случайная клетка</Button>
                    </div>
                </template>
            </div>

            <div class="page-mines__cells">
                <div class="win-t" v-if="game.state == 1">
                    <span>{{ parseFloat(game.total).toFixed(2) }}</span>
                    <span>{{ parseFloat(game.coeff).toFixed(2) }}x</span>
                </div>

                <div class="cells">
                    <button v-for="(item, key) in grid" @click="openPath(key + 1)" :key="key" :class="[
                        'cell',
                        { lose: item.bomb },
                        { win: item.diamond || item.diamond2 },
                        { opacity: game.state === 0 },
                        { wait: loaderCell == key + 1 },
                    ]"></button>
                </div>
            </div>

            <div class="page-mines__steps steps">
                <div class="steps-nav-button" @click="prevResult()">
                    <img src="data:image/svg+xml;charset=utf-8,%3Csvg width='16' height='16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M13.066 11.536a.5.5 0 00.708 0l.473-.473a.5.5 0 000-.707L8.354 4.464a.5.5 0 00-.708 0l-5.892 5.892a.5.5 0 000 .707l.472.473a.5.5 0 00.708 0l4.712-4.712a.5.5 0 01.708 0l4.713 4.712z' fill='%23777792'/%3E%3C/svg%3E"
                        width="20px" />
                </div>

                <Swiper
                    :modules="[FreeMode, Mousewheel]"
                    v-if="showSwiper"
                    :space-between="8"
                    :loop="false"
                    :auto-height="true"
                    :slides-per-view="swiperDirection === 'horizontal' ? 3 : 5"
                    :direction="swiperDirection"
                    :key="`dir-${swiperDirection}`"
                    class="steps-slider"
                    @swiper="onSwiperReady"
                >
                    <SwiperSlide v-for="key in 25 - bomb" :key="key">
                        <div :class="[
                            'steps-coff',
                            { win: click.length >= key },
                            { active: click.length == key - 1 && game.state == 2 },
                            { lose: game.state == 3 && click.length == key },
                        ]">
                            <div class="steps-coff__top">
                                <span class="steps-coff__step">{{ key }} ход</span>
                                <span class="steps-coff__value">{{ getPrefix(getCoff(key, bomb)) }}x</span>
                            </div>
                            <span>{{ parseFloat(getCoff(key, bomb) * +bet).toFixed(2) }}</span>
                        </div>
                    </SwiperSlide>
                </Swiper>


                <div class="steps-nav-button" @click="nextResult()">
                    <img src="data:image/svg+xml;charset=utf-8,%3Csvg width='16' height='16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M13.066 11.536a.5.5 0 00.708 0l.473-.473a.5.5 0 000-.707L8.354 4.464a.5.5 0 00-.708 0l-5.892 5.892a.5.5 0 000 .707l.472.473a.5.5 0 00.708 0l4.712-4.712a.5.5 0 01.708 0l4.713 4.712z' fill='%23777792'/%3E%3C/svg%3E"
                        width="20px" style="transform: rotate(180deg)" />
                </div>
            </div>
        </div>

        <LiveDropList />
    </div>
</template>

<script lang="ts">
import LiveDropList from "@/components/LiveDropList.vue";
import Button from "@/components/ui/Button.vue";
import { getEmptyArr } from "@/utils/getEmptyArr";
import ICountUp from "vue-countup-v2";
import Trash from "@/components/ui/TrashSVG.vue";
import Mines from "@/components/ui/MinesSVG.vue";
import axios from "axios";
import { ref } from "vue";
import { FreeMode, Mousewheel } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import type { Swiper as SwiperClass } from "swiper/types";
import 'swiper/css'
import 'swiper/css/free-mode'
import 'swiper/css/mousewheel'


export default {
    components: {
        LiveDropList,
        ICountUp,
        Button,
        Trash,
        Mines,
        Swiper,
        SwiperSlide,
    },

    data() {
        const swiperDirection = ref('vertical');

        return {
            swiperDirection,
            FreeMode,
            Mousewheel,
            swiper: null,
            bomb: 3,
            bet: "1.00",
            changeBomb: false,
            grid: [],
            click: [],
            showSwiper: true,
            isLoading: true,
            loaderCell: null,
            noty: {
                mess: null,
                type: null,
            },
            game: {
                state: 0, // 0 - inactive, 1 - win, 2 - process, 3 - lose
                total: 0,
                coeff: 0,
                step: 0,
            },
            splideOptions: {
                arrows: false,
                infinite: false,
                slidesToShow: 5,
                slidesToScroll: 5,
                vertical: true,
                gap: 10,
                responsive: [
                    {
                        breakpoint: 1025,
                        settings: {
                            arrows: false,
                            infinite: false,
                            slidesToShow: 2,
                            slidesToScroll: 2,
                            vertical: false,
                        },
                    },
                ],
            },
            options: {
                useEasing: true,
                useGrouping: true,
                separator: " ",
                decimal: ".",
                prefix: "",
                suffix: "",
                decimalPlaces: 2,
            },
            coefs: null,
            isAuthenticated: false,
        };
    },

    mounted() {
        const isMobile = window.matchMedia('(pointer: coarse) and (max-width: 768px)').matches
        this.swiperDirection = isMobile ? 'horizontal' : 'vertical'
        this.swiper.update();
        this.grid = getEmptyArr(25, { bomb: false, diamond: false });
        this.init();
    },

    methods: {
        prevResult() {
            this.swiper?.slidePrev();
        },
        nextResult() {
            this.swiper?.slideNext();
        },
        onSwiperReady(swiper: SwiperClass) {
            this.swiper = swiper;
        },
        updateResult() {
            this.result = parseFloat(this.goal * this.bet).toFixed(2);
        },
        init() {
            axios
                .post("/mines/init")
                .then((response) => {
                    const { data } = response;
                    if (typeof data.click !== "undefined") {
                        this.bet = data.amount;
                        this.bomb = data.bombs;
                        this.game = {
                            ...this.game,
                            state: 2,
                            total: data.total,
                        };
                        this.click = data.click;
                        this.grid = this.grid.map((item) =>
                            data.click.indexOf(item.index + 1) !== -1 ? { ...item, diamond: true } : item
                        );
                    }
                })
                .finally(() => (this.isLoading = false));
        },
        play() {
            const betAmount = parseFloat(this.bet);
            const userBalance = parseFloat(this.$store.state.user?.balance || 0);

            console.log(betAmount, userBalance);
            // Проверка на достаточность средств
            if (betAmount > userBalance) {
                this.noty = {
                    type: "error",
                    mess: "Недостаточно средств для ставки",
                };
                return;
            }

            axios
                .post("/mines/start", {
                    amount: betAmount,
                    bombs: this.bomb,
                })
                .then((response) => {
                    const { data } = response;

                    if (data.error) {
                        this.noty = {
                            type: "error",
                            mess: data.message || "Ошибка при запуске игры",
                        };
                        return;
                    }

                    // Сброс игрового состояния
                    this.grid = getEmptyArr(25, { bomb: false, diamond: false });
                    this.click = [];
                    this.game = {
                        ...this.game,
                        state: 2,
                        total: 0,
                    };

                    // Обновление баланса в Vuex
                    this.$store.commit("setUser", {
                        ...this.$store.state.user,
                        balance: parseFloat(data.balance), // Обновление баланса
                    });

                    this.noty = {
                        type: "success",
                        mess: "Игра начата!",
                    };
                })
                .catch((error) => {
                    console.error("Ошибка при запуске игры:", error);
                    this.noty = {
                        type: "error",
                        mess: "Произошла ошибка при запуске игры",
                    };
                });
        },

        async openPath(index) {
            if (this.click.includes(index)) return;
            if (this.game.state !== 2) return;

            this.loaderCell = index;

            try {
                const { data } = await axios.post("/mines/open", {
                    path: index,
                });

                if (data.error) {
                    this.noty = {
                        type: "error",
                        mess: data.message || "Ошибка при открытии клетки",
                    };
                    return;
                }

                this.click.push(index);

                if (data.continue) {
                    // Обновление состояния при успешном ходе
                    this.grid[index - 1] = { ...this.grid[index - 1], diamond: true };
                    this.game.total = data.total;

                    if (this.isMute) {
                        new Audio("/assets/sounds/mines-win.wav").play();
                    }
                } else {
                    // Обработка окончания игры
                    this.game.state = 3;
                    this.grid = this.grid.map((item, i) =>
                        data.bombs.includes(i + 1)
                            ? { ...item, bomb: true }
                            : { ...item, diamond: true }
                    );

                    if (this.isMute) {
                        new Audio("/assets/sounds/loose.wav").play();
                    }
                }

                if(data.instwin !== null) {
                    if(!this.$store.getters.getFlag){
                        if(this.mel){
                            var audio1 = new Audio('/assets/sounds/mines-mel-win-1.wav');
                            var audio2 = new Audio('/assets/sounds/mines-mel-win-2.wav');
                        }else{
                            var audio1 = new Audio('/assets/sounds/mines-win.wav');
                            var audio2 = new Audio('/assets/sounds/mines-win.wav');
                        }
                        var randomSoundIndex = Math.random() < 0.5 ? 0 : 1;
                        if (randomSoundIndex === 0) {
                            audio1.play();
                        } else {
                            audio2.play();
                        }
                    }

                    this.game = {
                        ...this.game,
                        state: 1,
                        total: data.instwin.total,
                        coeff: data.instwin.coeff
                    }

                    this.grid = this.grid.map(item =>
                        data.instwin.bombs.indexOf(item.index + 1) !== - 1
                            ? { ...item, bomb: true }
                            : { ...item, diamond: true }
                    )

                     // Обновление баланса в Vuex
                     this.$store.commit("setUser", {
                        ...this.$store.state.user,
                        balance: parseFloat(data.balance), // Обновление баланса
                    });

                }

            } catch (error) {
                console.error("Ошибка при открытии клетки:", error);
                this.noty = {
                    type: "error",
                    mess: "Ошибка при открытии клетки",
                };
            } finally {
                this.loaderCell = null;
            }
        },

        take() {
            axios.post("/mines/take").then((response) => {
                const { data } = response;

                if (data.error) {
                    this.noty = {
                        type: "error",
                        mess: data.message || "Ошибка при завершении игры",
                    };
                    return;
                }

                this.game = {
                    ...this.game,
                    state: 1,
                    total: data.total,
                    coeff: data.coeff,
                };

                this.grid = this.grid.map((item, i) =>
                    data.bombs.includes(i + 1)
                        ? { ...item, bomb: true }
                        : { ...item, diamond: true }
                );

                // Обновление баланса в Vuex
                this.$store.commit("setUser", {
                    ...this.$store.state.user,
                    balance: data.balance,
                });

                this.noty = {
                    type: "success",
                    mess: "Выигрыш забран!",
                };
            });
        },

        typeBet(type) {
            const userBalance = parseFloat(this.$store.state.user?.balance || 0);
            const betAmount = parseFloat(this.bet);

            const betTypes = {
                min: () => "1.00",
                max: () => userBalance.toFixed(2),
                "/2": () => Math.max(betAmount / 2, 1).toFixed(2),
                x2: () => Math.min(betAmount * 2, userBalance).toFixed(2),
                default: () => betAmount.toFixed(2),
            };

            this.bet = (betTypes[type] || betTypes.default)();
        },

        autoSelect() {
            var noActive = document.querySelectorAll(".cell:not(.win)");
            noActive[Math.floor(Math.random() * noActive.length)].click();
        },

        getCoff(step, bombs) {
            let currentBombs = bombs;
            if (!step) {
                step = 0;
            }
            if (bombs == "" || bombs == 0) {
                currentBombs = 1;
            }
            let coef = {
                1: [
                    1.04, 1.12, 1.23, 1.35, 1.5, 1.66, 1.86, 2.1, 2.35, 2.7, 3.12, 3.65, 4.3, 5.2, 6.3, 7.9, 10, 13.4, 19, 28.5, 47, 95,
                    285,
                ],
                2: [
                    1.04, 1.12, 1.23, 1.35, 1.5, 1.66, 1.86, 2.1, 2.35, 2.7, 3.12, 3.65, 4.3, 5.2, 6.3, 7.9, 10, 13.4, 19, 28.5, 47, 95,
                    285,
                ],
                3: [1.07, 1.23, 1.4, 1.65, 1.9, 2.23, 2.67, 3.2, 3.9, 4.8, 6, 7.6, 9.9, 13.3, 18.3, 26, 39, 62, 110, 220, 550, 2200],
                4: [1.12, 1.35, 1.65, 2, 2.45, 3.1, 3.9, 5, 6.55, 8.8, 12, 16.6, 24.5, 36.6, 57, 95, 170, 340, 800, 2400, 12000],
                5: [1.18, 1.5, 1.9, 2.5, 3.25, 4.35, 5.9, 8.2, 11.5, 16.8, 25, 39, 64, 110, 200, 400, 900, 2400, 8400, 50000],
                6: [1.25, 1.66, 2.25, 3.1, 4.3, 6.2, 9.1, 13.5, 21, 33.5, 56, 98, 182, 365, 800, 2000],
                7: [1.3, 1.85, 2.7, 3.9, 5.9, 9, 14.5, 23.5, 40, 71, 133, 265, 575, 1380, 3800, 12700],
                8: [1.4, 2.1, 3.2, 5, 8.2, 13.5, 23.5, 42, 80, 160, 340, 800, 2050, 6200, 23000, 115000, 1005000],
                9: [1.5, 2.4, 3.9, 6.6, 11.5, 21, 40, 80, 170, 390, 970, 2700, 8800, 35500, 195000, 1900000],
                10: [1.57, 2.7, 4.8, 8.8, 16.7, 33.3, 71, 160, 390, 1030, 3100, 10800, 47500, 285000, 3100000],
                11: [1.7, 3.1, 6, 12, 25, 56, 133, 340, 960, 3100, 11500, 54000, 350000, 4200000],
                12: [1.82, 3.65, 7.6, 16.5, 39, 98, 265, 800, 2700, 10750, 54500, 370000, 4900000],
                13: [2, 4.3, 10, 24.5, 64, 180, 580, 2050, 8900, 47500, 355000, 4950000],
                14: [2.15, 5.2, 13, 36, 110, 365, 1390, 6250, 35500, 285000, 4250000],
                15: [2.35, 6.3, 18, 57, 200, 805, 3800, 22800, 195000, 3100000],
                16: [2.65, 8, 26, 95, 405, 2000, 12700, 115000, 1950000],
                17: [3, 10, 39, 170, 910, 6000, 57500, 1000000],
                18: [3.4, 13.5, 63, 350, 2400, 24000, 460000],
                19: [4, 19, 107, 805, 8500, 170000],
                20: [4.8, 28.5, 220, 2400, 50000],
                21: [6, 47, 550, 12000],
                22: [8, 95, 2200],
                23: [12, 285],
                24: [24],
            };

            const coefficients = coef[currentBombs];
            this.coefs = coefficients;
            return coefficients[step - 1];
        },

        getPrefix(n) {
            if (n >= 1000000) return parseFloat(n / 1000000).toFixed(0) + "M";
            if (n >= 1000) return parseFloat(n / 1000).toFixed(0) + "K";
            return parseFloat(n).toFixed(2);
        },
        onBetFocusOut() {
            if (!this.bet || isNaN(+this.bet) || +this.bet < 1) {
                this.bet = '1.00';
            } else {
                this.bet = (+this.bet).toFixed(2);
            }
        },
    },
    computed: {
        isMute() {
            return this.$store.state.isMute;
        },
    },
    watch: {
        bomb: function () {
            this.bomb < 0 ? this.bomb = 3 : this.bomb
            this.bomb > 24 ? this.bomb = 24 : this.bomb
            this.showSwiper = false
            this.$nextTick(() => {
                this.showSwiper = true
            });
        },
    },
};
</script>


<style lang="scss" scoped>
.form__input.changes {
    display: flex;
    justify-content: center;
    align-items: center;

    svg {
        fill: var(--color-negative);
        stroke: rgba(0, 0, 0, 0);
        margin-right: 12px;
    }
}

.page-mines {
    display: flex;
    flex-direction: column;
    gap: 32px;

    &__content {
        width: 100%;
        display: flex;
        justify-content: space-between;
        padding: 32px;
    }

    &__form {
        width: 100%;
        max-width: 330px;
        flex-shrink: 0;
    }

    &__cells {
        margin: auto 0;
        position: relative;
        margin-left: auto;
        margin-right: auto;
    }

    &__steps {
        width: 148px;
        display: flex;
        gap: 4px;
        flex-direction: column;
        align-items: center;

        .steps-nav-button {
            padding: 2px 0;
            cursor: pointer;
            width: 100%;

            img {
                margin: auto;
            }
        }
    }
}

.cells {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
}

.win-box {
    align-items: center;
    display: flex;
    height: 100%;
    justify-content: center;
    position: absolute;
    width: 100%;
    z-index: 5;
}

.steps-coff {
    border: 1px solid #ced4da;
    background: var(--color-form);
    border-radius: 5px;
    margin: 4px 0;
    padding: 5px;
    position: relative;
    transition: 0.5s;
    color: var(--color-text);
    height: 51px;
    font-size: 14px !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;

    &__top {
        display: flex;
        justify-content: space-between;
        width: 100%;
    }
}

.swiper-initialized {
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.start-game-btn {
    margin-top: 42px !important;
}

.form__button,
.amount-number {
    border: 1px solid white !important;
}

.steps-coff.win {
    border: 1px solid #00aa80;
    color: #00aa80;
}

.steps-coff.lose {
    border: 1px solid #fd7078;
    color: #fd7078;
}

.steps-coff.active {
    border: 1px solid #1f50fb;
    color: #1e4ffb;
}

.cell.lose,
.cell.win {
    background-position: 50%;
    background-repeat: no-repeat;
    background-size: 98%;

    &::after {
        display: none;
    }
}

.cell.lose {
    background-image: url("/img/mines/fail.svg");
}

.cell {
    height: 65px;
    width: 65px;
    position: relative;
    cursor: pointer;
    background-image: url("/img/mines/cell.svg");
    background-repeat: no-repeat;
    background-size: contain;
    background-position: center;

    &::after {
        content: "";
        position: absolute;
        width: 65px;
        height: 65px;
        top: 0;
        left: 0;
        z-index: 50;
        background: url("/img/mines/cell.svg") no-repeat center/contain;
    }
}

.cell.win {
    background-image: url("/img/mines/win.svg");
}

.cell.opacity {
    opacity: 0.4;
}

.form {
    &__group {
        margin-bottom: 0;
    }

    &__label {
        margin-bottom: 8px;
    }

    .button_primary {
        order: 3;
        padding: 0;
        font-size: 14px;
        font-weight: 500;
    }
}

@media (max-width: 1024px) {
    .form {
        &__input.changes {
            padding-left: 12px;

            input {
                padding-left: 0;
            }
        }

        &__group {
            &:nth-child(1) {
                order: 2;
                margin-bottom: 8px;
                margin-top: 0;

                .form__buttons {
                    display: none;
                }
            }

            &:nth-child(2) {
                order: 1;
                margin-bottom: 0;
            }

            &:first-child {}
        }

        &__buttons {
            &:nth-child(2) button {
                height: 40px;
            }
        }
    }

    .page-mines {
        &__cells {
            padding: 0 28px;
        }

        &__steps {
            gap: 0;
        }

        .swiper-initialized {
            width: calc(100% - 42px);
            flex: none;
        }

        &__content {
            background-color: var(--color-content);
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;

            .steps-coff {}
        }

        &__form {
            order: 3;
            max-width: 100%;
        }

        &__steps {
            width: 100%;
            order: 1;
            display: flex;
            flex-direction: row;
            justify-content: center;
            height: 59px;

            .steps-nav-button {
                width: 59px;
                flex: none;

                &:first-child {
                    transform: rotate(-90deg) translateY(15px);
                }

                &:last-child {
                    transform: rotate(-90deg) translateY(-15px);
                }
            }
        }

        &__cells {
            order: 2;
            margin: 0;
            width: 100%;
        }
    }

    .cell {
        aspect-ratio: 1/1;
        width: 100%;
        height: auto;

        &::after {
            aspect-ratio: 1/1;
            width: 100%;
            height: auto;
        }
    }
}

.win-t {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #2E2A36;
    border: 2px solid #8D24D5;
    border-radius: 5px;
    box-shadow: 2px 2px 2px 0 #0000004a;
    color: #8D24D5;
    padding: 5px 35px;
    text-align: center;
    user-select: none;
    z-index: 55;
}

.win-t span {
    display: block;
    margin-bottom: 5px;
}
</style>
