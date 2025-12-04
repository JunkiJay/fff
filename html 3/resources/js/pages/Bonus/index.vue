<template>
    <div class="page-bonus">
        <div class="gap-6 flex flex-col w-full text-white">
            <div class="w-full page-bonus__invitation">
                <div
                    class="page-bonus__invitation-box bg-[#1F1B29]/25 w-full rounded-[32px] p-8 border !border-white/15 relative overflow-hidden">
                    <div class="space-y-5 md:w-1/2 page-bonus__invitation-left">
                        <h2 v-if="!vip" class="page-bonus__invitation-left-title">Стань участником
                            <span>VIP-Клуба!</span>
                        </h2>
                        <h2 v-if="vip" class="page-bonus__invitation-left-title text-center md:text-left">Поздравляем! Вы стали <br>
                            участником закрытого
                            <span class="text-[#8D24D5]">VIP-Клуба!</span>
                        </h2>
                        <p v-if="!vip" class="page-bonus__invitation-left-subtitle">
                            Пополни счет на сумму в <span class="text-[#8D24D5]">10 000 Рублей</span> и получи
                            возможность
                            присоединиться к закрытому VIP-Клубу STIMULE
                        </p>

                        <p v-if="vip" class="md:page-bonus__invitation-left-subtitle text-center md:text-left">
                            Отправьте команду /start в телеграмм бота и получите
                            доступ в закрытый VIP-Канал.
                        </p>

                        <div
                            v-if="!vip"
                            class="rounded-[8px] md:rounded-[16px] p-4 w-fit relative border !border-white/15 page-bonus__invitation-left-deposit">
                            <div
                                class="text-xs absolute -top-2 left-0 right-0 mx-auto px-3 w-fit bg-zinc-900 rounded-full page-bonus__invitation-left-deposit-title">
                                Ваш депозит
                            </div>
                            <div class="page-bonus__invitation-left-deposit-value text-center md:w-[490px] w-[320px]">
                                <div>
                                    <span class="text-[#8D24D5]">{{ sum ?? 0 }}</span> / 10 000
                                </div>
                            </div>
                        </div>

                        <div v-if="!vip" class="page-bonus__invitation-left-advantages">
                            <h3 class="page-bonus__invitation-left-advantages-title">Преимущества VIP-Клуба</h3>
                            <ol class="p-4 rounded-[32px] page-bonus__invitation-left-advantages-items">
                                <li class="page-bonus__invitation-left-advantages-item">
                                    <h4>1. Приватный VIP Канал</h4>
                                    <p>В нём публикуются крупные промокоды/акции</p>
                                </li>
                                <li class="page-bonus__invitation-left-advantages-item">
                                    <h4>2. Персональный менеджер</h4>
                                    <p>Подберёт именно под вас индивидуальный бонус</p>
                                </li>
                                <li class="page-bonus__invitation-left-advantages-item">
                                    <h4>3. Повышенные бонусы</h4>
                                    <p>Став VIP Игроком, вы получаете приоритетное<br> обслуживание и повышенные бонусы
                                    </p>
                                </li>
                            </ol>
                        </div>
                        <div v-if="vip" class="flex space-x-6 gap-4">
                            <Button variant="gradient"
                                class="rounded-[16px] text-white bg-[#8D24D5] px-6 md:h-[49px] md:w-[auto] h-[49px] w-[177px] font-[Inter] font-bold md:text-[20px] text-[13px] page-bonus__bonuses-item-btn"
                                :isDisabled="false"
                                @click.native="openTelegramBot">
                                Перейти к боту
                            </Button>
                            <Button
                                class="rounded-[16px] text-white px-4 bg-white/2 border border-white md:h-[49px] md:w-[auto] h-[49px] w-[177px] font-[Inter] font-bold md:text-[20px] text-[13px] page-bonus__bonuses-item-btn"
                                :isDisabled="false"
                                @click.native="goToFaq">
                                Возникла проблема?
                            </Button>
                        </div>
                    </div>
                    <div class="top-0 right-0 z-0 h-full page-bonus__invitation-right">
                        <img :src="vipClubImg"
                            class="page-bonus__invitation-right-pair --desktop h-full pt-2" />
                        <img :src="vipClubImg"
                            class="page-bonus__invitation-right-pair --mobile h-full pt-2" />
                    </div>
                </div>
            </div>
            <ul class="grid grid-cols-3 gap-3 m-3 md:m-0 p-0 place-items-center page-bonus__bonuses">
                <li class="page-bonus__bonuses-item">
                    <div
                        class="bg-[#1F1B29]/25 flex flex-col justify-between h-full rounded-[32px] border !border-white/15 p-8 page-bonus__bonuses-item">
                        <div class="page-bonus__bonuses-item-image">
                            <img  :src="bonus1Img" class="h-full pt-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <h3 class="page-bonus__bonuses-item-title">Первый бонус</h3>
                            <p class="page-bonus__bonuses-item-subtitle">Выполните необходимые условия, чтобы забрать
                                свой первый бонус!</p>
                            <ul class="my-2 space-y-1 p-0">
                                <li class="flex items-center gap-2 text-white" v-show="hasMail" style="text-decoration: none;">
                                    <IconVk />
                                    <a href="https://vk.com/club225380369">Подписка на <span
                                            class="text-sky-400 underline">группу ВКонтакте</span></a>
                                </li>
                                <li class="flex items-center gap-2 text-white" style="text-decoration: none; margin-top: 0.4rem !important">
                                    <IconTg />
                                    <a href="https://t.me/stimule_tg">Подпишитесь на наш <span
                                            class="text-sky-400 underline">TG Канал</span></a>
                                </li>
                            </ul>
                            <Button variant="gradient"
                                class="rounded-[16px] w-full text-white px-4 bg-[#8D24D5] h-[49px] mt-auto font-[Inter] font-bold text-[14px] page-bonus__bonuses-item-btn"
                                :isDisabled="false"
                                @click.native="fetchBonus('one')" :disabled="!bonus.one.active">
                                {{ bonus.one.active ? "Получить бонус " : "Вы уже получили" }}
                            </Button>
                        </div>
                    </div>
                </li>
                <li class="page-bonus__bonuses-item">
                    <div
                        class="bg-[#1F1B29]/25 flex flex-col justify-between h-full border !border-white/15 rounded-[32px] p-8">
                        <div class="page-bonus__bonuses-item-image">
                            <img :src="bonus2Img" class="h-full pt-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <h3 class="page-bonus__bonuses-item-title">Cashback 10%</h3>
                            <p class="page-bonus__bonuses-item-subtitle">Зарабатывайте кешбэк и компенсируйте свои
                                ставки!</p>

                            <div
                                class="rounded-[16px] border text-center p-3 w-full my-3 relative font-bold">
                                <div
                                    class="text-xs absolute -top-2 left-0 right-0 mx-auto px-3 w-fit bg-zinc-900 rounded-full">
                                    Доступно к сбору
                                </div>{{ cashback_sum.toFixed(2) ?? 0 }} руб
                            </div>
                            <Button variant="gradqient"
                                class="rounded-[16px] w-full text-white px-4 bg-[#8D24D5] h-[49px] mt-auto font-[Inter] font-bold text-[14px] page-bonus__bonuses-item-btn"
                                :isDisabled="false"
                                @click.native="getCashback">
                                Забрать кэшбек
                            </Button>
                        </div>
                    </div>
                </li>
                <li class="page-bonus__bonuses-item">
                    <div
                        class="bg-[#1F1B29]/25 flex flex-col justify-between h-full border !border-white/15 rounded-[32px] p-8">
                        <div class="page-bonus__bonuses-item-image">
                            <img :src="bonus4Img" class="h-full pt-2" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <h3 class="page-bonus__bonuses-item-title">Промокод</h3>
                            <p class="page-bonus__bonuses-item-subtitle">Активируй пригласительный промокод и забирай
                                свой бонус!</p>

                            <input v-model="promoCode"
                                class="rounded-[16px] border text-center border-white/25 p-3 w-full my-3 relative bg-transparent"
                                placeholder="Введите промокод" />

                            <Button variant="gradient"
                                class="rounded-[16px] w-full text-white px-4 bg-[#8D24D5] h-[49px] mt-auto font-[Inter] font-bold text-[14px] page-bonus__bonuses-item-btn"
                                :isDisabled="false"
                                @click.native="activatePromoCode">
                                Активировать
                            </Button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import BonusList from "@/pages/Bonus/BonusList.vue";
