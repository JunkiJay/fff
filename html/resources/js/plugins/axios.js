import axios from "axios";
import store from "@/store";
import router from "@/router";

// axios.defaults.baseURL = "https://test5215.fun";
// axios.defaults.baseURL = "http://127.0.0.1:8000";
axios.defaults.baseURL = process.env.APP_URL;
axios.defaults.withCredentials = true; // Отправлять cookies для сессионной аутентификации

axios.interceptors.request.use(
    (config) => {
        const token =
            localStorage.getItem("authToken") || sessionStorage.getItem("authToken");
        if (token) {
            config.headers["Authorization"] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response && error.response.status === 401) {
            const url = error.config?.url || "";
            // Не трогаем 401:
            //  - на /auth/login и /auth/register (форма сама показывает ошибку)
            //  - на /user/init (может временно вернуть 401 сразу после /auth/login)
            if (
                url.startsWith("/auth/login") ||
                url.startsWith("/auth/register") ||
                url.startsWith("/user/init")
            ) {
                return Promise.reject(error);
            }
            // Удаляем токены и перенаправляем на страницу логина
            localStorage.removeItem("authToken");
            store.dispatch("logout");
            router.push({ name: "Login" });
        }
        return Promise.reject(error);
    }
);

export default axios;