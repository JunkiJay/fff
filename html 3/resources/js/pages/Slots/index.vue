<template>
    <div class="flex flex-col w-full mt-8 md:mt-0 gap-8">
        <LiveDropList />

        <div class="content flex flex-col w-full gap-4 bg-[var(--color-content)]">
            <div class="grid lg:flex items-center justify-between gap-4">
                <CategoryTitle class="col-span-2 whitespace-nowrap" title="Stimule SLOTS" />

                <input
                    class="hidden md:block w-[300px] h-[48px] px-3 rounded-[8px] bg-[#3E3B47] font-rubik text-[18px] font-medium leading-[21.78px] text-white placeholder-opacity-60 placeholder-white mr-auto"
                    type="search" placeholder="Поиск" v-model="searchQuery" />

                <div class="content__providers hidden md:flex col-span-1 relative justify-end"
                    :class="providers.status && 'active'">
                    <Button variant="default" @click.native="onClickProvider" :isDisabled="false"
                        class="text-[16px] bg-[var(--color-primary)] text-white px-4 rounded-[8px] flex items-center gap-2 lg:gap-12 font-[Oswald] uppercase h-[34px]">
                        {{ providers.current.title === "Все провайдеры" ? "Все провайдеры" : providers.current.title }}
                        <IconArrow />
                    </Button>

                    <div class="provider absolute top-[calc(100%+10px)] w-[260px] py-4 px-3 border-2 border-[#FFFFFF25] bg-[#1F1B29] rounded-[8px] z-20"
                        v-if="providers.status" v-on-clickaway="closeProviders">
                        <ul class="provider__list">
                            <li class="flex items-center justify-between" v-for="(item, index) in providers.items"
                                :key="index" @click="selectProvider(item)">
                                <Button :isDisabled="false" variant="provider" class="flex items-center px-1 h-full !rounded-[5px]">
                                    <div class="flex items-center">
                                        <img v-if="false" class="provider__icon" :src="item.icon" alt="logo" />
                                        <img v-if="item.title !== 'Все провайдеры'" class="provider__icon"
                                            :src="`img/providers/${item.title}.svg`" alt="logo" />
                                        <span
                                            class="text-white truncate whitespace-nowrap overflow-hidden max-w-[90px] md:max-w-none">{{
                                                item.title }}</span>
                                    </div>
                                    <span
                                        class="h-5 px-2 rounded-[150px] text-white font-[Inter] font-bold text-[10px] flex items-center justify-center bg-[var(--color-primary)]">
                                        {{ item.count }}
                                    </span>
                                </Button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Мобильная версия -->
            <div class="flex gap-2 md:hidden w-full">
                <input
                    class="w-1/2 h-[34px] px-3 rounded-[8px] bg-[#3E3B47] font-rubik text-[12px] font-medium leading-[14.22px] text-white placeholder-opacity-60 placeholder-white"
                    type="search" placeholder="Поиск" v-model="searchQuery" />

                <div class="content__providers flex relative justify-end w-1/2" :class="providers.status && 'active'">
                    <Button :isDisabled="false" variant="default" @click.native="onClickProvider"
                        class="text-[12px] bg-[var(--color-primary)] text-white px-3 rounded-[8px] flex items-center gap-2 font-[Oswald] uppercase h-[34px] min-w-full md:min-w-auto">
                        {{ providers.current.title === "Все провайдеры" ? "Все провайдеры" : providers.current.title }}
                        <IconArrow />
                    </Button>

                    <div class="provider absolute top-[calc(100%+10px)] w-full py-4 px-3 border-2 border-[#FFFFFF25] bg-[#1F1B29] rounded-[8px] z-20"
                        v-if="providers.status" v-on-clickaway="closeProviders">
                        <ul class="provider__list">
                            <li class="flex items-center justify-between" v-for="(item, index) in providers.items"
                                :key="index" @click="selectProvider(item)">

                                <Button variant="provider"
                                    :isDisabled="false"
                                    class="flex items-center px-1 h-full !rounded-[5px] button button_provider min-w-full">
                                    <div class="flex items-center">
                                        <img v-if="false" class="provider__icon" :src="item.icon" alt="logo" />
                                        <img v-if="item.title !== 'Все провайдеры'" class="provider__icon"
                                            :src="`img/providers/${item.title}.svg`" alt="logo" />
                                        <span v-if="false"
                                            class="text-white truncate whitespace-nowrap overflow-hidden max-w-[90px] md:max-w-none">{{
                                                item.title }}</span>
                                        <span
                                            class="text-white truncate whitespace-nowrap overflow-hidden max-w-[70px] md:max-w-none text-white truncate whitespace-nowrap overflow-hidden max-w-[70px] md:max-w-none">{{
                                                item.title }}</span>
                                    </div>
                                    <span
                                        class="h-5 px-2 rounded-[150px] text-white font-[Inter] font-bold text-[10px] flex items-center justify-center bg-[var(--color-primary)]">
                                        {{ item.count }}
                                    </span>
                                </Button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div v-if="loading && page === 1" class="initial-loader">Загрузка...</div>

            <div v-else class="flex">
                <div class="slots__empty" v-if="!slots.length && !loading"></div>
                <div class="category grid gap-2">
                    <div v-for="slot in slots" :key="slot.id"
                        class="slot-card aspect-[166/222] rounded-[16px] overflow-hidden relative">
                        <div class="slot-card__wrapper">
                            <!-- Контент слота -->
                            <div class="slot-content" @click="handleSlotClick(slot)">
                                <img class="w-full h-full object-cover" :src="slot.preview_url" alt="Slot Image" />
                                <div class="category__description">
                                    <p>{{ slot.title ?? formatSlotName(slot.gm_url) }}</p>
                                    <Play />
                                    <!-- Кнопка "DEMO" -->
                                    <div class="demo-button mt-10" @click.prevent.stop="handleDemoClick(slot)">
                                        <Button variant="slot"
                                            :isDisabled="false"
                                            class="border-[2px] border-[#ffffff50] rounded-[8px] w-[60px] h-[32px] text-[12px] font-weight-bold text-white absolute top-2/3 left-1/2 transform -translate-x-1/2">
                                            Demo
                                        </Button>
                                    </div>
                                    <span
                                        class="text-white text-[10px] font-medium tracking-[0.14em] mb-4 leading-none pb-[5px] uppercase z-[1]">
                                        {{ slot.provider ?? slot.gr_title }}
                                    </span>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="loading && page > 1" class="load-more-loader">Подгружается...</div>
        </div>

        <div ref="infiniteScrollTrigger" style="height: 1px;"></div>
    </div>
