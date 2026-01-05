import { defineStore } from 'pinia';
import axios from 'axios';
import router from '@/router';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || null, // Tenta pegar do armazenamento local
        user: JSON.parse(localStorage.getItem('user')) || null,
        isAuthenticated: !!localStorage.getItem('token'),
    }),

    actions: {
        async login(email, password) {
            try {
                // 1. Pede o token pro Laravel
                const response = await axios.post('http://localhost:8000/api/login', {
                    email,
                    password
                });

                // 2. Salva na memória (Pinia) e no Navegador (LocalStorage)
                this.token = response.data.token;
                this.user = response.data.user;
                this.isAuthenticated = true;

                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));

                // 3. Configura o Axios para usar esse token nas próximas chamadas
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                // 4. Manda o usuário para o Kanban
                router.push('/kanban');

            } catch (error) {
                console.error('Erro no login', error);
                throw error; // Joga o erro para a tela tratar
            }
        },

        logout() {
            // Limpa tudo
            this.token = null;
            this.user = null;
            this.isAuthenticated = false;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            
            // Remove o cabeçalho do Axios
            delete axios.defaults.headers.common['Authorization'];

            router.push('/login');
        },
        
        // Função para verificar se o token existe ao recarregar a página
        checkToken() {
             if (this.token) {
                 axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
             }
        }
    }
});