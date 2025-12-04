<template>
  <div class="share-header">
    <div class="header px-4 py-2 lg:p-4 rounded-[24px]">
      <div class="header__left">
        <router-link to="/">
          <img class="header__logo" :src="logoSvg" alt="logo" />
        </router-link>
        <Online class="header__online" />

        <div class="header__links links">
          <router-link v-for="link in filteredLinks" :key="link.to" :to="link.to" class="link">
            <Button variant="secondary" class="link link_left primary">
              {{ link.title }}
            </Button>
          </router-link>
        </div>
      </div>

      <template v-if="user == null">
        <div class="header__right">
          <Button class="header__auth-button primary" @click.native="auth">
            <span>Авторизоваться</span>
          </Button>
        </div>
      </template>

      <template v-else>
        <div class="header__right">
          <div class="user-card-desktop">
            <div class="flex flex-col items-end">
              <span class="user-card-desktop__name">
                {{ user?.username || user?.email }}
              </span>

              <span class="font-['Oswald']">
                <span class="text-[#FFFFFF50]">Баланс:</span>
                <strong class="text-white">{{ Number(user.balance).toFixed(2) }} ₽</strong>
              </span>
            </div>

            <div class="user-card-desktop__avatar">
              <img :src="user.avatar || '/img/avatar-default.svg'" alt="user avatar" />
            </div>
          </div>

          <router-link class="link link_right" to="/pay">
            <Button class="link link_right primary">Пополнить</Button>
          </router-link>
          <router-link class="link link_right" to="/withdraw">
            <Button class="link link_right primary">Вывести</Button>
          </router-link>

          <div class="user-card-mob">
            <div class="user-card-mob__avatar">
              <img :src="user.avatar || '/img/avatar-default.svg'" alt="user avatar" />
            </div>
            <div class="user-card-mob__left">
              <span class="user-card-mob__balance-title">Кошелек</span>
              <span class="user-card-mob__balance-value">{{ user.balance }}₽</span>
            </div>
          </div>
        </div>
      </template>
    </div>
    <div v-if="user !== null" class="link_mobile">
      <router-link to="/pay">
        <Button :class="$route.path === '/pay' ? 'tab-btn--active' : 'tab-btn'">Пополнить</Button>
      </router-link>
      <router-link to="/withdraw">
        <Button :class="$route.path === '/withdraw' ? 'tab-btn--active' : 'tab-btn'">Вывести</Button>
      </router-link>
    </div>

    <AuthModal v-if="isAuth" @close="isAuth = false" :redirectPath="previousRoute" />
  </div>
</template>

<script>
import Preloader from "./Preloader.vue";
import Online from "./Online.vue";
import Button from "@/components/ui/Button.vue";
import AuthModal from "./modals/auth/AuthModal.vue";
import logoSvg from "@img/logo.svg";

export default {
  props: ["page"],
  components: {
    AuthModal,
    Preloader,
    Online,
    Button,
  },
  data() {
    return {
      logoSvg,
      isAuth: false,
      links: [
        {
          title: "Главная",
          to: "/",
          isAuth: false,
        },
        {
          title: "Рефералы",
          to: "/ref",
          isAuth: true,
        },
        {
          title: "Бонусы",
          to: "/bonus",
          isAuth: true,
        },
        {
          title: "FAQ",
          to: "/faq",
          isAuth: false,
        },
        {
          title: "Выход",
          to: "/logout",
          isAuth: true,
        },
      ],
    };
  },

  async mounted() {
    try {
      await this.$store.dispatch('fetchUser');
    } catch (e) {
    }
  },

  computed: {
    isDark() {
      return this.$store.state.isDark;
    },
    user() {
      return this.$store.state.user;
    },
    filteredLinks() {
      return this.links.filter(
        (link) => (link.isAuth && this.user) || !link.isAuth
      );
    },
  },
  methods: {
    async logout() {
      await this.$store.dispatch('logout');
      localStorage.removeItem('authToken');
      this.$store.commit('clearUserState');
      this.$router.push({ path: '/' });
    },
    auth() {
      this.isAuth = true;
    },
  },
};
</script>

<style lang="scss" scoped>
.tab-btn {
  background: #232025;
  color: #fff;
}
.tab-btn--active {
  background: #9D3FEF;
  color: #fff;
}
.link_mobile {
  margin-top: 16px;
  display: none;
}

