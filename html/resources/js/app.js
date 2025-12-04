import { createApp } from "vue";
import App from "@/components/Layout.vue";
import router from "@/router";
import store from "@/store";
import "@/plugins/axios";
import installPlugins from "@/plugins";
import socket from "@/plugins/socket";
import ModalPlugin from "@/plugins/modal";
import mitt from "mitt";
import { BModal } from "bootstrap-vue-next";
import Notifications from "@kyvg/vue3-notification";
import VueFullscreen from "vue-fullscreen";
import VueClipboard from 'vue3-clipboard'


(async function bootstrap() {
    try {
        await store.dispatch("init");
    } catch (e) {
        console.warn("Не удалось получить пользователя при старте");
    } finally {
        const app = createApp(App);
        
        // Configure app
        app.config.globalProperties.$socket = socket;
        const emitter = mitt()
        app.config.globalProperties.$emitter = emitter
        app.provide('emitter', emitter)
        installPlugins(app);

        // Use plugins
        app.use(BModal);
        app.use(store);
        app.use(router);
        app.use(Notifications);
        app.use(VueFullscreen);
        app.use(ModalPlugin);
        app.use(VueClipboard, {
            autoSetContainer: true,
            appendToBody: true,
        })


        // Provide global data
        app.provide('user', store.state.user);
        app.provide('config', store.state.config);
        
        // Mount app
        const vm = app.mount("#root");
        
        // Authenticate socket if user exists
        if (store.state.user) {
            socket.emit('authenticate', store.state.user.id);
        }
    }
})();
