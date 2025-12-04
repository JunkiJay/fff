<template>
    <div>
        <div v-if="loading" class="loader">
            <p>Loading...</p>
        </div>

        <div v-else>
            <div class="page-ref m-2">
                <RefBanner
                    class="page-ref__banner"
                    :link="link"
                    :refIncome="refIncome"
                    @copyRef="copyRef"
                    @take="take"
                />
                <RefInfo :data="refData" />
                <div class="page-ref__info-blocks">
                    <RefFriends class="page-ref__info-block" />
                    <RefBonus
                        class="page-ref__info-block"
                        :refIncome="refIncome"
                        :bonuses="bonusRewards"
                        @take="take"
                        @take-bonus="takeBonus"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import RefBanner from "./RefBanner.vue";
import RefInfo from "./RefInfo.vue";
import RefFriends from "./RefFriends.vue";
import RefBonus from "./RefBonus.vue";
import axios from "axios";

export default {
    components: {
        RefBanner,
        RefInfo,
        RefFriends,
        RefBonus,
    },

    data() {
        return {
            loading: true,
            refIncome: "0.00",
            refReward: "0.00",
            link: null,
            refData: [],
            bonusRewards: [],
            error: false,
        };
    },

    methods: {
        async init() {
            this.loading = true;
            this.error = false;
            try {
                const response = await axios.post(`/referral/get`);
                const { data, ref_income, ref_reward, link, bonus_rewards } = response.data;

                this.refIncome = parseFloat(ref_income).toFixed(2);
                this.refReward = parseFloat(ref_reward).toFixed(2);
                this.link = link;
                this.refData = this.formatRefData(data);
                this.bonusRewards = bonus_rewards || [];
            } catch (error) {
                console.error("Ошибка загрузки данных:", error);
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Не удалось загрузить данные",
                    type: "error",
                });
                this.error = true;
                this.refData = [];
            } finally {
                this.loading = false;
            }
        },

        formatRefData(data) {
            if (!data.lvl_1 || !data.lvl_2 || !data.lvl_3) {
                return [];
            }
            return [
                {
                    title: "Уровень",
                    class: "ref-info__col_passive",
                    data: ["1 уровень", "2 уровень", "3 уровень"]
                },
                {
                    title: "Рефералы",
                    data: [data.lvl_1.count, data.lvl_2.count, data.lvl_3.count]
                },
                {
                    title: "Доход",
                    class: "ref-info__col_active",
                    data: [
                        `${data.lvl_1.income} ₽`,
                        `${data.lvl_2.income} ₽`,
                        `${data.lvl_3.income} ₽`
                    ]
                },
                {
                    title: "Процент",
                    data: ["10%", "3%", "2%"]
                }
            ];
        },

        async take() {
            try {
                const response = await axios.post(`/referral/take`);
                const { error, message, balance } = response.data;

                if (error) {
                    this.$emitter.emit("noty", {
                        title: "Ошибка",
                        text: message,
                        type: "error",
                    });
                    return;
                }

                this.refIncome = "0.00";

                this.$store.commit("setUser", {
                    ...this.$store.state.user,
                    balance: parseFloat(balance),
                });

                this.$emitter.emit("noty", {
                    title: "Успешно",
                    text: "Награда успешно забрана!",
                    type: "success",
                });

                await this.init(); // обновить данные после сбора
            } catch (error) {
                console.error("Ошибка выполнения операции 'Забрать':", error);
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Не удалось выполнить операцию",
                    type: "error",
                });
            }
        },

        async takeBonus() {
            try {
                this.loading = true;
                const response = await axios.post(`/referral/bonus/take`);
                if (response.data.success) {
                    this.$emitter.emit("noty", {
                        title: "Успешно",
                        text: response.data.message || "Бонусы успешно зачислены!",
                        type: "success",
                    });
                    await this.init(); // обновить данные после сбора бонуса
                } else {
                    this.$emitter.emit("noty", {
                        title: "Нет бонусов",
                        text: response.data.message || "Нет доступных бонусов для сбора",
                        type: "info",
                    });
                }
            } catch (error) {
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Ошибка при попытке забрать бонусы",
                    type: "error",
                });
            } finally {
                this.loading = false;
            }
        },

        async copyRef() {
            const text = this.link ?? '';
            if (!text) {
                this.$emitter?.emit('noty', { title: 'Внимание', text: 'Пустая ссылка', type: 'warning' });
                return;
            }

            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(text);
                } else {
                    // Фолбэк для старых браузеров
                    const ta = document.createElement('textarea');
                    ta.value = String(text);
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }

                this.$emitter?.emit('noty', { title: 'Успех', text: 'Ссылка скопирована', type: 'success' });
            } catch (e) {
                this.$emitter?.emit('noty', { title: 'Ошибка', text: 'Не удалось скопировать', type: 'error' });
            }
        },
    },

    mounted() {
        this.init();
    },
};
</script>

<style lang="scss" scoped>
.page-ref {
    display: flex;
    flex-direction: column;
    gap: 24px;
    .page-ref__info-blocks {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        .page-ref__info-block {
            flex: 1;
            height: 100%;
        }
    }
}

@media (max-width: 1024px) {
    .page-ref {
        gap: 16px;
        .page-ref__info-blocks {
            grid-template-columns: 1fr;
            grid-template-rows: repeat(2, auto);
            gap: 16px;
            padding: 0 15px;
        }
    }
}
</style>
