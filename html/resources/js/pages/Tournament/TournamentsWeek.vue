<template>
    <div class="page-tournament tournament px-2">
        <Banner class="tournament__banner" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 m-2 mt-4 items-end">
            <div v-for="leader in topLeaders" :key="leader.id" :class="[{}, 'place-border', `place-border-${leader.position}`]">
                <div :class="[{}, 'place', `place-${leader.position}`]">
                    <div class="place__position">{{ leader.position }} Место</div>

                    <img :src="leader.avatar || '/img/avatar-default.svg'" alt="image" class="place__img" />

                    <div class="w-full place__name">
                        <span>{{ leader.username }}</span>
                    </div>
                    <div class="flex w-full gap-2 mb-1">
                        <div class="place__card">
                            <span class="place__card-title">Оборот</span>
                            <span class="place__card-value">{{ leader.amount }} ₽</span>
                        </div>
                        <div class="place__card">
                            <span class="place__card-title">Награда</span>
                            <span class="place__card-reward">{{ formatCurrency(leader.reward) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            ref="heightBox"
            :class="{ is_open: isOpen }"
            class="tournament__leaders-table items-end leaders-table bg-[var(--color-content)] m-2 overflow-x-auto text-[var(--color-text)] rounded-[24px] lg:bg-[var(--color-content)]"
        >
            <table>
                <thead>
                <tr>
                    <th></th>
                    <th class="text-center">Позиция</th>
                    <th class="text-center">Игрок</th>
                    <th class="text-center">Оборот</th>
                    <th class="text-center">Выигрыш</th>
                    <th></th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="leader in normalLeaders" :key="leader.id">
                    <td></td>
                    <td :class="{ 'not-current': Number(leader.id) !== Number(currentUserId) }">
                        #{{ leader.position }}
                    </td>
                    <td :class="{ 'not-current': Number(leader.id) !== Number(currentUserId) }">
                        {{ leader.username }}
                    </td>
                    <td>{{ formatCurrency(leader.amount) }}</td>
                    <td :class="{ 'text-[#28a745]': leader.reward !== 0 }">{{ formatCurrency(leader.reward) }}</td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>
        <LiveDropList />
    </div>
</template>
<script>
import Button from "@/components/ui/Button.vue";
import Banner from "@/pages/Tournament/Banner.vue";
import LiveDropList from "@/components/LiveDropList.vue";
import axios from "axios";
import banner1 from "./assets/1.png";
import Modal from "../../components/Modal.vue";

export default {
    components: {
        Modal,
        Button,
        Banner,
        LiveDropList,
        banner1
    },

    data() {
        return {
            places: [],
            leaders: [],
            isOpen: false,
            height: "",
        };
    },
    methods: {
        formatCurrency(v) {
            const n = Number(v) || 0;
            return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(n);
        },
        onOpen() {
            this.isOpen = !this.isOpen;
        },
        matchHeight() {
            this.height = this.$refs.heightBox.clientHeight;
        },
    },
    computed: {
        currentUserId() {
            return this.$store.state.user || undefined;
        },
        topLeaders() {
            // берём первых трёх участников
            const leaders = this.leaders.slice(0, 3);

            if (leaders.length >= 2) {
                [leaders[0], leaders[1]] = [leaders[1], leaders[0]];
            }

            return leaders;
        },
        normalLeaders() {
            return this.leaders.slice(3, 15);
        },
    },

    async mounted() {
        try {
            const response = await axios.get("/tournament/live");
            this.leaders = response.data.leaders;
            this.places = response.data.places;
        } catch (error) {
            console.error("Ошибка при загрузке данных:", error);
        }
    }
};
</script>

<style lang="scss" scoped>
.tournament {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 24px;

    .place-border {
        padding: 2px;
        border-radius: 24px;
        &-1 {
            height: 105%;
            background: linear-gradient(180deg, #EB810B, #09040F);
        }

        &-2 {
            background: linear-gradient(180deg, #7D7B88, #09040F);
        }

        &-3 {
            background: linear-gradient(180deg, #BD6844, #09040F);

        }

        .place {
            width: 100%;
            padding: 52px 24px 24px;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #15101D;
            position: relative;
            overflow: hidden;

            &__name {
                color: #fff;
                font-size: 18px;
                font-weight: 500;
                margin-bottom: 16px;
                text-align: center;
            }

            &__position {
                position: absolute;
                top: 0px;
                left: 0px;
                height: 39px;
                padding: 0px 24px;
                display: flex;
                align-items: center;
                border-bottom-right-radius: 24px;
                background: #fff;
                color: #fff;
                font-size: 16px;
            }

            &-1 {
                height: 100%;
                .place__position {
                    background: #EB810B;
                }
            }

            &-2 {
                .place__position {
                    background: #7D7B88;
                }
            }

            &-3 {
                .place__position {
                    background: #BD6844;
                }
            }

            &__card {
                padding: 10px 16px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                border-radius: 16px;
                width: 100%;
                background-color: #1F1B29;

                &-title {
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    overflow: hidden;
                    color: var(--color-text-secondary);
                    font-size: 14px;
                    font-weight: 300;
                    margin-bottom: 7px;
                }

                &-value {
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    overflow: hidden;
                    max-width: 100px;
                    color: var(--color-text);
                }

                &-reward {
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    overflow: hidden;
                    color: #47d524;
                    font-weight: 500;
                }
            }

            &__img {
                width: 100px;
                height: auto;
                aspect-ratio: 1/1;
                flex-shrink: 0;
                border-radius: 8px;
                overflow: hidden;
                object-position: center;
                object-fit: cover;
                margin-bottom: 16px;
            }
        }
    }

    .leaders-table {
        transition: max-height 0.4s ease, padding-top 0.4s ease;
        padding-bottom: 12px;
        max-height: 1200px;
        background-color: #15101D;
        font-weight: 400;
        &::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
        thead th:first-child,
        tbody td:first-child,
        thead th:last-child,
        tbody td:last-child {
            width: 20px;
            padding: 0;
            border: 0 !important;
        }

        thead,
        tr {
            border: none;
        }

        td {
            font-weight: 300;
        }

        th {
            font-size: 15px;
            color: #fff;
            font-weight: 300;
        }
        th,
        td {
            white-space: nowrap;
            padding: 16px 12px;
            margin: 15px 5px;
            border-color: #ffffff16;
            border-bottom-width: 1px;
        }
        td.not-current {
            color: rgba(255, 255, 255, 0.3);
            white-space: nowrap;
            padding-left: 12px;
            padding-right: 12px;
        }

        tr:last-child {
            td {
                margin-bottom: 20px;
                border: none;
            }
        }
        &.is_open {
            max-height: 0 !important;
            padding: 0;
        }
    }
}
</style>
