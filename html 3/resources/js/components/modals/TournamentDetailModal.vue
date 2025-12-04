<!-- vue -->
<template>
    <teleport to="body">
        <div v-if="modelValue" class="modal-backdrop bg-[#0A0410]/76" @click="onBackdrop"></div>
        <div
            class="modal modal-figma text-white rounded-[14px]"
            role="dialog"
            aria-modal="true"
            aria-label="Информация о турнире"
            @keydown.esc="close"
            tabindex="0"
        >
            <header class="modal__header">
                <h3 class="modal__title">
                    Ограниченный турнир “StimuleExclusive”
                </h3>

                <button class="modal__close" @click="close" aria-label="Закрыть">
                    ×
                </button>
            </header>

            <section class="modal__body">
                <div
                    ref="heightBox"
                    class="leaders-table mb-3 mt-2 overflow-x-auto text-[var(--color-text)]"
                    v-if="tournament.history_leaders"
                >
                    Победители турнира:
                    <table class="rounded-[24px] p-4">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="text-center">Место</th>
                            <th class="text-center">
                                <span>Победитель</span>
                            </th>
                            <th class="text-center">
                                <span>Приз</span>
                            </th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr v-for="leader in tournament.history_leaders" :key="tournament.id">
                            <td></td>
                            <td class="text-center" :class="{ 'not-current': leader.position > 3 }">{{ leader.position }}</td>
                            <td class="text-center" :class="{ 'not-current': leader.position > 3 }">
                                <span class="leader">
                                  <img width="25x" :src="leader.avatar" />
                                  <span class="line-clamp-2 max-w-[150px]">{{ leader.username }}</span>
                                </span>
                            </td>
                            <td class="text-center reward">{{ formatCurrency(leader.reward) }}</td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </teleport>
</template>

<script>
export default {
    name: 'TournamentModal',
    props: {
        modelValue: { type: Boolean, default: true },
        tournament: { type: Object, default: () => ({}) },
    },
    emits: ['update:modelValue'],
    methods: {
        close() {
            this.$emit('update:modelValue', false)
        },
        onBackdrop(e) {
            if (e.target === e.currentTarget) this.close()
        },
        formatCurrency(v) {
            const n = Number(v) || 0;
            return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(n);
        },
    }
}
</script>

<style scoped>
:root {
    --figma-bg: #15101D;
    --figma-border-outer: rgba(54, 52, 62, 0.8);
    --figma-border-inner: rgba(62, 59, 71, 0.8);
    --figma-text: #FFFFFF;
}

.modal-backdrop {
    position: fixed;
    inset: 0;
    display: grid;
    place-items: center;
    padding: 16px;
    z-index: 1000;
}

/* Каркас модалки под макет: 601x729 с адаптацией под вьюпорт */
.modal.modal-figma {
    box-sizing: border-box;
    width: min(601px, 100%);
    max-height: min(800px, 92vh);
    background: var(--figma-bg);
    color: var(--figma-text);
    display: grid;
    grid-template-rows: auto 1fr auto;
    background-color: #15101D !important;
    transform: translate(100%, 50px);

    .leaders-table {
        background-color: none;
        font-size: 14px !important;
        font-weight: normal !important;

        table {

            .leader {
                display: inline-flex;
                align-items: center; /* центр по вертикали */
                gap: 8px;            /* расстояние между картинкой и текстом */
            }

            .leader img {
                width: 24px;
                height: 24px;
                display: block;      /* убирает «хвост» по базовой линии */
                border-radius: 50%;
                object-fit: cover;
            }

            tr {
                border-bottom: none !important;
            }

            th {
                padding: 30px 0 !important;
            }

            th,
            td {
                font-weight: normal;
                border-bottom: 1px solid var(--color-secondary);
            }

            td.not-current {
                color: rgba(255, 255, 255, 0.3);
                white-space: nowrap;
            }

            thead th:first-child,
            tbody td:first-child,
            thead th:last-child,
            tbody td:last-child {
                width: 20px;
                padding: 0;
                border: 0 !important;
            }
        }
    }
}

/* Шапка: заголовок 24/36 Oswald и кнопка 28x28 */
.modal__header {
    display: grid;
    grid-template-columns: 1fr auto;
    place-items: left;
    gap: 12px;
    padding: 18px 20px;
    border-bottom: 1px solid var(--figma-border-inner);
    z-index: 1000;
}
.modal__title {
    margin: 0;
    font-family: 'Oswald', sans-serif;
    font-weight: 700;
    font-size: 24px;
    line-height: 36px;
}
.modal__close {
    width: 28px; height: 28px;
    display: grid; place-items: center;
    color: #36343E;
    position: absolute;
    left: 557px;
    top: 18px;
    font-size: 28px;


}
.modal__close:hover { filter: brightness(1.1); }

/* Тело: сетка из строк, подписи Rubik 16/23 и значения */
.modal__body {
    padding: 16px 20px;
    display: grid;
    gap: 14px;
    font-family: 'Rubik', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, sans-serif;
}
.modal__row {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 12px;
    align-items: start;
}
.modal__label {
    font-weight: 400;
    font-size: 16px;
    line-height: 145%;
    color: #fff;
    opacity: 0.9;
}
.modal__value {
    font-weight: 500;
    font-size: 16px;
    line-height: 145%;
}

/* Внутренняя прокручиваемая панель «история» из макета */
.panel-scroll {
    box-sizing: border-box;
    border: 1px solid var(--figma-border-inner);
    border-radius: 8px;
    background: var(--figma-bg);
    padding: 16px;
    display: grid;
    gap: 12px;
    /* В макете высота 588px — делаем максимум, чтобы адаптировалось */
    max-height: min(588px, 50vh);
}
.panel-scroll__head {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 16px;
    font-family: 'Rubik', sans-serif;
    font-size: 16px;
    line-height: 24px;
    color: #fff;
    opacity: 0.95;
}
.panel-scroll__body {
    overflow: auto;
    display: grid;
    gap: 10px;
    padding-right: 6px; /* для скроллбара */
}
.panel-scroll .row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 16px;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.panel-scroll .row:last-child { border-bottom: 0; }

.reward {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    color: #47d524;
    font-weight: 500;
}
</style>