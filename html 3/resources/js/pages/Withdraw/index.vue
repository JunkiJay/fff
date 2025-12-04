<template>
    <div class="page-withdraw">
        <img src="/assets/withdraw/withdraw.jpg" class="page-withdraw__banners-desc aspect-[11/1]" alt="" />
        <img src="/assets/withdraw/withdraw2.png" class="page-withdraw__banners-mobile aspect-[6/1]" alt="" />
        <div class="flex mb-8  rounded-[24px] lg:bg-[var(--color-content)]" v-if="providers.length > 0">
            <div class="w-full lg:w-[100%] flex flex-col mx-auto">
                <div class="page-withdraw__parent ">
                    <div class="w-[100%] p-4">
                        <div class="page-withdraw__withdraw-methods withdraw-methods ">
                            <p class="withdraw-methods__desc mb-4">Выберите метод</p>
                            <div class="withdraw-methods__items">
                                <button v-for="withdrawMethod in withdrawMethods()" :key="withdrawMethod.provider"
                                        class="withdraw-method relative"
                                        :class="[provider === withdrawMethod.provider && method === withdrawMethod.method ? 'withdraw-method_active' : '']"
                                        @click="provider = withdrawMethod.provider; method = withdrawMethod.method; method = withdrawMethod.method;"
                                >
                                    <img v-if="withdrawMethod.hot" class="absolute top-[6px] right-[6px] w-5 h-5"
                                         src="/assets/withdraw/fire.svg" alt="" />
                                    <img :src="withdrawMethod.icon" class="withdraw-method__img" />
                                    <div class="withdraw-method__title">{{ withdrawMethod.title }}</div>
                                    <div class="withdraw-method__desc">Мин. {{ formatCurrency(withdrawMethod.min) }}
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-4   md:border-l border-l-0 border-[#3E3B47CC] w-[100%]">
                        <div class="withdraw-methods">
                            <h2 class="page-withdraw__title mb-6">Вывод баланса</h2>
                            <div class="page-withdraw__form form">
                                <div class="form__group">
                                    <label class="form__label">{{ systemGet?.wallet_input_title }}</label>

                                    <div class="form__content">
                                        <input class="form__input" type="text"
                                               :placeholder="systemGet?.wallet_input_placeholder"
                                               v-model="wallet" />
                                    </div>
                                </div>

                                <div class="form__group" v-if="systemGet?.variants">
                                    <label class="form__label">Выберите банк</label>

                                    <div class="form__content">
                                        <select class="form__input" v-model="variant">
                                            <option v-for="variant in systemGet?.variants" :key="variant.name"
                                                    :value="variant.name">
                                                {{ variant.title }}
                                            </option>
                                        </select>
                                    </div>
                                </div>


                                <div class="form__group">
                                    <label class="form__label">Сумма</label>

                                    <div class="form__content">
                                        <input class="form__input" type="number" placeholder="Сумма" v-model="amount" />
                                    </div>
                                </div>
                            </div>
                            <div class="page-withdraw__comission mb-3 flex flex-col">
                                <span>
                                    К зачислению: <span
                                    class="">{{ formatCurrency((amount * ((100 - systemGet?.commission_percents) / 100)))
                                    }}</span>
                                </span>

                                <span>
                                    Комиссия: <span class="">{{ parseFloat(systemGet?.commission_percents) }}%</span>
                                </span>
                            </div>

                            <Button class="page-withdraw__button w-full" @click.native="create"
                                    :isDisabled="isSubmitted">Создать выплату
                            </Button>

                            <p class="page-withdraw__warning">
                                Вывод может занимать до 24 часов! В редких ситуациях возможны задержки банком до 72
                                часов.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-withdraw__history p-4 rounded-[24px] lg:bg-[var(--color-content)]" v-if="userWithdraws">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Система</th>
                    <th>Сумма</th>
                    <th>Кошелек</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="withdraw in userWithdraws" :key="withdraw.id">
                    <td>{{ withdraw.id }}</td>
                    <td class="system_wallet" style="height: 50px !important; width: 80px">
                        <img style="height: 50px; margin: auto;" :src="withdraw.image" :alt="withdraw.system" />
                    </td>
                    <td>{{ parseFloat(withdraw.sumWithCom).toFixed(2) }}</td>
                    <td class="">{{ maskLast4Digits(withdraw.wallet) }}</td>

                    <td class="expect text-yellow-500 cursor-pointer" v-if="withdraw.status === 0">
                        <span @click="returnWithdraw(withdraw.id)">Отменить</span>
                    </td>
                    <td class="expect text-green-500" v-if="withdraw.status === 1">{{ withdraw.status_human_name }}</td>
                    <td class="expect text-red-500" v-if="withdraw.status === 2">
                        {{ withdraw.reason || withdraw.status_human_name }}
                    </td>
                    <td class="expect text-blue-500" v-if="withdraw.status === 3">{{ withdraw.status_human_name }}</td>

                    <td>{{ $moment(withdraw.created_at).format("lll") }}</td>
                </tr>
                <td colspan="6" class="pb-3 pt-6 text-[var(--color-text-secondary)]" v-if="userWithdraws.length === 0">
                    История пуста
                </td>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import Button from "@/components/ui/Button.vue";
import axios from "axios";
import { mapActions, mapGetters, mapState } from "vuex";
import { ref } from "vue";

export default {
    components: {
        Button
    },

    data() {
        const isSubmitted = ref(false);

        return {
            isSubmitted,
            withdraws: [],
            showPurse: false,
            amount: null,
            wallet: null,
            unq: [],
            provider: "fk",
            method: "fk",
            variant: "sberbank"
        };
    },
    computed: {
        ...mapState(['user']),
        ...mapState('payments', ['providers', 'userWithdraws']),
        systemGet: function() {
            return this.withdrawMethods().find((item) => item.provider === this.provider && item.method === this.method);
        }
    },
    methods: {
        ...mapGetters("payments", {
            withdrawMethods: "availableWithdrawMethods",
        }),
        ...mapActions('payments', ['fetchPaymentMethods', 'fetchUserWithdraws']),
        maskLast4Digits(value) {
            value = String(value ?? '');
            let digitsToMask = 4;
            let out = '';
            for (let i = value.length - 1; i >= 0; i--) {
                const ch = value[i];
                if (digitsToMask > 0 && /\d/.test(ch)) {
                    out = '*' + out;
                    digitsToMask--;
                } else {
                    out = ch + out;
                }
            }
            return out;
        },
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
            if (!this.amount || !this.wallet) {
                return this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Заполните все поля",
                    type: "error"
                });
            }

            const data = {
                sum: this.amount,
                wallet: this.wallet,
                provider: this.provider,
                method: this.method
            };

            if (this.method === "sbp" && this.variant) {
                data.variant = this.variant;
            }

            this.isSubmitted = true;

            axios.post("/withdraw/create", data)
                .then((response) => {
                    const { data } = response;

                    if (data.error) {
                        return this.$emitter.emit("noty", {
                            title: "Ошибка",
                            text: data.message,
                            type: "error"
                        });
                    }

                    this.withdraws.unshift(data.withdraw);
                    this.user.balance = data.balance;

                    this.$emitter.emit("noty", {
                        title: "Успешно",
                        text: "Заявка на вывод создана",
                        type: "success"
                    });
                    this.fetchUserWithdraws()
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
                .finally(() => this.isSubmitted = false);
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
                    this.user.balance = data.balance;

                    this.fetchUserWithdraws()
                });
        },
    },
    created() {
        this.fetchPaymentMethods({userId: this.user.id})
        this.fetchUserWithdraws()
    },
};
</script>

<style lang="scss" scoped>
.page-withdraw {
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

// @media (max-width: 500px) {
//     .page-withdraw {
//         &__parent {
//             background: none;
//             border: 0px solid var(--color-secondary);
//             padding: 0px;
//             border-radius: 9px;
//             display: flex;
//             flex-direction: column;
//             padding: 0px
//         }

//         &__withdraw-methods {
//             margin-bottom: 0px;
//         }

//         .withdraw-methods {
//             .withdraw-method {
//                 aspect-ratio: initial;

//                 &__img {
//                     width: 80%;
//                 }
//             }

//             &__items {
//                 display: grid;
//                 grid-template-columns: repeat(3, 1fr);
//                 gap: 8px;
//                 margin-bottom: 16px;
//             }

//             background: #15101D;
//             border: 2px solid var(--color-secondary);
//             padding: 16px;
//             border-radius: 9px;
//         }
//     }
// }
@media (max-width: 700px) {
    .page-withdraw {
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