</template>

<script>
import axios from "axios";
import { debounce } from "lodash";
import CategoryTitle from "../../pages/Main/CategoryTitle.vue";
import Button from "@/components/ui/Button.vue";
import LiveDropList from "@/components/LiveDropList.vue";
import IconArrow from "@/components/icons/IconArrow.vue";
import Search from "@/components/ui/SearchSVG.vue";
import Play from "@/components/ui/PlaySVG.vue";
import prod1Img from "@img/prod1.png";

export default {
    components: {
        CategoryTitle,
        Button,
        Play,
        LiveDropList,
        IconArrow,
        Search,
    },

    data() {
        return {
            page: 1,
            last_page: null,
            search: "",
            searchQuery: "",
            slots: [],
            perPage: 24,
            loading: true,
            providers: {
                status: false,
                current: {
                    title: "Все провайдеры",
                    icon: null,
                    count: null,
                    source: "all"
                },
                items: [],
            },
            isAuthenticated: false,
            observer: null,
        };
    },

    async mounted() {
        console.log('start');

        await this.loadProviders();
        const providerFromQuery = this.$route.query.provider;
        if (providerFromQuery) {
            const matchedProvider = this.providers.items.find(
                (provider) => provider.title === providerFromQuery
            );
            if (matchedProvider) {
                this.selectProvider(matchedProvider);
            } else {
                this.selectProvider(this.providers.items[0]);
            }
        } else {
            this.selectProvider(this.providers.items[0]);
        }

        await this.getSlots();
        this.checkAuthentication();
        this.createObserver();
    },

    beforeDestroy() {
        this.destroyObserver();
    },

    methods: {
        formatSlotName(name) {
            const cleanedName = name.replace('.game', '');

            return cleanedName?.replace(/([a-z])([A-Z])/g, '$1 $2');
        },

        async getSlots() {
            try {
                const isAllProviders = this.providers.current.title === "Все провайдеры";
                let slotsData = [];

                if (isAllProviders) {
                    // Запрос для всех провайдеров
                    const [mobuleResponse, b2bResponse] = await Promise.all([
                        axios.get('/api/mobule/slots', {
                            params: {
                                search: this.search,
                                page: this.page,
                                per_page: this.perPage
                            }
                        }),
                        axios.get('/api/b2b/slots', {
                            params: {
                                search: this.search,
                                page: this.page,
                                per_page: this.perPage
                            }
                        })
                    ]);

                    // Обработка данных от обоих провайдеров
                    const mobuleSlots = mobuleResponse.data.data.map(slot => ({
                        ...slot,
                        isB2B: false
                    }));

                    const b2bSlots = b2bResponse.data.data.map(slot => ({
                        ...slot,
                        isB2B: true
                    }));

                    slotsData = [...mobuleSlots, ...b2bSlots];
                    this.last_page = Math.max(
                        mobuleResponse.data.last_page,
                        b2bResponse.data.last_page
                    );
                } else {
                    // Запрос для конкретного провайдера
                    const response = await axios.get(`/api/${this.providers.current.source}/slots`, {
                        params: {
                            provider: this.providers.current.title,
                            search: this.search,
                            page: this.page,
                            per_page: this.perPage
                        }
                    });

                    slotsData = response.data.data.map(slot => ({
                        ...slot,
                        isB2B: slot.provider_url === "b2b"
                    }));

                    this.last_page = parseInt(response.data.last_page);
                }

                // Обновление списка слотов
                this.slots = this.page === 1 ? slotsData : [...this.slots, ...slotsData];

            } catch (error) {
                console.error("Ошибка при получении списка игр:", error);
            } finally {
                this.loading = false;
            }
        },


        async loadProviders() {
            try {
                const [mobuleRes, b2bRes] = await Promise.all([
                    axios.get(`/api/mobule/providers`),
                    axios.get(`/api/b2b/providers`),
                ]);

                const providersData = [
                    ...mobuleRes.data.map((provider) => ({
                        ...provider,
                        source: "mobule",
                    })),
                    ...b2bRes.data.map((provider) => ({
                        ...provider,
                        source: "b2b",
                    })),
                ];

                const totalProvidersCount = providersData.reduce(
                    (total, provider) => total + provider.count,
                    0
                );

                this.providers.items = [
                    {
                        title: "Все провайдеры",
                        icon: prod1Img,
                        count: totalProvidersCount,
                        source: "all",
                    },
                    ...providersData.map((provider) => ({
                        title: provider.title,
                        icon: prod1Img,
                        count: provider.count,
                        source: provider.source,
                    })),
                ];
            } catch (error) {
                console.error("Ошибка при получении данных провайдеров:", error);
            }
        },



        createObserver() {
            const options = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1,
            };

            this.observer = new IntersectionObserver(this.handleIntersect, options);
            if (this.$refs.infiniteScrollTrigger) {
                this.observer.observe(this.$refs.infiniteScrollTrigger);
            }
        },

        destroyObserver() {
            if (this.observer && this.$refs.infiniteScrollTrigger) {
                this.observer.unobserve(this.$refs.infiniteScrollTrigger);
                this.observer.disconnect();
            }
        },

        handleIntersect(entries) {
            const entry = entries[0];
            if (entry.isIntersecting && !this.loading && (this.last_page === null || this.page < this.last_page)) {
                this.loading = true;
                this.page++;
                this.getSlots();
            }
        },

        onClickProvider() {
            this.providers.status = !this.providers.status;
        },

        closeProviders() {
            this.providers.status = false;
        },

        checkAuthentication() {
            this.isAuthenticated = !!this.$store.state.user;
        },

        selectProvider(selectedProvider) {
            this.providers.current = selectedProvider;
            this.page = 1;
            this.last_page = null;
            this.slots = [];
            this.loading = true;
            this.getSlots();
            this.closeProviders();

            // Перезапускаем наблюдатель после обновления данных
            this.destroyObserver();
            this.$nextTick(() => {
                this.createObserver();
            });

            // Обновляем URL с параметром выбранного провайдера
            // this.$router.replace({
            //     name: this.$route.name,
            //     query: { provider: selectedProvider.title },
            // });
        },

        handleInfinityScroll(isVisible) {
            if (isVisible && !this.loading && (this.last_page === null || this.page < this.last_page)) {
                this.loading = true;
                this.page++;
                this.getSlots();
            }
        },

        handleSlotClick(slot) {
            if (!this.isAuthenticated) {
                this.$emitter.emit("noty", {
                    title: "Необходима авторизация",
                    text: "Пожалуйста, войдите в аккаунт для открытия слота",
                    type: "error",
                });
            } else {
                const provider = slot.isB2B ? 'b2b' : 'mobule';
                console.log(provider)
                localStorage.setItem(`slotSource_${provider}_${slot.id}`, provider);
                this.$router.push({
                    path: `/slots/game/${slot.id}`
                });
            }
        },

        handleDemoClick(slot) {
            if (!this.isAuthenticated) {
                this.$emitter.emit("noty", {
                    title: "Необходима авторизация",
                    text: "Пожалуйста, войдите в аккаунт для открытия демо-версии слота",
                    type: "error",
                });
            } else {
                const provider = slot.isB2B ? 'b2b' : 'mobule';
                localStorage.setItem(`slotSource_${provider}_${slot.id}`, provider);
                this.$router.push({
                    path: `/slots/game/${slot.id}`,
                    query: { type: 'demo' }
                });
            }
        },

        debouncedSearch: debounce(function () {
            this.search = this.searchQuery;
            this.page = 1;
            this.last_page = null;
            this.slots = [];
            this.loading = true;
            this.getSlots();
        }, 500),

        onSearchInput() {
            this.debouncedSearch();
        },
    },

    watch: {
        searchQuery: 'onSearchInput'
    }
};
</script>

