<template>
    <b-modal
        v-model="open"
        id="connectTg"
        hide-header-close
        centered
        dialog-class="tg-modal-dialog"
        content-class="tg-modal-content"
        hide-backdrop
        static
        lazy
    >
        <template #header>
            <div class="w-100 text-center">
                <h5 class="mb-0 tg-modal-title">
                    <b-icon-telegram class="mr-2" style="color: #0088cc; font-size: 1.5rem;"></b-icon-telegram>
                    Подписка на Telegram
                </h5>
            </div>
        </template>
        <div class="tg-modal-body">
            <div class="tg-modal-intro">
                Чтобы разблокировать бонусы, вам нужно привязать свой аккаунт к нашему боту и подписаться на наш
                телеграм-канал.
            </div>
            <form class="tg-modal-form">
                <div role="group" class="form-group">
                    <label for="input-1" class="d-block tg-modal-label">Ваша уникальная команда:</label>
                    <div>
                        <input
                            type="text"
                            class="form-control tg-modal-command"
                            :value="'/bind ' + this.user.unique_id"
                            v-if="this.user !== null"
                            disabled
                        />
                    </div>
                </div>
            </form>
            <div class="tg-modal-steps">
                <h6 class="tg-modal-steps-title">Что нужно сделать?</h6>
                <ol class="tg-modal-steps-list">
                    <li>
                        Подписаться на <a :href="config?.tg_channel" target="_blank" class="tg-modal-link">наш телеграм
                        канал</a>
                    </li>
                    <li>
                        Отправить команду <a :href="config?.tg_bot" target="_blank" class="tg-modal-link">нашему
                        боту</a>
                    </li>
                </ol>
                <div class="tg-modal-done">Все! Теперь вам доступны все бонусы!</div>
            </div>
        </div>
        <template #footer>
            <div class="d-flex w-100 justify-content-between align-items-center tg-modal-footer-btns">
                <b-button
                    variant="outline-secondary"
                    class="tg-modal-btn tg-modal-btn-cancel"
                    @click="this.open = false"
                >
                    Отмена
                </b-button>
                <b-button
                    variant="primary"
                    class="tg-modal-btn tg-modal-btn-ok"
                    @click="tryBonus()"
                >
                    ОК
                </b-button>
            </div>
        </template>
    </b-modal>
</template>

<script>
import axios from "axios";
import { mapState } from "vuex";
import { BModal } from "bootstrap-vue-next";
import { ref } from "vue";

export default {
    data() {
        const open = ref(false);

        return {
            open,
            isClient: false
        };
    },

    mounted() {
        this.isClient = true;
        this.$emitter.on("connectTg", () => {
            this.open = true
        })
    },
    computed: {
        ...mapState(["user", "isLoading", "config"])
    },
    components: {
        BModal
    },
    methods: {
        async tryBonus() {
            try {
                const response = await axios.post("/bonus/take", { type: "one" });
                const { data } = response;
                if (data.error) {
                    this.$emitter.emit("noty", {
                        title: "Ошибка",
                        text: data.message,
                        type: "error"
                    });
                } else {
                    this.$emitter.emit("noty", {
                        title: "Успех",
                        text: data.text || "Бонус успешно получен!",
                        type: "success"
                    });
                    this.open = false;
                    // Обновить баланс пользователя, если пришёл в ответе и пользователь существует
                    if (typeof data.balance !== "undefined" && this.$store.state.user) {
                        this.user.balance = data.balance;
                    }
                }
            } catch (error) {
                console.error("Ошибка при получении бонуса:", error);
                const errorMessage = error.response?.data?.message || error.message || "Неизвестная ошибка";
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: `Произошла ошибка при получении бонуса`,
                    type: "error"
                });
            }
        }
    },
    sockets: {
        connectTelegram(data) {
            if (this.user !== null && data.user_id == this.$root.user.id) {
                this.$modal.hide("connectTg");
                return this.$emitter.emit("noty", {
                    title: "Успех",
                    text: "Telegram привязан!",
                    type: "success"
                });
            }
        }
    }
};
</script>

<style>
/* Глобальные стили для модалки */
.modal {
    background: rgba(0, 0, 0, 0.7) !important;
    backdrop-filter: blur(5px);
    height: 100%;

    .modal-dialog {
        margin: 1.75rem auto;
        max-width: 450px;
        width: 95%;

        .modal-content {
            background: #1a162d !important;
            border: 1px solid #2a2440 !important;
            border-radius: 12px !important;
            color: #e0e0ef;
            box-shadow: 0 8px 32px rgba(138, 43, 226, 0.3) !important;

            .modal-header {
                border-bottom: 1px solid #2a2440 !important;
                padding: 20px 24px !important;
                border-top-left-radius: 12px !important;
                border-top-right-radius: 12px !important;
            }

            .modal-body {
                padding: 24px !important;
            }

            .modal-footer {
                border-top: 1px solid #2a2440 !important;
                padding: 16px 24px !important;
                justify-content: center !important;
                border-bottom-left-radius: 12px !important;
                border-bottom-right-radius: 12px !important;
            }
        }
    }
}





.close {
    color: #e0e0ef !important;
    opacity: 0.7;
    text-shadow: none;
    position: absolute;
    right: 20px;
    top: 20px;
}

:global(.close:hover) {
    opacity: 1;
    color: #fff !important;
}

