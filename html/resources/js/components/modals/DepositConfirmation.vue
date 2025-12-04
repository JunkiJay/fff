<template>
    <Teleport to="body">
        <div v-if="visible" class="popup-overlay" @click="close">
            <div class="popup-content" @click.stop>
                <button class="popup-close" @click="close">✕</button>
                <div class="popup-title">
                     <img :src="signImg"><span class="font-['Oswald']">Успешное пополнение!</span>
                </div>
                <p class="popup-text font-['Rubick Medium']">
                    Ваш депозит на {{ amount }}₽ успешно зачислен на счёт.<br>
                    Приятной игры!
                </p>
                <button class="popup-button font-['Rubick Medium']" @click="goToGames">Перейти к играм</button>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import signImg from '@img/popups/mark.png'
import router from "@/router";

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

function goToGames() {
    router.push({ name: 'slots' });
    close()
}
</script>

<style scoped>
.popup-overlay {
    position: fixed !important;
    inset: 0;
    background-color: rgba(10, 4, 16, 0.7);
    z-index: 2147483647 !important; /* Максимальное значение z-index */
    display: flex;
    justify-content: center;
    align-items: center;
    pointer-events: auto;
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
    z-index: 2147483647 !important; /* Максимальное значение z-index */
    pointer-events: auto;
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

.popup-button {
    width: 392px;
    height: 44px;
    background-color: rgba(141, 36, 213, 1);
    color: white;
    border: none;
    padding: 10px 25px;
    font-size: 14px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
}

.popup-button:hover {
    background-color: #8f3be1;
}
</style>