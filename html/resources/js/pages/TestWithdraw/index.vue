<template>
    <div class="page-withdraw">
        <div class="flex mb-8 lg:p-4 rounded-[24px] lg:bg-[var(--color-content)]">
            <div class="w-full lg:w-[60%] flex flex-col mx-auto">
                <h2 class="page-withdraw__title">Вывод баланса</h2>

                <p class="page-withdraw__desc">Внимание! Платёжные системы могут взимать дополнительную комиссию за проведение платежа!</p>

                <div class="page-withdraw__form form">
                    <div class="form__group">
                        <label class="form__label">{{ systemGet.title }}</label>

                        <div class="form__content">
                            <input class="form__input" type="number" :placeholder="systemGet.placeholder" />
                        </div>
                    </div>

                    <div class="form__group">
                        <label class="form__label">Сумма</label>

                        <div class="form__content">
                            <input class="form__input" type="number" placeholder="Сумма" v-model="amount" />
                            <div class="form__buttons">
                                <Button variant="default" class="form__button" @click.native="amount = 200">200</Button>
                                <Button variant="default" class="form__button" @click.native="amount = 500">500</Button>
                                <Button variant="default" class="form__button" @click.native="amount = 1500">1500</Button>
                                <Button variant="default" class="form__button" @click.native="amount = 3000">3000</Button>
                                <Button variant="default" class="form__button" @click.native="amount = 5000">5000</Button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="page-withdraw__withdraw-methods withdraw-methods">
                    <h2 class="withdraw-methods__title">Способы оплаты</h2>
                    <p class="withdraw-methods__desc">Выберите систему платежей, для пополнения вашего баланса</p>
                    <small class="withdraw-methods__min-deposite">Мин. вывод от 1100 рублей</small>

                    <div class="withdraw-methods__items">
                        <button
                            v-for="withdrawMethod in withdrawMethods"
                            :key="withdrawMethod.provider"
                            class="withdraw-method"
                            :class="[system == withdrawMethod.provider ? 'withdraw-method_active' : '']"
                            @click="
                                system = withdrawMethod.provider;
                                method = withdrawMethod.method;
                            "
                        >
                            <img :src="withdrawMethod.img" class="withdraw-method__img" />
                            <div class="withdraw-method__title">{{ withdrawMethod.title }}</div>
                        </button>
                    </div>
                </div>

                <template v-if="method == 'sbp'">
                    <div class="page-withdraw__banks banks">
                        <h2 class="banks__title">Выберите банк</h2>
                        <p class="banks__desc">Выберите банк, для вывода с помощью СБП</p>

                        <div class="banks__items">
                            <button
                                class="bank"
                                v-for="sbpBank in sbpBanks"
                                :key="sbpBank.provider"
                                :class="[system == sbpBank.provider ? 'bank_active' : '']"
                                @click="system = sbpBank.provider"
                            >
                                <img :src="sbpBank.img" class="pay-img" />
                            </button>
                        </div>
                    </div>
                </template>

                <div class="page-withdraw__comission">
                    <span>
                        К зачислению: <strong>{{ (amount * ((100 - systemGet.comissia) / 100)).toFixed(2) }}</strong>
                    </span>

                    <span>
                        Комиссия: <strong>{{ parseFloat(systemGet.comissia).toFixed(2) }}%</strong>
                    </span>
                </div>

                <Button class="page-withdraw__button" @click.native="create">Создать выплату</Button>

                <p class="page-withdraw__warning">Внимание! Вывод на платежные системы может занимать до 24 часов!</p>
            </div>
        </div>

        <div class="page-withdraw__history lg:p-4 rounded-[24px] lg:bg-[var(--color-content)]">
            <table>
                <thead>
                    <tr>
                        <th>Система</th>
                        <th>Сумма</th>
                        <th>Кошелек</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="withdraw in withdraws" :key="withdraw.id">
                        <td class="system_wallet" style="height: 50px !important; max-width: 94px !important; width: 80px;">
                            <img v-if="withdraw.provider == 'sberbank'" src="/assets/image/sberbank_logo.svg" >
                            <img v-else-if="withdraw.provider == 'tinkoff'" src="/assets/image/tinkoff_logo.svg" />
                            <img v-else-if="withdraw.provider == 'alfabank'" src="/assets/image/alfabank_logo.svg" />
                            <img v-else :src="'/assets/image/' + withdraw.provider + '.png'" />
                        </td>

                        <td>{{ parseFloat(withdraw.sumWithCom).toFixed(2) }}</td>
                        <td class="">{{ withdraw.wallet }}</td>

                        <td class="expect text-warning" v-if="withdraw.status == 0">
                            <span @click="returnWithdraw(withdraw.id)">Отменить</span>
                        </td>
                        <td class="expect text-success" v-if="withdraw.status == 1">Выполнено</td>
                        <td class="expect text-danger" v-if="withdraw.status == 2">{{ withdraw.reason || "Отменено" }}</td>
                        <td class="expect text-primary" v-if="withdraw.status == 3">Обработка</td>

                        <td>{{ $moment(withdraw.created_at).format("lll") }}</td>
                    </tr>
                    <td colspan="6" class="p-3" v-if="withdraws.length == 0">История пуста</td>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import Button from "@/components/ui/Button.vue";
import sberbankImg from "@img/banks/sberbank.png";
import tinkoffImg from "@img/banks/tinkoff.png";
import alfaImg from "@img/banks/alfa.png";