import Repost from "@/pages/Bonus/Repost.vue";
import Levels from "@/pages/Bonus/Levels.vue";
import { parseTime } from "@/utils/parseTime.js";
import IconVk from "@/components/icons/IconVk.vue";
import IconTg from "@/components/icons/IconTg.vue";
import { mapGetters } from "vuex";
import Button from "../../components/ui/Button.vue";
import vipClubImg from "@/pages/Bonus/assets/VIP-CLUB-1.png";
import bonus1Img from "@/pages/Bonus/assets/Bonus_1_1.png";
import bonus2Img from "@/pages/Bonus/assets/Bonus_1_2.png";
import bonus4Img from "@/pages/Bonus/assets/Bonus_1_4.png";
import ref1BannerImg from "@img/banners/ref1.png";
import ref2BannerImg from "@img/banners/ref2.png";

export default {
    components: {
        Button,
        BonusList,
        Repost,
        Levels,
        IconVk,
        IconTg,
    },
    data() {
        return {
            vipClubImg,
            bonus1Img,
            bonus2Img,
            bonus4Img,
            isLoading: true,
            hasMail: true,
            repostInfo: null,
            onetime: 0,
            sum: 0,
            vip: null,
            tg_link: null,
            cashback_sum: 0,
            interval: {},
            banners: [
                {
                    img: ref1BannerImg,
                    link: "/ref",
                },
                {
                    img: ref2BannerImg,
                    link: "/ref",
                },
            ],
            bonus: {
                one: {
                    active: null,
                },
                daily: {
                    finishAt: null,
                    finishView: null,
                    active: null,
                },
                hourly: {
                    finishAt: null,
                    finishView: null,
                    active: null,
                },
            },
            promoCode: "",
        };
    },
    methods: {
        startTimer(end, type) {
            const now = Math.round(new Date().getTime() / 1000);
            let seconds = end - now;
            this.bonus[type].finishView = parseTime(seconds);

            this.interval[type] = setInterval(() => {
                if (seconds <= 1) {
                    clearInterval(this.interval[type]);
                    this.bonus[type].active = true;
                    return;
                }
                seconds--;
                this.bonus[type].finishView = parseTime(seconds);
            }, 1000);
        },

        async openTelegramBot() {
            this.isLoading = true;
            try {
                const response = await axios.post('/api/telegram/binding/generate');
                const code = response.data.code;
                if (!code) throw new Error('Нет кода для привязки');
                const link = `https://t.me/stimule_bot?start=${code}`;
                window.open(link, '_blank');
            } catch (e) {
                this.$emitter.emit('noty', {
                    title: 'Ошибка',
                    text: 'Ошибка генерации кода для Telegram! Попробуйте еще раз.',
                    type: 'error',
                });
            } finally {
                this.isLoading = false;
            }
        },

        goToFaq() {
            this.$router.push('/faq');
        },

        init() {
            // Получаем депозит и VIP-инфу
            axios.post("/deposit/init").then((response) => {
                const { data } = response;

                console.log(data)

                if (data.error) {
                    return this.$emitter.emit("noty", {
                        title: "Ошибка",
                        text: data.message,
                        type: "error",
                    });
                }

                this.onetime = data.onetime;
                this.sum = data.sum;
                this.vip = data.vip;
                this.tg_link = data.tg_link;
                this.repostInfo = data.repostInfo;
                // Остальные данные из /deposit/init оставляем как есть
            });

            // Получаем только бонусы
            axios.post("/bonus/init").then((response) => {
                const { data } = response;

                if (data.error) {
                    return this.$emitter.emit("noty", {
                        title: "Ошибка",
                        text: data.message,
                        type: "error",
                    });
                }

                // Корректно инициализируем только бонусы
                // Если бонус НЕ получен (0) — кнопка активна
                this.bonus.one.active = data.bonuses.one === 0;
                this.bonus.daily.active = data.bonuses.daily === 0;
                this.bonus.hourly.active = data.bonuses.hourly === 0;
                // Если есть таймеры — тоже обработай
                // ...
                this.isLoading = false;
            });

            // Остальные вызовы (например, cashback) оставляем как есть
            axios.post("/cashback/init").then((response) => {
                const { data } = response;

                if (data.error) {
                    return this.$emitter.emit("noty", {
                        title: "Ошибка",
                        text: data.message,
                        type: "error",
                    });
                }

                this.cashback_sum = data;
            });
        },

        async activatePromoCode() {
            if (this.promoCode.trim() === "") {
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Пожалуйста, введите промокод.",
                    type: "error",
                });
                return;
            }
            try {
                const response = await axios.post(`/promo/activate`, { code: this.promoCode });
                const { data } = response;

                if (data.type == 'fs') {
                    this.$emitter.emit('noty', {
                        title: 'Успешно',
                        text: data.text,
                        type: 'success'
                    });
                    this.$router.push(`/slots/game/${data.slot_id}?type=fs`);
                    return;
                }

                if (data.error) {
                    this.$emitter.emit("noty", {
                        title: "Ошибка",
                        text: data.message,
                        type: "error",
                    });
                } else {
                    this.$emitter.emit("noty", {
                        title: "Успех",
                        text: "Промокод успешно активирован!",
                        type: "success",
                    });
                    this.promoCode = "";
                }
            } catch (error) {
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Произошла ошибка при активации промокода.",
                    type: "error",
                });
            }
        },

        /**
         * Получить бонус (daily, hourly, one)
         * @param {string} type - Тип бонуса: 'daily', 'hourly', 'one'
         */
        async fetchBonus(type) {
            try {
                const response = await axios.post('/bonus/take', { type });
                const { data } = response;
                console.log(data)
                if (data.error) {
                    if(data.showModal) this.$emitter.emit('connectTg');
                    this.$emitter.emit("noty", {
                        title: "Ошибка",
                        text: data.message,
                        type: "error",
                    });
                } else {
                    this.$emitter.emit("noty", {
                        title: "Успех",
                        text: data.text || "Бонус успешно получен!",
                        type: "success",
                    });
                    // Обновить баланс пользователя, если пришёл в ответе и пользователь существует
                    if (typeof data.balance !== 'undefined' && this.$store.state.user) {
                        this.user = data.balance
                    }
                }
            } catch (error) {
                console.error('Ошибка при получении бонуса:', error);
                const errorMessage = error.response?.data?.message || error.message || 'Неизвестная ошибка';
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: `Произошла ошибка при получении бонуса`,
                    type: "error",
                });
            }
        },

        async getCashback() {
            try {
                const response = await axios.post(`/cashback/getCashback`);
                const { data } = response;
                if (data.error) {
                    this.$emitter.emit("noty", {
                        title: data.error ?? "Ошибка",
                        text: data.message,
                        type: "error",
                    });
                } else {
                    this.$emitter.emit("noty", {
                        title: "Успех",
                        text: "Кэшбек успешно забран!",
                        type: "success",
                    });
                }
            } catch (error) {
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Произошла ошибка при получении кэшбека.",
                    type: "error",
                });
            }
        },
    },

    async mounted() {
        await this.init();
        this.$emitter.on("bonusStartTimer", (data) => {
            this.startTimer(data.remaining, data.type);
        });

        if (this.$store.state.user && this.$store.state.user.vk_id === null) {
            this.hasMail = false;
        }

    },

    beforeDestroy() {
        for (let key in this.interval) {
            clearInterval(this.interval[key]);
        }
    },
};
</script>

