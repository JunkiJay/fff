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
            // ВАЖНО:
            //  - здесь НЕ нужно ловить ошибки и очищать user,
            //    иначе при временном 401 после успешного /auth/login
            //    мы "выбиваем" пользователя из стора
            const { data } = await axios.get("/user/init");

            if (data && data.user) {
                commit("setUser", data.user);
                if (data.config) {
                    commit("setConfig", data.config);
                }
            } else {
                // Если запрос успешен, но пользователя нет — очищаем состояние
                commit("clearUserState");
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
        async login({ commit, dispatch }, { email, password, remember }) {
            // Сначала пробуем использовать ответ /auth/login,
            // чтобы не зависеть от второго запроса /user/init
            const { data } = await axios.post("/auth/login", { email, password, remember });

            // Проверяем наличие ошибки в ответе
            if (data && data.error) {
                throw new Error(data.error === 'Unauthorized' ? 'Неверный email или пароль' : data.error);
            }

            if (data && data.user) {
                commit("setUser", data);
                // Аутентифицируем сокет после логина
                if (window.$socket && data.user && data.user.id) {
                    window.$socket.emit('authenticate', data.user.id);
                }
            } else {
                // Если нет пользователя в ответе, выбрасываем ошибку
                throw new Error('Неверный email или пароль');
            }

            // Дополнительно обновляем пользователя и конфиг из /user/init,
            // но если он по какой‑то причине вернёт 401, текущий user в сторе уже есть
            try {
                await dispatch("fetchUser");
                // Аутентифицируем сокет после получения пользователя
                if (window.$socket && store.state.user && store.state.user.id) {
                    window.$socket.emit('authenticate', store.state.user.id);
                }
            } catch (e) {
                // игнорируем — авторизация уже есть из /auth/login
            }
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

// Глобальный polling для проверки новых успешных пополнений (БЕЗ Socket.io)
let paymentPollingInterval = null;
let shownPaymentIds = new Set(); // Множество уже показанных ID пополнений
let lastBalance = null;
let lastBalanceCheckTime = null;
let isInitialized = false; // Флаг инициализации - чтобы не показывать старые платежи при первом запуске

function startPaymentPolling(store) {
    // Останавливаем предыдущий polling, если есть
    if (paymentPollingInterval) {
        clearInterval(paymentPollingInterval);
    }
    
    const user = store.state.user;
    if (!user || !user.id) {
        return;
    }
    
    // Инициализируем: загружаем уже показанные ID из localStorage
    const storedIds = localStorage.getItem('shownPaymentIds');
    if (storedIds) {
        try {
            shownPaymentIds = new Set(JSON.parse(storedIds));
        } catch (e) {
            shownPaymentIds = new Set();
        }
    } else {
        shownPaymentIds = new Set();
    }
    
    // Инициализируем баланс
    lastBalance = user.balance || 0;
    lastBalanceCheckTime = Date.now();
    isInitialized = false; // Сбрасываем флаг при новом запуске
    
    // Инициализация: помечаем все текущие успешные платежи как показанные
    // (чтобы не показывать старые платежи при загрузке страницы)
    (async () => {
        try {
            const payload = {
                "available_fields": ["id", "sum", "status", "created_at"],
                "limit": 50,
                "sort": { "created_at": "desc" }
            };
            
            const response = await axios.post('/api/v1/user/payments/list?per_page=50', payload);
            const payments = response?.data?.data || response?.data || [];
            
            // Помечаем все текущие успешные платежи как показанные
            payments.forEach(p => {
                if (p.status === 1 && p.id) {
                    shownPaymentIds.add(p.id);
                }
            });
            
            // Сохраняем в localStorage
            localStorage.setItem('shownPaymentIds', JSON.stringify(Array.from(shownPaymentIds)));
            
            isInitialized = true;
            console.log('✅ Payment polling инициализирован, показано ID:', shownPaymentIds.size);
        } catch (e) {
            // Если не удалось инициализировать, просто продолжаем
            isInitialized = true;
        }
    })();
    
    // Проверяем каждые 3 секунды
    paymentPollingInterval = setInterval(async () => {
        const currentUser = store.state.user;
        if (!currentUser || !currentUser.id) {
            stopPaymentPolling();
            return;
        }
        
        // Не проверяем, пока не инициализировались
        if (!isInitialized) {
            return;
        }
        
        try {
            // Получаем последние успешные пополнения через API
            const payload = {
                "available_fields": ["id", "sum", "status", "created_at"],
                "limit": 20,
                "sort": { "created_at": "desc" }
            };
            
            const response = await axios.post('/api/v1/user/payments/list?per_page=20', payload);
            const payments = response?.data?.data || response?.data || [];
            
            // Фильтруем только свежие платежи (созданные за последние 10 минут)
            const now = Date.now();
            const tenMinutesAgo = now - (10 * 60 * 1000);
            
            // Находим все успешные пополнения (status = 1), которые:
            // 1. Еще не показывались
            // 2. Созданы не более 10 минут назад (чтобы не показывать старые)
            const newSuccessPayments = payments.filter(p => {
                if (p.status !== 1 || !p.id) return false;
                if (shownPaymentIds.has(p.id)) return false;
                
                // Проверяем время создания
                const createdAt = new Date(p.created_at || 0).getTime();
                if (createdAt < tenMinutesAgo) {
                    // Помечаем старые платежи как показанные, чтобы не проверять их снова
                    shownPaymentIds.add(p.id);
                    return false;
                }
                
                return true;
            });
            
            // Показываем уведомление только для самого свежего нового пополнения
            if (newSuccessPayments.length > 0) {
                // Сортируем по дате создания (самое свежее первым)
                newSuccessPayments.sort((a, b) => {
                    const dateA = new Date(a.created_at || 0);
                    const dateB = new Date(b.created_at || 0);
                    return dateB - dateA;
                });
                
                const newestPayment = newSuccessPayments[0];
                
                // Помечаем как показанное
                shownPaymentIds.add(newestPayment.id);
                
                // Сохраняем в localStorage (ограничиваем размер до 200 последних)
                if (shownPaymentIds.size > 200) {
                    const idsArray = Array.from(shownPaymentIds);
                    shownPaymentIds = new Set(idsArray.slice(-100)); // Оставляем только последние 100
                }
                localStorage.setItem('shownPaymentIds', JSON.stringify(Array.from(shownPaymentIds)));
                
                // Отправляем событие через window для показа уведомления
                if (window.$emitter) {
                    window.$emitter.emit('depositConfirmation', {
                        amount: newestPayment.sum || newestPayment.amount || 0
                    });
                    console.log('🔔 Показано уведомление о пополнении:', newestPayment.sum, 'ID:', newestPayment.id);
                }
                
                // Обновляем баланс пользователя
                try {
                    const userResponse = await axios.get('/user/init');
                    if (userResponse?.data?.user) {
                        store.commit('setUser', userResponse.data.user);
                        lastBalance = userResponse.data.user.balance || 0;
                    }
                } catch (e) {
                    // Игнорируем ошибки обновления баланса
                }
            }
            
            // УБРАНО: проверка изменения баланса, чтобы не показывать дубликаты
        } catch (error) {
            // Игнорируем ошибки polling (не логируем, чтобы не засорять консоль)
        }
    }, 3000); // Проверяем каждые 3 секунды
}

function stopPaymentPolling() {
    if (paymentPollingInterval) {
        clearInterval(paymentPollingInterval);
        paymentPollingInterval = null;
    }
    lastBalance = null;
    lastBalanceCheckTime = null;
    // НЕ очищаем shownPaymentIds, чтобы они сохранились между сессиями
}

// Запускаем polling при изменении пользователя
store.watch(
    (state) => state.user,
    (newUser, oldUser) => {
        if (newUser && newUser.id) {
            // Пользователь залогинился - запускаем polling
            startPaymentPolling(store);
        } else {
            // Пользователь разлогинился - останавливаем polling
            stopPaymentPolling();
        }
    }
);

// Запускаем polling сразу, если пользователь уже залогинен
if (store.state.user && store.state.user.id) {
    startPaymentPolling(store);
}

export default store;