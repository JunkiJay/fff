<template>
    <div class="category-list">
        <slot></slot>
    </div>
</template>

<script>
export default {
    components: {},
    methods: {},
};
</script>

<style lang="scss" scoped>
.category-list {
    display: grid;
    /* grid-template-columns: repeat(auto-fit, minmax(136px, 1fr)); */
    /* grid-template-columns: repeat(6, minmax(136px, 1fr)); */
    grid-template-columns: repeat(6, 1fr);
    gap: 8px;
    position: relative;
}

@media (max-width: 1024px) {
    .category-list {
        /* grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); */
    }
}

@media (max-width: 768px) {
    .category-list {
        /* grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); */
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 480px) {
    .category-list {
        /* grid-template-columns: repeat(auto-fit, minmax(90px, 1fr)); */
    }
}

@media (max-width: 320px) {
    .category-list {
        /* grid-template-columns: repeat(2, minmax(70px, 1fr)); */
    }
}
</style>
