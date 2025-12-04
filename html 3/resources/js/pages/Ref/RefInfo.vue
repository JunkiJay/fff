<template>
    <div class="ref-info">
        <div class="ref-info__title text-center md:text-left">Ваши рефералы</div>
        <div class="ref-info__subtitle text-center md:text-left">
            В таблице представлено количество ваших рефералов и доход, полученный с них.
        </div>

        <div v-if="data.length === 0" class="no-data">
            Нет данных
        </div>

        <div v-else class="ref-info__table">
            <div
                v-for="(column, index) in data"
                :key="index"
                :class="['ref-info__col', column.class]"
            >
                <div class="ref-info__col-title text-[16px] md:text-[20px]">{{ column.title }}</div>
                <div class="ref-info__col-content">
                    <div
                        v-for="(item, idx) in column.data"
                        :key="idx"
                        class="ref-info__col-content-item whitespace-nowrap text-[14px] md:text-[16px]"
                    >
                        {{ item }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "RefInfo",
    props: {
        data: {
            type: Array,
            required: true,
        }
    },
    methods: {
        // formatCurrency(value) {
        //     // Преобразуем строку или другое значение в число
        //     const numericValue = parseFloat(value);

        //     // Если значение не число (NaN), возвращаем его как есть
        //     if (isNaN(numericValue)) return value;

        //     // Округляем до двух знаков после запятой
        //     const roundedValue = numericValue.toFixed(2);

        //     // Форматируем с использованием Intl.NumberFormat
        //     return new Intl.NumberFormat('ru-RU', {
        //         style: 'currency',
        //         currency: 'RUB',
        //     }).format(roundedValue);
        // }
    },
};
</script>

<style lang="scss" scoped>
.ref-info {
    .ref-info__title {
        margin-bottom: 8px;
        font-family: Inter, sans-serif;
        font-size: 32px;
        font-weight: 500;
        line-height: 38.73px;
        color: #fff;
    }
    .ref-info__subtitle {
        margin-bottom: 24px;
        font-family: Inter, sans-serif;
        font-size: 16px;
        font-weight: 400;
        line-height: 19.36px;
        color: rgba(255, 255, 255, 0.6);
    }

    .no-data {
        text-align: center;
        color: rgba(255, 255, 255, 0.6);
        font-size: 18px;
        font-weight: 400;
        line-height: 21.8px;
    }

    .ref-info__table {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        padding: 24px 16px;
        background: #FFFFFF0D;
        border-radius: 32px;

        .ref-info__col {
            display: flex;
            flex-direction: column;
            gap: 16px;
            align-items: center;

            .ref-info__col-title {
                width: 100%;
                padding-bottom: 16px;
                border-bottom: 1px solid #FFFFFF40;
                color: #fff;
                font-family: Inter, sans-serif;
                font-weight: 500;
                line-height: 24.2px;
                text-align: center;
            }

            .ref-info__col-content {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
                gap: 24px;

                .ref-info__col-content-item {
                    width: 100%;
                    font-family: Rubik, sans-serif;
                    font-weight: 400;
                    line-height: 18.35px;
                    text-align: center;
                    color: #fff;
                }
            }

            &.ref-info__col_passive .ref-info__col-content-item {
                color: rgba(255, 255, 255, 0.4);
            }

            &.ref-info__col_active .ref-info__col-content-item {
                color: #47D524;
            }
        }
    }
}
</style>