/* Стили для компонента */
.tg-modal-title {
    color: #b18cff;
    font-size: 1.5rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tg-modal-intro {
    color: #e0e0ef;
    margin-bottom: 20px;
    line-height: 1.5;
    font-size: 1.05rem;
}

.tg-modal-steps {
    background: rgba(138, 43, 226, 0.1);
    border-radius: 10px;
    padding: 16px;
    margin: 20px 0;
}

.tg-modal-steps-title {
    color: #fff;
    font-size: 1.1rem;
    margin-bottom: 12px;
    font-weight: 600;
}

.tg-modal-steps-list {
    color: #e0e0ef;
    padding-left: 20px;
    margin-bottom: 0;
}

.tg-modal-steps-list li {
    margin-bottom: 8px;
    line-height: 1.5;
}

.tg-modal-link {
    color: #8a2be2;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
    cursor: pointer;
}

.tg-modal-link:hover {
    color: #b18cff;
    text-decoration: underline;
}

.tg-modal-command {
    background: rgba(138, 43, 226, 0.1) !important;
    border: 1px solid #2a2440 !important;
    color: #b18cff !important;
    font-family: monospace;
    padding: 10px 15px !important;
    border-radius: 8px !important;
    margin-bottom: 15px;
    cursor: copy;
    width: 100%;
}

/* Адаптивность */
@media (max-width: 576px) {
    :global(.modal-dialog),
    .tg-modal-dialog {
        margin: 10px auto;
        width: 100vw;
        max-width: 100vw;
        min-width: 0;
    }

    :global(.modal-content),
    .tg-modal-content {
        max-height: 90vh !important;
        min-height: unset;
        height: auto;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 100vw;
        box-sizing: border-box;
    }

    .tg-modal-content {
        max-height: 80vh !important;
        min-height: unset;
        height: 80vh;
        overflow-y: scroll;
        width: 100vw;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 0 8px;
        margin-top: 150px;
    }

    .tg-modal-body {
        flex: 1 1 auto;
        overflow-y: auto;
        min-height: 0;
        max-height: 100%;
        width: 100%;
        -webkit-overflow-scrolling: touch;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 8px 16px 8px;
    }

    .tg-modal-steps {
        width: 100%;
    }

    .tg-modal-footer-btns {
        flex-direction: row;
        gap: 12px;
    }
}


/* Анимации */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

:global(.modal-content) {
    animation: fadeIn 0.3s ease-out;
}
</style>

<style scoped>
:global(.modal) {
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

:global(.modal-content) {
    background: #1a162d;
    border: 1px solid #2a2440;
    border-radius: 12px;
    color: #e0e0ef;
    box-shadow: 0 8px 32px rgba(138, 43, 226, 0.3);
}

:global(.modal-header) {
    border-bottom: 1px solid #2a2440;
    padding: 20px 24px;
}

:global(.modal-body) {
    padding: 24px;
}

:global(.modal-footer) {
    border-top: 1px solid #2a2440;
    padding: 16px 24px;
    justify-content: flex-end;
}

:global(.close) {
    color: #e0e0ef;
    opacity: 0.7;
    text-shadow: none;
}

:global(.close:hover) {
    opacity: 1;
    color: #fff;
}

/* Остальные стили */
.tg-modal-dialog {
    max-width: 420px;
    border-radius: 22px;
}

.tg-modal-content {
    background: #18142b;
    color: #fff;
    border-radius: 22px;
    border: none;
    box-shadow: 0 8px 32px 0 rgba(80, 48, 180, 0.25);
    padding: 0;
}

.tg-modal-title {
    color: #b18cff;
    font-size: 1.5rem;
    font-weight: bold;
    letter-spacing: 0.5px;
}

.tg-modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    min-height: 0;
    max-height: 100%;
    width: 100%;
    -webkit-overflow-scrolling: touch;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 8px 16px 8px;
}

.tg-modal-intro {
    font-size: 1.1rem;
    margin-bottom: 18px;
    color: #e0e0ef;
}

.tg-modal-form {
    margin-bottom: 18px;
}

.tg-modal-label {
    font-size: 1rem;
    color: #b18cff;
    margin-bottom: 6px;
}

.tg-modal-command {
    background: #232040;
    color: #fff;
    border: 1px solid #312a5a;
    border-radius: 8px;
    font-size: 1rem;
    font-family: monospace;
    padding: 8px 12px;
}

.tg-modal-command:disabled {
    opacity: 1;
}

.tg-modal-steps {
    margin-bottom: 0;
}

.tg-modal-steps-title {
    font-size: 1.08rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: #fff;
}

.tg-modal-steps-list {
    list-style: decimal inside;
    margin: 0 0 12px 0;
    padding: 0;
    color: #fff;
}

.tg-modal-steps-list li {
    margin-bottom: 6px;
    font-size: 1rem;
}

.tg-modal-link {
    color: #b18cff;
    text-decoration: underline;
    transition: color 0.2s;
}

.tg-modal-link:hover {
    color: #fff;
    background: #b18cff;
    border-radius: 4px;
    padding: 0 2px;
}

.tg-modal-done {
    margin-top: 8px;
    color: #b18cff;
    font-weight: 600;
    font-size: 1.02rem;
}

.tg-modal-btn {
    background: linear-gradient(90deg, #a259ff 0%, #5224d5 100%);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1.09rem;
    font-weight: 600;
    padding: 8px 28px;
    margin: 18px 0 10px 0;
    box-shadow: 0 2px 8px 0 rgba(80, 48, 180, 0.12);
    transition: background 0.2s;
}

.tg-modal-btn:hover {
    background: linear-gradient(90deg, #b18cff 0%, #5224d5 100%);
    color: #fff;
}
</style>