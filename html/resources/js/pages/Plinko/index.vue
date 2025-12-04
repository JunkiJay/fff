<template>
    <div class="flex flex-col gap-8 m-3 md:m-0">
        <div class="form_share flex flex-col lg:flex-row p-3.5 lg:p-8 rounded-[24px] bg-[var(--color-content)]">
            <div class="form">
                <div class="form__group">
                    <label class="form__label">Уровень риска</label>
                    <div class="form__buttons">
                        <Button
                            variant="default"
                            v-for="button in buttons"
                            :key="button.difficulty"
                            class="h-[40px] px-2 w-full rounded-[8px] font-['Oswald'] text-[14px]"
                            :class="
                                game.difficulty === button.difficulty
                                    ? 'bg-[var(--color-primary)] text-white'
                                    : 'border border-[var(--color-form-border)] text-[var(--color-text)] bg-[var(--color-secondary)]'
                            "
                            :isDisable="game.balls !== 0"
                            @click.native="game.difficulty = button.difficulty"
                        >
                            {{ button.title }}
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
                                v-on:focusout="bet = +bet < 1 ? '1.00' : bet"
                                @change="typeBet('default')"
                                :disabled="game.balls !== 0"
                            />
                            <button v-on:click="bet = '1.00'" class="form__trash"><Trash /></button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('min')">MIN</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('max')">MAX</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('x2')">X2</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('/2')">/2</Button>
                        </div>

                        <div class="form__buttons">
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('min')">MIN</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('max')">MAX</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('x2')">X2</Button>
                            <Button :isDisabled="false" variant="default" class="form__button" @click.native="typeBet('/2')">/2</Button>
                        </div>
                    </div>
                </div>

                <div class="form__group">
                    <label class="form__label">Количество пинов</label>

                    <div class="flex gap-1">
                        <Button
                            :isDisabled="false"
                            variant="default"
                            v-for="(item, key) in 9"
                            :key="key"
                            class="flex w-full h-[32px] rounded-[8px] items-center justify-center text-[14px] font-['Oswald']"
                            :class="[
                                key + 8 === game.pins
                                    ? 'bg-[var(--color-primary)] text-white'
                                    : 'border border-[var(--color-form-border)] text-[var(--color-text)] bg-[var(--color-secondary)]',
                                { 'btn-disabled': game.balls !== 0 },
                            ]"
                            @click.native="game.pins = key + 8"
                        >
                            {{ key + 8 }}
                        </Button>
                    </div>
                </div>

                <div class="begin-game flex flex-col gap-2 w-full mt-[50px]">
                    <Button @keydown.enter.prevent @click.native="play" :isDisable="isAutoBet" :class="[{ 'opacity-50': isAutoBet }]"
                        >Начать игру</Button
                    >
                    <Button :isDisabled="false" @click.native="switchAutoBet" :variant="isAutoBet ? 'primary' : 'secondary'">
                        {{ !isAutoBet ? "Авто" : "Остановить" }}
                    </Button>
                </div>
            </div>

            <div class="flex flex-col grow items-center justify-center">
                <div class="plinko_list">
                    <div
                        v-for="(item, key) in game.history"
                        class="h-[28px] rounded-[4px] px-3 text-black text-[11px] font-bold flex items-center border-white border-[1px]"
                        :key="key"
                        :style="'background:' + item.background"
                    >
                        {{ parseFloat(item.coeff).toFixed(1) }}x
                    </div>
                </div>
                <template v-if="this.isLoading">
                    <Loader />
                </template>
                <template v-else>
                    <div id="plinko" size="10" ref="plinko"></div>
                </template>
            </div>
        </div>

        <LiveDropList />
    </div>
</template>

<script>
import $ from "jquery";
import LiveDropList from "@/components/LiveDropList.vue";
import Button from "@/components/ui/Button.vue";
import Ellipsis from "@/components/ui/loader/Ellipsis.vue";
import { getColor, getRandomNumber } from "@/utils";
import MainInput from "@/components/ui/input/Input.vue";
import Trash from "../../components/ui/TrashSVG.vue";
import Loader from "@/components/ui/loader/Loader.vue";

import axios from "@/plugins/axios";

