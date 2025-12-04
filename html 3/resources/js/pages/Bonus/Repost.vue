<template>
    <div class="repost flex gap-6">
        <div class="repost__cash flex items-center flex-col lg:p-4 rounded-[24px] bg-[#1F1B29] w-[285px]">
            <div class="font-[Oswald] text-[24px] text-white mb-4">Кэшбек 10%</div>

            <h5 class="text-[42px] font-bold font-[Inter] text-white flex items-center gap-2">
                <IOdometer :value="Number(bonusBalance ?? 0)" />
                <span>₽</span>
            </h5>

            <p class="repost__cash__sbor font-[Oswald] text-[16px] text-[#ffffff60]">Доступно к сбору</p>

            <Button class="w-full mt-auto" :isDisabled="false">Забрать</Button>
        </div>
        <div class="repost__share">
            <div class="repost__balans flex items-center flex-col lg:p-4 rounded-[24px] bg-[#1F1B29] w-[285px]">
                <div class="font-[Oswald] text-[24px] text-white mb-4">Ваш бонусный баланс</div>

                <h5 class="text-[42px] font-bold font-[Inter] text-white flex items-center gap-2">
                    <IOdometer :value="Number(bonusBalance ?? 0)" />
                    <span>₽</span>
                </h5>

                <div class="repost__balans__button-group flex flex-col gap-2 mt-auto">
                    <Button @click.native="transfer" :isDisabled="false">Перевести на реальный счет</Button>
                    <Button @click.native="checkReposts" variant="secondary" :isDisabled="isLoading.check">
                        <span v-if="!isLoading.check">Проверить посты</span>
                    </Button>
                </div>
            </div>

            <div class="repost__bonus flex flex-col lg:p-4 rounded-[24px] bg-[#1F1B29] grow">
                <h5 class="text-[24px] text-white font-[Oswald]">Бонус за репост</h5>

                <hr class="my-4 border-[var(--color-secondary)]" />

                <div class="flex flex-col gap-0.5">
                    <div
                        v-for="(likeItem, index) in likeItems"
                        :key="index"
                        class="repost__bonus__list flex items-center gap-2 text-[16px]"
                    >
                        <IconCheck />
                        <span class="text-white" v-html="likeItem.title"></span>
                    </div>
                </div>

                <Button class="mt-6" :isDisabled="false">Заработать бонусы!</Button>
            </div>
        </div>
    </div>
</template>

<script>
import IOdometer from "vue-odometer";
import Button from "@/components/ui/Button.vue";
import IconCheck from "@/components/icons/IconCheck.vue";

export default {
    props: ["bonusBalance"],

    components: {
        IOdometer,
        Button,
        IconCheck,
    },

    data() {
        return {
            isLoading: {
                check: false,
                transfer: false,
            },
            likeItems: [
                {
                    title: "Делай репосты постов в <b>ВК</b> и <b>зарабатывай</b> бонусы",
                    icon: "like",
                    isChecked: true,
                },
                {
                    title: "Чем больше <b>репостов</b>, тем больше <b>заработок<b/>",
                    icon: "like",
                    isChecked: true,
                },
                {
                    title: "Учитываются только <b>15 последних</b> постов",
                    icon: "like",
                    isChecked: true,
                },
                {
                    title: "Аккаунт должен быть <b>открыт</b>",
                    icon: "like",
                    isChecked: false,
                },
            ],
        };
    },

    methods: {
        async transfer() {
            this.isLoading.transfer = true;
            const { data } = await this.$root.axios.post("/bonus/transfer").finally(() => (this.isLoading.transfer = false));

            if (data.error) {
                return this.$emitter.emit("noty", {
                    title: "Ошибка",
                    type: "error",
                    text: data.message,
                });
            }

            this.$root.user.balance = data.balance;
            this.bonusBalance = 0;
        },

        async checkReposts() {
            this.isLoading.check = true;
            const { data } = await this.$root.axios.post("/bonus/checkReposts").finally(() => (this.isLoading.check = false));

            if (data.error) {
                return this.$emitter.emit("noty", {
                    title: "Ошибка",
                    type: "error",
                    text: data.message,
                });
            }

            this.bonusBalance = data.bonusBalance;
            this.total = data.total;
        },
    },
};
</script>

<style lang="scss" scoped>
.like {
    align-items: center;
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.balance-panel {
    display: flex;
    flex-direction: column;
    align-items: center;

    &__title {
        font-size: 24px;
        font-family: Oswald !important;
        color: var(--color-text);
    }

    &__balance {
        font-size: 24px;
        font-family: Oswald !important;
        color: var(--color-primary);
    }

    &__buttons {
        width: 100%;
        display: flex;
        align-items: center;
        flex-direction: column;
        gap: 8px;
    }

    &__button {
        width: 100%;
    }
}

.item {
    margin-top: 4px;

    ::v-deep a {
        color: var(--color-primary) !important;
    }
}

.list {
    background: var(--color-form);
    color: var(--color-text);
    padding: 24px;
    border-radius: 8px;

    &__title {
        font-family: Oswald !important;
    }
}

.bonus {
    color: #235ed7;
    font-weight: 600;
}

.bonus {
    margin-left: 2rem;
}

.repost {
    &__balans {
        max-width: 285px;
        width: 100%;
        padding: 16px;
        &__button-group {
            width: 100%;
            button {
                &:last-child {
                    color: white;
                    background-color: #3b314e;
                }
                padding: 0 10px;
            }
        }
    }
    &__share {
        display: contents;
        gap: 24px;
    }
    &__bonus {
        max-width: 476px;
    }
    &__balans {
        max-width: 285px;
    }
}
@media (max-width: 1024px) {
    .repost {
        flex-direction: column;
        gap: 16px;
        &__share {
            display: flex;
            flex-direction: column;
            background-color: var(--color-content);
            border-radius: 24px;
            padding: 16px;
        }
        &__cash {
            width: 100%;
            padding: 16px;
            &__sbor {
                margin-bottom: 45px;
            }
        }
        &__balans {
            width: 100%;
            max-width: 100%;
            &__button-group {
                width: 100%;
            }
            max-width: 100%;
            padding: 0;
        }
        &__bonus {
            max-width: 100%;
            padding: 16px;
            background-color: var(--color-secondary-light);
            border-radius: 8px;
            button {
                display: none;
            }
        }
    }
    .balance-panel {
        width: 100%;
    }

    .list {
        width: 100%;
    }

    .like {
        flex-direction: column;
        gap: 24px;
    }
}
</style>