<style lang="scss" scoped>
.page-bonus {
    &__content {
        display: flex;
        flex-direction: column;
        width: 100%;
        gap: 24px;
    }

    &__invitation {

        &-box {
            border-color: hsla(0, 0%, 100%, .25);
        }

        &-left {
            width: 60%;
            position: relative;
            z-index: 2;

            &-title {
                font-size: 32px;
                font-weight: 700;
            }

            &-subtitle {
                font-size: 18px;
                font-weight: 400;
            }

            &-deposit {
                margin-top: 2rem !important;

                &-title {
                    font-weight: 600;
                    font-size: 16px;
                }

                &-value {
                    font-size: 36px;
                    font-weight: bold;
                }
            }

            &-advantages {
                padding-top: 2rem;

                &-title {
                    font-weight: 700;
                    font-size: 32px;
                }

                &-items {
                    margin-top: 1rem;
                }

                &-item {
                    margin-top: 1rem;

                    &>h4 {
                        font-size: 18px;
                        font-weight: 600;
                    }

                    &>p {
                        font-size: 16px;
                        font-weight: 400;
                        opacity: .6;
                    }
                }
            }
        }

        &-right {
            position: absolute;

            &-pair {
                &.--desktop {
                    display: block;
                    float: right;
                }

                &.--mobile {
                    display: none;
                }
            }
        }
    }

    &__bonuses {
        &-item {
            height: 100%;

            &-btn {
                margin-top: 0.5rem;
                // background: linear-gradient(90deg, #7b1fb8, #49136F);

                &:hover {
                    background: #8d24d5;
                }
            }

            &-title {
                font-weight: 700;
                font-size: 32px;
            }

            &-subtitle {
                padding-bottom: 1rem;
                font-size: 17px;
                font-weight: 400;
                opacity: .6;
            }

            &-image {
                display: flex;
                justify-content: center;
                margin-bottom: 1rem;
            }
        }
    }
}

