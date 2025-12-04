<template>
    <footer class="footer">
        <div class="footer__links">
            <router-link to="/terms">Пользовательское соглашение</router-link>
            <router-link to="/policy">Политика конфиденциальности</router-link>
        </div>
        <p class="col-md-auto ml-auto">Stimule 一 {{ new Date().getFullYear() }}</p>
        <DepositConfirmation
            :visible="showDepositConfirmation"
            :amount="depositAmount"
            @update:visible="showDepositConfirmation = $event"
        />
    </footer>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, getCurrentInstance } from 'vue';
import DepositConfirmation from "./modals/DepositConfirmation.vue";

const showDepositConfirmation = ref(false);
const depositAmount = ref(0);

const instance = getCurrentInstance();
const emitter = instance?.appContext.config.globalProperties.$emitter;

onMounted(() => {
  // Слушаем событие depositConfirmation через emitter (отправляется из app.js)
  if (emitter) {
    emitter.on('depositConfirmation', (data: any) => {
      console.log('📬 Footer: получено событие depositConfirmation', data);
      depositAmount.value = data.amount || 0;
      showDepositConfirmation.value = true;
    });
    
    emitter.on('withdrawConfirmation', (data: any) => {
      console.log('📬 Footer: получено событие withdrawConfirmation', data);
      // Можно добавить модалку для вывода, если нужно
    });
  }
});

onUnmounted(() => {
  // Очищаем слушатели при размонтировании
  if (emitter) {
    emitter.off('depositConfirmation');
    emitter.off('withdrawConfirmation');
  }
});
</script>

<style lang="scss" scoped>
.footer {
    color: var(--color-text) !important;
    display: flex;
    justify-content: space-between;
    font-weight: 300;
    z-index: 1;

    &__links {
        display: flex;
        gap: 24px;
    }

    a {
        color: var(--color-text) !important;
        margin-right: 5px;
        transition: all 0.2s ease;
        &:hover {
            opacity: 0.5;
        }
    }
}

@media (max-width: 1024px) {
    .footer {
        display: none;
    }
}
</style>