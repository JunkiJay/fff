<template>
    <div class="content">
        <h6 class="text-[24px] text-[var(--color-text)] font-[500] font-['Oswald']">Система уровней</h6>

        <hr class="my-6 border-[var(--color-secondary-light)]" />

        <div class="content__levels flex overflow-x-auto w-full gap-4">
            <div
                class="h-[70px] bg-[var(--color-secondary-light)] rounded-[16px] p-3 flex flex-col grow shrink-0"
                v-for="(level, index) in levels"
                :key="index"
            >
                <div class="flex text-[18px] justify-between w-full gap-5">
                    <div class="text-[var(--color-text)]">{{ level.title }}</div>
                    <div class="text-[#52D233]" :style="{ color: level.background }">{{ level.reward }}</div>
                </div>

                <div class="flex justify-between w-full text-[14px] gap-5">
                    <div class="text-[var(--color-text-secondary)]">{{ `${total > level.goal ? level.goal : total}/${level.goal}` }}</div>
                    <div class="text-[var(--color-text-secondary)]">{{ level.goal }} репостов</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["total", "levels"],

    components: {},

    data() {
        return {};
    },

    methods: {},
};
</script>
<style lang="scss" scoped>
@media (max-width: 1024px) {
    .content {
        background-color: var(--color-content) !important;
        border-radius: 24px !important;
        padding: 16px !important;
        &__levels {
            &::-webkit-scrollbar {
                height: 0;
                width: 0;
            }
        }
    }
}
</style>
