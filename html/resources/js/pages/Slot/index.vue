<template>
    <div class="flex flex-col w-full gap-4">
        <div class="header grid grid-cols-3 place-items-center w-full">
            <router-link
                class="header__back bg-[var(--color-primary)] justify-self-start h-[40px] rounded-[8px] text-white text-[16px] flex items-center font-[Oswald]"
                to="/slots">
                <Button
                    :isDisabled="false"
                    class="w-[120px] header__back justify-center h-[40px] rounded-[8px] text-white text-[16px] px-10 flex items-center font-[Oswald]">
                    <Arrow />
                    <span>Назад</span>
                </Button>
            </router-link>

            <div class="header__game h-[40px] flex items-center gap-2">
                <img class="" :src="logoSvg" alt="logo" />
                <span class="text-[var(--color-text)] text-[18px] font-[Oswald]">{{ slot?.title ??
                    formatSlotName(slot?.gm_url) }}</span>
            </div>

            <Button variant="default" @click.native="toggle"
                :isDisabled="false"
                class="header__toggle justify-self-end bg-[var(--color-primary)] h-[40px] aspect-square rounded-[8px] text-white text-[16px] p-2 flex items-center font-[Oswald]">
                <img class="h-full" src="/assets/image/fullscreen.svg" />
            </Button>
        </div>

        <div class="game-container w-full rounded-[16px]">
            <fullscreen v-if="slotUrl" v-model="isFullscreen" style="height: 100%">
                <iframe id="iframe_slot"
                        scrolling="no"
                        frameborder="0"
                        webkitallowfullscreen="true"
                        allowfullscreen
                        allow="autoplay; fullscreen"
                        mozallowfullscreen="true"
                        :src="slotUrl"
                ></iframe>
            </fullscreen>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Button from "@/components/ui/Button.vue";
