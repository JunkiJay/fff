<template>
    <div class="left-menu">
        <div class="left-menu__desktop">
            <router-link to="/" class="left-menu__logo">
                <img :src="logoUrl" />
                <div>Stimule</div>
            </router-link>

            <div class="left-menu__list list">
                <template v-for="link in links">
                    <router-link class="mb-[16px]" v-if="link.to === '/tournament'" :to="link.to">
                        <TournamentLink v-if="link.to === '/tournament'" :icon="link.icon" />
                    </router-link>

                    <router-link v-else-if="!link.external" :to="link.to">
                        <Button variant="default" class="w-full h-full items-center list-item">
                            <img class="list-item__icon" :src="link.icon" />
                            <div class="list-item__title">{{ link.title }}</div>
                        </Button>
                    </router-link>

                    <a v-else :href="link.to">
                        <Button variant="default" class="w-full h-full items-center list-item">
                            <img class="list-item__icon" :src="link.icon" />
                            <div class="list-item__title">{{ link.title }}</div>
                        </Button>
                    </a>
                </template>
            </div>

            <div class="flex flex-col gap-3">
                <button @click="toggleMute"
                    class="w-[40px] h-[40px] rounded-[8px] bg-white/[25%] flex items-center justify-center">
                    <img v-if="!isMute" :src="muteIcon" alt="mute" />
                    <img v-else :src="unmuteIcon" alt="unmute" />
                </button>

                <button v-if="false" @click="toggleTheme"
                    class="w-[40px] h-[40px] rounded-[8px] bg-white/[25%] flex items-center justify-center">
                    <img v-if="isDark" :src="themeDarkSvg" alt="mute" />
                    <img v-else :src="themeLightSvg" alt="unmute" />
                </button>
            </div>

            <Online class="left-menu__online" />
        </div>

        <div class="left-menu__mob z-[999] h-full" :class="{ active: isShowMobilePanel }">
            <template v-for="link in linksMobPanel" class="h-full">
                <div :class="{ 'bg-[#8D24D5]': $route.path === link.to && link.to !== '/tournament' }"
                    class="flex items-center justify-center h-full rounded-[8px] padding-[7px]">
                    <router-link v-if="link.to === '/tournament'" :to="link.to"
                        class="tournament-link text-white">
                        <TournamentLink :icon="link.icon" />
                    </router-link>
                    <router-link v-else-if="link.external === false" :to="null"
                        @click.native="handleClick($event, link)"
                        :class="['list-item flex items-center h-full padding-[7px]']">
                        <img class="list-item__icon" :src="link.icon" />
                        <div class="list-item__title">{{ link.title }}</div>
                    </router-link>

                    <div v-else class="h-full py-[7px]">
                        <router-link :to="link.to" :class="['list-item justify-center']">
                            <img class="list-item__icon" :src="link.icon" />
                            <div class="list-item__title">{{ link.title }}</div>
                        </router-link>
                    </div>
                </div>
            </template>

            <button @click="toggleShowMobilePanel"
                :class="['list-item flex items-center justify-center', { active: isShowMobilePanel }]">
                <svg width="22" height="6" viewBox="0 0 22 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M3.125 0.75C3.46972 0.75 3.81106 0.817897 4.12954 0.949816C4.44802 1.08173 4.7374 1.27509 4.98116 1.51884C5.22491 1.7626 5.41827 2.05198 5.55018 2.37046C5.6821 2.68894 5.75 3.03028 5.75 3.375C5.75 3.71972 5.6821 4.06106 5.55018 4.37954C5.41827 4.69802 5.22491 4.9874 4.98116 5.23116C4.7374 5.47491 4.44802 5.66826 4.12954 5.80018C3.81106 5.9321 3.46972 6 3.125 6C2.42881 6 1.76113 5.72344 1.26884 5.23116C0.776562 4.73887 0.5 4.07119 0.5 3.375C0.5 2.67881 0.776562 2.01113 1.26884 1.51884C1.76113 1.02656 2.42881 0.75 3.125 0.75ZM11 0.75C11.3447 0.75 11.6861 0.817897 12.0045 0.949816C12.323 1.08173 12.6124 1.27509 12.8562 1.51884C13.0999 1.7626 13.2933 2.05198 13.4252 2.37046C13.5571 2.68894 13.625 3.03028 13.625 3.375C13.625 3.71972 13.5571 4.06106 13.4252 4.37954C13.2933 4.69802 13.0999 4.9874 12.8562 5.23116C12.6124 5.47491 12.323 5.66826 12.0045 5.80018C11.6861 5.9321 11.3447 6 11 6C10.3038 6 9.63613 5.72344 9.14384 5.23116C8.65156 4.73887 8.375 4.07119 8.375 3.375C8.375 2.67881 8.65156 2.01113 9.14384 1.51884C9.63613 1.02656 10.3038 0.75 11 0.75ZM18.875 0.75C19.2197 0.75 19.5611 0.817897 19.8795 0.949816C20.198 1.08173 20.4874 1.27509 20.7312 1.51884C20.9749 1.7626 21.1683 2.05198 21.3002 2.37046C21.4321 2.68894 21.5 3.03028 21.5 3.375C21.5 3.71972 21.4321 4.06106 21.3002 4.37954C21.1683 4.69802 20.9749 4.9874 20.7312 5.23116C20.4874 5.47491 20.198 5.66826 19.8795 5.80018C19.5611 5.9321 19.2197 6 18.875 6C18.1788 6 17.5111 5.72344 17.0188 5.23116C16.5266 4.73887 16.25 4.07119 16.25 3.375C16.25 2.67881 16.5266 2.01113 17.0188 1.51884C17.5111 1.02656 18.1788 0.75 18.875 0.75Z"
                        fill="white" />
                </svg>
                <div class="list-item__title">Еще</div>
            </button>
        
        </div>
        <Transition name="fade">
    <div
      v-if="isShowMobilePanel"
      ref="modalPanel"
      class="modal modal-mob-add left-menu__mob-panel_leftmenu mob-panel_leftmenu"
      :class="{ active: isDark }"
      @click.self="toggleShowMobilePanel"
      style="display: flex; position: fixed; z-index: 1050; left: 0; top: 0; width: 100vw; height: 100vh;"
    >
      <div
        class="panel_leftmenu-swipe-area"
        @touchstart="onTouchStart"
        @touchmove="onTouchMove"
        @touchend="onTouchEnd"
      >
        <div @click="toggleShowMobilePanel" class="panel_leftmenu"></div>
      </div>
      <ul class="mob-panel_leftmenu__links">
        <template v-for="link in linksMobile.filter((l) => l.isMobPanel)">
            <a
            v-if="link.title === 'Помощь'"
            :href="link.to"
            target="_blank"
            rel="noopener noreferrer"
            class="mob-panel_leftmenu__link"
            >
            <Button variant="default">
                <component :is="link.icon" />
                <span class="mob-panel_leftmenu__link-title">{{ link.title }}</span>
            </Button>
            </a>
            <router-link
            v-else
            :to="link.to"
            @click.native="toggleShowMobilePanel"
            class="mob-panel_leftmenu__link"
            >
            <Button variant="default">
                <component :is="link.icon" @click="toggleShowMobilePanel" />
                <span class="mob-panel_leftmenu__link-title">{{ link.title }}</span>
            </Button>
            </router-link>
        </template>
        </ul>

      <div class="mob-panel_leftmenu__settings">
        <Button
          variant="default"
          @click.native="toggleMute"
          :class="['mob-panel_leftmenu__settings-button', { active: isMute }]"
        >
          <component :is="!isMute ? 'Mute' : 'Sound'" />
          <span>{{ !isMute ? "Выключен" : "Включен" }}</span>
        </Button>

        <router-link to="/ref" class="mob-panel_leftmenu__settings-button" @click.native="toggleShowMobilePanel">
          <Search />
          <span>Рефералы</span>
        </router-link>
      </div>

      <!-- Темы (если нужно) -->
      <div v-if="false" class="mob-panel_leftmenu__theme">
        <div class="mob-panel_leftmenu__theme-item">
          <Sun />
          <span>Светлая</span>
        </div>
        <button @click="toggleTheme" :class="['toggle-theme-button', { active: isLight }]">
          <div class="toggle-theme-button__circle">
            <div></div>
          </div>
        </button>
        <div class="mob-panel_leftmenu__theme-item">
          <span>Темная</span>
          <Moon />
        </div>
      </div>

      <div class="mob-panel_leftmenu__socials_leftmenu socials_leftmenu">
        <a
          v-for="contact in contacts"
          :key="contact.to"
          :href="contact.to"
          class="socials_leftmenu__item"
          target="_blank"
        >
          <img :src="contact.icon" class="socials_leftmenu__item-icon" alt="img" />
        </a>
      </div>

      <div class="mob-panel_leftmenu__agreements_leftmenu agreements_leftmenu">
        <router-link @click.native="toggleShowMobilePanel" class="agreements_leftmenu__item" to="/terms">
          Пользовательское соглашение
        </router-link>
        <router-link @click.native="toggleShowMobilePanel" class="agreements_leftmenu__item" to="/policy">
          Политика конфиденциальности
        </router-link>
        <div class="mob-panel_leftmenu__desc">
          <span>Stimule 一 {{ new Date().getFullYear() }}</span>
        </div>
      </div>

      <div class="mob-panel_leftmenu__agreements_leftmenu agreements_leftmenu">
        <button @click="logout" class="agreements_leftmenu__item">Выход</button>
      </div>
    </div>
  </Transition>
    </div>
