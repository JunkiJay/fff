import axios from "@/plugins/axios";

const state = () => ({
    lastTournaments: []
});

const getters = {};

const mutations = {
    setLastTournaments(state, methods) {
        state.lastTournaments = methods;
    },
};

const actions = {
    async fetchTournaments({ state, commit }) {
        try {
            const payload = {
                "available_fields": [],
                "sort": {
                    "created_at": "desc"
                }
            };

            const response = await axios.post(`/api/v1/tournaments/list?per_page=10`, payload);
            const data = response && response.data ? response.data : {};

            commit("setLastTournaments", data);
        } catch (e) {
            console.error(e)
        }
    },

    setLastTournaments({ commit }, methods) {
        commit("setLastTournaments", methods);
    },
};

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
};