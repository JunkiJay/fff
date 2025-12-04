<template>
    <div class="page-pay">
        <img src="/assets/withdraw/withdraw.jpg" class="page-pay__banners-desc aspect-[11/1]" alt="" />
        <img src="/assets/withdraw/withdraw2.png" class="page-pay__banners-mobile aspect-[6/1]" alt="" />
        <div class="flex mb-8  rounded-[24px] lg:bg-[var(--color-content)]" v-if="providers">
            <div class="w-full lg:w-[100%] flex flex-col mx-auto">
                <div class="page-pay__parent ">
                    <div class="w-[100%] p-4">
                        <div class="page-pay__withdraw-methods withdraw-methods ">
                            <p class="withdraw-methods__desc mb-4">Выберите метод</p>
                            <!-- <small class="withdraw-methods__min-deposite">Мин. вывод от 1100 рублей</small> -->
                            <div class="withdraw-methods__items">
                                <button v-for="paymentProvider in availablePaymentMethods()"
                                        :key="paymentProvider.provider"
                                        class="withdraw-method relative"
                                        :class="[provider === paymentProvider.provider ? 'withdraw-method_active' : '']"
                                        @click="provider = paymentProvider.provider; method = paymentProvider.method; limit = `${formatCurrency(paymentProvider.min)} - ${formatCurrency(paymentProvider.max)}`;"
                                >
                                    <img v-if="paymentProvider.hot" class="absolute top-[6px] right-[6px] w-5 h-5"
                                         src="/assets/withdraw/fire.svg" alt="" />
                                    <img :src="paymentProvider.icon" class="withdraw-method__img" />
                                    <div class="withdraw-method__title">{{ paymentProvider.title }}</div>
                                    <div class="withdraw-method__desc">
                                        Мин. {{ formatCurrency(paymentProvider.min) }}
                                        <span
                                            v-if="Number(paymentProvider.bonus_percent) > 0">+{{ paymentProvider.bonus_percent
                                            }}%</span>
                                    </div>
                                </button>

                            </div>
                        </div>
                    </div>
                    <div class="p-4   md:border-l border-l-0 border-[#3E3B47CC] w-[100%]">
                        <div class="withdraw-methods">
                            <div v-if="!istimerSectionActive">
                                <h2 class="page-pay__title mb-6">Пополнение счета</h2>
                                <div class="page-pay__form form">

                                    <div class="form__group">
                                        <label class="form__label">Сумма</label>

                                        <div class="form__content">
                                            <input class="form__input" type="number" placeholder="Сумма" v-model="amount" />
                                            <div class="form__buttons">
                                                <Button variant="default" class="form__button" :isDisabled="false"
                                                        @click.native="amount = 200">200
                                                </Button>
                                                <Button variant="default" class="form__button" :isDisabled="false"
                                                        @click.native="amount = 500">500
                                                </Button>
                                                <Button variant="default" class="form__button" :isDisabled="false"
                                                        @click.native="amount = 1500">1500
                                                </Button>
                                                <Button variant="default" class="form__button" :isDisabled="false"
                                                        @click.native="amount = 3000">3000
                                                </Button>
                                                <Button variant="default" class="form__button" :isDisabled="false"
                                                        @click.native="amount = 5000">5000
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form__group" v-if="method === 'usdt'">
                                    <label class="form__label">Выберите сеть</label>

                                    <div class="form__content">
                                        <select class="form__input" v-model="network">
                                            <option value="trc-20">trc-20</option>
                                            <option value="erc-20">erc-20</option>
                                            <option value="bep-20">bep-20</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="page-pay__comission mb-3 flex flex-col">
                                <span>
                                    Лимит одной операции <span class="">{{ limit }}</span>
                                </span>
                                </div>

                                <Button class="page-pay__button w-full" :isDisabled="this.isCreating"
                                        @click.native="create">Перейти к оплате
                                </Button>
                            </div>
                            <div v-else>
                                <h2 class="page-pay__title mb-2">Пополнение счета</h2>
                                <div class="text-[var(--color-text-secondary)] mb-6">Скопируйте точную сумму, в случае
                                    ошибки
                                    платеж не будет зачислен
                                </div>
                                <!-- <p class="page-pay__desc">Внимание! Платёжные системы могут взимать дополнительную комиссию
                                за
                                проведение платежа!</p> -->

                                <div class="page-pay__form form">
                                    <div class="form__group">
                                        <div class="relative">
                                            <BaseCopyInput :class="yourPaymentChanged ? 'z-[131]' : ''"
                                                           v-model="amount2"
                                                           label="Сумма перевода, ₽"
                                                           placeholder="Сумма перевода, ₽"
                                                           copyText="Скопировать"
                                                           :value="amount2"
                                                           :placeholder="amount2"
                                            />
                                            <div v-if="yourPaymentChanged"
                                                 class="bg-[#0A0410C2] top-0 right-0 fixed w-screen h-screen z-[121]">
                                            </div>
                                            <div v-if="yourPaymentChanged"
                                                 class="absolute bottom-[90px] w-full bg-[#1F1D28] z-[131] rounded-xl">
                                                <div class="w-full h-full px-[23px]  py-4">
                                                    <div class="flex gap-[6px]">
                                                        <img src="/assets/pay/alert.svg" alt="">
                                                        <p
                                                            class="text-[var(--color-text)] text-[24px] font-medium font-[Oswald]">
                                                            Ваша
                                                            сумма изменена!
                                                        </p>
                                                    </div>
                                                    <div class="mt-[6px] text-[15px]">
                                                    <span class="text-[var(--color-text)]"> Скопируйте точную сумму, в
                                                        случае ошибки платеж </span> <span class="text-[#B048F8]">не
                                                        будет зачислен!</span>
                                                    </div>
                                                    <Button @click.native="yourPaymentChanged = false"
                                                            :isDisabled="false"
                                                            class="mt-[19px] mb-1 bg-[var(--color-primary)] w-full text-center py-3 text-white rounded-xl">
                                                        Ознакомлен
                                                    </Button>
                                                </div>
                                                <img class="absolute -bottom-4 right-[calc(50%-24px)] -translate-x-1/2"
                                                     src="/assets/pay/chevron.svg" alt="">
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <BaseCopyInput class="mt-4"
                                                           v-model="phoneNumber" label="Номер телефона"
                                                           :value="phoneNumber"
                                                           :placeholder="phoneNumber"
                                                           copyText="Скопировать" />


                                        </div>
                                        <div class="relative">
                                            <BaseUserInput class="mt-4" v-model="fioInput" label="ФИО"
                                                           :placeholder="`${fioInput}`"
                                                           :value="`${fioInput}`"
                                            />
                                        </div>

                                        <div class="relative">
                                            <BaseUserInput :class="yourBankChanged ? 'z-[131]' : ''" class="mt-4"
                                                           v-model="bankInput" label="Банк"
                                                           :value="`Банк ${bankInput}`"
                                                           :placeholder="`Банк ${bankInput}`"
                                            />

                                            <div v-if="yourBankChanged"
                                                 class="bg-[#0A0410C2] top-0 right-0 fixed w-screen h-screen z-[120]">
                                            </div>
                                            <div v-if="yourBankChanged"
                                                 class="absolute bottom-[90px] w-full bg-[#1F1D28] z-[131] rounded-xl">
                                                <div class="w-full h-full px-[23px]  py-4">
                                                    <div class="flex gap-[6px]">
                                                        <img src="/assets/pay/alert.svg" alt="">
                                                        <p
                                                            class="text-[var(--color-text)] text-[24px] font-medium font-[Oswald]">
                                                            Переводите только на банк <span class="text-[#B048F8]">
                                                            {{ bankInput }}!</span>
                                                        </p>
                                                    </div>
                                                    <div class="mt-[6px] text-[16px] text-[var(--color-text)]">
                                                        При переводе в другой банк вы можете потерять свои средства!
                                                    </div>
                                                    <Button @click.native="closeBankPopup"
                                                            class="mt-[19px] mb-1 bg-[var(--color-primary)] w-full text-center py-3 text-white rounded-xl">
                                                        Понятно, переведу на банк {{ bankInput }}
                                                    </Button>
                                                </div>
                                                <img class="absolute -bottom-4 right-[calc(50%-24px)] -translate-x-1/2"
                                                     src="/assets/pay/chevron.svg" alt="">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="page-pay__comission mb-3 flex flex-col">
                                <span>
                                    Ожидание оплаты: <span class="">{{ formattedTime }}</span>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-pay__history p-4 rounded-[24px] lg:bg-[var(--color-content)]" v-if="!userPaymentsLoading">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Система</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="payment in userPayments" :key="payment.id">
                    <td>{{ payment.id }}</td>
                    <td class="system_wallet">
                        <img :src="payment.image" style="margin: auto;height: 55px;" />
                    </td>
                    <td>{{ parseFloat(payment.sum).toFixed(2) }}</td>
                    <td :class="[
                            payment.status === 0 ? 'text-yellow-500' : '',
                            payment.status === 1 ? 'text-green-500' : ''
                        ]">
                        {{ payment.status === 1 ? "Зачислено на счет" : "Ожидание" }}
                    </td>
                    <td>{{ $moment(payment.created_at).format("lll") }}</td>
                </tr>
                <td colspan="6" class="p-3" v-if="userPayments.length === 0">История пуста</td>
                </tbody>
            </table>
        </div>

        <div v-if="showModal" class="modal-overlay">
            <div class="modal-content">
                <br>
                <h2 style="font-size: 18px;">Реквизиты для перевода {{ formattedTime }} </h2>
                <div style="
                background-image: url('/assets/withdraw/bank-cards.png');
                height: 200px;
                width: 360px;
                margin: auto;
                color: white;
                background-repeat: no-repeat;">
                    <h2 data-v-1c75a801="" style="
                        margin-top: 100px;
                        margin-right: 120px;
                        font-size: 20px;
                    ">{{ formatPhoneNumber(paymentDetails.receiver) }}</h2>
                    <h6 style="
                    margin-top: 40px;
                    margin-right: 220px;
                ">{{ formatCardholderName(paymentDetails.name) }}</h6>
                </div>
                <div class="copyable-field">
                    <p><strong>Сумма к оплате:</strong> {{ paymentDetails.amount }} ₽</p>
                    <button class="copy-button"
                            @click="copyToClipboard(paymentDetails.amount)">Скопировать
                    </button>
                </div>

                <div class="copyable-field">
                    <p><strong>Номер счета:</strong> {{ paymentDetails.receiver }}</p>
                    <button class="copy-button"
                            @click="copyToClipboard(paymentDetails.receiver)">Скопировать
                    </button>
                </div>

                <p><strong>Банк:</strong> {{ paymentDetails.bank }}</p>
                <p><strong>Получатель:</strong> {{ formatCardholderName(paymentDetails.name) }}</p>

                <div class="modal-buttons">
                    <button @click="confirmPayment" class="button_success">Я перевел</button>
                    <button @click="cancelPayment" class="button_danger">Отменить</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import Button from "@/components/ui/Button.vue";
