// Vue 3 compatible imports
import { createBootstrap } from 'bootstrap-vue-next';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css';
import moment from "moment";
import "moment/locale/ru";
import "../../../resources/scss/app.scss";

// Export plugin installation function for Vue 3
export default function (app) {
    // Install Bootstrap Vue Next
    app.use(createBootstrap());
    
    // Moment.js global property
    app.config.globalProperties.$moment = moment;
    
    // Note: Other plugins need to be replaced with Vue 3 compatible versions:
    // - vue-notification → vue3-notification (already in package.json)
    // - vue-cookies → vue3-cookies (already in package.json)  
    // - v-clipboard → use navigator.clipboard API or @vueuse/core
    // - vue-fullscreen → use @vueuse/core or native Fullscreen API
    // - vue-match-media → use @vueuse/core useMediaQuery
    // - portal-vue → use built-in <Teleport> component
    // - vue-clickaway → use @vueuse/core onClickOutside
    // - vue-observe-visibility → use @vueuse/core useIntersectionObserver
}
