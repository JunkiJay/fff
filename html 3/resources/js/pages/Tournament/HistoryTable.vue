<template>
    <!-- Card Container -->
    <article class="relative flex items-center justify-between
                  bg-[#15101D] text-white rounded-xl p-4 md:p-6">
        <!-- Top Label Badge (if provided) -->
        <div v-if="label" class="mb-2 inline-block bg-gray-800 text-xs uppercase
                              font-medium px-3 py-1 rounded-md">
            {{ label }}
        </div>

        <!-- Text Content -->
        <div class="flex-1">
            <!-- Tournament Title -->
            <h3 class="text-lg md:text-2xl font-bold">
                {{ title }}
            </h3>
            <!-- Prize Pool -->
            <p class="mt-1 text-sm md:text-base">
                Призовой фонд: <span class="font-extrabold text-green-500">{{ prize }}</span>
            </p>
            <!-- Status (e.g., time left or "coming soon") -->
            <p class="mt-1 text-sm text-gray-300 flex items-center">
                <!-- Optional icon can be placed here -->
                <span class="material-icons text-base mr-1">schedule</span>
                {{ status }}
            </p>
            <!-- Action Button -->
            <button class="mt-4 bg-purple-600 hover:bg-purple-700 text-white
                     text-sm font-medium px-4 py-2 rounded">
                {{ buttonText }}
            </button>
        </div>

        <!-- Image Placeholder / Slot on the right -->
        <div class="ml-4 w-20 h-20 md:w-32 md:h-32 bg-gray-500 rounded-lg flex-shrink-0">
            <slot name="image"></slot>  <!-- Slot for tournament image/art -->
        </div>
    </article>
</template>

<script setup>
// Define props for all dynamic content
const props = defineProps({
    title:       { type: String, required: true },
    prize:       { type: String, required: true },      // e.g. "100 000 ₽"
    status:      { type: String, required: true },     // e.g. "7д 13ч 15м" or "Скоро запустится"
    label:       { type: String, default: "" },        // e.g. "По сумме ставок" (optional badge)
    buttonText:  { type: String, default: "Подробнее" } // defaults to "Подробнее"
});
</script>