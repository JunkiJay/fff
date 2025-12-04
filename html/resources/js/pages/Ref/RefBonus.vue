<template>
    <div class="ref-bonus">
        <div class="ref-bonus__title">Получи дополнительный бонус!</div>
        <div class="ref-bonus__subtitle">
            Для получения бонусов, ваш реферал должен внести общую сумму пополнений, указанные в таблице ниже
        </div>
        <div class="ref-bonus__table">
            <div class="ref-bonus__table-header">
                <div class="ref-bonus__table-header-item">Вы получите</div>
                <div class="ref-bonus__table-header-item">Депозит друга</div>
            </div>
            <div class="ref-bonus__table-body">
                <div
                    v-for="(bonus, index) in bonuses"
                    :key="index"
                    :class="[
                        'ref-bonus__table-body-row',
                        {
                          'ref-bonus__table-body-row_highlight': index === 0 || index === bonuses.length - 1,
                          'ref-bonus__table-body-row_middle': index === 1 && bonuses.length === 3
                        }
                    ]"
                >
                    <div class="ref-bonus__table-body-item">{{ formatCurrency(bonus.reward) }}</div>
                    <div class="ref-bonus__table-body-item">{{ formatCurrency(bonus.deposit) }}</div>
                </div>
            </div>
        </div>

        <div class="ref-banner__actions">
            <div class="ref-banner__action">
                <label class="ref-banner__label">Доступно к сбору</label>
                <div class="ref-banner__input-container">
                    <input
                        type="text"
                        readonly
                        :value="formattedAvailableSum"
                        class="ref-banner__input"
                        aria-label="Доступно к сбору"
                    />
                    <button
                        class="ref-banner__button"
                        @click="take"
                        :disabled="loading || availableSum < 1">
                        {{ loading ? "Загрузка..." : "Забрать" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "RefBonus",
    props: {
        refIncome: {
            type: [String, Number],
            required: false,
            default: "0.00",
        },
        bonuses: {
            type: Array,
            required: true,
            default: () => [],
        },
    },
    data() {
        return {
            loading: false,
            error: false,
        };
    },
    computed: {
        availableSum() {
            // Сумма всех бонусов, которые доступны к сбору
            return this.bonuses
                .filter(b => b.active && !b.collected && b.available > 0)
                .reduce((sum, b) => sum + Number(b.available), 0);
        },
        formattedAvailableSum() {
            return this.availableSum > 0 ? `${this.availableSum} ₽` : '0 ₽';
        },
    },
    methods: {
        formatCurrency(value) {
            if (value === null || value === undefined) return '';
            return Number(value).toLocaleString('ru-RU') + ' ₽';
        },

        async take() {
            if (this.availableSum < 1) {
                this.$emitter.emit("noty", {
                    title: "Нет бонусов",
                    text: "Нет доступных бонусов для сбора",
                    type: "info",
                });
                return;
            }
            this.loading = true;
            this.$emit('take-bonus');
        },
    },
};
</script>

<style lang="scss" scoped>
.ref-bonus {
    position: relative;
    padding: 28px 40px;
    backdrop-filter: blur(34.3px);
    background: #1f1b2940;
    border: 1px solid #ffffff40;
    border-radius: 32px;
    .ref-bonus__title {
        margin-bottom: 16px;
        font-family: Inter, sans-serif;
        font-size: 24px;
        font-weight: 700;
        line-height: 29.05px;
        text-align: center;
        color: #fff;
    }
    .ref-bonus__subtitle {
        margin-bottom: 24px;
        font-family: Inter, sans-serif;
        font-size: 16px;
        font-weight: 400;
        line-height: 19.36px;
        text-align: center;
        color: rgba(255, 255, 255, 0.6);
    }
    .ref-bonus__table {
        display: flex;
        flex-direction: column;
        gap: 24px;
        .ref-bonus__table-header {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            padding: 12px 0;
            border-bottom: 1px solid #ffffff40;
            border-top: 1px solid #ffffff40;
            .ref-bonus__table-header-item {
                font-family: Inter, sans-serif;
                font-size: 20px;
                font-weight: 500;
                line-height: 24.2px;
                text-align: center;
                color: #fff;
            }
        }
        .ref-bonus__table-body {
            display: flex;
            flex-direction: column;
            .ref-bonus__table-body-row {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                height: 52px;
                &.ref-bonus__table-body-row_highlight {
                    background: rgba(255,255,255,0.05);
                    border-radius: 8px;
                }
                &.ref-bonus__table-body-row_middle {
                    background: transparent;
                    border-radius: 0;
                }

                .ref-bonus__table-body-item {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    font-family: Rubik, sans-serif;
                    font-size: 16px;
                    font-weight: 400;
                    line-height: 18.35px;
                }
            }
        }
    }
    .ref-banner__actions {
        display: flex;
        flex-direction: column;
        max-width: 528px;
        margin-top: 24px;
        gap: 16px;
        color: #fff;
        .ref-banner__action {
            display: flex;
            flex-direction: column;
            max-width: 100%;
            .ref-banner__label {
                font-family: Inter, sans-serif;
                font-size: 16px;
                font-weight: 500;
                line-height: 19.36px;
            }
            .ref-banner__input-container {
                display: flex;
                align-items: center;
                margin-top: 8px;
                .ref-banner__input {
                    width: 100%;
                    height: 52px;
                    padding: 0 16px;
                    border-radius: 12px 0 0 12px;
                    border: 1px solid #CED4DA40;
                    background: #2E2A3640;

                    font-family: Inter, sans-serif;
                    font-size: 16px;
                    font-weight: 400;
                    line-height: 19.36px;
                }
                .ref-banner__button {
                    min-width: 154px;
                    width: 154px;
                    height: 52px;
                    padding: 0 24px;
                    border-radius: 0 12px 12px 0;
                    border: 1px solid #8D24D5;
                    background: #8D24D5;
                    font-family: Inter, sans-serif;
                    font-size: 16px;
                    font-weight: 600;
                    line-height: 19.36px;
                    cursor: pointer;
                }
            }
        }
    }
}

@media (max-width: 1024px) {
    .ref-bonus {
        padding: 16px;
        .ref-bonus__title {
            font-size: 20px;
            line-height: 24.2px;
        }
        .ref-bonus__subtitle {
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 16.94px;
        }
        .ref-bonus__table {
            .ref-bonus__table-header {
                .ref-bonus__table-header-item {
                    font-size: 16px;
                    font-weight: 500;
                    line-height: 19.36px;
                }
            }
        }
    }
}
</style>
