import { modalBus } from '@/plugins/modalBus';

export default {
    install(app) {
        app.config.globalProperties.$modal = {
            show(id, payload) {
                modalBus.emit('open', { id, payload });
            },
            hide(id) {
                modalBus.emit('hide', { id });
            },
            toggle(id, payload) {
                modalBus.emit('toggle', { id, payload });
            },
        };
    },
};