<template>
    <div class="flex flex-col gap-8 m-3 md:m-0">
        <div
            class="form_share flex flex-col lg:flex-row justify-between items-center gap-[24px] lg:gap-[58px] p-3.5 lg:p-8 rounded-[24px] bg-[var(--color-content)]">
            <div class="max-w-[330px] w-full form order-2 lg:order-2">
                <label class="form__label">Ставка</label>
                <div class="form__content">
                    <div class="form__input">
                        <input v-model="bet" v-on:focusout="bet = +bet < 1 ? '1.00' : bet" @change="typeBet('default')"
                            type="text" />
                        <button v-on:click="bet = '1.00'" class="form__trash">
                            <Trash />
                        </button>
                        <Button variant="default" class="form__button" @click.native="typeBet('min')">MIN</Button>
                        <Button variant="default" class="form__button" @click.native="typeBet('max')">MAX</Button>
                        <Button variant="default" class="form__button" @click.native="typeBet('x2')">X2</Button>
                        <Button variant="default" class="form__button" @click.native="typeBet('/2')">/2</Button>
                    </div>
                    <div class="form__buttons bet">
                        <Button variant="default" class="form__button" @click.native="typeBet('min')">MIN</Button>
                        <Button variant="default" class="form__button" @click.native="typeBet('max')">MAX</Button>
                        <Button variant="default" class="form__button" @click.native="typeBet('x2')">X2</Button>
                        <Button variant="default" class="form__button" @click.native="typeBet('/2')">/2</Button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full order-2 lg:order-2">
                <div>
                    <h2 class="text-center text-[var(--color-primary)] text-[62px] font-[500]">{{
                        parseFloat(result).toFixed(2) }}</h2>
                    <div class="text-center text-[var(--color-text)]">Возможный выигрыш</div>
                </div>

                <div class="p-0 mt-[24px] w-full">
                    <Button class="w-full" variant="primary" @click.native="play" @keydown.enter.prevent> Играть
                    </Button>

                    <div class="mt-3 h-[44px] rounded-[8px] text-white w-full flex items-center justify-center"
                        :class="[noty.type === 'success' ? 'bg-[#47D524]' : 'bg-[#D82828]', noty.mess ?? 'opacity-0']">
                        {{ noty.mess }}
                    </div>
                </div>
            </div>

            <div class="max-w-[330px] w-full form order-3">
                <label class="form__label">Цель игры</label>

                <div class="form__content chance">
                    <input type="text"  class="form__input"
                        v-model="goal" @change="goal = (goal * 1).toFixed(2)" />
                    <div class="form__buttons chance">
                        <Button variant="default" class="form__button" @click.native="goal = '2.00'">2X</Button>
                        <Button variant="default" class="form__button" @click.native="goal = '3.00'">3X</Button>
                        <Button variant="default" class="form__button" @click.native="goal = '5.00'">5X</Button>
                        <Button variant="default" class="form__button" @click.native="goal = '15.00'">15X</Button>
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
            lastCall: 0,
            bet: "1.00",
            goal: "1.50",
            result: 1.5,
            noty: {
                mess: null,
                type: null,
            },
        };
    },
    mounted() {
        this.updateResult();
    },
    methods: {
        updateResult() {
            const betAmount = parseFloat(this.bet);
            const goalMultiplier = parseFloat(this.goal);
            this.result = (betAmount * goalMultiplier).toFixed(2);
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

        play() {

            const now = Date.now();
            if (now - this.lastCall < 100) {
                this.noty = {
                    mess: "Слишком частый вызов метода",
                    type: "error",
                };
                return;
            }
            this.lastCall = now;

            const userBalance = parseFloat(this.$store.state.user?.balance || 0);
            const betAmount = parseFloat(this.bet);

            // Проверка на достаточность средств
            if (betAmount > userBalance) {
                this.noty = {
                    mess: "Недостаточно средств для ставки",
                    type: "error",
                };
                return;
            }

            this.noty = {};
            axios
                .post("/bubbles/play", {
                    bet: betAmount,
                    goal: this.goal,
                })
                .then((response) => {
                    const { data } = response;

                    if (data.error) {
                        this.noty = {
                            mess: data.message,
                            type: "error",
                        };
                        return;
                    }

                    // Обновление Vuex после успешного ответа
                    this.$store.commit("setUser", {
                        ...this.$store.state.user,
                        balance: data.balance,
                    });

                    this.noty = {
                        mess: data.text,
                        type: data.isWin ? "success" : "error",
                    };

                    // Воспроизведение звуков
                    if (this.isMute) {
                        const audioPath = data.isWin
                            ? "/assets/sounds/win_dice.wav"
                            : "/assets/sounds/loose.wav";
                        new Audio(audioPath).play();
                    }
                })
                .catch((error) => {
                    console.error("Ошибка при выполнении игры:", error);
                    this.noty = {
                        mess: "Ошибка при выполнении игры.",
                        type: "error",
                    };
                });
        },
    },
    watch: {
        goal: function () {
            if (parseFloat(this.goal) < 1) {
                this.goal = "1";
            }
            if (parseFloat(this.goal) > 5000) {
                this.goal = "5000.00";
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


<style>

.form__button {
    border: 1px solid white !important;
}

@media (max-width: 1024px) {
    .form_share {
        align-items: center;

        .form {
            order: 3;
            padding-bottom: 20px;
            max-width: 100%;
            padding-bottom: 0;
        }

        .form__buttons.chance button {
            display: block;
            height: 40px;
        }
    }

    .form__content.chance input {
        outline: none;
        text-align: center;
    }
}
@media (max-width: 900px) {
  .form__buttons.bet {
    display: none !important;
  }
}
</style>
