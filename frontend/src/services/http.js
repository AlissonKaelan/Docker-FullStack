import axios from 'axios';
import router from '../router';

// A MÁGICA: Pega o IP ou domínio que está escrito lá na barra de endereços do navegador!
const host = window.location.hostname; 
const baseURL = `http://${host}:8000/api`;

const http = axios.create({
    baseURL: baseURL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest' 
    }
});

// Interceptador: Anexa o token Bearer e o Workspace-Id
http.interceptors.request.use(config => {
    // 1. Lógica do Token (Mantida)
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    // 2. A MÁGICA NOVA: Descobre em qual Workspace o usuário está pela URL
    // Procura na barra de endereços algo como "/workspace/5" e extrai o "5"
    const match = window.location.pathname.match(/\/workspace\/(\d+)/);
    if (match) {
        config.headers['Workspace-Id'] = match[1]; // Gruda o crachá no cabeçalho!
    }

    return config;
});

// Interceptador: Tratamento de erros globais (Mantido)
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