export default {
    components: {
        LiveDropList,
        Button,
        Ellipsis,
        Trash,
        MainInput,
        Loader,
    },
    data() {
        return {
            isLoading: true,
            btnDisabled: true,
            bet: "1.00",
            autoBet: null,
            isAutoBet: false,
            game: {
                pins: 8,
                difficulty: "medium",
                multipliers: [],
                history: [],
                balls: 0,
            },
            buttons: [
                {
                    title: "Легкий",
                    difficulty: "low",
                },
                {
                    title: "Средний",
                    difficulty: "medium",
                },
                {
                    title: "Сложный",
                    difficulty: "high",
                },
            ],
        };
    },
    mounted() {
        this.init();
    },

    beforeRouteLeave(to, from, next) {
        $(".ball").stop();
        clearInterval(this.autoBet);
        next();
    },
    methods: {
        updateResult() {
            const betAmount = parseFloat(this.bet);
            const goalMultiplier = parseFloat(this.goal);
            this.result = (betAmount * goalMultiplier).toFixed(2);
        },
        setPlinkoCoeff(value) {
            this.$store.commit("setPlinkoCoeff", value);
        },
        init() {
            if (this.plinkoCoeff.length === 0) {
                axios.post("/plinko/init").then((response) => {
                    const { data } = response;
                    this.setPlinkoCoeff(data);
                    this.game.multipliers = this.plinkoCoeff;
                    this.isLoading = false;
                    this.$nextTick(() => {
                        this.createBoard(this.game.pins);
                        this.btnDisabled = false;
                    });
                });
            } else {
                this.isLoading = false;
                this.$nextTick(() => {
                    this.createBoard(this.game.pins);
                    this.btnDisabled = false;
                });
                this.game.multipliers = this.plinkoCoeff;
            }
        },
        switchAutoBet() {
            this.isAutoBet = !this.isAutoBet;

            if (this.isAutoBet) {
                return (this.autoBet = setInterval(this.play, 300));
            }

            clearInterval(this.autoBet);
        },
        play() {
            const currBet = +this.bet;

            if (this.userBalance < currBet) {
                return this.$emit("noty", {
                    title: "Ошибка",
                    text: "Недостаточно средств",
                    type: "error",
                });
            }

            this.btnDisabled = true;

            axios
                .post("/plinko/play", {
                    bet: String(currBet),
                    pins: this.game.pins,
                    difficulty: this.game.difficulty,
                })
                .then(({ data }) => {
                    if (data.error) {
                        return this.$emit("noty", {
                            title: "Ошибка",
                            text: data.message,
                            type: "error",
                        });
                    }

                    // Обновляем баланс через store
                    this.$store.commit("setUser", {
                        ...this.user,
                        balance: this.userBalance - currBet,
                    });

                    this.animateBall(data.bucket + 1, data.result, data.coeff);
                    this.game.balls += 1;
                })
                .finally(() => {
                    setTimeout(() => (this.btnDisabled = false), 250);
                });
        },
        animateBall(target, result, coeff) {
            const { el, duration } = this.createBall(target);
            $("#plinko").append(el);

            $(el).attr("result", result);
            $(el).attr("coeff", coeff);
            $(el).animate(this.getPosition(el), duration, () => this.moveBall(el, duration));
        },
        moveBall(el, duration) {
            const ballSize = parseInt($("#plinko").attr("size"));
            const { step, delta, target, result, coeff } = this.getAttributesValues(el, parseFloat);
            const updatedStep = step + 1;

            let muted = this.isMute;

            let deltaUpdate = getRandomNumber(0, 1);
            let heading = 0;

            $(el).attr("step", updatedStep);

            if (updatedStep !== ballSize + 1) {
                const pin = $(`.pin[row="${step}"][pos="${delta}"]`);

                $(pin).addClass("touched");
                setTimeout(() => $(pin).removeClass("touched"), 300);

                if (delta === target) {
                    deltaUpdate = 0;
                } else if (ballSize - step == target - delta) {
                    deltaUpdate = 1;
                }

                heading = !deltaUpdate ? getRandomNumber(10, 14) : getRandomNumber(20, 24);

                deltaUpdate += delta;

                $(el).attr("delta", deltaUpdate);
                $(el)
                    .removeAttr("heading")
                    .delay(duration / 1e3)
                    .queue(function () {
                        $(el).attr("heading", heading).dequeue();
                    });

                $(el).animate(this.getPosition(el), duration, () => this.moveBall(el, duration));
            } else {
                const bucket = $(`.bucket-${target}`);
                const bucketWrap = $(`.bucket-${target} .payout-value-wrap`);

                $(bucket).addClass("animated");
                setTimeout(() => $(bucket).removeClass("animated"), 300);
                this.game.balls -= 1;
                this.game.history.unshift({ coeff, background: $(bucketWrap).css("background") });

                // Обновляем баланс через `store`
                const newBalance = parseFloat(this.$store.state.user?.balance || 0) + result;
                this.$store.commit("setUser", {
                    ...this.$store.state.user,
                    balance: newBalance.toFixed(2),
                });

                $(el)
                    .removeAttr("heading")
                    .delay(duration / 1e3)
                    .queue(function () {
                        $(el).attr("heading", 2).dequeue();
                    })
                    .delay(duration)
                    .queue(function () {
                        $(el).remove().dequeue();

                        if (muted) {
                            const audio = new Audio("/assets/sounds/plinko.wav");
                            audio.play();
                        }
                    });
            }
        },
        getPosition(ball) {
            const { step, delta } = this.getAttributesValues(ball);
            const el = $(`[row="${step}"][pos="${delta}"]`);

            const top = parseFloat(el.css("top"));
            const left = parseFloat(el.css("left"));

            return { top, left };
        },
        getAttributesValues(el, parseFn) {
            // нужно вынести в utils
            const attributeValues = {};
            for (const { name, value } of el.attributes) {
                if (parseFn && typeof parseFn === "function") {
                    attributeValues[name] = parseFn(value);
                } else {
                    attributeValues[name] = value;
                }
            }
            return attributeValues;
        },
        createBall(target) {
            const size = $("#plinko").attr("size");
            const ballSize = 1 / 3 / (parseInt(size) + 2);
            const duration = getRandomNumber(150, 250);

            const styles = {
                position: "absolute",
                top: `${-100 * ballSize}%`,
                left: "50%",
                width: `${100 * ballSize}%`,
                height: `${100 * ballSize}%`,
                background: "#ffd700",
                borderRadius: "50%",
                animationDuration: `${duration / 1e3}s`,
                transform: "translate(-50%, -125%)",
            };

            const attrs = {
                class: "ball",
                step: 2,
                delta: 1,
                target,
            };

            const el = document.createElement("div");

            Object.keys(styles).forEach((key) => {
                el.style[key] = styles[key];
            });

            Object.keys(attrs).forEach((key) => {
                el.setAttribute(key, attrs[key]);
            });

            return { el, duration };
        },
        createBoard(pins) {
            pins += 2;
            $("#plinko").empty();
            $("#plinko").attr("size", pins);

            for (let row = 2; row <= pins; row++) {
                for (let pos = 0; pos <= row; pos++) {
                    const left = 0.5 + (pos - row / 2) / (pins + 2);
                    const top = (row + 1 - 2) / (pins + 2);
                    const widthHeight = 1 / (row == pins ? 3 : 5) / (pins + 2);

                    if (row == pins) {
                        if (pos !== 0 && pos !== pins) {
                            const html = `
                                <div
                                    class="payout-value bucket bucket-${pos}"
                                    row="${row}"
                                    pos="${pos}"
                                    style="z-index:2;position: absolute;top:${100 * top}%;left:${100 * left}%;width: ${
                                250 * widthHeight
                            }%;height: ${250 * widthHeight}%;transform: translate(-50%, -50%)"
                                >
                                    <div class="payout-value-wrap" style="background: ${getColor(pos, this.game.pins)}">
                                        ${this.getMulti(pos)}
                                        <div class="payout-value-wrap__pocket"></div>
                                    </div>
                                </div>
                            `;
                            $("#plinko").append(html);
                        }
                    } else {
                        const styles = {
                            position: "absolute",
                            top: `${100 * top}%`,
                            left: `${100 * left}%`,
                            width: `${150 * widthHeight}%`,
                            height: `${150 * widthHeight}%`,
                            background: "#8D24D5",
                            borderRadius: "50%",
                            transform: "translate(-50%, -50%)",
                        };

                        const el = document.createElement("div");

                        Object.keys(styles).forEach((key) => {
                            el.style[key] = styles[key];
                        });

                        el.setAttribute("row", row);
                        el.setAttribute("pos", pos);
                        el.setAttribute("class", "pin");

                        $("#plinko").append(el);
                    }
                }
            }
        },
        getMulti(position) {
            let multiplier = this.game.multipliers[this.game.difficulty][this.game.pins][position - 1];

            if (multiplier >= 1000) return parseFloat(multiplier / 1000).toFixed(0) + "K";
            return multiplier;
        },
        typeBet(type) {
            switch (type) {
                case "min":
                    this.bet = "1.00";
                    break;
                case "max":
                    this.bet = this.user ? this.userBalance.toFixed(2) : "0.00";
                    break;
                case "/2":
                    this.bet = (this.bet / 2).toFixed(2) < 1 ? "1.00" : (this.bet / 2).toFixed(2);
                    break;
                case "x2":
                    this.bet = this.bet * 2 > this.userBalance ? this.userBalance.toFixed(2) : (this.bet * 2).toFixed(2);
                    break;
                case "default":
                    this.bet = (+this.bet).toFixed(2);
                    break;
            }
        },
    },
    watch: {
        "game.pins"() {
            this.createBoard(this.game.pins);
        },
        "game.difficulty"() {
            this.createBoard(this.game.pins);
        },
        "game.history"() {
            if (this.game.history.length > 15) {
                this.game.history.pop();
            }
        },
        bet: function () {
            this.bet < 0 ? (this.bet = "1.00") : this.bet;
            this.bet > 1000000 ? (this.bet = "1000000.00") : this.bet;
            return (this.bet = bet);
        },
    },
    computed: {
        user() {
            return this.$store.state.user;
        },
        userBalance() {
            return parseFloat(this.user?.balance || 0);
        },
        isMute() {
            return this.$store.state.isMute;
        },
        plinkoCoeff() {
            return this.$store.state.plinkoCoeff;
        },
    },
};
</script>