</template>

<script>
import Online from "./Online.vue";
import Dice from "./ui/DiceSVG.vue";
import Help from "./ui/HelpSVG.vue";
import Search from "./ui/SearchSVG.vue";
import Button from "./ui/Button.vue";
import Bubbles from "./ui/BubblesSVG.vue";
import Mines from "./ui/MinesSVG.vue";
import Plinko from "./ui/PlinkoSVG.vue";
import Tournament from "./ui/TournamentSVG.vue";
import Wheel from "./ui/WheelSVG.vue";
import Sound from "./ui/SoundSVG.vue";
import Mute from "./ui/MuteSVG.vue";
import Moon from "./ui/MoonSVG.vue";
import Sun from "./ui/SunSVG.vue";
import logoUrl from '@img/logo.svg'
import muteIcon from '@img/mute.svg'
import unmuteIcon from '@img/unmute.svg'
import themeDarkSvg from '@img/left-menu/theme-dark.svg'
import themeLightSvg from '@img/left-menu/theme-light.svg'
import tgIcon from '@img/tg.svg'
import vkIcon from '@img/vk.svg'
import CupIcon from '@img/left-menu/cup.svg'
import SlotsIcon from '@img/left-menu/slots.svg'
import DiceIcon from '@img/left-menu/dice.svg'
import MinesIcon from '@img/left-menu/mines.svg'
import BubblesIcon from '@img/left-menu/bubbles.svg'
import WheelIcon from '@img/left-menu/wheel.svg'
import PlinkoIcon from '@img/left-menu/pinko.svg'
import HelpIcon from '@img/left-menu/help.svg'
import BonusesIcon from '@img/left-menu/bonuses.svg'
import PlayIcon from '@img/left-menu/play.svg'


