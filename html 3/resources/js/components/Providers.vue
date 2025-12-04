<template>
    <div class="providers-container">
        <div class="providers-container__header">
            <CategoryTitle title="Провайдеры" />
        </div>
        <swiper
            :modules="[Autoplay]"
            :loop="true"
            :slides-per-view="5.3"
            :slides-per-group="1"
            :space-between="0"
            :centered-slides="false"
            :speed="3000"
            :autoplay="{ delay: 3000, disableOnInteraction: false, pauseOnMouseEnter: true }"
            :breakpoints="breakpoints"
            :observer="true"
            :observe-parents="true"
            :loop-additional-slides="2"
            @swiper="onSwiperReady"
        >
            <swiper-slide
                v-for="(provider, index) in providers"
                :key="index"
                class="providers__item"
                @click.native="navigateToSlots(provider.title)"
            >
                <img style="width: 190px !important;"
                     :src="provider.img ? provider.img : '/images/default-provider.png'" alt="provider" />
            </swiper-slide>
        </swiper>
    </div>
</template>

<script>
import axios from "axios";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import CategoryTitle from "../pages/Main/CategoryTitle.vue";
import { Autoplay } from "swiper/modules";


export default {
    components: {
        Swiper,
        SwiperSlide,
        CategoryTitle
    },
    data() {
        return {
            Autoplay,
            providers: [],
            breakpoints: {
                0: { slidesPerView: 3.3, spaceBetween: 12 },
                640: { slidesPerView: 3.3, spaceBetween: 12 },
                1024: { slidesPerView: 5.3, spaceBetween: 12 },
                1280: { slidesPerView: 5.3, spaceBetween: 12 }
            },
        };
    },
    async created() {
        await this.fetchProviders();
    },
    methods: {
        onSwiperReady(swiperInstance) {
            this.swiper = swiperInstance;
            this.swiper.autoplay.start();
        },
        navigateToSlots(provider) {
            this.$router.push({ name: "slots", query: { provider } });
        },

        async fetchProviders() {
            try {
                const response = await axios.get(`/api/mobule/providers`);

                if (response.data && Array.isArray(response.data)) {
                    // Обработка провайдеров с асинхронной проверкой изображений
                    this.providers = await Promise.all(
                        response.data.map(async (provider) => {
                            const formattedTitle = provider.title.replace(/[^a-zA-Z0-9]/g, "");
                            const svgPath = `/assets/image/providers/${formattedTitle}.svg`;
                            const pngPath = `/assets/image/providers/${formattedTitle}.png`;

                            // Проверка наличия картинки
                            const imgPath = (await this.checkImageExists(svgPath))
                                ? svgPath
                                : (await this.checkImageExists(pngPath))
                                    ? pngPath
                                    : "/assets/image/soon.png";

                            return {
                                title: provider.title,
                                img: imgPath,
                                count: provider.count
                            };
                        })
                    );

                    // Обновляем слайдер
                    this.$nextTick(() => {
                        if (this.$refs.swiper && this.$refs.swiper.swiper) {
                            this.$refs.swiper.swiper.update();
                            this.$refs.swiper.swiper.slideTo(0, 0);
                        }
                    });
                } else {
                    console.error("Неверный формат данных провайдеров");
                }
            } catch (error) {
                console.error("Ошибка при получении данных провайдеров:", error);
            }
        },

        checkImageExists(path) {
            return new Promise((resolve) => {
                const img = new Image();
                img.src = path;
                img.onload = () => resolve(true); // Картинка загружена успешно
                img.onerror = () => resolve(false); // Картинка не существует
            });
        }


    }
};
</script>

<style scoped lang="scss">
.providers-container {
    padding: 24px 0;
    background: rgba(31, 27, 41, 0.5215686275);
    gap: 24px;
    border-radius: 32px;

    .swiper {
        margin-left: 17px;
    }

    .providers-container__header {
        margin-bottom: 24px;
        padding-left: 15px;
    }

    .providers__list {
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;

        display: flex;

        &:deep(.swiper-wrapper) {
            padding: 10px 0;
        }

        .providers__item {
            min-width: 185px;
            width: auto;
            /* background-color: rgba(31, 27, 41, 0.5); */
            /* border-radius: 16px; */
            /* padding: 12px; */
            cursor: pointer;

            img {
                display: block;
                width: 100%;
                height: auto;
                max-height: 100%;
                border-radius: 16px;
                object-fit: cover;
                transition: all 0.3s ease;
                box-shadow: 0 0 12px rgba(255, 255, 255, 0);
            }

            &:hover {
                img {
                    box-shadow: 0 0 12px rgba(255, 255, 255, 0.2);
                }
            }
        }
    }

    @media (max-width: 1023px) {
        border-radius: 0;

        .providers__item {
            width: 136px;
            height: 72px;
        }
    }

    @media (min-width: 1024px) {
        .providers__item {
            width: 206px;
            height: 110px;
        }
    }
}
</style>