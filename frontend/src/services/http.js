import axios from 'axios';
import router from '../router';

const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const http = axios.create({
    baseURL: baseURL,
    withCredentials: true, // Permite receber os cookies
    withXSRFToken: true,   // Permite LER o cookie e enviar no cabeçalho X-XSRF-TOKEN
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest' 
    }
});
// Interceptador: Anexa o token Bearer (para rotas da API)
http.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Interceptador: Tratamento de erros globais
http.interceptors.response.use(response => {
    return response;
}, error => {
    // Se der erro 401 (Token expirado ou inválido) ou 419 (Sessão expirada)
    if (error.response && (error.response.status === 401 || error.response.status === 419)) {
        console.warn('Sessão expirada. Redirecionando para login...');
        localStorage.removeItem('token');
        
        // Evita loop de redirecionamento se já estiver no login
        if (window.location.pathname !== '/login') {
            router.push('/login');
        }
    }
    return Promise.reject(error);
});

export default http;