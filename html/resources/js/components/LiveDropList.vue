<template>
    <div :class="{ is_open: isOpen }" class="live-drops lg:p-4 rounded-[24px]">
        <div class="flex justify-between items-center">
            <h2 class="uppercase text-[var(--color-text)] font-bold font-['Oswald']">Лента выигрышей</h2>

            <Button variant="default" @click.native="onOpen"
                class="text_button sm:text-[16px] bg-[var(--color-primary)] text-white px-2 sm:px-4 rounded-[8px] flex items-center gap-2 sm:gap-4 h-[34px]">
                {{ !isOpen ? "Свернуть" : "Развернуть" }}
            </Button>
        </div>

        <div class="live-drops__items">
            <div name="slide-right" mode="out-in">
                <router-link draggable="false" v-for="(h, index) in filteredHistory" :key="index"
                    :to="'/slots/game/' + h.game_id" class="live-drop">
                    <img class="live-drop__img" :src="h.image" draggable="false" />
                    <div class="live-drop__info">
                        <span class="live-drop__number">{{ h.win.toFixed(2) }} ₽</span>
                        <span class="live-drop__username">
                            {{ h.username.length > 15 ? h.username.substring(0, 15) + '..' : h.username }}
                        </span>
                        <span class="live-drop__slot">
                            {{ (h.slot_name && h.slot_name.length > 15) ? h.slot_name.substring(0, 12) + '..' : h.slot_name }}
                        </span>
                    </div>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import Button from "@/components/ui/Button.vue";

export default {
    components: {
        Button
    },
    data() {
        return {
            isOpen: false,
            history: [],
            filteredHistory: [],
            pendingWins: [],
            placeholderImage: '/assets/image/soon.png',
            isLoading: true,
            animationDelay: 500,
            intervalId: null // ID интервала
        };
    },
    created() {
        this.loadFromStorage();
        this.setupSocketListeners();
    },
    methods: {
        loadFromStorage() {
            try {
                const savedHistory = localStorage.getItem('slotsHistory');
                if (savedHistory) {
                    const parsedHistory = JSON.parse(savedHistory);
                    if (Array.isArray(parsedHistory) && parsedHistory.length > 0) {
                        this.history = parsedHistory;
                        this.filteredHistory = parsedHistory;
                        this.isLoading = false;
                    }
                }
            } catch (error) {
                console.error('Error loading history:', error);
                this.clearHistory();
            }
        },

        setupSocketListeners() {
            this.$socket.on("slotsHistory", (winData) => {
                if (winData.win >= 1) {
                    winData.uniqueKey = this.generateUniqueId();
                    this.history.unshift(winData);

                    localStorage.setItem('slotsHistory', JSON.stringify(this.history));

                    if (this.isLoading) {
                        this.filteredHistory.unshift(winData);
                        if (this.filteredHistory.length >= 4) {
                            this.isLoading = false;
                        }
                    } else {
                        this.pendingWins.push(winData);
                        if (!this.intervalId) {
                            this.startProcessingWins();
                        }
                    }
                }
            });
        },


        clearHistory() {
            this.history = [];
            this.filteredHistory = [];
            localStorage.removeItem('slotsHistory');
        },

        generateUniqueId() {
            const generatedIds = new Set();

            function generateUniqueId() {
                let newId;
                do {
                    newId = Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                } while (generatedIds.has(newId));

                generatedIds.add(newId);
                return newId;
            }
        },

        startProcessingWins() {
            this.intervalId = setInterval(() => {
                if (this.pendingWins.length > 0) {
                    const nextWin = this.pendingWins.shift();

                    this.filteredHistory.unshift(nextWin);

                    if (this.filteredHistory.length > 10) {
                        this.filteredHistory.pop();
                    }
                } else {
                    clearInterval(this.intervalId);
                    this.intervalId = null;
                }
            }, this.animationDelay);
        },

        getPlaceholder() {
            return this.placeholderImage;
        },

        onImageError(event) {
            event.target.src = this.getPlaceholder();
        },

        onOpen() {
            this.isOpen = !this.isOpen;
        },
    },
    watch: {
        history: {
            handler(newHistory) {
                if (newHistory.length > 100) {
                    this.history = newHistory.slice(0, 100);
                    localStorage.setItem('slotsHistory', JSON.stringify(this.history));
                }
            },
            deep: true
        }
    },
    // mounted() {
    //     this.$socket.emit("getHistory");
    //     this.setupSocketListeners();
    // },

    beforeDestroy() {
        this.$socket.off("getHistory");
        this.$socket.off("slotsHistory");
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    }
};
</script>

