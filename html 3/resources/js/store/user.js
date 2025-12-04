import axios from "@/plugins/axios";

const state = () => ({
    user: null,
    error: null,
});

const getters = {
    user: (s) => s.user,
}

const mutations = {
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
    }
}

const actions = {
    async fetchUser({ commit }) {
        try {
            const { data } = await axios.get("/user/init");
            if (data && data.user) {
                commit("setUser", data.user);
            }
        } catch (e) {
            commit("setUser", null);
            commit("setConfig", {});
        }
    }
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
};