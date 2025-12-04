<template>
    <div class="max-w-[1110px] mx-auto px-2 md:px-0 space-y-8 md:space-y-10 text-white opacity-100">

        <!-- HERO BANNER: только визуал -->
        <section class="static rounded-lg flex items-center justify-between overflow-hidden">
            <img :src="bannerTop" class="hidden md:block" alt="">
            <img :src="bannerTopMobile" class="block md:hidden w-full" alt="">
        </section>

        <!-- АКТИВНЫЕ ТУРНИРЫ -->
        <section class="static space-y-4">
            <h2 class="text-xl md:text-2xl font-bold uppercase font-['Oswald']">Активные турниры</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" heading="267px" style="transform: translate(0, -38px);">
                <!-- CARD 1 -->
                <article class="static rounded-xl p-4 flex items-center justify-between" heading="267px" width="545px"
                         :style="{
                            backgroundImage: `url('${banner1}')`,
                            backgroundSize: '100% 100%',
                            backgroundRepeat: 'no-repeat',
                            paddingTop: '110px !important',
                            transformOrigin: 'center'
                          }"
                >
                    <div class="flex-1 pr-3">
                        <h3 class="text-2xl md:text-[22px] leading-tight mb-2 font-['Oswald']">
                            Еженедельный турнир <span class="whitespace-nowrap">"StimuleWeek"</span>
                        </h3>

                        <div class="flex items-baseline gap-2 mb-3">
                            <span class="text-sm md:text-base font-medium">Призовой фонд:</span>
                            <span class="text-[#47D524] font-extrabold text-lg md:text-xl">100 000 ₽</span>
                        </div>

                        <div class="inline-flex items-center gap-2 bg-[#1F1B29] rounded-md px-3 py-2 mb-4">
                            <svg width="20" height="20" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g filter="url(#filter0_d_131_62)">
                                    <path d="M14 0C19.5226 7.37026e-05 23.9997 4.47651 24 9.99902C24 15.5218 19.5227 19.9989 14 19.999C8.47723 19.999 4 15.5218 4 9.99902C4.00026 4.47647 8.47739 0 14 0ZM14 4.9502C13.5127 4.9502 13.1172 5.3457 13.1172 5.83301V10.833C13.1172 11.1672 13.3066 11.4726 13.6055 11.6221L16.9385 13.2891C17.3741 13.5066 17.9041 13.3299 18.1221 12.8945C18.3398 12.4588 18.1631 11.928 17.7275 11.71L14.8828 10.2871V5.83301C14.8828 5.34585 14.4871 4.95045 14 4.9502Z" fill="white"/>
                                </g>
                                <defs>
                                    <filter id="filter0_d_131_62" x="0" y="0" width="28" height="27.999" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                                        <feOffset dy="4"/>
                                        <feGaussianBlur stdDeviation="2"/>
                                        <feComposite in2="hardAlpha" operator="out"/>
                                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_131_62"/>
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_131_62" result="shape"/>
                                    </filter>
                                </defs>
                            </svg>

                            <span class="text-sm">{{ this.weeklyTimer.days }}д {{ this.weeklyTimer.hours }}ч {{ this.weeklyTimer.minutes }}м</span>
                        </div>

                        <div>
                            <button class="w-full md:w-auto bg-[#8D24D5] hover:opacity-90 rounded-md h-12 px-6" @click="goToWeekly" style="width: 100%;">
                                Подробнее
                            </button>
                        </div>
                    </div>
                </article>

                <!-- CARD 2 -->
                <article class="static rounded-xl p-4 md:p-6 flex items-center justify-between" heading="267px" width="545px;" style="background-position-y: 10px"
                         :style="{
                            backgroundImage: `url('${banner2}')`,
                            backgroundSize: '100% 100%',
                            backgroundRepeat: 'no-repeat',
                            paddingTop: '110px !important',
                            transformOrigin: 'center'
                          }"
                >
                    <div class="flex-1 pr-3">
                        <h3 class="text-2xl md:text-[22px] leading-tight mb-2 font-['Oswald']">
                            Ограниченный турнир <span class="whitespace-nowrap">StimuleExclusive</span>
                        </h3>

                        <div class="flex items-baseline gap-2 mb-3">
                            <span class="text-sm md:text-base font-medium">Призовой фонд:</span>
                            <span class="text-[#47D524] font-extrabold text-lg md:text-xl">100 000 ₽</span>
                        </div>

                        <div class="inline-flex items-center gap-2 bg-[#1F1B29] rounded-md px-3 py-2 mb-4">
                            <span
                                class="inline-block w-3.5 h-3.5 rounded-full bg-white/20 ring-2 ring-[#357BF6]"></span>
                            <span class="font-semibold text-sm">Скоро запустится</span>
                        </div>

                        <div>
                            <button class="w-full md:w-auto bg-[#8D24D5] hover:opacity-90 rounded-md h-12 px-6" style="width: 100%;">
                                Подробнее
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- ИСТОРИЯ ТУРНИРОВ -->
        <section class="static space-y-4" v-if="lastTournaments">
            <h2 class="text-xl md:text-2xl font-bold uppercase font-['Oswald']">История турниров</h2>

            <div
                ref="heightBox"
                class="leaders-table bg-[var(--color-content)] mt-4 overflow-x-auto text-[var(--color-text)] rounded-[24px] lg:bg-[var(--color-content)]"
            >
                <table>
                    <thead>
                    <tr>
                        <th></th>
                        <th class="text-left">Название</th>
                        <th class="text-center">
                            <span class="md:hidden text-center">Фонд</span>
                            <span class="hidden md:inline text-center">Призовой фонд</span>
                        </th>
                        <th class="text-center">
                            <span class="md:hidden text-center">Завершения</span>
                            <span class="hidden md:inline text-center">Дата завершения</span>
                        </th>
                        <th class="text-right">Победители</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr v-for="tournament in lastTournaments" :key="tournament.id">
                        <td></td>
                        <td class="text-left not-current">{{ tournament.id }}</td>
                        <td class="text-center text-[#28a745]">
                            100 000 ₽
                        </td>
                        <td class="text-center not-current">{{ new Date(tournament.end_at).toISOString().slice(0, 10) }}</td>
                        <td class="text-right not-current cursor-pointer" style="font-size: 24px;" @click="showDetails(tournament.id)">
                            <template v-if="tournament.status === 'finished'">
                            +
                            </template>
                        </td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <section class="static space-y-4 hidden md:block">
            <LiveDropList />
        </section>
    </div>
    <TournamentModal v-if="isDetailOpen" v-model="isDetailOpen" :tournament="tournament" />
