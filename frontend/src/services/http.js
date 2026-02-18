import axios from 'axios';
import router from '../router';

const http = axios.create({
    // 1. GARANTIA DE IP: Usa o IP da rede em vez de localhost (para o celular funcionar)
    baseURL: import.meta.env.VITE_API_URL || 'http://192.168.1.44:8000/api',

    // 2. O PULO DO GATO: Permite envio de Cookies (CSRF e Sessão)
    // Sem isso, o navegador bloqueia o login na primeira tentativa.
    withCredentials: true,

    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        // Ajuda o Laravel a identificar que é uma requisição Ajax
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