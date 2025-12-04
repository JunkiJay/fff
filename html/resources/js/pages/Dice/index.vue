<template>
    <div class="flex flex-col gap-8">
        <div
            class="form_share flex flex-col lg:flex-row  justify-between m-3 md:m-0 p-[15px] lg:gap-[20px] lg:p-8 rounded-[24px] bg-[var(--color-content)]">
            <div class="order-2 lg:order-1 max-w-[330px] w-full form gap-[24px] lg:gap-[55px]">
                <div class="form__group">
                    <label class="form__label">Ставка</label>
                    <div class="form__content">
                        <div class="form__input">
                            <input type="text" v-model="bet" v-on:focusout="bet = +bet < 1 ? '1.00' : bet"
                                @change="typeBet('default')" />
                            <button v-on:click="bet = '1.00'" class="form__trash">
                                <Trash />
                            </button>
                            <Button variant="default" :isDisabled="false" class="form__button" @click.native="typeBet('min')">MIN</Button>
                            <Button variant="default" :isDisabled="false" class="form__button" @click.native="typeBet('max')">MAX</Button>
                            <Button :isDisabled="!$store.state.user || parseFloat(bet) * 2 > $store.state.user.balance"
                                variant="default" class="form__button" @click.native="typeBet('x2')">X2</Button>
                            <Button :isDisabled="!$store.state.user || parseFloat(bet) / 2 < 1" variant="default"
                                class="form__button" @click.native="typeBet('/2')">/2</Button>
                        </div>
                        <div class="form__buttons bet">
                            <Button variant="default" :isDisabled="false" class="form__button" @click.native="typeBet('min')">MIN</Button>
                            <Button variant="default" :isDisabled="false" class="form__button" @click.native="typeBet('max')">MAX</Button>
                            <Button :isDisabled="!$store.state.user || parseFloat(bet) * 2 > $store.state.user.balance"
                                variant="default" class="form__button" @click.native="typeBet('x2')">X2</Button>
                            <Button :isDisabled="!$store.state.user || parseFloat(bet) / 2 < 1" variant="default"
                                class="form__button" @click.native="typeBet('/2')">/2</Button>
                        </div>
                    </div>
                </div>

                <div class="form__group">
                    <label class="form__label">Шанс игры</label>
                    <div class="form__content">
                        <input class="form__input text-center" type="text"
                            v-on:focusout="chance = +chance <= 1 ? '1.00' : chance" v-model="chance"
                            @change="typeChance('default')" />
                        <div class="form__buttons chance">
                            <Button variant="default" class="form__button"
                                    :isDisabled="false"
                                @click.native="typeChance('min')">MIN</Button>
                            <Button variant="default" class="form__button"
                                :isDisabled="false"
                                @click.native="typeChance('max')">MAX</Button>
                            <Button variant="default" :isDisabled="false" class="form__button" @click.native="typeChance('x2')">X2</Button>
                            <Button variant="default" :isDisabled="false" class="form__button" @click.native="typeChance('/2')">/2</Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-2 lg:order-2 flex items-center flex-col max-w-[480px] w-full">
                <span class="text-[62px] font-[500] text-[var(--color-primary)]">{{ parseFloat(diceResult).toFixed(2)
                    }}</span>
                <span class="mb-3 text-[var(--color-text)]">Возможный выигрыш</span>

                <div class="gap-3 grid grid-cols-2 w-full">
                    <div
                        class="h-[40px] text-[13px] lg:text-[14px] rounded-[8px] text-[var(--color-text)] bg-[var(--color-form)] flex items-center justify-center">
                        0 - {{ buttons.min }}
                    </div>
                    <div
                        class="h-[40px] text-[13px] lg:text-[14px] rounded-[8px] text-[var(--color-text)] bg-[var(--color-form)] flex items-center justify-center">
                        {{ buttons.max }} - 999999
                    </div>
                    <Button class="panel__button" :isDisabled="false" @click.native="play('min')" @keydown.enter.prevent>Меньше</Button>
                    <Button class="panel__button" :isDisabled="false" @click.native="play('max')" @keydown.enter.prevent>Больше</Button>
                </div>

                <div class="mt-3 h-[44px] rounded-[8px] text-white w-full flex items-center justify-center"
                    :class="[noty.type === 'success' ? 'bg-[#47D524]' : 'bg-[#D82828]', noty.mess ?? 'opacity-0']">
                    {{ noty.mess }}
                </div>
            </div>
        </div>

        <LiveDropList />
    </div>
</template>

<script>
import axios from "axios";
import LiveDropList from "@/components/LiveDropList.vue";
import Button from "@/components/ui/Button.vue";
import Trash from "@/components/ui/TrashSVG.vue";

