<template>
    <div class="page-faq">
        <div class="page-faq__content lg:p-4 min-[568px]:p-8 p-4 rounded-[24px] lg:bg-[var(--color-content)]">
            <h5 class="page-faq__title">Основные правила сайта</h5>
            <hr class="page-faq__hr" />
            <ul class="">
                <li>Запрещено использовать баги/уязвимости сайта. Все попытки использование таковых ведут за собой
                    блокировку аккаунта.</li>
                <li>Администрация сайта в праве заблокировать учетную запись без объяснения причин блокировки.</li>
                <li>Сайт не несет ответственность за проигранный баланс.</li>
                <li>Сайт не является азартной игрой!</li>
                <li>Сайт предназначен для лиц старше 18 лет, если вам менее 18, немедленно покиньте его.</li>
                <li>
                    Запрещено использовать более 1 аккаунта для игры на сайте, при факте нарушения данного правила, ваш
                    аккаунт может быть
                    ограничен или заблокирован.
                </li>
                <li>Запрещено использовать фейк аккаунты (пустые аккаунты, без записей/друзей и пр).</li>
                <li>Запрещено использовать один кошелек для вывода на нескольких аккаунтах.</li>
                <li>Запрещено использовать бонусную систему сайта для корыстных умыслов, с целью получения прибыли.</li>
                <li>Запрещено использовать трансфер средств с одной платежной системы на другую.</li>
            </ul>
        </div>

        <div class="page-faq__content lg:p-4 min-[568px]:p-8 p-4 rounded-[24px] lg:bg-[var(--color-content)]">
            <h5 class="page-faq__title">Ответы на часто задаваемые вопросы</h5>
            <hr class="page-faq__hr" />
            <div class="faq" v-for="(item, key) in items" :key="key">
                <h6 class="faq__question">{{ item.title }}</h6>
                <div class="faq__answer">{{ item.quest }}</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            items: [
                {
                    id: 1,
                    title: "Не пришел вывод",
                    quest: "Все выводы на нашем сайте осуществляются в среднем от 1 минуты до 24 часов."
                },
                {
                    id: 2,
                    title: "Как начать с вами сотрудничать?",
                    quest: "Чтобы предложить нам сотрудничество, вы можете написать нам в лс группы VK."
                },
                {
                    id: 3,
                    title: "Нужен ли депозит для вывода?",
                    quest: "Нет, депозит для вывода не нужен."
                },
                {
                    id: 4,
                    title: "Есть ли отыгрыш депозита?",
                    quest: "Да, сумма любой вашей ставки учитывается при отыгрыше. Отыгрыш составляет x3 от депозита."
                },
                {
                    id: 5,
                    title: "Как часто я могу активировать промокоды?",
                    quest: "Лимит на активацию промокода на баланс - 2 раза в день, на фриспины - 2 раза в день. У каждого промокода разные условия и требования для активации."
                }
            ],
        };
    },
};
</script>

<style lang="scss" scoped>
.page-faq {
    display: flex;
    flex-direction: column;
    gap: 32px;
    color: var(--color-text);

    &__title {
        font-size: 18px;
        font-family: Oswald !important;
    }

    hr {
        background-color: var(--color-text-secondary);
        margin: 24px 0;
    }

    ul li {
        font-weight: 300;
        font-size: 14px;
    }

    .faq {
        &__question {
            color: var(--color-primary);
            font-family: Oswald !important;
            margin-top: 16px;
        }

        &__answer {
            font-size: 14px;
            font-weight: 300;
            margin-bottom: 16px;
        }
    }
}
</style>
