<template>
    <div class="flex flex-col gap-8 w-full">
        <div class="slots-banner-wrapper">
            <SlotsBannerNew />
        </div>
        <LiveDropList class="mt-[650px]" />
        <div ref="heightBox" :class="{ is_open: isOpen }" :style="{ maxHeight: height ? height + 'px' : '100%' }"
            class="flex flex-col gap-8 w-full">
            <div
                class="flex flex-col gap-4 p-[24px_15px] bg-[#1F1B2985] rounded-[32px] md:p-8 md:bg-[#1F1B29/50] md:border border-white/15 w-full">
                <CategoryTitle title="Stimule games" />
                <CategoryList ref="gamesContainer">
                    <StimuleCategoryItem v-for="item in stimuleGames" :key="item.to" class="category-list__item"
                        :item="item" />
                </CategoryList>
            </div>

            <Providers />

            <div
                class="flex flex-col gap-4 p-[24px_15px] bg-[#1F1B2985] rounded-[32px] md:p-8 md:bg-[#1F1B29/50] md:border border-white/15 w-full">
                <div class="flex flex-col gap-6" v-if="lastGames.length > 0">
                    <CategoryTitle title="Последние игры">
                    </CategoryTitle>
                    <CategoryList ref="gamesContainer">
                        <CategoryItem v-for="item in lastGames" :key="item.to" class="category-list__item"
                                      :item="item"
                                      @click="handleSlotClick(item)"
                        />
                    </CategoryList>
                </div>
                <div class="flex flex-col gap-6">
                    <CategoryTitle title="Новые игры">
                        <Button variant="gradient" :to="'/slots'"
                            :isDisabled="false"
                            class="w-[165px] h-[43px] font-[Inter] font-bold !bg-[#8D24D5]/50 text-white lg:mb-0 float-right">
                            Показать все
                        </Button>
                    </CategoryTitle>
                    <CategoryList ref="gamesContainer">
                        <CategoryItem v-for="item in newGames" :key="item.to" class="category-list__item"
                            :item="item"
                            @click="handleSlotClick(item)"
                        />
                    </CategoryList>
                </div>

                <div class="flex flex-col gap-6">
                    <CategoryTitle title="Популярные игры">
                        <Button :to="'/slots'"
                            :isDisabled="false"
                            class="w-[165px] h-[43px] font-[Inter] font-bold !bg-[#8D24D5]/50 text-white lg:mb-0 float-right"
                            variant="gradient">
                            Показать все
                        </Button>
                    </CategoryTitle>
                    <CategoryList ref="gamesContainer">
                        <CategoryItem v-for="item in popularGames" :key="item.to" class="category-list__item"
                            :item="item" />
                    </CategoryList>
                </div>

                <div class="flex flex-col gap-6">
                    <CategoryTitle title="Live игры">
                        <Button :to="'/slots'"
                            :isDisabled="false"
                            class="w-[165px] h-[43px] font-[Inter] font-bold !bg-[#8D24D5]/50 text-white lg:mb-0 float-right"
                            variant="gradient">
                            Показать все
                        </Button>
                    </CategoryTitle>
                    <CategoryList ref="gamesContainer">
                        <CategoryItem 
                            v-for="item in liveGames" 
                            :key="item.id" 
                            class="category-list__item" 
                            :item="item"
                            @click.prevent.stop="handleLiveClick(item)" 
                            />
                    </CategoryList>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Button from "@/components/ui/Button.vue";
import CategoryTitle from "./CategoryTitle.vue";
import SlotsBannerNew from "./SlotsBannerNew.vue";
import CategoryList from "./CategoryList.vue";
import CategoryItem from "./CategoryItem.vue";
import LiveDropList from "@/components/LiveDropList.vue";
import SoonCard from "./SoonCard.vue";
import Providers from "../../components/Providers.vue";
import StimuleCategoryItem from "./StimuleCategoryItem.vue";
import { ref } from "vue";
import diceImg from "@img/games/dice.png";
import minesImg from "@img/games/mines.png";
import bubblesImg from "@img/games/bubbles.png";
import wheelImg from "@img/games/wheel.png";
import plinkoImg from "@img/games/plinko.png";