import BaseCopyInput from "../../components/base/BaseCopyInput.vue";
import BaseUserInput from "../../components/base/BaseUserInput.vue";
import axios from "axios";
import { mapActions, mapGetters, mapState } from "vuex";
import { ref } from "vue";

export default {
    components: {
        Button,
        BaseCopyInput,
        BaseUserInput
    },
    setup() {
        const isCreating = ref(false);

        return {
            isCreating
        };
    },
    data() {
        return {
            showModal: false,
            paymentSuccess: false,
            yourPaymentChanged: false,
            yourBankChanged: false,
            istimerSectionActive: false,
            isLoading: true,
            showPurse: false,
            amount: 0,
            network: "trc-20",
            wallet: null,
            minutes: 10,
            seconds: 0,
            timer: null,
            isRunning: false,
            promocode: null,
            phoneNumber: "",
            amount2: 0,
            bankInput: "",
            fioInput: "",
            windowWidth: window.innerWidth,
            method: "cryptobot",
            limitPayment: false,
            provider: "cryptobot",
            limit: "1,00 ₽ - 300 000,00 ₽"
        };
    },

    methods: {
        ...mapGetters("payments", ["availablePaymentMethods"]),
        ...mapActions("payments", ["fetchPaymentMethods", "fetchUserPayment"]),
        extractErrorMessages(error) {
            const messages = [];

            const data = error?.response?.data;
            const errors = data?.errors;

            if (errors && typeof errors === "object" && !Array.isArray(errors)) {
                for (const [field, arr] of Object.entries(errors)) {
                    if (Array.isArray(arr)) {
                        for (const item of arr) {
                            if (typeof item === "string") {
                                messages.push(item);
                            } else if (item && typeof item.message === "string") {
                                messages.push(item.message);
                            } else if (item != null) {
                                messages.push(String(item));
                            }
                        }
                    } else if (typeof arr === "string") {
                        messages.push(arr);
                    }
                }
            } else if (typeof data?.message === "string") {
                messages.push(data.message);
            } else if (typeof data === "string") {
                messages.push(data);
            } else if (typeof error?.message === "string") {
                messages.push(error.message);
            } else {
                messages.push("Произошла неизвестная ошибка");
            }

            return messages;
        },
        formatCurrency(v) {
            const n = Number(v) || 0;
            return new Intl.NumberFormat("ru-RU", {
                style: "currency",
                currency: "RUB",
                minimumFractionDigits: 2
            }).format(n);
        },
        create() {
            this.isCreating = true;

            axios.post("/payment/create", {
                amount: this.amount,
                code: this.promocode,
                provider: this.provider,
                network: this.network,
                method: this.method
            })
                .then(response => {
                    const { data } = response;

                    if (data.error) {
                        if (Array.isArray(data.message)) {
                            data.message.forEach(error => {
                                this.$emitter.emit("noty", {
                                    title: "Ошибка",
                                    text: error,
                                    type: "error"
                                });
                            })

                            return
                        } else {
                            return this.$emitter.emit("noty", {
                                title: "Ошибка",
                                text: data.message,
                                type: "error"
                            });
                        }
                    }

                    if (data.payment) {
                        this.paymentDetails = data.payment;
                        this.istimerSectionActive = true;
                        this.startTimer();
                        this.amount2 = data.payment.amount;
                        this.bankInput = data.payment.bank;
                        this.fioInput = data.payment.name;
                        this.phoneNumber = data.payment.receiver;
                        this.yourBankChanged = true;
                        if (this.amount !== +this.amount2) {
                            this.yourPaymentChanged = true;
                        }
                    } else if (data.url) {
                        location.href = data.url;
                    } else {
                        this.$emitter.emit("noty", {
                            title: "Ошибка",
                            text: "Не удалось получить ссылку для оплаты. Попробуйте еще раз.",
                            type: "error"
                        });
                        this.isCreating = false;
                    }
                })
                .catch(error => {
                    const messages = this.extractErrorMessages(error);
                    if (messages.length > 0) {
                        messages.forEach(error => {
                            this.$emitter.emit("noty", {
                                title: "Ошибка",
                                text: error,
                                type: "error"
                            });
                        });
                    }
                })
                .finally(() => this.isCreating = false);
        },
        returnWithdraw(id) {
            axios
                .post("/withdraw/decline", {
                    id
                })
                .then((response) => {
                    const { data } = response;

                    if (data.error) {
                        return this.$emitter.emit("noty", {
                            title: "Ошибка",
                            text: data.message,
                            type: "error"
                        });
                    }

                    this.withdraws = this.withdraws.map((item) => (item.id !== id ? item : { ...item, status: 2 }));
                    this.$root.user.balance = data.balance;
                });
        },
        selectPurse(id) {
            let select = this.unq.find((item) => item.id === id);
            this.system = select.provider;
            this.wallet = select.wallet;
            this.showPurse = false;
        },
        closeBankPopup() {
            this.yourBankChanged = false;
            // this.amount2 = '209'
            if (this.amount !== +this.amount2) {
                this.yourPaymentChanged = true;
            }
        },

        startTimer() {
            if (!this.isRunning) {
                this.isRunning = true;
                this.timer = setInterval(() => {
                    this.tick();
                }, 1000);
            }
        },
        pauseTimer() {
            this.isRunning = false;
            clearInterval(this.timer);
        },
        resetTimer() {
            this.pauseTimer();
            this.minutes = 10;
            this.seconds = 0;
        },

        tick() {
            if (this.totalSeconds <= 0) {
                // Timer reached 00:00
                this.resetTimer();

                this.istimerSectionActive = false;
            }

            // Decrease total seconds by 1
            const newTotalSeconds = this.totalSeconds - 1;

            // Convert back to minutes and seconds
            this.minutes = Math.floor(newTotalSeconds / 60);
            this.seconds = newTotalSeconds % 60;
        },
        handleResize() {
            this.windowWidth = window.innerWidth;
        }
    },

    created() {
        this.fetchUserPayment();
        this.fetchPaymentMethods({ userId: this.user.id });
    },
    beforeDestroy() {
        clearInterval(this.timer);
        window.removeEventListener("resize", this.handleResize);

    },
    computed: {
        ...mapState(["user"]),
        ...mapState("payments", ["userPayments", "providers"]),
        formattedTime() {
            // Format the time as MM:SS with leading zeros
            const mins = this.minutes.toString().padStart(2, "0");
            const secs = this.seconds.toString().padStart(2, "0");
            return `${mins}:${secs}`;
        },

        totalSeconds() {
            // Calculate total seconds for easier time manipulation
            return this.minutes * 60 + this.seconds;
        }
    }
};
</script>