.header {
  position: relative;
  border-radius: 8px;
  display: flex;
  align-items: center;
  height: 72px;
  background-color: #1f1b29;
  z-index: 101;

  &__left {
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 532px;
    width: 100%;

    >a {
      display: none;
    }
  }

  &__right {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    width: 100%;

    .link {
      max-width: 120px;
    }

    justify-content: end;
  }

  &__logo {
    width: 40px;
    height: 40px;
    display: none;
  }

  &__online {
    display: none !important;
  }

  &__auth-button {
    display: flex;
    align-items: center;
    gap: 2px;
    font-weight: 500;
    height: 44px;
    padding: 0 20px;
    font-size: 16px;
    font-family: Segoe UI, sans-serif;
        span {
            white-space: nowrap;
            text-overflow: ellipsis;
        }
    }
}
.blick {
    position: relative;
    box-shadow: rgba(155, 94, 255, 0.6);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.blick::before {
    content: "";
    position: absolute;
    width: 10px;
    height: 200%;
    top: 0;
    left: 0;
    // background: rgba(255, 255, 255, 0.6);
    pointer-events: none;
    transform: translateY(-30%) translateX(290%) rotate(35deg);
    transition: transform 0.5s ease;
    filter: blur(5px);
}

.blick:hover::before {
    transform: translateX(1500%) rotate(45deg);
}

.blick:hover {
    box-shadow: 0 0 25px rgba(155, 94, 255, 1);
}
.user-card-desktop {
    display: flex;
    font-size: 14px;
    gap: 12px;
    align-items: stretch;
    text-decoration: none;

    &__left {
        display: flex;
        flex-direction: column;
    }

    &__name {
        text-align: right;
        font-weight: 500;
        color: #ffffff;
        -webkit-line-clamp: 1; /* Число отображаемых строк */
        display: -webkit-box; /* Включаем флексбоксы */
        -webkit-box-orient: vertical; /* Вертикальная ориентация */
        overflow: hidden; /* Обрезаем всё за пределами блока */
    }

    &__balance {
        color: var(--color-text-secondary);

        strong {
            color: var(--color-text);
        }
    }

    &__avatar {
        flex: none;
        border-radius: 4px;
        overflow: hidden;
        width: 40px;
        height: 40px;

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }
}

.user-card-mob {
    display: none;
    align-items: center;
    position: relative;
    gap: 6px;
    font-family: Oswald !important;

    &__left {
        display: flex;
        flex-direction: column;
    }

    &__balance-title {
        color: #ffffff50;
        font-size: 10px;
        text-transform: uppercase;
    }

    &__balance-value {
        color: white;
        font-size: 14px;
    }

    &__payment-button {
        border-radius: 8px;
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--color-primary);
        position: relative;
        font-family: Rubik !important;
    }

    &__payment-menu {
        position: absolute;
        right: 0;
        top: 100%;
        z-index: 30;
    }

    .payemnt-menu {
        background: var(--color-content);
        display: flex;
        flex-direction: column;
        width: 285px;
        padding: 8px;
        gap: 8px;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 1);

        a {
            gap: 10px;
            height: 46px;
            background-color: var(--color-primary);
            justify-content: center;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 14px;
            font-family: Rubik !important;
            font-weight: 400;
        }
    }

    &__avatar {
        border-radius: 4px;
        overflow: hidden;
        width: 36px;
        height: 36px;
        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }
}

.links {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.link {
    font-family: "Rubik", sans-serif;
    height: 40px;
    max-width: 100px;
    width: 100%;
    justify-content: center;
    text-align: center;
    color: #ffffff;
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
    transition: 0.1s;
    border-radius: 8px;
    background-color: #f0f0f015;
    display: flex;
    align-items: center;
    justify-items: center;

    &_primary {
        background: var(--color-primary);
        color: #ffffff;
    }

    &.router-link-exact-active {
        button {
            color: white;
        }
    }
}

@media (max-width: 1024px) {
    .share-header {
        left: 0;
        z-index: 9999;
        border-radius: 0 0 8px 8px;
        width: 100%;
        // transform: translateX(-15px);
        position: fixed;
        //margin-left: -15px;
        top: 0;
        background-color: rgb(14, 13, 15);

      padding: 12px 15px;
    }
    .header {
        top: 0;
        width: 100%;
        z-index: 500;
        background-color: rgb(14, 13, 15);
        position: sticky;
        &__logo {
            display: flex;
        }

        &__online {
            display: flex !important;
        }

        &__auth-button {
            span {
                /* display: none; */
            }
        }
        &__left {
            max-width: none;
            width: auto;
            flex: none;
            > a {
                display: block;
                flex: none;
                button {
                    @media (hover: none) and (pointer: coarse) {
                        &:hover {
                            background: red !important;
                        }
                    }
                }
            }
        }
        &__right {
            width: 100%;
        }
    }

    .links {
        display: none;
    }
}
@media (max-width: 568px) {
    .share-header {
        padding: 12px;
        .header {
            padding: 0;
            height: 42px;
        }
    }
    .user-card-mob {
        display: flex;
    }
    .link_mobile {
        display: flex;
        gap: 4px;
        margin-top: 16px;
        a,
        button {
            max-width: 100%;
            justify-content: center;
            width: 100%;
            text-decoration:none;
        }
    }
    .user-card-desktop {
        display: none;
    }
    .header .link_right {
        display: none;
    }
}
</style>