export default {
    components: {
        StimuleCategoryItem,
        Button,
        SlotsBannerNew,
        CategoryTitle,
        CategoryList,
        CategoryItem,
        LiveDropList,
        SoonCard,
        Providers,
        diceImg,
        minesImg,
        bubblesImg,
        wheelImg,
        plinkoImg,
    },
    data() {
        return {
            stimuleGames: [
                { image: diceImg, to: "/dice" },
                { image: minesImg, to: "/mines" },
                { image: bubblesImg, to: "/bubbles" },
                { image: wheelImg, to: "/wheel" },
                { image: plinkoImg, to: "/plinko" },
                { component: SoonCard, to: "/" },
            ],
            newGames: [],
            popularGames: [],
            liveGames: [],
            height: null,
            isOpen: false,
            lastGames: [],
        };
    },
    methods: {
        matchHeight() {
            // Если нужно настроить высоту контейнера
        },

        handleLiveClick(slot) {
            console.log('handleLiveClick')
            if (!this.isAuthenticated) {
                this.$emitter.emit("noty", {
                    title: "Вход необходим",
                    text: "Пожалуйста, войдите в аккаунт для открытия демо-версии слота",
                    type: "error",
                });
            } else {
                this.$emitter.emit("noty", {
                    title: "Режим недоступен",
                    text: "Режим временно недоступен",
                    type: "error",
                });
            }
        },

        async fetchLastGames() {
            if (this.$store.state.user) {
                axios.get('/api/v1/user/' + this.$store.state.user.id + '/slots/last')
                    .then((response) => {
                        console.log(response.data)
                        this.lastGames = response.data;
                    });
            }
        },

        async fetchRandomGames() {
            try {
                // Проверяем кеш
                const cachedData = JSON.parse(localStorage.getItem("slotsData"));
                const cacheTime = localStorage.getItem("slotsCacheTime");
                const isCacheValid = cacheTime && Date.now() - cacheTime < 7 * 24 * 60 * 60 * 1000;

                const response = await axios.get(`/api/mobule/slots`, {
                    params: { page: 1, per_page: 50 },
                });
                const slots = response.data.data;

                // Генерируем данные
                this.newGames = this.getRandomGames(slots, 6);
                this.popularGames = this.getRandomGames(slots, 18);

                this.liveGames = [
                    {
                        id: 1,
                        imagePrimary: '/assets/image/slots/CrazyTime.jpg',
                        to: '/slots/game/live'
                    },
                    {
                        id: 2,
                        imagePrimary: '/assets/image/slots/DreamCatcher.jpg',
                        to: '/slots/game/live'
                    },
                    {
                        id: 3,
                        imagePrimary: '/assets/image/slots/Blackjack.jpg',
                        to: '/slots/game/live'
                    },
                    {
                        id: 4,
                        imagePrimary: '/assets/image/slots/Baccarat.jpg',
                        to: '/slots/game/live'
                    },
                    {
                        id: 5,
                        imagePrimary: '/assets/image/slots/poker.jpeg',
                        to: '/slots/game/live'
                    },
                    {
                        id: 6,
                        imagePrimary: '/assets/image/slots/LightningRoulette.jpeg',
                        to: '/slots/game/live'
                    },
                ];

            } catch (error) {
                console.error("Ошибка при получении слотов:", error);
            }
        },

        getRandomGames(slots = [], count = 24) {
            // Разделяем на приоритетные и остальные
            const prioritized = [];
            const others = [];

            if (count === 18) {

                for (const s of slots) {
                    const ord = Number(s?.order);
                    if (Number.isFinite(ord) && ord > 0) {
                        prioritized.push(s);
                    } else {
                        others.push(s);
                    }
                }

                // Сортируем приоритетные по order DESC (и по id DESC как тай-брейкер)
                prioritized.sort((a, b) => {
                    const ao = Number(a?.order) || 0;
                    const bo = Number(b?.order) || 0;
                    if (ao !== bo) return ao - bo; // по возрастанию order
                    const aid = Number(a?.id) || 0;
                    const bid = Number(b?.id) || 0;
                    return aid - bid; // по возрастанию id (тай-брейкер)
                });

                // Перемешиваем остальных (Fisher–Yates)
                for (let i = others.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [others[i], others[j]] = [others[j], others[i]];
                }

                // Склеиваем: приоритетные сначала, затем рандомные остальные
                const combined = prioritized.concat(others);

                // Берём нужное количество и приводим к нужному формату
                return combined.slice(0, count).map(slot => {
                    const primaryPath = slot.preview_url;
                    return {
                        imagePrimary: primaryPath,
                        to: `/slots/game/${slot.id}`,
                        id: slot.id,
                        isB2B: false,
                        provider: 'mobule',
                    };
                });
            } else {
                const shuffled = slots.sort(() => 0.5 - Math.random());
                return shuffled.slice(0, count).map(slot => {
                    const primaryPath = slot.preview_url;
                    return {
                        imagePrimary: primaryPath,
                        to: `/slots/game/${slot.id}`,
                        id: slot.id,
                        isB2B: false, // универсально
                        provider: 'mobule',
                    };
                });
            }
        },

        handleSlotClick(slot) {
            console.log('slot id + :' +slot);
            // Универсальная обработка открытия слота с записью провайдера в localStorage
            const provider = slot.isB2B ? 'b2b' : 'mobule';
            console.log(provider)
            localStorage.setItem(`slotSource_mobule_${slot.id}`, 'mobule');

            // this.$router.push({ path: `/slots/game/${slot.id}` });
        },
    },

    async mounted() {
        this.matchHeight();
        await this.fetchRandomGames();
        await this.fetchLastGames();
    },
};

</script>

<style lang="scss" scoped>
*{
    border-color: initial !important;
}
.slots-banner-wrapper {
    position: static;
    width: 100%;
    aspect-ratio: 22 / 9;
    max-height: 100%;
}

.content {
    overflow: hidden;
    height: 100%;
    transition: max-height 0.5s ease;

    @media (min-width: 1024px) {
        &.is_open {
            max-height: 65px !important;
        }
    }
}

@media (max-width: 1024px) {
    .content {
        border-radius: 0 !important;
    }

    .is_open {
        max-height: 45px !important;
    }
}
</style>