<style lang="scss">
.form_share .form {
    width: 100%;
    max-width: 440px;
    &__group {
        margin-bottom: 16px;
    }
}
.ball {
    background-color: #ff9900 !important;
}

.plinko_list {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    width: 100%;
    overflow: auto;
    height: 64px;
    padding: 0 10px;
    gap: 8px;
    max-width: 440px;
    &::-webkit-scrollbar {
        width: 0;
        height: 0;
    }
}

.plinko_list .plinko__history-item {
    transition: all 0.5s ease;
    border-radius: 4px;
    width: auto;
    min-width: 60px;
    margin: 0 4px;
    text-align: center;
    color: #333;
    font-size: 12px;
    font-weight: 500;
    padding: 5px 10px;
    user-select: none !important;
}

.plinko_game {
    align-items: center;
    display: flex;
    flex-direction: column;
    height: 100%;
    max-width: 650px;
    width: 100%;
}

.payout-line {
    display: flex;
    justify-content: space-around;
}

.payout-value-wrap {
    align-items: center;
    display: flex;
    justify-content: center;
    position: relative;
}

.payout-value-wrap {
    border-radius: 5px;
    color: #333;
    font-size: 0.6em;
    font-weight: 800;
    height: 25px;
    overflow: hidden;
    text-align: center;
    width: 100%;
}

