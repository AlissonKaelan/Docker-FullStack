import { defineStore } from 'pinia';
import axios from 'axios';
import router from '@/router';
import http from '@/services/http'; // Usando o seu http configurado

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || null,
        user: JSON.parse(localStorage.getItem('user')) || null,
        isAuthenticated: !!localStorage.getItem('token'),
    }),

    actions: {
        // ESSA É A ESTRELA DO SHOW: Lê o localStorage de verdade e avisa o Vue
        checkToken() {
            const currentToken = localStorage.getItem('token');
            const currentUser = localStorage.getItem('user');

            if (currentToken) {
                this.token = currentToken;
                this.isAuthenticated = true;
                if (currentUser) this.user = JSON.parse(currentUser);
                
                // Garante que o Axios e o HTTP tenham o token
                axios.defaults.headers.common['Authorization'] = `Bearer ${currentToken}`;
                http.defaults.headers.common['Authorization'] = `Bearer ${currentToken}`;
            } else {
                this.token = null;
                this.isAuthenticated = false;
                this.user = null;
            }
        },

        logout() {
            // 1. Limpa o Pinia
            this.token = null;
            this.user = null;
            this.isAuthenticated = false;
            
            // 2. Limpa o Navegador
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            
            // 3. Remove os cabeçalhos
            delete axios.defaults.headers.common['Authorization'];
            delete http.defaults.headers.common['Authorization'];

            // 4. Manda pro Login
            router.push('/login');
        }
    }
});