export default {
    components: {
        LiveDropList,
        Button,
        Trash,
    },
    data() {
        return {
            lastCall: 0,
            chance: "80.00",
            bet: "1.00",
            diceResult: 0,
            noty: {
                mess: null,
                btn: null,
                type: null,
            },
            buttons: {
                min: null,
                center: [null, null],
                max: null,
            },
        };
    },
    mounted() {
        this.updateResult();
    },
    methods: {
        // Обновление расчетов кнопок и результатов
        updateResult() {
            const chanceRatio = this.chance / 100;
            this.diceResult = (100 / this.chance) * this.bet;
            this.buttons.min = Math.floor(chanceRatio * 999999);
            this.buttons.max = 999999 - this.buttons.min;
            const offset = Math.floor(chanceRatio * 999999) / 2;
            this.buttons.center = [
                Math.floor(500000 - offset),
                Math.floor(500000 + offset),
            ];
        },

        // Основная игровая логика
        async play(type) {

            const now = Date.now();
            if (now - this.lastCall < 100) {
                this.setNotification(type, "error", "Слишком частый вызов метода");
                return;
            }
            this.lastCall = now;

            console.log('Тип ставки:', type);
            console.log('Пользователь:', this.$store.state.user);

            const user = this.$store.state.user || {};
            const userBalance = parseFloat(user.balance || 0);
            const betAmount = parseFloat(this.bet);

            console.log('Баланс пользователя:', userBalance);
            console.log('betAmount:', betAmount);


            // Проверка на недостаточность баланса
            if (betAmount > userBalance) {
                this.setNotification(type, "error", "Недостаточно средств для ставки");
                return;
            }

            try {
                const { data } = await axios.post("/dice/bet", {
                    amount: this.bet,
                    chance: this.chance,
                    type,
                });


                // Обновление только баланса пользователя
                this.$store.commit("setUser", {
                    ...this.$store.state.user,
                    balance: parseFloat(data.balance), // Обновление баланса
                });

                this.handleGameResult(data, type);
            } catch (error) {
                console.error("Ошибка при выполнении запроса:", error);

                if (error.response && error.response.status === 401) {
                    this.$store.dispatch("logout");
                } else {
                    this.setNotification(type, "error", "Произошла ошибка запроса.");
                }
            }

        },

        // Обработка результата игры
        handleGameResult({ status, text, balance }, type) {
            if (typeof balance !== "undefined") {
                this.$store.commit("setUser", {
                    ...this.$store.state.user,
                    balance: parseFloat(balance),
                });
            } else {
                console.error("Ошибка: balance отсутствует в ответе сервера");
            }

            const sound = status
                ? "/assets/sounds/win_dice.wav"
                : "/assets/sounds/loose.wav";

            this.setNotification(type, status ? "success" : "error", text);

            if (this.isMute) new Audio(sound).play();
        },

        // Установка уведомления
        setNotification(btn, type, mess) {
            this.noty = { btn, type, mess };
        },

        // Управление ставками
        typeBet(type) {
            const user = this.$store.state.user || {};
            const userBalance = parseFloat(user.balance || 0);
            const bet = parseFloat(this.bet);

            const betTypes = {
                min: () => "1.00",
                max: () => userBalance.toFixed(2),
                "/2": () => Math.max(bet / 2, 1).toFixed(2),
                x2: () => Math.min(bet * 2, userBalance).toFixed(2),
                default: () => bet.toFixed(2),
            };

            this.bet = (betTypes[type] || betTypes.default)();
        },

        // Управление шансами
        typeChance(type) {
            const chance = parseFloat(this.chance);

            const chanceTypes = {
                min: () => "1.00",
                max: () => "95.00",
                "/2": () => Math.max(chance / 2, 1).toFixed(2),
                x2: () => Math.min(chance * 2, 95).toFixed(2),
                default: () => chance.toFixed(2),
            };

            this.chance = (chanceTypes[type] || chanceTypes.default)();
        },
    },
    watch: {
        chance: function () {
            if (parseFloat(this.chance) < 1) {
                this.chance = "1.00";
            }
            if (parseFloat(this.chance) > 95) {
                this.chance = "95.00";
            }
            this.updateResult();
        },
        bet: function () {
            if (parseFloat(this.bet) < 1) {
                this.bet = "1.00";
            }
            if (parseFloat(this.bet) > 1000000) {
                this.bet = "1000000.00";
            }
            this.updateResult();
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
@-webkit-keyframes anim {
    0% {
        bottom: 5px;
    }

    50% {
        bottom: -5px;
    }

    to {
        bottom: 5px;
    }
}

@keyframes anim {
    0% {
        bottom: 5px;
    }

    50% {
        bottom: -5px;
    }

    to {
        bottom: 5px;
    }
}

.form {
    &__group {
        margin-bottom: 0;
    }

    &__label {
        margin-bottom: 8px;
    }
}

@media (max-width: 1024px) {
    .form_share {
        align-items: center;

        .form {
            order: 3;
            padding-bottom: 20px;
            max-width: 100%;
            padding-bottom: 0;

            &__group {
                &:first-child {
                    margin-top: 16px;
                    margin-bottom: 0;
                }
            }
        }

        .form__buttons.chance button {
            display: block;
            height: 40px;
        }
    }
}
@media (max-width: 900px) {
    .form__buttons.bet {
        display: none !important;
    }
}
</style>