.payout-value-wrap__pocket {
    background: var(--color-content);
    border-radius: 50%;
    height: 40%;
    left: 50%;
    position: absolute;
    top: -6px;
    transform: translateX(-50%);
    width: 50%;
}

.payout-value.animated {
    -webkit-animation: bucket_drop 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28) 0s;
    animation: bucket_drop 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28) 0s;
}

#plinko {
    width: 400px;
    height: 400px;
    position: relative;
}

#plinko [heading]:not([heading=""]) {
    -webkit-animation-iteration-count: 1;
    animation-iteration-count: 1;
    -webkit-animation-timing-function: ease-in-out;
    animation-timing-function: ease-in-out;
}

#plinko [heading="2"] {
    -webkit-animation-name: fallAway;
    animation-name: fallAway;
}

#plinko [heading="10"] {
    -webkit-animation-name: bounceLeft-1;
    animation-name: bounceLeft-1;
}

#plinko [heading="11"] {
    -webkit-animation-name: bounceLeft-2;
    animation-name: bounceLeft-2;
}

#plinko [heading="12"] {
    -webkit-animation-name: bounceLeft-3;
    animation-name: bounceLeft-3;
}

#plinko [heading="13"] {
    -webkit-animation-name: bounceLeft-4;
    animation-name: bounceLeft-4;
}

