<template>
    <main class="main">
        <LeftMenu :page="page" />
        <RingLoader v-if="$root.isLoading" />

        <div
            :class="['main__right container', { 'unauthenticated': !isAuthenticated }]"
        >
            <Header :page="page" />
            <div :class="['main__view', { disabled: isShowMobilePanel }]">
                <router-view></router-view>
                <Footer class="mt-auto z-[2]" />
            </div>

            <notifications position="bottom right" />
        </div>

        <portal-target class="modal" name="modals" />
        <ConnectTG />
    </main>
</template>

<script>
import Header from "./Header.vue";
import LeftMenu from "./LeftMenu.vue";
import Footer from "./Footer.vue";
import History from "./History.vue";
import RingLoader from "./ui/loader/Ring.vue";
import ConnectTG from "@/components/modals/ConnectTG.vue";
import { useNotification } from "@kyvg/vue3-notification";

export default {
    components: {
        LeftMenu,
        Header,
        Footer,
        History,
        RingLoader,
        ConnectTG,
    },

    data() {
        return {
            page: null,
            darkTheme: false,
        };
    },

    beforeMount() {
        this.page = this.$router.currentRoute.name;
    },

    beforeUpdate() {
        this.page = this.$router.currentRoute.name;
    },

    computed: {
        isDark() {
            return this.$store.state.isDark;
        },

        isShowMobilePanel() {
            return this.$store.state.isShowMobilePanel;
        },

        isAuthenticated() {
            const token = localStorage.getItem('authToken') || sessionStorage.getItem('authToken');
            return Boolean(token);
        }
    },

    mounted() {
        this.$emitter.on("open", this.openPage);
        this.$emitter.on("noty", ({ title, text, type }) => {
            const { notify } = useNotification();
            notify({ title, text, type });

        });
    },

    methods: {
        openPage(name) {
            this.$router.push({ name }).catch((err) => {});
        },

    },
}
</script>

<style lang="scss" scoped>
.dark-lighter {
    position: absolute;
    top: 1400px;
    z-index: 1;
}
body.dark {
    .main {
        // background-image: url("@img/bg_test.png");
        position: relative;
    }
    .dark-lighter {
        display: block;
    }
    background-color: inherit;
}

.main {
    overflow: hidden;
    .dark-lighter {
        display: none;
    }
    // background-image: url("@img/bg_light.png");
    display: flex;
    position: relative;
    background-size: cover;
    background-repeat: no-repeat;
    min-height: 100vh;
    justify-content: space-between;
    &__right {
        //overflow: hidden;
        max-width: 1140px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        width: 100%;
        gap: 32px;
        margin-bottom: 23px;
        padding-bottom: 15px;
        padding-right: 15px;
        padding-left: 15px;
    }

    &__view {
        display: flex;
        flex-direction: column;
        width: 100%;
        transition: all;
        transition-duration: 0.5s;
        gap: 32px;
        flex-grow: 1;

        &.disabled {
            filter: blur(10px);
            pointer-events: none;
        }

        @media (max-width: 1024px) {
            padding-top: 90px;
        }
    }
}
@media (max-width: 1024px) {
    .main {
        flex-direction: column;

        &__right {
            overflow: initial;
            margin-top: 0px;
            margin-bottom: 0px;
            //padding-left: 15px;
          padding-bottom: 80px;
          padding-left: 0;
          padding-right: 0;
        //   padding-top: 96px;
        }
    }
    body.dark .main {
        background-image: none;
    }
    body.dark .dark-lighter {
        .dark-lighter {
            display: none !important;
        }
    }
}

@media (min-width: 1025px) and (max-width: 1300px) {
    .main {
        &__right {
            //width: calc(100% - 86px) !important;
            width: calc(100% - 86px);
            margin-left: 86px;
        }
    }
}

@media (max-width: 568px) {
  .main {
    &__right {
    //   padding-top: 122px;
        &.unauthenticated {
            padding-top: 42px;
        }
    }
  }
}
</style>