import TournamentLink from "./TournamentLink.vue";
import { inject } from "vue";

export default {
    components: {
        Online,
        TournamentLink,
        Dice,
        Bubbles,
        Search,
        Help,
        Mines,
        Tournament,
        Wheel,
        Sound,
        Mute,
        Moon,
        Sun,
        Button,
    },
    setup() {
        const config = inject('config')

        return { config }
    },
    data() {
        return {
            logoUrl,
            muteIcon,
            unmuteIcon,
            themeLightSvg,
            themeDarkSvg,
            contacts: [
                {
                    to: this.config.tg_channel,
                    icon: tgIcon,
                },
                {
                    to: this.config.vk_url,
                    icon: vkIcon,
                },
            ],

            links: [
                {
                    to: "/tournament",
                    icon: CupIcon,
                    title: "Турниры",
                    isMobPanel: true,
                },
                {
                    to: "/slots",
                    icon: SlotsIcon,
                    title: "Slots",
                    isMobPanel: false,
                },
                {
                    to: "/dice",
                    icon: DiceIcon,
                    title: "Dice",
                    isMobPanel: true,
                },
                {
                    to: "/mines",
                    icon: MinesIcon,
                    title: "Mines",
                    isMobPanel: true,
                },
                {
                    to: "/bubbles",
                    icon: BubblesIcon,
                    title: "Bubbles",
                    isMobPanel: true,
                },
                {
                    to: "/wheel",
                    icon: WheelIcon,
                    title: "Wheel",
                    isMobPanel: true,
                },
                {
                    to: "/plinko",
                    icon: PlinkoIcon,
                    title: "Plinko",
                    isMobPanel: true,
                },
                {
                    to: "https://vk.com/im?sel=-225111416",
                    icon: HelpIcon,
                    title: "Помощь",
                    external: true,
                    isMobPanel: false,
                },
            ],
            linksMobPanel: [
                {
                    to: "/tournament",
                    icon: CupIcon,
                    title: "Турниры",
                    isMobPanel: true,
                },
                {
                    to: "/bonus",
                    icon: BonusesIcon,
                    title: "Бонусы",
                    requiresAuth: true,
                },
                {
                    to: "/",
                    icon: PlayIcon,
                    title: "Играть",
                },
                {
                    to: "/slots",
                    icon: SlotsIcon,
                    title: "Slots",
                },
            ],
            linksMobile: [
                {
                    to: "/dice",
                    icon: Dice,
                    title: "Dice",
                    isMobPanel: true,
                },
                {
                    to: "/mines",
                    icon: Mines,
                    title: "Mines",
                    isMobPanel: true,
                },
                {
                    to: "/bubbles",
                    icon: Bubbles,
                    title: "Bubbles",
                    isMobPanel: true,
                },
                {
                    to: "/wheel",
                    icon: Wheel,
                    title: "Wheel",
                    isMobPanel: true,
                },
                {
                    to: "/plinko",
                    icon: Plinko,
                    title: "Plinko",
                    isMobPanel: true,
                },
                {
                    to: "https://vk.com/im?sel=-225111416",
                    icon: Help,
                    title: "Помощь",
                    external: true,
                    isMobPanel: true,
                },
            ],
            position: { top: 0 },
            dragging: false,
            startY: 0,
            touchStartY: null,
            touchCurrentY: null,
            isPanelClosing: false,
        };
    },

    computed: {
        isDark() {
            return this.$store.state.isDark;
        },
        isMute() {
            return this.$store.state.isMute;
        },
        isShowMobilePanel() {
            return this.$store.state.isShowMobilePanel;
        },
    },

    methods: {
        handleClick(event, link) {
            if (this.isLinkDisabled(link)) {
                location.href = `/auth/vkontakte`;

                event.preventDefault();
                event.stopPropagation();

                return;
            }

            if (!link.external) {
                this.$router.push(link.to);
            } else {
                window.location.href = link.to;
            }

            this.toggleShowMobilePanel();
        },
        isLinkDisabled(link) {
            return link.requiresAuth && !(this.$root.user !== null);
        },
        async logout() {
            await this.$store.dispatch('logout');
            localStorage.removeItem('authToken');
            this.$store.commit('clearUserState');
            this.toggleShowMobilePanel();
            this.$router.push({ path: '/' });
        },
        toggleTheme() {
            this.$store.commit("toggleTheme");
        },
        toggleMute() {
            this.$store.commit("toggleMute");
        },
        toggleShowMobilePanel() {
            this.$store.commit("toggleShowMobilePanel");
        },
        onTouchStart(e) {
            if (!this.isShowMobilePanel) return;
            this.touchStartY = e.touches[0].clientY;
            this.touchCurrentY = e.touches[0].clientY;
        },
        onTouchMove(e) {
            if (!this.isShowMobilePanel || this.isPanelClosing) return;
            this.touchCurrentY = e.touches[0].clientY;
            const deltaY = this.touchCurrentY - this.touchStartY;
            if (deltaY > 0 && this.$refs.modalPanel) {
                this.$refs.modalPanel.style.transform = `translateY(${deltaY}px)`;
            }
        },
        onTouchEnd() {
            if (!this.isShowMobilePanel || this.isPanelClosing) return;
            const deltaY = this.touchCurrentY - this.touchStartY;
            if (deltaY > 80) {
                this.isPanelClosing = true;
                if (this.$refs.modalPanel) {
                    this.$refs.modalPanel.style.transition = 'transform 0.3s cubic-bezier(.4,0,.2,1)';
                    this.$refs.modalPanel.style.transform = 'translateY(100vh)';
                }
                setTimeout(() => {
                    this.isPanelClosing = false;
                    if (this.$refs.modalPanel) {
                        this.$refs.modalPanel.style.transition = '';
                        this.$refs.modalPanel.style.transform = '';
                    }
                    this.toggleShowMobilePanel();
                }, 300);
            } else {
                if (this.$refs.modalPanel) {
                    this.$refs.modalPanel.style.transition = 'transform 0.2s';
                    this.$refs.modalPanel.style.transform = '';
                }
                setTimeout(() => {
                    if (this.$refs.modalPanel) {
                        this.$refs.modalPanel.style.transition = '';
                    }
                }, 200);
            }
            this.touchStartY = null;
            this.touchCurrentY = null;
        },
    },
    watch: {
        isShowMobilePanel(newValue) {
            if (newValue) {
                document.body.style.overflowY = "hidden";
            } else {
                document.body.style.overflowY = "auto";
            }
        },
    },
};
</script>