<style lang="scss" scoped>
.live-drops {
    margin-top: 0;
    transition: max-height 0.5s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
    max-height: 400px;
    flex: 1;
    padding: 0;
    z-index: 1;

    @media (max-width: 1023px) {
        padding: 0 15px;
    }

    h2 {
        font-size: 24px;
        font-weight: 700;
        line-height: 29.05px;
    }

    &__items {
        transition: all 0.5s ease;
        display: flex;
        flex-direction: row;
        /* Установлено направление по горизонтали */
        height: 124px;
        gap: 12px;
        border-radius: 20px;
        overflow-x: hidden;
        /* Отключить прокрутку */
        margin-top: 16px;
        position: relative;

        @media (max-width: 1023px) {
            height: 96px;
        }

        &>div {
            display: flex;
            gap: 12px;
        }

        /* Отключение скроллинга при использовании колеса мыши или touch-событий */
        pointer-events: none;

        /* Отключение скроллбара */
        &::-webkit-scrollbar {
            display: none;
        }

        a {
            transition: transform 0.4s, opacity 0.4s;
            z-index: 2;

            &:hover {
                box-shadow: 0 0 18.4px 0 #FFFFFF4A;
                transition: box-shadow 0.3s ease;
            }
        }

        @media (max-width: 1023px) {
            margin-top: 14px;
        }
    }

    &.is_open {
        max-height: 36px;
    }

    @media (max-width: 1023px) {
        &.is_open {
            max-height: 36px;
        }

        h2 {
            font-size: 18px;
        }
    }
}

.slide-right-enter-active,
.slide-right-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.slide-right-enter {
    transform: translateX(-100%);
}

.slide-right-leave-to {
    transform: translateX(100%);
}

.live-drop {
    display: flex;
    align-items: flex-start;
    background-color: rgb(255, 255, 255, 0.05);
    padding: 8px;
    gap: 10px;
    font-size: 18px;
    border: 1px solid rgb(255, 255, 255, 0.15);
    border-radius: 24px;
    font-weight: 500;
    font-family: Oswald !important;
    text-decoration: none;
    flex-shrink: 0;
    user-select: none;

    &__img {
        user-select: none;
        width: 82px;
        height: 100%;
        aspect-ratio: 122/182;
        object-fit: cover;
        border-radius: 16px;

        @media (max-width: 1023px) {
            width: 60px;
        }
    }

    &__info {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        width: 158px;
        padding-top: 8px;
        padding-bottom: 8px;

        @media (max-width: 1023px) {
            padding: 0;
        }
    }

    &__number {
        margin-bottom: 6px;
        font-family: 'Inter', sans-serif;
        font-size: 24px;
        font-weight: 700;
        line-height: 29.05px;
        text-align: left;
        text-underline-position: from-font;
        text-decoration-skip-ink: none;
        color: #fff;

        @media (max-width: 1023px) {
            font-size: 18px;
            line-height: 21.78px;
        }
    }

    &__username {
        margin-bottom: 16px;
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        font-weight: 500;
        line-height: 19.36px;
        text-align: left;
        text-underline-position: from-font;
        text-decoration-skip-ink: none;
        color: #9E9AA1;

        @media (max-width: 1023px) {
            font-size: 14px;
            line-height: 16.94px;
        }
    }

    &__slot {
        font-family: 'Inter', sans-serif;
        font-size: 18px;
        font-style: italic;
        font-weight: 400;
        line-height: 21.78px;
        text-align: left;
        text-underline-position: from-font;
        text-decoration-skip-ink: none;
        color: #fff;

        @media (max-width: 1023px) {
            font-size: 16px;
            font-weight: 400;
            line-height: 19.36px;
        }
    }
}
</style>
