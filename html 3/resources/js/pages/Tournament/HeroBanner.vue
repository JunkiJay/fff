<template>
    <!-- HeroBanner Container -->
    <section class="w-full relative flex items-center justify-between
                 bg-purple-700 text-white rounded-lg overflow-hidden
                 px-4 py-8 md:py-12">
        <!-- Slogan Text -->
        <h1 class="text-xl md:text-3xl font-bold">
            {{ slogan }}
        </h1>
        <!-- Image Placeholder / Slot -->
        <div class="ml-4 w-24 h-24 md:w-40 md:h-40 bg-gray-500 rounded-lg flex-shrink-0">
            <slot name="image"></slot> <!-- Slot for banner image -->
        </div>
    </section>
</template>

<script setup>
// Define the slogan as a prop (allows reusability or localization)
const props = defineProps({
    slogan: { type: String, default: "Участвуй в турнирах и побеждай!" }
});
</script>