</template>

<script lang="ts">

import banner1 from "./assets/1.png";
import banner2 from "./assets/2.png";
import bannerTop from "./assets/top.png";
import bannerTopMobile from "./assets/top-mobile.png";
import LiveDropList from "../../components/LiveDropList.vue";
import axios from "axios";
import { ref } from "vue";
import { mapActions, mapState } from "vuex";
import TournamentModal from "../../components/modals/TournamentDetailModal.vue";

export default {
    components: { TournamentModal, LiveDropList },
    data() {
        const weeklyTimer = ref(
            {
                interval: null,
                days: null,
                hours: null,
                minutes: null,
                seconds: null,
                isEnded: null
            }
        )

        return {
            tournament: null,
            isDetailOpen: false,
            weeklyTimer,
            banner1,
            banner2,
            bannerTop,
            bannerTopMobile
        };
    },
    mounted(): any {
        this.fetchTournaments()
        axios.get(`/tournament/timer`)
            .then((response) => {
                const currentTime = new Date();
                const distance = Math.max(new Date(response.data.endDate) - currentTime, 0);
                this.timerUpdateRemaining(distance);
            })
            .catch(() => {
                this.weeklyTimer.days = 0;
                this.weeklyTimer.hours = "00";
                this.weeklyTimer.minutes = "00";
                this.weeklyTimer.seconds = "00";
            });
    },
    computed: {
        ...mapState('tournaments', ['lastTournaments'])
    },
    methods: {
        ...mapActions('tournaments', ['fetchTournaments']),
        goToWeekly() {
            this.$router.push({ name: 'tournament-week' })
        },
        showDetails(tournamentId: int) {
            this.tournament = (this.lastTournaments ?? []).find(t => t.id === tournamentId)
            console.log(this.tournament)
            this.isDetailOpen = true;
        },
        timerUpdateRemaining(distance) {
            this.weeklyTimer.days = Math.floor(distance / (1000 * 60 * 60 * 24));
            this.weeklyTimer.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this.weeklyTimer.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            this.weeklyTimer.seconds = Math.floor((distance % (1000 * 60)) / 1000);
            this.weeklyTimer.hours = this.weeklyTimer.hours < 10 ? `0${this.weeklyTimer.hours}` : this.weeklyTimer.hours;
            this.weeklyTimer.minutes = this.weeklyTimer.minutes < 10 ? `0${this.weeklyTimer.minutes}` : this.weeklyTimer.minutes;
            this.weeklyTimer.seconds = this.weeklyTimer.seconds < 10 ? `0${this.weeklyTimer.seconds}` : this.weeklyTimer.seconds;
        },
    }
};
</script>

<style lang="scss" scoped>
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
        width: 35px;
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
        padding: 30px 0 !important;
    }
    th,
    td {
        white-space: nowrap;
        padding: 16px 0;
        margin: 15px 5px;
        border-color: #ffffff16;
        border-bottom-width: 1px;
    }
    td.not-current {
        color: rgba(255, 255, 255, 0.3);
        white-space: nowrap;
        padding-left: 12px;
        padding-right: 0;
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
</style>