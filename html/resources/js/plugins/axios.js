import axios from "axios";

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
            // Удаляем токены и перенаправляем на страницу логина
            localStorage.removeItem("authToken");
            store.dispatch("logout");
            router.push({ name: "Login" });
        }
        return Promise.reject(error);
    }
);

export default axios;