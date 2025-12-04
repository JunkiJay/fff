<template>
    <!-- Герой-блок турнира -->
    <section class="bg-[#15101D] text-white rounded-[15px] md:rounded-[19px] p-4 md:p-6 flex flex-col md:flex-row md:justify-between">
        <!-- Левая колонка: текстовая информация о турнире -->
        <div class="md:w-1/2 md:pr-4">
            <!-- Название турнира -->
            <h2 class="text-2xl font-oswald mb-2">{{ title }}</h2>
            <!-- Призовой фонд -->
            <div class="flex items-baseline gap-2 mb-4">
                <span class="text-base font-medium">Призовой фонд:</span>
                <span class="text-xl font-extrabold text-[#47D524]">
          {{ prizeAmount }} {{ prizeCurrency }}
        </span>
            </div>
            <!-- Статус/таймер (если передан) -->
            <div v-if="statusText" class="inline-flex items-center bg-[#1F1B29] rounded-md px-3 py-2 mb-4">
                <!-- Иконка (кружок с обводкой) -->
                <span class="w-2 h-2 rounded-full bg-white border border-[#357BF6] mr-2"></span>
                <span class="text-sm font-semibold">{{ statusText }}</span>
            </div>
            <!-- Кнопка действия (например, "Подробнее") -->
            <button v-if="buttonText" type="button"
                    class="bg-[#8D24D5] hover:bg-opacity-90 text-white font-medium py-2 px-4 md:px-6 md:py-3 rounded-md md:rounded-lg">
                {{ buttonText }}
            </button>
        </div>
        <!-- Правая колонка: место под изображение/баннер турнира -->
        <div class="relative md:w-1/2 mt-4 md:mt-0">
            <slot name="image">
                <!-- Заглушка под изображение (фоновой баннер) -->
                <div class="md:absolute md:inset-0 bg-gray-700 rounded-[13px] md:rounded-[13px]"></div>
            </slot>
        </div>
    </section>
</template>

<script setup>
defineProps({
    /** Название турнира */
    title: { type: String, required: true },
    /** Призовой фонд (число) */
    prizeAmount: { type: [Number, String], required: true },
    /** Валюта/единица приза (например, '₽') */
    prizeCurrency: { type: String, default: '₽' },
    /** Статус или таймер (текстом), например "Скоро запустится" или "7д 13ч 15м" */
    statusText: { type: String, default: '' },
    /** Текст на кнопке действия */
    buttonText: { type: String, default: 'Подробнее' }
});
</script>