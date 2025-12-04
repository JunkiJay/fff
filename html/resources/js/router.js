import { createRouter, createWebHistory } from "vue-router";
const Main = () => import("./pages/Main/index.vue");
const Dice = () => import("./pages/Dice/index.vue");
const Mines = () => import("./pages/Mines/index.vue");
const Bubbles = () => import("./pages/Bubbles/index.vue");
const Wheel = () => import("./pages/Wheel/index.vue");
const Slots = () => import("./pages/Slots/index.vue");
const Slot = () => import("./pages/Slot/index.vue");
const Plinko = () => import("./pages/Plinko/index.vue");
const Bonus = () => import("./pages/Bonus/index.vue");
const Ref = () => import("./pages/Ref/index.vue");
const Faq = () => import("./pages/Faq/index.vue");
const Terms = () => import("./pages/Terms/index.vue");
const Policy = () => import("./pages/Policy/index.vue");
const Payment = () => import("./pages/Payment/index.vue");
const Withdraw = () => import("./pages/Withdraw/index.vue");
const Profile = () => import("./pages/Profile/index.vue");
const Tournament = () => import("./pages/Tournament/index.vue");
const TournamentsWeek = () => import("./pages/Tournament/TournamentsWeek.vue");
const TournamentWeek = () => import("./pages/Tournament/index.vue");
const AuthModal = () => import("@resources/js/components/modals/auth/AuthModal.vue");
// import AuthModal from "@resources/js/components/modals/auth/AuthModal.vue";

import store from "@/store";

const routes = [
    {
        path: "/",
        name: "main",
        component: Main,
    },
    {
        path: "/register",
        name: "register",
        component: AuthModal,
        meta: { showAuthModal: true, isRegistration: true },
    },
    {
        path: "/login",
        name: "login",
        component: AuthModal,
        meta: { showAuthModal: true, isRegistration: false },
    },
    {
        path: "/logout",
        name: "logout",
        beforeEnter: async (to, from, next) => {
            try {
                await store.dispatch("logout");
            } catch (error) {
                console.error("Ошибка при выполнении logout:", error);
            }
            next("/");
        },
    },
    {
        path: "/profile",
        name: "profile",
        component: Profile,
    },
    {
        path: "/dice",
        name: "dice",
        component: Dice,
    },
    {
        path: "/mines",
        name: "mines",
        component: Mines,
    },
    {
        path: "/bubbles",
        name: "bubbles",
        component: Bubbles,
    },
    {
        path: "/plinko",
        name: "plinko",
        component: Plinko,
    },
    {
        path: "/wheel",
        name: "wheel",
        component: Wheel,
    },
    {
        path: "/slots",
        name: "slots",
        component: Slots,
    },
    {
        path: "/slots/game/:id/:type?",
        name: "slot",
        component: Slot,
    },
    {
        path: "/ref",
        name: "ref",
        component: Ref,
    },
    {
        path: "/bonus",
        name: "bonus",
        component: Bonus,
    },
    {
        path: "/pay",
        name: "payment",
        component: Payment,
    },
    {
        path: "/withdraw",
        name: "withdraw",
        component: Withdraw,
    },
    {
        path: "/faq",
        name: "faq",
        component: Faq,
    },
    {
        path: "/terms",
        name: "terms",
        component: Terms,
    },
    {
        path: "/policy",
        name: "policy",
        component: Policy,
    },
    {
        path: "/tournament/week",
        name: "tournament-week",
        component: TournamentsWeek,
    },
    {
        path: "/tournament",
        name: "tournament",
        component: TournamentsWeek,
    },
];

const router = createRouter({
    mode: "history",
    base: process.env.BASE_URL,
    routes,
    history: createWebHistory(),
    scrollBehavior(to, from, savedPosition) {
        // Если есть сохраненная позиция прокрутки, используем ее
        if (savedPosition) {
            return savedPosition;
        } else {
            // В противном случае прокручиваем страницу наверх
            return { x: 0, y: 0 };
        }
    },
});

// Глобальная защита от битых ссылок слотов
router.beforeEach((to, from, next) => {
    if (to.name === "slot") {
        const rawId = to.params.id;
        const numericId = parseInt(rawId, 10);

        // Разрешаем только числовые ID, иначе возвращаем в лобби слотов
        if (!rawId || Number.isNaN(numericId) || numericId <= 0) {
            console.error("Blocked invalid slot route ID:", rawId);
            return next({ name: "slots" });
        }

        // Нормализуем ID (на случай, если был строкой)
        to.params.id = String(numericId);
    }

    next();
});

export default router;
