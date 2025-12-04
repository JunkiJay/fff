export default {
    install(app) {
        app.config.globalProperties.$clipboard = async (text) => {
            if (!text) return;
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text);
                return;
            }
            // Фолбэк для старых браузеров
            const el = document.createElement('textarea');
            el.value = String(text);
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        };
    }
}