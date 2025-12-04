<template>
    <div class="swiper-container my-slider">
        <swiper
            ref="slotsBannerSwiper"
            :modules="[Autoplay]"
            v-bind="swiperOptions"
            @swiper="onSwiperReady"
            @slideChange="onSlideChange"
        >
            <swiper-slide v-for="(video, index) in videos" :key="video.id" class="swiper-slide">
                <div class="slide" v-show="video.src">

                    <!-- Видео или заглушка -->
                    <video
                        v-if="!video.error"
                        ref="video"
                        :src="video.src"
                        class="slide-image"
                        loop
                        @error="handleVideoError(index)"
                        type="video/mp4"
                    ></video>
                    <img v-else :src="video.fallbackImage" loading="lazy" class="slide-image" alt="Fallback Image" />

                    <div class="buttons-container">
                        <button
                            v-for="button in video.buttons"
                            :key="button.id"
                            :class="button.bg"
                            @click="navigateTo(button.to)"
                        >
                            {{ button.text }}
                        </button>
                    </div>
                </div>
            </swiper-slide>
        </swiper>
    </div>
</template>

<script>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/swiper.css';

export default {
    components: {
        Swiper,
        SwiperSlide,
    },
    data() {
        return {
            Autoplay,
            swiperOptions: {
                slidesPerView: 1.3,
                spaceBetween: 10,
                centeredSlides: true,
                loop: true,
                autoplay: {
                    delay: 8000,
                    disableOnInteraction: true,
                },
                breakpoints: {
                    768: {
                        slidesPerView: 1.5,
                        spaceBetween: 10,
                    },
                    1024: {
                        slidesPerView: 2.2,
                        spaceBetween: 10,
                    },
                    1440: {
                        slidesPerView: 2.5,
                        spaceBetween: 10,
                    },
                },
            },
            videos: [
                {
                    id: 1, src: "/assets/banner/banner3.mp4", fallbackImage: "/assets/banner/banner3.jpg",
                    buttons: [
                        { id: 1, text: 'Участвовать', bg: 'bg-main', to: '/tournament' },
                        { id: 2, text: 'Подробнее', bg: 'bg-white/[25%]', to: '/tournament' }
                    ]
                },
                {
                    id: 2, src: "/assets/banner/banner2.mp4", fallbackImage: "/assets/banner/banner2.jpg",
                    buttons: [
                        { id: 1, text: 'Присоедениться', bg: 'bg-main', to: '/bonus' }
                    ]
                },
                {
                    id: 3, src: "/assets/banner/banner1.mp4", fallbackImage: "/assets/banner/banner1.jpg",
                    buttons: [
                        { id: 1, text: 'Вывести', bg: 'bg-main', to: '/withdraw' },
                        { id: 2, text: 'Пополнить', bg: 'bg-white/[25%]', to: '/pay' }
                    ]
                },
                {
                    id: 4, src: "/assets/banner/banner5.mp4", fallbackImage: "/assets/banner/banner1.jpg",
                    buttons: [
                        { id: 1, text: 'Забрать кешбэк', bg: 'bg-main', to: '/pay' }
                    ]
                },
            ],
        };
    },
    mounted() {
        this.manageVideoPlaybackMount();
    },
    methods: {
        onSwiperReady(swiperInstance) {
            this.swiper = swiperInstance;
            this.swiper?.update();
            this.swiper?.slideTo(0, 0);
            this.swiper?.autoplay?.start?.();
        },
        // onSlideChange уже существует в методах или добавьте реализацию
        // handleVideoError, navigateTo, manageVideoPlaybackMount — без изменений
    },
};
</script>