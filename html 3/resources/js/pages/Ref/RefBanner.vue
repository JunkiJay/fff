<template>
    <div class="ref-banner">
        <div class="ref-banner__content">
            <div class="ref-banner__title">
                Зарабатывай, приглашая друзей, <div>благодаря нашей партнерской программе!</div>
            </div>
            <div class="ref-banner__subtitle opacity-60">
                Получай до 15% с каждого пополнения, приведённого друга, <div>в 3 уровня и бонусом до 3000 ₽ с человека!</div>
            </div>
            <div class="ref-banner__actions">
                <div class="ref-banner__action">
                    <label for="referralLink" class="ref-banner__label">Ваша реферальная ссылка</label>
                    <div class="ref-banner__input-container">
                        <input
                            id="referralLink"
                            type="text"
                            readonly
                            :value="link || 'Ссылка отсутствует'"
                            class="ref-banner__input"
                            aria-label="Your referral link"
                        />
                        <button class="ref-banner__button" @click="copyRef">Скопировать</button>
                    </div>
                </div>
                <div class="ref-banner__action">
                    <label class="ref-banner__label">Ваш реферальный баланс</label>
                    <div class="ref-banner__input-container">
                        <input
                            type="text"
                            readonly
                            :value="formattedRefIncome"
                            class="ref-banner__input"
                            aria-label="Your referral income"
                        />
                        <button class="ref-banner__button" @click="take">Забрать</button>
                    </div>
                </div>
            </div>
        </div>
        <img :src="referalImg" alt="icon" class="ref-banner__banner-girl" />
        <img :src="bannerBgMoneyImg" alt="icon" class="ref-banner__banner-bg-money" />
        <img :src="bannerBgRoundImg" alt="icon" class="ref-banner__banner-bg-round" />

        <img :src="bannerGirlMobileImg" alt="icon" class="ref-banner__banner-girl_mobile" />
        <img :src="bannerBgMoneyMobileImg" alt="icon" class="ref-banner__banner-bg-money_mobile" />
    </div>
</template>

<script>
import referalImg from '@public/images/referal.png';
import bannerBgMoneyImg from '@public/images/banner-bg-money.webp';
import bannerBgRoundImg from '@public/images/banner-bg-round.png';
import bannerGirlMobileImg from '@public/images/banner-girl-mobile.webp';
import bannerBgMoneyMobileImg from '@public/images/banner-bg-money_mobile.webp';

export default {
    name: "RefBanner",
    props: {
        link: {
            type: String,
            required: false,
            default: "",
        },
        refIncome: {
            type: [String, Number],
            required: false,
            default: "0.00",
        },
    },
    data() {
        return {
            referalImg,
            bannerBgMoneyImg,
            bannerBgRoundImg,
            bannerGirlMobileImg,
            bannerBgMoneyMobileImg,
        };
    },
    computed: {
        formattedRefIncome() {
            const income = parseFloat(this.refIncome);
            return isNaN(income) ? "0.00 ₽" : `${income.toFixed(2)} ₽`;
        },
    },
    methods: {
        copyRef() {
            if (!this.link) {
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Реферальная ссылка отсутствует",
                    type: "error",
                });
                return;
            }
            this.$emit("copyRef");
        },
        take() {
            const income = parseFloat(this.refIncome);
            if (income < 20) {
                this.$emitter.emit("noty", {
                    title: "Ошибка",
                    text: "Минимальный вывод 20 ₽",
                    type: "error",
                });
                return;
            }
            this.$emit("take");
        },
    },
};
</script>

<style lang="scss" scoped>
.ref-banner {
    position: relative;
    padding: 32px;
    border: 1px solid #FFFFFF40;
    border-radius: 32px;
    background: #1F1B2940;
    overflow: hidden;
    .ref-banner__content {
        position: relative;
        max-width: 70%;
        z-index: 3;
        .ref-banner__title {
            margin-bottom: 16px;
            font-family: Inter, sans-serif;
            font-size: 36px;
            font-weight: 800;
            line-height: 43.57px;
            color: #fff;
        }
        .ref-banner__subtitle {
            margin-bottom: 44px;
            font-family: Inter, sans-serif;
            font-size: 20px;
            font-weight: 400;
            line-height: 24.2px;
            letter-spacing: -0.20000000298023224px;
            color: #fff;
        }
        .ref-banner__actions {
            display: flex;
            flex-direction: column;
            max-width: 528px;
            gap: 16px;
            color: #fff;
        }
        .ref-banner__action {
            display: flex;
            flex-direction: column;
            max-width: 100%;
            .ref-banner__label {
                font-family: Inter, sans-serif;
                font-size: 16px;
                font-weight: 500;
                line-height: 19.36px;
            }
            .ref-banner__input-container {
                display: flex;
                align-items: center;
                margin-top: 8px;
                .ref-banner__input {
                    width: 100%;
                    height: 52px;
                    padding: 0 16px;
                    border-radius: 12px 0 0 12px;
                    border: 1px solid #CED4DA40;
                    background: #2E2A3640;

                    font-family: Inter, sans-serif;
                    font-size: 16px;
                    font-weight: 400;
                    line-height: 19.36px;
                }
                .ref-banner__button {
                    min-width: 154px;
                    width: 154px;
                    height: 52px;
                    padding: 0 24px;
                    border-radius: 0 12px 12px 0;
                    border: 1px solid #8D24D5;
                    background: #8D24D5;
                    font-family: Inter, sans-serif;
                    font-size: 16px;
                    font-weight: 600;
                    line-height: 19.36px;
                    cursor: pointer;
                }
            }
        }
    }

    img {
        position: absolute;
        z-index: 1;
    }
    .ref-banner__banner-girl {
        right: 0;
        bottom: 0;
        z-index: 2;
    }
    .ref-banner__banner-bg-money {
        top: 0;
        right: 0;
    }
    .ref-banner__banner-bg-round {
        top: 0;
        left: 0;
    }

    .ref-banner__banner-girl_mobile, .ref-banner__banner-bg-money_mobile, .ref-banner__banner-bg-round_mobile {
        display: none;
    }
}

@media (max-width: 1024px) {
    .ref-banner {
        padding: 24px 15px 500px;

        .ref-banner__content {
            max-width: 100%;

            .ref-banner__title {
                margin-bottom: 14px;
                font-size: 20px;
                font-weight: 700;
                line-height: 38.73px;
            }

            .ref-banner__subtitle {
                margin-bottom: 32px;
                font-size: 16px;
                font-weight: 500;
                line-height: 19.36px;
            }

            .ref-banner__actions {
                max-width: 100%;
                gap: 24px;
            }

            .ref-banner__action .ref-banner__input-container .ref-banner__input {
                padding: 0 12px;
                font-size: 14px;
                font-weight: 400;
                line-height: 16.94px;
            }
        }

        img {
            display: none;
        }

        .ref-banner__banner-girl_mobile,
        .ref-banner__banner-bg-money_mobile,
        .ref-banner__banner-bg-round_mobile {
            display: block;
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        .ref-banner__banner-girl_mobile {
            z-index: 2;
            width: clamp(50vw, 70vw, 80vw);
            height: auto;
        }
        .ref-banner__banner-bg-money_mobile {
            width: 100%;
            height: auto;
            object-fit: cover;
        }
    }
}
</style>
