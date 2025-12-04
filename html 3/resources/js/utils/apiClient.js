// javascript
import axios from 'axios';

const apiClient = axios.create({
  baseURL: '/', // при необходимости подставьте ваш baseURL
  withCredentials: true, // если нужны куки
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
  },
});

// Каждый запрос будет брать токен из localStorage
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('api_token');
  if (token) {
    config.headers = config.headers || {};
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => Promise.reject(error));

// Утилиты для явной установки/удаления токена (удобно использовать при логине/логауте)
export function setApiToken(token) {
  if (token) {
    localStorage.setItem('api_token', token);
  } else {
    localStorage.removeItem('api_token');
  }
}

export function removeApiToken() {
  localStorage.removeItem('api_token');
}

export default apiClient;