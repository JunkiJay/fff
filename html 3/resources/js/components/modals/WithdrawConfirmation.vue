<template>
    <div v-if="visible" class="popup-overlay">
        <div class="popup-content">
            <button class="popup-close" @click="close">✕</button>
            <div class="popup-title">
                 <img :src="signImg"><span class="font-['Oswald']">Успешный вывод!</span>
            </div>
            <p class="popup-text font-['Rubick Medium']">
                Ваш вывод на {{ amount }}₽ успешно проведен и отправлен на Ваш счет.<br>
            </p>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import signImg from '@img/popups/mark.png'

const props = defineProps({
    visible: Boolean,
    amount: {
        type: [Number, String],
        default: 0,
    },
})

const emit = defineEmits(['update:visible'])

function close() {
    emit('update:visible', false)
}
</script>

<style scoped>
.popup-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(10, 4, 16, 0.7);
    z-index: 999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.popup-content {
    width: 466px;
    height: 256px;
    background: rgba(21, 16, 29, 1);
    border: 1px solid rgba(54, 52, 62, 0.8);
    color: #fff;
    border-radius: 13px;
    padding: 30px 20px;
    position: relative;
    text-align: center;
    font-family: sans-serif;
}

.popup-close {
    position: absolute;
    top: 12px;
    right: 15px;
    background: transparent;
    border: none;
    font-size: 16px;
    color: rgba(54, 52, 62, 0.8);
    cursor: pointer;
}

.popup-title {
    text-align: center;
    vertical-align: middle;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.popup-title span {
    font-weight: 500;
    font-size: 24px;
    line-height: 36px;
}

.popup-title img {
    width: 22px;
    margin-right: 10px;
    margin-top: 7px;
}

.popup-text {
    color: rgba(255, 255, 255, 0.3);
    margin-bottom: 25px;
    font-weight: 100;
    font-size: 16px;
    line-height: 145%;
    text-align: center;
    vertical-align: middle;
}
</style>