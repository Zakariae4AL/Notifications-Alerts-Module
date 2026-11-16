import axios from "axios";

export const TOKEN_KEY = "na.token";
export const LOCALE_KEY = "na.locale";

const client = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? "http://localhost:8000/api/v1",
  headers: { Accept: "application/json" },
});

client.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY);
  if (token) config.headers.Authorization = `Bearer ${token}`;
  config.headers["Accept-Language"] = localStorage.getItem(LOCALE_KEY) ?? "en";
  return config;
});

client.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem(TOKEN_KEY);
      window.dispatchEvent(new Event("auth:expired"));
    }
    return Promise.reject(error);
  },
);

/** First validation message from a Laravel 422 response, or a generic fallback. */
export function errorMessage(error, fallback) {
  const errors = error.response?.data?.errors;
  if (errors) return Object.values(errors)[0][0];
  return error.response?.data?.message ?? fallback;
}

export default client;
