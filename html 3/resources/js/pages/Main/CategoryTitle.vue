<template>
    <div class="header">
        <h2 class="text-[18px] lg:text-[24px] font-[Inter] uppercase text-[var(--color-text)] font-bold">
            {{ title }}
        </h2>
        <slot></slot>
    </div>
</template>

<script>
import IconArrow from "@/components/icons/IconArrow.vue";
import Button from "@/components/ui/Button.vue";

export default {
    components: { IconArrow, Button },
    props: {
        isOpen: Boolean,
        onOpen: Function,
        title: String,
    },
};
</script>

<style lang="scss" scoped>
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    .text_button {
        justify-content: center;
        font-family: "Rubick Medium", sans-serif;
        font-weight: 500;
        font-size: 16px;
        width: 144px;
        text-transform: uppercase;
        justify-content: space-between;
    }

    svg {
        flex: none;
        transition: all 0.4s ease;
    }
    .active {
        svg {
            transform: rotate(180deg);
        }
    }
    @media (max-width: 1024px) {
        .text_button {
            width: calc(120px + 44 * ((100vw - 390px) / 1024));
        }
    }
    @media (max-width: 768px) {
        .text_button {
            display: none;
        }
    }
}
</style>