<style>
    @media (max-width: 767px) {
        .modal {
            margin-top: auto;
            height: fit-content !important;
        }
    }
</style>

<style lang="scss" scoped>

.modal{
    display:flex!important;
    transition: transform 0.3s cubic-bezier(.4,0,.2,1);
    will-change: transform;
}

a:hover {
    text-decoration: none;
}

.fade-enter-active {
    transform: translateY(calc(100vh - 200px));
    transition: all 0.5s ease;
}

.fade-leave-active {
    transition: all 0.1s ease;
    transform: translateY(0%);
    //transition: all 0.1s ease;
}

.fade-enter-from {
    // transition: all 0.1s ease;
    transform: translateY(0%);
}

.fade-leave-to {
    // transition: all 0.1s ease;
    transform: translateY(calc(100vh - 200px));
    //transform: translateY(400px);
}

.left-menu::-webkit-scrollbar {
    width: 0px;
}

.left-menu {
    background-color: var(--color-content);
    padding: 12px;
    display: flex;
    flex-direction: column;
    //position: sticky;

    //height: 100%;
    position: fixed;
    height: 100vh;
    top: 0;
    width: 86px;
    flex-shrink: 0;
    z-index: 101;
    overflow: auto;
    overflow-x: hidden;

    &__logo {
        margin-bottom: 32px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        color: var(--color-text);
        font-size: 14px;
        font-weight: 600;

        div {
            font-family: "Oswald";
            font-weight: 400;
            margin-top: 4px;
            text-transform: uppercase;
        }
    }

    &__online {
        //margin-top: 145px;
        margin-top: auto;
    }

    &__list {
        margin-bottom: 32px;
    }

    .list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    &__desktop {
        display: flex;
        flex-grow: 1;
        flex-direction: column;
        align-items: center;
    }

    &__mob {

        display: none;

        &:not(.active) {
            .router-link-exact-active {
                background-color: var(--color-primary);
                padding: 4px 4px;
            }
        }

        .tournament-link.router-link-exact-active {
            background: none;
        }

        .tournament-link {
            &_disabled {
                opacity: 0.5;
                cursor: default;
                pointer-events: none;
            }
        }
    }

    .list-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
        color: var(--color-text);
        border-radius: 8px;
        width: 62px;
        align-items: center;
        padding: 2.5px 0;
        font-size: 14px;
        text-decoration: none;
        font-family: "Oswald" !important;
        text-transform: uppercase;

        &.disabled {
            pointer-events: none;
            opacity: 0.5;
        }

        &.active {
            background-color: var(--color-primary);
        }

        &__icon {
            width: 28px;
            height: 28px;
        }
    }

    .controls {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;

        &__item {
            height: 40px;
            width: 40px;
            flex-shrink: 0;
            padding: 0 !important;
            border-radius: 8px;
            background: #ffffff25;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }

    .socials_leftmenu {
        width: 100%;
        margin-top: auto;
        display: flex;
        justify-content: center;
        gap: 10px;
        align-items: center;
        padding: 10px 0;
        color: var(--color-text);
        font-size: 14px;
        font-weight: 600;

        &__item {
            width: 34px;
            height: 34px;
        }

        &__item-icon {
            width: 100%;
            height: 100%;
        }
    }
}

.mob-panel_leftmenu {

    transition: all 0.2s linear;
    display: flex;
    position: relative;
    flex-direction: column;
    align-items: center;
    background-color: #0e0d0f;
    width: 100%;
    border-top-right-radius: 8px;
    border-top-left-radius: 8px;
    gap: 10px;
    height: 100%;

    &::-webkit-scrollbar {
        width: 0;
    }

    overflow-y: auto;
    max-height: calc(100vh - 200px);
    padding: 52px 15px 28px 15px;
    bottom: 0;
    top:15%;
    border-radius: 20px 20px 0 0;

    &__links {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin: 0;
        padding: 0;
    }

    &__link {
        height: 42px;
        display: flex;
        border-radius: 4px;
        align-items: center;
        color: white;
        background-color: #ffffff05;

        span {
            font-family: "Oswald";
            font-size: 14px;
            text-transform: uppercase;
        }

        svg {
            fill: white;
        }

        button {
            width: 100%;
            display: flex;
            gap: 6px;
            border-radius: 4px;
            align-items: center;
            height: 42px;
            padding: 0 5px;

            @media (hover: hover) {
                &:hover {
                    svg {
                        fill: white !important;
                    }
                }
            }

            &:active {
                svg {
                    fill: white !important;
                }
            }
        }
    }

    .router-link-exact-active button {
        svg {
            fill: #ffffff;
        }

        color: #ffffff;
    }

    &__settings {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    &__settings-button {
        font-family: "Oswald";
        font-weight: 400;
        text-transform: uppercase;
        font-size: 14px;
        height: 42px;
        border-radius: 4px;
        color: white;
        gap: 6px;
        display: flex;
        background-color: #ffffff05;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;

        svg {
            fill: white;
            stroke: white;
        }

        &.active {
            background-color: var(--color-primary);
            color: white;

            svg {
                fill: white;
                stroke: white;
            }
        }

        @media (hover: hover) {
            &:hover {
                background-color: var(--color-primary-hover) !important;

                svg {
                    stroke: white;
                    fill: white;
                }
            }
        }

        &:active {
            background-color: var(--color-primary-active) !important;

            svg {
                stroke: white;
                fill: white;
            }
        }
    }

    &__theme {
        flex: none;
        height: 46px;
        display: flex;
        align-items: center;
        width: 100%;
        justify-content: space-around;
        background-color: #17191f25;
        border-radius: 8px;
    }

    &__theme-item {
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 300;
        color: #ffffff60;

        svg {
            fill: white;
        }
    }

    &__social {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .agreements_leftmenu {
        display: flex;
        flex-direction: column;
        width: 100%;
        gap: 8px;

        &__item {
            height: 42px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff05;
            color: white;
            border-radius: 4px;
        }

        &:last-child {}
    }

    &__desc {
        text-align: center;
        color: #ffffff30;
    }

    .socials_leftmenu {
        width: 100%;
        margin-top: auto;
        display: flex;
        justify-content: center;
        gap: 10px;
        align-items: center;
        padding: 10px 0;
        color: var(--color-text);
        font-size: 14px;
        font-weight: 600;

        &__item {
            width: 34px;
            height: 34px;
        }

        &__item-icon {
            width: 100%;
            height: 100%;
        }
    }

    &.active {
        .mob-panel_leftmenu__link {
            background-color: #ffffff05;

            svg {
                fill: white;
            }
        }

        .mob-panel_leftmenu__settings-button {
            background-color: #ffffff07;

            &.active {
                background-color: var(--color-primary);
            }

            color: #ffffff;

            svg {
                fill: white;
                stroke: white;
            }

            &.active svg {
                fill: white;
                stroke: white;
            }
        }

        .mob-panel_leftmenu__theme-item {
            color: #ffffff60;

            svg {
                fill: white;
            }
        }

        .mob-panel_leftmenu__theme {
            background-color: #17191f25;
        }

        .agreements_leftmenu__item {
            background-color: #ffffff05;
        }
    }
}

.panel_leftmenu-swipe-area {
    width: 100%;
    height: 48px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    position: fixed;
    top:5px;
    z-index: 2;
}

.panel_leftmenu {
    position: absolute;
    top: 20px;
    width: 150px;
    height: 5px;
    border-radius: 4px;
    background-color: white;
    cursor: pointer;
    transition: transform 0.3s cubic-bezier(.4,0,.2,1);
    will-change: transform;
}

.toggle-theme-button {
    $p: &;
    height: 30px;
    width: 50px;
    background-color: #8d24d515;
    border-radius: 50px;
    display: flex;
    position: relative;
    align-items: center;
    justify-content: center;

    &:focus-visible {
        outline: none;
    }

    &__circle {
        position: absolute;
        transition: all 0.3s;
        transform: translateX(-50%);
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border: 4px solid var(--color-primary);
        border-radius: 50%;
        padding: 4px;

        div {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: var(--color-primary);
        }
    }

    &.active {
        #{$p} {
            &__circle {
                transform: translateX(50%);
            }
        }
    }
}

@media (max-width: 1024px) {
    .left-menu {
        position: fixed;
        bottom: 0;
        top: unset;

        order: 5;
        background-color: #0e0d0f;
        width: 100%;
        height: 70px;
        padding: 0;
        flex-shrink: 0;
        flex-grow: 0;

        &__desktop {
            display: none;
        }

        &__mob {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            width: 100%;
        }

        .list-item {
            width: 100%;
            height: 100%;
            background: none;
            flex-direction: column;
            padding: 0px 10px !important;
            font-size: 12px;
            gap: 0;

            &:last-child {
                svg {
                    margin-bottom: 10px;
                }
            }

            .list-item__icon {
                height: 24px;
                width: 24px;
            }
        }
    }
}

@media (max-width: 480px) {
    .mob-panel_leftmenu {
        &__settings-button {
            font-size: 12px;
        }
    }
}
</style>
