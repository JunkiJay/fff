import axios from "@/plugins/axios";

const state = () => ({
    methods: [],
    providers: [],
    userWithdraws: [],
    userPayments: [],
    userProvidersLoading: true,
    userWithdrawsLoading: true,
    userPaymentsLoading: true,
    error: null
});

const getters = {
    userWithdraws: (s) => s.userWithdraws,
    userPayments: (s) => s.userPayments,
    availablePaymentMethods: (s) => {
        let available = [];

        s.providers.forEach((provider) => {
            if (provider.payment !== undefined && Array.isArray(provider.payment)) {
                provider.payment.forEach((payment) => {
                    if (!payment || !payment.method) return;
                    
                    const method = s.methods[payment.method];
                    if (!method) return;
                    
                    available.push(
                        {
                            title: method.title,
                            icon: method.icon,
                            min: payment.min,
                            max: payment.max,
                            hot: payment.hot,
                            bonus_percent: payment.bonus_percent,
                            method: payment.method,
                            provider: provider.provider
                        }
                    );
                });
            }
        });

        return available;
    },
    availableWithdrawMethods: (s) => {
        let available = [];
        s.providers.forEach((provider) => {
            if (provider.withdraw !== undefined && Array.isArray(provider.withdraw)) {
                provider.withdraw.forEach((withdraw) => {
                    if (!withdraw || !withdraw.method) return;
                    
                    const method = s.methods[withdraw.method];
                    if (!method) return;

                    let data = {
                        title: method.title,
                        icon: method.icon,
                        min: withdraw.min,
                        max: withdraw.max,
                        hot: withdraw.hot,
                        method: withdraw.method,
                        provider: provider.provider,
                        commission_percents: withdraw.commission_percents,
                        wallet_input_title: method.wallet_input_title,
                        wallet_input_placeholder: method.wallet_input_placeholder
                    };
                    if (withdraw.variants && Array.isArray(withdraw.variants) && withdraw.variants.length) {
                        data.variants = withdraw.variants;
                    }

                    available.push(data);
                });
            }
        });
        return available;
    }
};

const mutations = {
    setMethods(state, methods) {
        state.methods = methods;
    },
    setProviders(state, providers) {
        state.providers = Array.isArray(providers) ? providers : [];
    },
    setUserProvidersLoading(state, flag) {
        state.userProvidersLoading = !!flag;
    },
    setUserWithdrawsLoading(state, flag) {
        state.userProvidersLoading = !!flag;
    },
    setUserWithdraws(state, userWithdraws) {
        state.userWithdraws = Array.isArray(userWithdraws) ? userWithdraws : [];
    },
    setUserPaymentLoading(state, flag) {
        state.userPaymentsLoading = !!flag;
    },
    setUserPayments(state, userPayments) {
        state.userPayments = Array.isArray(userPayments) ? userPayments : [];
    },
    setError(state, err) {
        state.error = err || null;
    },
};

const actions = {
    async fetchPaymentMethods({ state, commit }, { force = false, userId = null } = {}) {
        if (state.providers.length && !force) return;

        if (userId === null) {
            return;
        }

        commit("setUserProvidersLoading", true);
        commit("setError", null);

        try {
            const response = await axios.post(`/api/v1/user/${userId}/payments/list`);
            const data = response && response.data ? response.data : {};

            // Нормализуем возможные форматы ответа
            let methods = {};
            let providers = [];

            if (data.methods && typeof data.methods === "object") {
                methods = data.methods;
            } else if (Array.isArray(data.data)) {
                methods = data.data;
            }

            if (Array.isArray(data.providers)) {
                providers = data.providers;
            } else if (data.providers && typeof data.providers === "object") {
                providers = Object.values(data.providers);
            }

            // Коммитим через мутации
            commit("setProviders", providers);
            commit("setMethods", methods);
        } catch (e) {
            commit("setError", e);
            // опционально: console.error(e);
        } finally {
            commit("setUserProvidersLoading", false);
        }
    },

    async fetchUserWithdraws({ state, commit }) {
        commit("setUserWithdrawsLoading", true);
        commit("setError", null);

        try {
            const payload = {
                "available_fields": [
                    "id",
                    "sum",
                    "sumWithCom",
                    "image",
                    "wallet",
                    "system",
                    "status",
                    "status_human_name",
                    "created_at",
                    "reason"
                ],
                "sort": {
                    "created_at": "desc"
                }
            };

            const response = await axios.post(`/api/v1/user/withdraws/list?per_page=200`, payload);
            const data = response && response.data ? response.data : {};

            commit("setUserWithdraws", data);
        } catch (e) {
            commit("setError", e);
        } finally {
            commit("setUserWithdrawsLoading", false);
        }
    },

    async fetchUserPayment({ state, commit }) {
        commit("setUserPaymentLoading", true);
        commit("setError", null);

        try {
            const payload = {
                "available_fields": [
                    "id",
                    "sum",
                    "image",
                    "system",
                    "status",
                    "status_human_name",
                    "created_at"
                ],
                "limit": 200,
                "sort": {
                    "created_at": "desc"
                }
            };

            const response = await axios.post(`/api/v1/user/payments/list?per_page=200`, payload);
            const data = response && response.data ? response.data : {};

            commit("setUserPayments", data);
        } catch (e) {
            commit("setError", e);
        } finally {
            commit("setUserPaymentLoading", false);
        }
    },

    setPaymentMethods({ commit }, methods) {
        commit("setMethods", methods);
    },
    setPaymentProviders({ commit }, providers) {
        commit("setProviders", providers);
    }
};

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
};