<style lang="scss" scoped>
.page-pay {
    &__parent {
        background: #15101D;
        border: 2px solid var(--color-secondary);
        padding: 16px;
        border-radius: 24px;
        // flex md:flex-row flex-col md:border-[#3E3B47CC] md:p-4
        display: flex;
        flex-direction: row;
        margin: 0;
    }

    &__banners-mobile {
        display: none;
    }

    &__banners-desc {
        display: block;
        padding: 0 0px;
        margin-bottom: 24px;
    }

    &__title {
        font-size: 24px;
        color: var(--color-text);
        font-weight: 500;
        font-family: Oswald !important;
        // text-align: center;
    }

    &__desc {
        text-align: center;
        font-size: 16px;
        color: var(--color-text);
    }

    &__form {
        margin-bottom: 24px;
    }

    &__withdraw-methods {
        margin-bottom: 24px;
    }

    &__comission {
        span {
            color: var(--color-text-secondary);

            strong {
                color: var(--color-text);
            }

            span {
                color: var(--color-text);
            }
        }
    }


    .withdraw-methods {
        display: flex;
        flex-direction: column;
        //  align-items: center;


        &__title {
            font-size: 24px;
            color: var(--color-text);
            font-family: Oswald !important;
        }

        &__desc {
            font-size: 16px;
            color: var(--color-text);
            // margin-bottom: 0px;
        }

        &__min-deposite {
            font-size: 16px;
            color: var(--color-text-secondary);
            margin-bottom: 24px;
        }

        &__items {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;

            margin-bottom: 24px;
        }

        .withdraw-method {
            width: 100%;
            height: 160px;
            border-radius: 8px;
            padding: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #1f1d28;
            // border: 2px solid var(--color-secondary);
            transition: all 0.2s ease;

            &_active {
                border-color: var(--color-primary);
                background: #2e2a36;
            }

            &__img {
                width: 50%;
                height: 80px;
                object-position: center;
                object-fit: contain;

            }

            &__title {
                margin-top: auto;
                font-size: 16px;
                color: var(--color-text);
            }

            &__desc {
                font-size: 14px;
                color: var(--color-text-secondary);
            }
        }
    }

    .banks {
        flex-direction: column;
        align-items: center;

        &__title {
            font-size: 24px;
            color: var(--color-text);
            font-family: Oswald !important;
        }

        &__desc {
            font-size: 16px;
            color: var(--color-text);
            margin-bottom: 0px;
            margin-bottom: 24px;
        }

        &__items {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .bank {
            background: var(--color-form);
            border-radius: 8px;
            justify-items: center;
            width: 100%;
            height: 160px;
            border: 1px solid var(--color-form-border);
            padding: 10px;
            transition: all 0.2s ease;

            @media (hover: hover) {
                &:hover {
                    transform: scale(1.05);
                }
            }

            &_active {
                border: 2px solid var(--color-primary);
            }
        }
    }

    &__button {
        border-radius: 4px !important;
        font-family: Oswald !important;
        text-transform: uppercase;
        margin-bottom: 24px;
    }

    &__warning {
        font-size: 14px;
        color: var(--color-text-secondary);
        // text-align: center;
    }

    &__history {
        color: var(--color-text);
        width: 100%;
        overflow: auto;

        table {
            min-width: 700px;
            color: var(--color-text);
            width: 100%;
        }
    }
}

@media (max-width: 1024px) {
    .page-withdraw {
        &__desc {
            width: 100%;
        }

        &__form {
            width: 100%;
        }

        &__button {
            width: 100%;
        }

        &__parent {
            margin: 16px;
        }

        &__banners-mobile {
            //  w-full md:mb-10 mb-6 aspect-[6/1] px-4
            display: none;
        }

        &__banners-desc {
            //  w-full md:mb-10 mb-6 aspect-[6/1] px-4
            padding: 0 16px;
            display: block;
            margin-bottom: 24px;

        }


        .withdraw-methods {
            &__items {
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
            }
        }

        &__warning {
            width: 100%;
            color: var(--color-text-secondary);
        }

        .banks {
            &__items {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        &__history {
            color: var(--color-text);
            width: 100%;
            overflow: auto;

            table {
                min-width: 700px;
                width: 100%;
            }
        }
    }

    .form {
        &__input {
            padding-left: 8px;
        }
    }
}


@media (max-width: 700px) {
    .page-pay {
        &__parent {
            background: none;
            border: 0px solid var(--color-secondary);
            padding: 0px;
            border-radius: 9px;
            display: flex;
            flex-direction: column;
            padding: 0px;
            margin: 0px;
        }

        &__banners-mobile {
            padding: 0 16px;
            display: block;
            margin-bottom: 24px;
        }

        &__banners-desc {
            display: none;
        }

        &__withdraw-methods {
            margin-bottom: 0px;
        }

        .withdraw-methods {
            .withdraw-method {
                aspect-ratio: initial;
                height: auto;

                &__img {
                    width: 43px;
                }
            }

            &__items {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
                margin-bottom: 16px;
            }

            background: #15101D;
            border: 2px solid var(--color-secondary);
            padding: 16px;
            border-radius: 9px;
        }
    }
}
</style>