export default {
    components: {
        Button,
    },

    data() {
        return {
            sberbankImg,
            tinkoffImg,
            alfaImg,
            isLoading: true,
            withdraws: [],
            showPurse: false,
            amount: null,
            wallet: null,
            method: null,
            withdrawMethods: [
                {
                    img: "/assets/image/fk.svg",
                    title: "FK Wallet",
                    provider: "fk",
                    method: null,
                },
                {
                    img: "/assets/image/card.png",
                    title: "Карта",
                    provider: "card",
                    method: null,
                },
                {
                    img: "/assets/image/sbp.png",
                    title: "СБП",
                    provider: "sberbank",
                    method: "sbp",
                },
            ],
            sbpBanks: [
                {
                    img: sberbankImg,
                    title: "Сбербанк",
                    provider: "sberbank",
                    method: "sbp",
                },
                {
                    img: tinkoffImg,
                    title: "Тинькофф",
                    provider: "tinkoff",
                    method: "sbp",
                },
                {
                    img: alfaImg,
                    title: "Альфабанк",
                    provider: "alfabank",
                    method: "sbp",
                },
            ],
            systems: [
                {
                    sys: "sberbank",
                    comissia: 5,
                    placeholder: "Номер телефона +7XXXXXXXXX",
                    title: "Номер телефона",
                },
                {
                    sys: "alfabank",
                    comissia: 5,
                    placeholder: "Номер телефона +7XXXXXXXXX",
                    title: "Номер телефона",
                },
                {
                    sys: "tinkoff",
                    comissia: 5,
                    placeholder: "Номер телефона +7XXXXXXXXX",
                    title: "Номер телефона",
                },
                {
                    sys: "raiffaizen",
                    comissia: 5,
                    placeholder: "Номер телефона +7XXXXXXXXX",
                    title: "Номер телефона",
                },
                {
                    sys: "fk",
                    comissia: 5,
                    placeholder: "Номер кошелька FXXXXXXXXXXXXXXXX",
                    title: "FK кошелек",
                },
                {
                    sys: "card",
                    comissia: 5,
                    placeholder: "Номер карты",
                    title: "Номер карты",
                },
            ],
            unq: [],
            provider: "fk",
        };
    },

    methods: {
        create() {
            if (!this.amount || !this.wallet) {
                return this.$root.$emit("noty", {
                    title: "Ошибка",
                    text: "Заполните все поля",
                    type: "error",
                });
            }

            this.$root.axios
                .post("/withdraw/create", {
                    sum: this.amount,
                    wallet: this.wallet,
                    provider: this.provider,
                })
                .then((response) => {
                    const { data } = response;

                    if (data.error) {
                        return this.$root.$emit("noty", {
                            title: "Ошибка",
                            text: data.message,
                            type: "error",
                        });
                    }

                    this.withdraws.unshift(data.withdraw);
                    this.$root.user.balance = data.balance;

                    this.$root.$emit("noty", {
                        title: "Успешно",
                        text: "Заявка на вывод создана",
                        type: "success",
                    });
                });
        },
        returnWithdraw(id) {
            this.$root.axios
                .post("/withdraw/decline", {
                    id,
                })
                .then((response) => {
                    const { data } = response;

                    if (data.error) {
                        return this.$root.$emit("noty", {
                            title: "Ошибка",
                            text: data.message,
                            type: "error",
                        });
                    }

                    this.withdraws = this.withdraws.map((item) => (item.id !== id ? item : { ...item, status: 2 }));
                    this.$root.user.balance = data.balance;
                });
        },
        init() {
            this.$root.axios.post("/withdraw/init").then((response) => {
                const { data } = response;
                this.isLoading = false;
                this.unq = data.unq;
                this.withdraws = data.data;
            });
        },
        selectPurse(id) {
            let select = this.unq.find((item) => item.id === id);

            this.system = select.provider;
            this.wallet = select.wallet;
            this.showPurse = false;
        },
    },

    mounted() {
        this.init();
    },

    computed: {
        systemGet: function () {
            return this.systems.find((item) => item.sys == this.provider);
        },
    },
};
</script>

<style lang="scss" scoped>
.page-withdraw {
    &__title {
        font-size: 24px;
        color: var(--color-text);
        font-weight: 500;
        font-family: Oswald !important;
        text-align: center;
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
        gap: 16px;
        display: flex;
        justify-content: space-between;
        font-weight: 300;
        margin-bottom: 12px;

        span {
            color: var(--color-text-secondary);

            strong {
                color: var(--color-text);
            }
        }
    }

    .withdraw-methods {
        display: flex;
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
            background: var(--color-secondary-light);
            border: 2px solid var(--color-secondary);
            transition: all 0.2s ease;

            &_active {
                border-color: var(--color-primary);
            }

            &__img {
                width: 50%;
                height: 120px;
                object-position: center;
                object-fit: contain;
            }

            &__title {
                margin-top: auto;
                font-size: 14px;
                font-weight: 700;
                color: var(--color-text);
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
        color: var(--color-text);
        text-align: center;
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

        .withdraw-methods {
            &__items {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
        }

        &__warning {
            width: 100%;
        }

        .banks {
            &__items {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    }
    .form {
        &__input {
            padding-left: 8px;
        }
    }
}
@media (max-width: 500px) {
    .page-withdraw {
        .withdraw-methods {
            .withdraw-method {
                aspect-ratio: initial;
                &__img {
                    width: 100%;
                }
            }
        }
    }
}
</style>