h3 {
    font-size: 24px;
    font-weight: 600;
}


@media (max-width: 1024px) {
    .page-bonus {
        //padding-top: 4rem;
        padding-top: 0;

        &__invitation {
            &-box {
                border: none;
                background: #09040F;
                padding-top: 0;
            }

            &-left {
                width: 100%;
                display: flex;
                align-items: center;
                flex-direction: column;

                &-title {
                    font-size: 24px;

                    span {
                        white-space: nowrap;
                    }
                }

                &-subtitle {
                    text-align: center;
                    font-size: 16px;
                }

                &-deposit {
                    width: 100%;
                    align-items: center;
                    display: flex;
                    justify-content: center;
                }

                &-advantages {
                    &-title {
                        font-size: 24px;
                    }

                    &-items {
                        margin-top: .2rem;
                    }

                    &-item {
                        margin-top: 1rem;

                        &>h4 {
                            font-size: 16px;
                            font-weight: 600;
                        }

                        &>p {
                            font-size: 14px;
                            font-weight: 400;
                            opacity: .6;
                        }
                    }
                }

                &-deposit {
                    &-value {
                        font-size: 24px;
                    }
                }
            }

            &-right {
                position: relative;
                display: flex;
                justify-content: center;

                &-pair {
                    &.--desktop {
                        display: none;
                    }

                    &.--mobile {
                        display: block;
                    }
                }
            }
        }

        &__bonuses {
            grid-template-columns: 1fr;

            &-item {
                height: unset;
                width: 100%;
            }
        }
    }
}

@media (max-width: 568px) {
    .page-bonus {
        //padding-top: 8rem;
        padding-top: 0;
    }
}
</style>