<style lang="scss" scoped>
.content {
    &__providers {
        button {
            justify-content: space-between;
            max-width: 230px;
            width: 100%;

            svg {
                transition: all 0.4s ease;
            }
        }

        &.active {
            button svg {
                transform: rotate(180deg);
            }
        }
    }
}

.provider {
    &__search {
        display: flex;
        align-items: center;
        padding: 6px;
        background-color: #3E3B47;

        input {
            width: 100%;
            font-family: "Inter";
            font-weight: 400;
            background-color: #3E3B47;
        }

        svg {
            stroke: #838383;
        }

        margin-bottom: 16px;
    }

    &__icon {
        width: 18px;
        height: 18px;
        margin-right: 8px;
    }

    &__list {
        li {
            height: 32px;

            &:first-child {
                font-family: "Inter";
                font-weight: 500;
            }
        }
    }
}

.category {
    width: 100%;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));

    .slot-card {
        .slot-content {
            height: 100%;
        }

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }

    .slot-card__wrapper {
        position: relative;
        overflow: hidden;
        transition: all 400ms ease;
        height: 100%;

        &:hover {
            transform: scale(1.05);
            cursor: pointer;

            .category__description {
                opacity: 1;
            }
        }

        .demo-button {
            z-index: 9999;
        }
    }

    &__description {
        position: absolute;
        left: 0;
        opacity: 0;
        top: 0;
        width: 100%;
        height: 100%;
        flex-direction: column;
        display: flex;
        transition: opacity 0.4s ease;
        justify-content: space-between;
        padding: 25px 4px 0px 4px;
        text-align: center;
        z-index: 10;
        color: #ffffff;

        svg {
            margin: auto;
            width: 42px;
            height: 42px;
        }

        p {
            font-size: 14px;
            color: #ffffff;
            font-weight: 700;
        }

        &::before {
            background-color: rgba(32, 43, 77, 0.9);
            content: "";
            height: 100%;
            left: 0;
            opacity: 0.85;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: -1;
        }

        button {
            transition: all 0.2s ease-out;
            opacity: 0.95;
        }

        span {
            font-size: 8px;
            font-weight: 500;
        }
    }

    @media (max-width: 568px) {
        grid-template-columns: repeat(3, 1fr);
    }

    @media (max-width: 320px) {
        grid-template-columns: repeat(2, 1fr);
    }
}

.initial-loader,
.load-more-loader {
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 16px;
    font-weight: bold;
    color: white;
    padding: 20px;
}

.initial-loader {
    font-size: 20px;
}

.load-more-loader {
    margin-top: 10px;
}
</style>
