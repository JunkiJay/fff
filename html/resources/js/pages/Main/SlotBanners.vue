<template>
    <div class="rounded-[24px] md:border border-[var(--color-text)] flex-none overflow-hidden">
        <VueperSlides :infinite="true">
            <VueperSlide v-for="banner in banners" :key="banner.id">
                <template #content>
                    <router-link style="-webkit-user-drag: none" class="slide relative flex w-full h-full p-6" :to="banner.to">
                        <img
                            style="-webkit-user-drag: none"
                            :src="banner.image"
                            class="absolute object-cover top-0 left-0 w-full h-full user-select-none"
                            alt="img"
                        />
                    </router-link>
                </template>
            </VueperSlide>
        </VueperSlides>
    </div>
</template>

<script>
import { VueperSlides, VueperSlide } from "vueperslides";
import banner1Img from "@img/banners/banner1.webp";
import banner2Img from "@img/banners/banner2.webp";
import banner3Img from "@img/banners/banner3.webp";

export default {
    components: { VueperSlides, VueperSlide },

    data() {
        return {
            banners: [
                {
                    id: 1,
                    image: banner1Img,
                    text: "Получай ежедневно бонус до 100 ₽",
                    button_text: "Получить",
                    to: "/bonus",
                },
                {
                    id: 2,
                    image: banner2Img,
                    text: "Получай ежедневно бонус до 100 ₽",
                    button_text: "Получить",
                    to: "/bonus",
                },
                {
                    id: 3,
                    image: banner3Img,
                    text: "Получай ежедневно бонус до 100 ₽",
                    button_text: "Получить",
                    to: "/bonus",
                },
            ],
        };
    },
};
</script>

<style lang="scss" scoped>
.slide {
    h2 {
        font-size: calc(24px + 24 * (100vw - 390px) / 880);
    }
}
</style>
