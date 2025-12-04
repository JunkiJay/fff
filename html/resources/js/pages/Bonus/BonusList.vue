<template>
    <div class="grid grid-cols-3 gap-6 bonus">
        <div
            class="bonus__everyday bg-[#121015] relative h-[300px] rounded-[24px] flex flex-col w-full overflow-hidden border border-[var(--color-form)]"
        >
            <div
                class="relative z-10 rounded-br-[24px] bg-[var(--color-primary)] w-fit h-[40px] px-6 text-[16px] font-[Inter] font-bold flex items-center text-white"
            >
                Ежедневный бонус!
            </div>

            <div class="realtive z-10 p-6 flex flex-col font-[Inter] gap-2 grow">
                <h2 class="text-[24px] font-bold text-white">До 50 000 РУБЛЕЙ!</h2>
                <p class="text-[18px] text-white">С КАЖДЫМ УСПЕШНЫМ ДЕПОЗИТОМ ПРОКАЧИВАЕТСЯ БОНУС</p>

                <Button variant="gradient" class="rounded-[8px] w-full text-white h-[49px] mt-auto font-[Inter] font-bold text-[14px]" :isDisabled="false">
                    Получить
                </Button>
            </div>

            <img class="absolute top-0 right-0 z-0" :src="cornerBonusImg" alt="corner_bonus" />
        </div>
        <div
            class="bonus__oneday w-full relative p-4 gap-2 flex-col flex bg-[#121015] h-[300px] rounded-[24px] overflow-hidden border border-[var(--color-form)]"
        >
            <h2 class="text-[20px] font-[Oswald] font-bold text-white">Одноразовый бонус</h2>
            <p class="text-[14px] text-white mb-6">Выполните условия</p>

            <div class="flex flex-col gap-2 text-white mb-auto">
                <p class="flex items-center gap-2">
                    <IconVk /><span> Подписка на <a class="text-[#3983DA] underline" :href="config.vk_url">группу ВКонтакте</a></span>
                </p>

                <p class="flex items-center gap-2">
                    <IconTg /> <span>Подписка на <a class="text-[#3983DA] underline" :href="config.tg_channel">TG Канал</a></span>
                </p>
            </div>

            <Button class="bonus-card__button" @click.native="takeBonus('one')" :disabled="!bonus.one.active" :isDisabled="false">
                {{ bonus.one.active ? "Получить бонус " : "Вы уже получили" }}
            </Button>

            <div
                class="absolute z-0 bg-[#5224D5] rounded-full w-[208px] h-[208px] top-0 right-0 translate-x-1/2 -translate-y-1/2 blur-2xl"
            ></div>
        </div>
        <div
            class="bonus__promo w-full relative p-4 gap-2 flex-col flex bg-[#121015] h-[300px] rounded-[24px] overflow-hidden border border-[var(--color-form)]"
        >
            <h2 class="text-[20px] font-[Oswald] font-bold text-white">Активировать промокод</h2>
            <p class="text-[14px] text-white mb-6">Выполните условия</p>

            <div class="flex flex-col gap-2 text-white mb-auto">
                <p class="flex items-center">
                    <IconVk />
                    <span>Подписка на <a class="text-[#3983DA] underline" :href="config.vk_url">группу ВКонтакте</a></span>
                </p>

                <p class="flex items-center">
                    <IconTg />
                    <span>Подписка на <a class="text-[#3983DA] underline" :href="config.tg_channel">TG Канал</a></span>
                </p>
            </div>

            <input class="form__input" type="text" placeholder="Введите промокод" v-model="promocode" />
            <Button class="bonus-card__button" @click.native="activate" :isDisabled="false">Активировать</Button>

            <div
                class="absolute z-0 bg-[#5224D5] rounded-full w-[208px] h-[208px] top-0 right-0 translate-x-1/2 -translate-y-1/2 blur-2xl"
            ></div>
        </div>
    </div>
</template>

<script>
import Button from "@/components/ui/Button.vue";
import IconVk from "@/components/icons/IconVk.vue";
import IconTg from "@/components/icons/IconTg.vue";
import cornerBonusImg from "@/pages/Bonus/assets/corner_bonus.png";
import { mapState } from "vuex";

export default {
    props: ["bonus", "onetime"],

    components: {
        Button,
        IconVk,
        IconTg,
    },

    data() {
        return {
            promocode: null,
            cornerBonusImg,
            create: {
                code: "",
                activation: "",
                sum: "",
            },
        };
    },
    
    computed: {
        ...mapState('config')
    },

    methods: {
        takeBonus(type) {
            this.$root.axios
                .post("/bonus/take", {
                    type,
                })
                .then((response) => {
                    const { data } = response;

                    if (data.showModal) this.$bvModal.show("connectTg");
                    if (data.error) {
                        return this.$emitter.emit("noty", {
                            title: "Ошибка",
                            type: "error",
                            text: data.message,
                        });
                    }

                    this.bonus[data.type].active = false;

                    if (data.type !== "one") {
                        this.$emitter.emit("bonusStartTimer", {
                            remaining: data.remaining,
                            type: data.type,
                        });
                    }

                    this.$root.user.balance = data.balance;
                    this.$emitter.emit("noty", {
                        title: "Успешно",
                        type: "success",
                        text: data.text,
                    });
                });
        },

        createPromo() {
            if (this.create.code == "" || this.create.activation == "" || this.create.sum == "") {
                return this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Заполните все поля",
                    type: "error",
                });
            }

            this.$root.axios
                .post("/promo/create", {
                    code: this.create.code,
                    activate: this.create.activation,
                    sum: this.create.sum,
                })
                .then((response) => {
                    const { data } = response;

                    if (data.error) {
                        return this.$emitter.emit("noty", {
                            title: "Ошибка",
                            text: data.message,
                            type: "error",
                        });
                    }

                    this.promocode = "";
                    this.$root.user.balance = data.balance;

                    this.$emitter.emit("noty", {
                        title: "Успешно",
                        text: data.text,
                        type: "success",
                    });
                });
        },

        activate() {
            if (!this.promocode) {
                return this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Заполните все поля",
                    type: "error",
                });
            }
            this.$root.axios
                .post("/promo/activate", {
                    code: this.promocode,
                })
                .then((response) => {
                    const { data } = response;

                    if (data.error) {
                        return this.$emitter.emit("noty", {
                            title: "Ошибка",
                            text: data.message,
                            type: "error",
                        });
                    }

                    this.promocode = "";
                    this.$root.user.balance = data.balance;

                    this.$emitter.emit("noty", {
                        title: "Успешно",
                        text: data.text,
                        type: "success",
                    });
                });
        },
    },
};
</script>
<style lang="scss" scoped>
.form__input {
    text-align: center;
    padding-left: 8px;
    padding-right: 8px;
    background-color: #3e3b47;
    color: white;
}
.bonus {
    &__promo {
        p {
            svg {
                margin-right: 8px;
            }
        }
    }
}
@media (max-width: 1024px) {
    .bonus {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}
</style>
