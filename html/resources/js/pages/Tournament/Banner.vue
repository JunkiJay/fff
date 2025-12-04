<template>
    <article class="banner static rounded-xl p-4 flex items-center justify-between text-white"
             :style="{
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
                        <path
                            d="M14 0C19.5226 7.37026e-05 23.9997 4.47651 24 9.99902C24 15.5218 19.5227 19.9989 14 19.999C8.47723 19.999 4 15.5218 4 9.99902C4.00026 4.47647 8.47739 0 14 0ZM14 4.9502C13.5127 4.9502 13.1172 5.3457 13.1172 5.83301V10.833C13.1172 11.1672 13.3066 11.4726 13.6055 11.6221L16.9385 13.2891C17.3741 13.5066 17.9041 13.3299 18.1221 12.8945C18.3398 12.4588 18.1631 11.928 17.7275 11.71L14.8828 10.2871V5.83301C14.8828 5.34585 14.4871 4.95045 14 4.9502Z"
                            fill="white" />
                    </g>
                    <defs>
                        <filter id="filter0_d_131_62" x="0" y="0" width="28" height="27.999"
                                filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="4" />
                            <feGaussianBlur stdDeviation="2" />
                            <feComposite in2="hardAlpha" operator="out" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_131_62" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_131_62" result="shape" />
                        </filter>
                    </defs>
                </svg>

                <span class="text-sm">{{ this.timer.days }}д {{ this.timer.hours }}ч {{ this.timer.minutes }}м</span>
            </div>

            <div class="gap-1">
                <button class="md:w-auto bg-[#8D24D5] hover:opacity-90 rounded-md h-12 px-6 mr-4">
                    Вы участвуете!
                </button>
                <button @click.native="isShowModal = true"
                        class="md:w-auto bg-[#1F1B29] hover:opacity-90 text-[#6D6E77] rounded-md h-12 px-6">
                    Информация турнира
                </button>
            </div>
        </div>
    </article>
    <Modal :open="isShowModal" v-on:close="isShowModal = false">
        <div class="block modal max-w-[488px] w-full rounded-[16px] align-items-center">
            <div class="modal__info w-full p-4 bg-[var(--color-content)] overflow-y-auto text-[var(--color-text)]">
                <div
                    class="h-[128px] w-full rounded-[16px] p-8 !bg-cover"
                    :style="{
                                    backgroundImage: `url(${bannerModal})`,
                                }"
                >
                    <p class="text-white text-[20px] font-bold">Детали и правила проведения еженедельных турниров</p>
                </div>

                <div class="p-4">
                    <h2 class="text-[20px] mb-2.5">Сроки проведения</h2>
                    <p class="text-[18px] mb-6">
                        Турнир проходит еженедельно, начиная с понедельника, заканчивая воскресением.
                    </p>

                    <h2 class="text-[20px] mb-2.5">Механика турнира</h2>
                    <p class="text-[18px] mb-6">
                        Лидируйщее место занимает игрок, с наивысшим оборотом средств в течении данной недели.
                        <br />
                        Для квалификации необходимо иметь общий оборот средств, равный
                        <strong class="text-[var(--color-primary)] font-bold">10 000 РУБЛЕЙ</strong>
                    </p>

                    <h2 class="text-[20px] mb-2.5">Количество призовых мест</h2>
                    <p class="text-[18px] mb-6">Всего предусмотренно 10 призовых мест</p>

                    <h2 class="text-[20px] mb-2.5">Распределение призов</h2>
                    <table class="modal__table">
                        <thead>
                        <tr>
                            <th class="text-center">Место</th>
                            <th class="text-center">Приз</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="prize in prizes" :key="prize.place">
                            <td>{{ prize.place }}</td>
                            <td>{{ prize.prize }} ₽</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-2 items-center p-4 gap-2 bg-[var(--color-secondary-light)]">
                <Button
                    variant="default"
                    @click.native="isShowModal = false"
                    class="bg-[var(--color-secondary)] text-[var(--color-text)] w-full rounded-[10px] h-[42px] flex items-center justify-center"
                >
                    Закрыть
                </Button>

                <Button
                    variant="default"
                    @click.native="isShowModal = false"
                    class="w-full rounded-[10px] h-[42px] bg-[var(--color-primary)] text-white flex items-center justify-center"
                >
                    Понятно
                </Button>
            </div>
        </div>
    </Modal>
</template>
<script>
import axios from "axios";
import Button from "@/components/ui/Button.vue";
import Modal from "@/components/Modal.vue";
import weeklyBannerImg from "./assets/weekly_banner.png";
import weeklyBannerMobileImg from "./assets/weekly_banner_mobile.png";
import bannerModal from "@/pages/Tournament/assets/banner-modal.png";
import banner1 from "./assets/1.png";

