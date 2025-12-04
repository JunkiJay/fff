<template>
    <div class="controls">
        <button class="controls__button" @click="scrollLeft('slotsContainer')">&larr;</button>
        <button class="controls__button" @click="scrollRight('slotsContainer')">&rarr;</button>
    </div>
</template>

<script>
export default {
    components: {},
    methods: {},
};
</script>

<style lang="scss" scoped>
.controls {
    display: flex;
    align-items: center;
    gap: 4px;

    &__button {
        border: 1px solid #000;
    }
}
</style>
