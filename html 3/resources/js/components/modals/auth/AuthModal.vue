<template>
    <div @click="$emit('close')" class="overlay">
        <div @click.stop class="registration-modal">
            <!-- Левая часть модального окна
            <div class="registration-modal__left">
                <img src="/assets/image/modal/modal-reg-gradient-top.png" alt="gradient"
                    class="registration-modal__img registration-modal__gradient_top" />
                <img src="/assets/image/modal/modal-reg-money.png" alt="money"
                    class="registration-modal__img registration-modal__money" />
                <img src="/assets/image/modal/modal-reg-man.png" alt="man"
                    class="registration-modal__img registration-modal__man" />
                <img src="/assets/image/modal/modal-reg-gradient-bottom.png" alt="gradient"
                    class="registration-modal__img registration-modal__gradient_bottom" />

                <div class="registration-modal__content">
                    <div class="registration-modal__title">Безлимитные выводы!</div>
                    <div class="registration-modal__subtitle">Без верификаций</div>
                </div>
            </div> -->

            <!-- Правая часть модального окна -->
            <div class="registration-modal__right">
                <div class="registration-modal__nav">
                    <div class="registration-modal__nav-button"
                        :class="{ 'registration-modal__nav-button--active': isRegistration }"
                        @click="isRegistration = true">
                        Регистрация
                    </div>
                    <div class="registration-modal__nav-button"
                        :class="{ 'registration-modal__nav-button--active': !isRegistration }"
                        @click="isRegistration = false">
                        Вход
                    </div>
                    <div class="registration-modal__close" @click="$emit('close')">
                        <img src="/assets/image/close.svg" alt="close" />
                    </div>
                </div>

                <button class="registration-modal__social-button" @click="socialAuth('vkontakte')">
                    <span class="registration-modal__social-icon">
                        <img src="/assets/image/vk.svg" alt="vk" />
                    </span>
                    <span>Авторизация через ВК</span>
                </button>

                <div class="registration-modal__divider">Или</div>

                <AuthForm :isRegistration="isRegistration" @close="$emit('close')" @auth-success="onAuthSuccess" />
            </div>
        </div>
    </div>
</template>

<script>
import AuthForm from './AuthForm.vue';

export default {
    name: 'AuthModal',
    components: {
        AuthForm,
    },
    props: {
        redirectPath: {
            type: String,
            default: '/'
        },
    },
    data() {
        return {
            isRegistration: true,
        };
    },
    methods: {
        onAuthSuccess() {
            this.$emit('close');
            this.$router.push(this.redirectPath || '/');
        },
        socialAuth(provider) {
            // Реализация социальной авторизации (если требуется)
            window.location.href = `/auth/${provider}`;
        },
    },
    mounted() {
        document.body.classList.add('no-scroll');
    },
    beforeDestroy() {
        document.body.classList.remove('no-scroll');
    },
};
</script>

<style lang="scss" scoped>
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    //left: 43px;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(21.3px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.registration-modal {
    position: relative;
    display: flex;
    max-width: 700px;
    width: 65%;
    min-height: 640px;
    border-radius: 32px;
    background-color: #090410;
    z-index: 10;
    overflow: hidden;

    .registration-modal__left,
    .registration-modal__right {
        flex: 1;
        overflow: hidden;
    }

    /* Левая часть модального окна */
    .registration-modal__left {
        position: relative;
        padding: 0 21px;
        border-right: 1px solid #FFFFFF40;

        .registration-modal__img {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            min-width: 362px;

            &.registration-modal__gradient_top {
                top: 0;
                min-width: 600px;
                width: 100%;
                z-index: 1;
            }

            &.registration-modal__money {
                top: 0;
                z-index: 2;
            }

            &.registration-modal__man {
                bottom: 0;
                min-width: 371px;
                z-index: 2;
            }

            &.registration-modal__gradient_bottom {
                bottom: -130px;
                min-width: 600px;
                width: 100%;
                z-index: 1;
            }
        }

        .registration-modal__content {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 142px;

            .registration-modal__title,
            .registration-modal__subtitle {
                font-family: 'Futura PT', sans-serif;
                font-weight: 900;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .registration-modal__title {
                color: #FCEC10;
                font-size: 30px;
            }

            .registration-modal__subtitle {
                font-size: 24px;
                color: rgba(255, 255, 255, 0.4);
            }
        }
    }

    /* Правая часть модального окна */
    .registration-modal__right {
        display: flex;
        flex-direction: column;
        padding: 24px;
        overflow: auto;

        .registration-modal__nav {
            display: flex;
            align-items: center;
            margin-bottom: 24px;

            .registration-modal__nav-button {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 44px;
                padding: 0 16px;
                color: #fff;
                font-family: 'Inter', sans-serif;
                font-weight: 500;
                font-size: 16px;
                border-radius: 12px;
                cursor: pointer;
            }

            .registration-modal__nav-button--active {
                background-color: rgba(255, 255, 255, 0.1);
            }

            .registration-modal__close {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 42px;
                height: 42px;
                margin-left: auto;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 12px;
                cursor: pointer;

                img {
                    width: 14px;
                    height: 14px;
                }
            }
        }

        .registration-modal__social-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 52px;
            height: 52px;
            background-color: #2764FF;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 600;
            line-height: 19.36px;
            color: #fff;
            border-radius: 12px;

            .registration-modal__social-icon {
                img {
                    width: 24px;
                        height: 24px;
                }
            }
        }

        .registration-modal__divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 16px 0;
            font-family: 'Inter', sans-serif;
            font-size: 24px;
            font-weight: 600;
            line-height: 29.05px;
            color: rgba(255, 255, 255, 0.3);
        }
    }
}

@media (max-width: 1400px) {
    .registration-modal {

        /* Левая часть модального окна */
        .registration-modal__left {
            .registration-modal__img {
                min-width: 300px;

                &.registration-modal__money {
                    width: 80%;
                }

                &.registration-modal__man {
                    min-width: 300px;
                    width: 80%;
                }
            }

            .registration-modal__content {
                .registration-modal__title {
                    font-size: 22px;
                }

                .registration-modal__subtitle {
                    font-size: 18px;
                }
            }
        }
    }
}

@media (max-width: 1024px) {
    .overlay {
        position: relative;
        left: unset;
        top: unset;
        bottom: unset;
        right: unset;
        width: 100%;
        height: 100%;
    }

    .registration-modal {
        width: 100%;
        min-height: auto;
        height: calc(100vh - 96px - 28px);
        margin-top: 28px;
        padding: 24px;
        border: 1px solid #FFFFFF3D;

        top: unset;
        left: unset;
        right: unset;
        bottom: unset;
        transform: none;

        .registration-modal__left {
            display: none;
        }

        .registration-modal__right {
            padding: 0;
        }
    }
}

@media (max-width: 568px) {
    .registration-modal {
        width: 100%;
        min-height: auto;
        height: calc(100vh - 93px);
        margin-top: 28px;
        padding: 24px;
        border: 1px solid #FFFFFF3D;

        top: unset;
        left: unset;
        right: unset;
        bottom: unset;
        transform: none;

        .registration-modal__left {
            display: none;
        }

        .registration-modal__right {
            padding: 0;
        }
    }
}
</style>