#plinko [heading="14"] {
    -webkit-animation-name: bounceLeft-5;
    animation-name: bounceLeft-5;
}

#plinko [heading="20"] {
    -webkit-animation-name: bounceRight-1;
    animation-name: bounceRight-1;
}

#plinko [heading="21"] {
    -webkit-animation-name: bounceRight-2;
    animation-name: bounceRight-2;
}

#plinko [heading="22"] {
    -webkit-animation-name: bounceRight-3;
    animation-name: bounceRight-3;
}

#plinko [heading="23"] {
    -webkit-animation-name: bounceRight-4;
    animation-name: bounceRight-4;
}

#plinko [heading="24"] {
    -webkit-animation-name: bounceRight-5;
    animation-name: bounceRight-5;
}

@-webkit-keyframes bucket_drop {
    0% {
        transform: translate(-50%, -50%);
    }
    50% {
        transform: translate(-50%, -10%);
    }
    to {
        transform: translate(-50%, -50%);
    }
}

@keyframes bucket_drop {
    0% {
        transform: translate(-50%, -50%);
    }
    50% {
        transform: translate(-50%, -10%);
    }
    to {
        transform: translate(-50%, -50%);
    }
}

@-webkit-keyframes bounceLeft-1 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -250%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@keyframes bounceLeft-1 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -250%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@-webkit-keyframes bounceLeft-2 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -260%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@keyframes bounceLeft-2 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -260%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@-webkit-keyframes bounceLeft-3 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -240%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@keyframes bounceLeft-3 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -240%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@-webkit-keyframes bounceLeft-4 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -220%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@keyframes bounceLeft-4 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -220%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@-webkit-keyframes bounceLeft-5 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -200%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@keyframes bounceLeft-5 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -200%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@-webkit-keyframes bounceRight-1 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-250%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@keyframes bounceRight-1 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-250%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@-webkit-keyframes bounceRight-2 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-260%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@keyframes bounceRight-2 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-260%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@-webkit-keyframes bounceRight-3 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-240%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@keyframes bounceRight-3 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-240%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@-webkit-keyframes bounceRight-4 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-220%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceRight-4 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-220%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@-webkit-keyframes bounceRight-5 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-200%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@keyframes bounceRight-5 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-200%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}

@-webkit-keyframes fallAway {
    0% {
        transform: translate(-50%, -125%) scale(1);
    }
    to {
        transform: translate(-50%, -50%) scale(1);
    }
}

@keyframes fallAway {
    0% {
        transform: translate(-50%, -125%) scale(1);
    }
    to {
        transform: translate(-50%, -50%) scale(1);
    }
}

@-webkit-keyframes touched {
    0% {
        box-shadow: 0 0 0 0 hsla(226, 93%, 55%, 0.5);
    }
    to {
        box-shadow: 0 0 0 2.5px hsla(226, 93%, 55%, 0);
    }
}
@keyframes touched {
    0% {
        box-shadow: 0 0 0 0 hsla(226, 93%, 55%, 0.5);
    }
    to {
        box-shadow: 0 0 0 5px hsla(226, 93%, 55%, 0);
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
        }
        .form__buttons .form__button {
            display: none;
        }
    }
    .form__group {
        &:nth-child(1) {
            order: 2;
            margin-top: 8px;
            .form__label {
                margin-bottom: 8px;
            }
            margin-bottom: 16px;
        }
        &:nth-child(2) {
            order: 3;
            margin-bottom: 16px;
        }
        &:nth-child(3) {
            order: 4;
            margin-bottom: 0;
        }
    }
    .begin-game {
        order: 1;
        margin-top: 35px !important;
    }
    #plinko {
        order: 1;
        width: 400px;
        height: 400px;
        margin-bottom: -55px;
    }
}
@media (max-width: 768px) {
    .form__group {
        &:nth-child(3) > div {
            border: 1px solid var(--color-form-border);
            background-color: var(--color-secondary);
            border-radius: 8px;
            gap: 0;
            height: 40px;
            button {
                border-radius: 0;
                border: none;
                border-right: 1px solid var(--color-form-border);
                height: 100%;
                &:first-child {
                    border-radius: 8px 0 0 8px;
                }
                &:last-child {
                    border-radius: 0 8px 8px 0;
                }
            }
        }
    }
}
</style>