<template>
    <router-link :to="item.to" class="category-item">
        <template v-if="!!item.component">
            <component :is="item.component" />
        </template>
        <template v-else>
            <img :src="item.image" alt="Stimule game image" loading="lazy"/>
        </template>
    </router-link>
</template>

<script>
export default {
    props: {
        item: {
            type: Object,
            required: true,
        },
    },
};
</script>

<style lang="scss" scoped>
.category-item {
    width: 100%;
    aspect-ratio: 166/222;
    overflow: hidden;
    border-radius: 16px;
    z-index: 2;
    transition: transform 0.4s, opacity 0.4s;

    &:hover {
        opacity: 0.75;
        transform: translateY(-10px);
    }

    img {
        width: 100%;
        height: auto;
    }
}
</style>
