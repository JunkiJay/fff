// src/store/index.js

import Vue from "vue";
import Vuex, { createStore } from "vuex";
import axios from "@/plugins/axios";
import payments from "./payments";
import tournaments from "./tournaments";


const store = createStore({
    modules: { payments, tournaments },
    state: {
        user: null,
        config: {},
        isLoading: true,
        isMute: JSON.parse(localStorage.getItem("isMute")) || true,
        isDark: JSON.parse(localStorage.getItem("isDark")) || true,
        isShowMobilePanel: false,
        plinkoCoeff: [],
    },
    mutations: {
        setUser(state, userData) {
            if (userData && userData.user) {
                state.user = { ...userData.user };
            } else if (userData) {
                state.user = { ...userData };
            } else {
                state.user = null;
            }
            // Обновляем баланс напрямую, если передан отдельно
            if (userData && userData.balance !== undefined) {
                state.user = {
                    ...state.user,
                    balance: userData.balance,
                };
            }
        },
        setConfig(state, config) {
            state.config = config;
        },
        setLoading(state, loading) {
            state.isLoading = loading;
        },
        toggleMute(state) {
            state.isMute = !state.isMute;
            localStorage.setItem("isMute", state.isMute);
        },
        toggleTheme(state) {
            state.isDark = !state.isDark;
            localStorage.setItem("isDark", state.isDark);
        },
        toggleShowMobilePanel(state) {
            state.isShowMobilePanel = !state.isShowMobilePanel;
            localStorage.setItem("isShowMobilePanel", state.isShowMobilePanel);
        },
        setPlinkoCoeff(state, value) {
            state.plinkoCoeff = value;
        },
        clearUserState(state) {
            state.user = null;
            state.config = {};
            state.isLoading = false;
        },
    },
    actions: {
        // Получить пользователя через сессию
        async fetchUser({ commit }) {
            try {
                const { data } = await axios.get("/user/init");
                if (data && data.user) {
                    commit("setUser", data.user);
                    if (data.config) {
                        commit("setConfig", data.config);
                    }
                } else {
                    commit("setUser", null);
                    commit("setConfig", {});
                }
            } catch (e) {
                commit("setUser", null);
                commit("setConfig", {});
            }
        },
        // Инициализация приложения — получить пользователя и конфиг
        async init({ commit, dispatch }) {
            commit("setLoading", true);
            try {
                await dispatch("fetchUser");
                // config теперь всегда приходит вместе с user/init и кладётся в стор
            } catch {
                commit("clearUserState");
            } finally {
                commit("setLoading", false);
            }
        },
        // Логин через сессию
        async login({ dispatch }, { email, password }) {
            await axios.post("/auth/login", { email, password });
            await dispatch("fetchUser");
        },
        // Регистрация через сессию
        async register({ dispatch }, { email, password, confirmPassword }) {
            await axios.post("/auth/register", {
                email,
                password,
                c_password: confirmPassword,
            });
            await dispatch("fetchUser");
        },
        // Выход из системы
        async logout({ commit }) {
            try {
                await axios.post("/auth/logout");
            } catch (error) {
            } finally {
                commit("clearUserState");
                // Сбросить клиентские куки Laravel (опционально)
                document.cookie = "XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie = "laravel_session=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            }
        },
    },
});

export default store;