export default {
    components: {
        Button,
        Modal
    },

    data() {
        return {
            bannerModal,
            banner1,
            weeklyBannerImg,
            weeklyBannerMobileImg,
            endDate: null,
            timer: {
                interval: null,
                days: null,
                hours: null,
                minutes: null,
                seconds: null,
                isEnded: null
            },
            isShowModal: false,
            prizes: [
                { place: 1, prize: 50000 },
                { place: 2, prize: 20000 },
                { place: 3, prize: 10000 },
                { place: 4, prize: 5000 },
                { place: 5, prize: 5000 },
                { place: 6, prize: 2000 },
                { place: 7, prize: 2000 },
                { place: 8, prize: 2000 },
                { place: 9, prize: 2000 },
                { place: 10, prize: 2000 }
            ]
        };
    },

    mounted() {
        axios.get(`/tournament/timer`)
            .then((response) => {
                this.endDate = response.data.endDate ? new Date(response.data.endDate) : new Date();
                this.timerTick();
                this.timer.interval = setInterval(this.timerTick.bind(this), 1000);
            })
            .catch(() => {
                this.timer.days = 0;
                this.timer.hours = "00";
                this.timer.minutes = "00";
                this.timer.seconds = "00";
            });
    },

    destroyed() {
        clearInterval(this.timer.interval);
    },

    methods: {
        timerUpdateRemaining(distance) {
            this.timer.days = Math.floor(distance / (1000 * 60 * 60 * 24));
            this.timer.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this.timer.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            this.timer.seconds = Math.floor((distance % (1000 * 60)) / 1000);
            this.timer.hours = this.timer.hours < 10 ? `0${this.timer.hours}` : this.timer.hours;
            this.timer.minutes = this.timer.minutes < 10 ? `0${this.timer.minutes}` : this.timer.minutes;
            this.timer.seconds = this.timer.seconds < 10 ? `0${this.timer.seconds}` : this.timer.seconds;
        },

        timerTick() {
            const currentTime = new Date();
            const distance = Math.max(this.endDate - currentTime, 0);
            this.timerUpdateRemaining(distance);

            if (distance === 0) {
                clearInterval(this.timer.interval);
                this.isEnded = true;
            }
        }
    }
};
</script>
<style lang="scss" scoped>
.banner {
    height: 320px;
    background-image: url('/img/banners/tournaments/weekly_banner_mobile.png');
}
@media (min-width: 768px) {
    .banner {
        height: 404px;
        background-image: url('/img/banners/tournaments/weekly_banner.png')!important;
    }
}

.modal {
    display: block;
    max-height: calc(100vh - 90px);
    margin-left: calc(50vw - 240px);
    margin-top: 100px;
    height: 100%;
    overflow: auto;
    &::-webkit-scrollbar {
        width: 0;
    }
    &__info {
        max-height: calc(100vh - 170px);
        height: 100%;
        &::-webkit-scrollbar {
            width: 0;
        }
    }
    &__table {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        thead {
            tr {
                th {
                    &:first-child {
                        border: 1px solid #ffffff15;
                        border-right: none;
                        border-top-left-radius: 8px;
                    }
                    &:last-child {
                        border: 1px solid #ffffff15;
                        border-left: none;
                        border-top-right-radius: 8px;
                    }
                }
            }
        }
        tbody {
            tr {
                td:first-child {
                    border-bottom: 1px solid #ffffff15;
                    border-left: 1px solid #ffffff15;
                }
                td:last-child {
                    border-bottom: 1px solid #ffffff15;
                    border-right: 1px solid #ffffff15;
                }
            }
            tr:last-child {
                td:first-child {
                    border-bottom-left-radius: 8px;
                }
                td:last-child {
                    border-bottom-right-radius: 8px;
                }
            }
        }
    }
}

.modal_tournament {
    max-height: calc(100vh - 90px);
    height: 100%;

    &::-webkit-scrollbar {
        width: 0;
    }

    &__info {
        max-height: calc(100vh - 300px);
        height: 100%;
        overflow: auto;
        margin-top: 10%;

        &::-webkit-scrollbar {
            width: 0;
        }
    }

    &__table {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;

        thead {
            tr {
                th {
                    &:first-child {
                        border: 1px solid #ffffff15;
                        border-right: none;
                        border-top-left-radius: 8px;
                    }

                    &:last-child {
                        border: 1px solid #ffffff15;
                        border-left: none;
                        border-top-right-radius: 8px;
                    }
                }
            }
        }

        tbody {
            tr {
                td:first-child {
                    border-bottom: 1px solid #ffffff15;
                    border-left: 1px solid #ffffff15;
                }

                td:last-child {
                    border-bottom: 1px solid #ffffff15;
                    border-right: 1px solid #ffffff15;
                }
            }

            tr:last-child {
                td:first-child {
                    border-bottom-left-radius: 8px;
                }

                td:last-child {
                    border-bottom-right-radius: 8px;
                }
            }
        }
    }
}
</style>