import Arrow from "@/components/ui/ArrowSVG.vue";
import logoSvg from "@img/logo.svg";
import { ref } from "vue";
export default {
    components: {
        Button,
        Arrow,
    },
    data() {
        const fullscreen = ref(false)

        return {
            isFullscreen: fullscreen,
            logoSvg,
            slot: null,
            slotUrl: null,
            type: "real",
            noty: {
                mess: null,
                type: null,
            },
        };
    },

    mounted() {
        // Валидация ID из роута перед использованием
        const routeId = this.$route.params.id;
        if (routeId && typeof routeId !== 'function' && !isNaN(parseInt(routeId))) {
            if (this.isAuthenticated()) {
                this.getSlot(routeId);
            }
        } else {
            console.error('Invalid route ID:', routeId, typeof routeId);
            this.$router.push("/slots");
        }
        if (this.isMobileDevice()) this.isFullscreen = true;
    },
    watch: {
        '$store.state.user'(newVal) {
            if (newVal && !this.slot) {
                const routeId = this.$route.params.id;
                if (routeId && typeof routeId !== 'function' && !isNaN(parseInt(routeId))) {
                    this.getSlot(routeId);
                }
            }
        }
    },

    methods: {
        async getSlot(id) {
            // Валидация ID - должен быть числом или строкой с числом
            if (!id || id === undefined || id === null) {
                console.error('Slot ID is missing:', id);
                this.$router.push("/slots");
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "ID слота не указан",
                    type: "error",
                });
                return;
            }
            
            // Проверяем что ID это число (или строка с числом), не функция
            if (typeof id === 'function') {
                console.error('Slot ID is a function, not a number:', id);
                this.$router.push("/slots");
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Неверный ID слота",
                    type: "error",
                });
                return;
            }
            
            const numericId = parseInt(id);
            if (isNaN(numericId) || numericId <= 0) {
                console.error('Invalid slot ID:', id, typeof id);
                this.$router.push("/slots");
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Неверный ID слота",
                    type: "error",
                });
                return;
            }
            
            // Используем числовой ID
            id = numericId;
            
            try {
                if (!this.isAuthenticated()) {
                    this.$router.push("/login");
                    this.$emitter.emit("noty", {
                        title: "Требуется авторизация",
                        text: "Пожалуйста, войдите в аккаунт, чтобы играть в слоты.",
                        type: "warning",
                    });
                    return;
                }

                if (id === 'live') {
                     if (this.$store.state.user && this.$store.state.user.balance < 1000) {
                         this.$router.push("/pay");
                         this.$emitter.emit("noty", {
                             title: "Необходим депозит",
                             text: "Необходим депозит 1000 Руб для входа в игру!",
                             type: "warning",
                         });
                         return;
                     } else {
                         this.$router.push("/slots");
                         this.$emitter.emit("noty", {
                             title: "Попробуйте позже!",
                             text: "На данный момент игра недоступна! Попробуйте позже!",
                             type: "warning",
                         });
                         return;
                     }
 
                 }

                // Проверяем, является ли это демо-режимом
                if (this.$route.query.type === "demo") this.type = "demo";

                // Получаем source по уникальному ключу: сначала b2b, потом mobule, иначе fallback
                let source = 'b2b';
                if (localStorage.getItem(`slotSource_b2b_${id}`)) {
                    source = 'b2b';
                } else if (localStorage.getItem(`slotSource_mobule_${id}`)) {
                    source = 'mobule';
                }
                // Не удаляем ключи! Оставляем их в localStorage для корректной работы после F5 или прямого перехода

                const apiUrl = source === 'b2b' ? `/b2b/load` : `/mobule/load`;

                // Основной запрос для загрузки слота
                const { data } = await axios.post(apiUrl, { slot_id: id, mobile: this.isMobileDevice() });

                if (data.error) {
                    this.$router.push("/slots");
                    if (data.message) {
                        this.$emitter.emit("noty", {
                            title: "Ошибка",
                            text: data.message,
                            type: "error",
                        });
                    }
                    return;
                }

                // Проверяем что данные пришли корректно
                if (!data || !data.link) {
                    throw new Error('Неверный ответ от сервера');
                }

                // Формируем slot из ответа load: копируем все поля из data, добавляем provider info
                this.slot = {
                    ...data,
                    id: id, // id из маршрута всегда актуален
                    provider: source,
                    title: this.formatSlotName(data.title || '')
                };

                console.log('slot:', this.slot)

                // Используем правильную ссылку в зависимости от режима
                // НЕ добавляем timestamp к URL - это может вызывать ошибки 400
                this.slotUrl = this.type === 'demo' ? (data.demo_link || data.link) : data.link;
            } catch (error) {
                console.error("Ошибка при загрузке слота:", error);
                
                // Показываем пользователю понятное сообщение
                let errorMessage = "Ошибка при загрузке слота";
                
                if (error.response) {
                    // Ошибка от сервера
                    if (error.response.status === 404) {
                        errorMessage = "Слот не найден";
                    } else if (error.response.status === 401) {
                        errorMessage = "Требуется авторизация";
                    } else if (error.response.data && error.response.data.message) {
                        errorMessage = error.response.data.message;
                    }
                } else if (error.message) {
                    errorMessage = error.message;
                }
                
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: errorMessage,
                    type: "error",
                });
                
                // Перенаправляем на список слотов
                this.$router.push("/slots");
            }
        },

        isAuthenticated() {
            return !!this.$store.state.user;
        },


        formatSlotName(name) {
            console.log('slot name' + name)
             const cleanedName = name?.replace('.game', '');
            return cleanedName?.replace(/([a-z])([A-Z])/g, '$1 $2');
         },

        toggle() {
            this.isFullscreen = !this.isFullscreen;
        },

        isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }
    }
};
</script>

<style lang="scss" scoped>
.game-container {
    position: relative;
    width: 100%;
    height: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
    border-radius: 16px;
    z-index: 9999 !important;

}

::v-deep(.fullscreen) {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 9999 !important;
    background: rgba(0, 0, 0, 1); // Optional: darken background
}

@supports (height: 100dvh) {
    ::v-deep(.fullscreen) {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100dvh !important;
        z-index: 9999 !important;
        background: rgba(0, 0, 0, 1); // Optional: darken background
    }
}

@supports (-webkit-touch-callout: none) {
    .game-container {
        position: relative;
        width: 100%;
        height: 100%;
        aspect-ratio: 16/9;
        overflow: hidden;
        border-radius: 16px;
    }
}

iframe {
    width: 100%;
    height: 100%;
    border-radius: inherit;
}

.header {
    &__back {
        svg {
            width: 40px;
            height: 40px;
            stroke: white;
            display: none;
        }
    }
}

@media (max-width: 568px) {
    .header {
        &__game {
            padding: 0 5px;

            span {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            img {
                display: none;
            }
        }

        &__back {
            padding: 0;
            width: 100%;
            max-width: 100px;
            justify-content: center;
            width: 40px;

            svg {
                display: block;
            }

            span {
                display: none;
            }
        }
    }
}
</style>
