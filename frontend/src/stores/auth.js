import { defineStore } from 'pinia';
import axios from 'axios';
import router from '@/router';
import http from '@/services/http';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || null,
        user: JSON.parse(localStorage.getItem('user')) || null,
        isAuthenticated: !!localStorage.getItem('token'),
    }),

    actions: {
        // =================================================================
        // A NOVA AÇÃO DE LOGIN (Com a correção do CSRF Token para o Celular)
        // =================================================================
        async login(email, password) {
            try {
                // 1. Pega a URL base (ex: http://192.168.1.3:8000/api) e remove o "/api" do final
                const appUrl = import.meta.env.VITE_API_URL.replace(/\/api$/, '');

                // 2. Bate na porta do Laravel pedindo o cookie de segurança (CSRF)
                // Usamos o axios puro aqui para ignorar a baseURL do http.js e bater na raiz do servidor
                await axios.get(`${appUrl}/sanctum/csrf-cookie`, {
                    withCredentials: true
                });

                // 3. Faz o login normal enviando as credenciais via http.js
                const response = await http.post('/login', {
                    email: email,
                    password: password
                });

                // 4. Salva o Token e os Dados do Usuário no Pinia e no LocalStorage
                this.token = response.data.token;
                this.isAuthenticated = true;
                this.user = response.data.user || null; 

                localStorage.setItem('token', this.token);
                if (this.user) {
                    localStorage.setItem('user', JSON.stringify(this.user));
                }

                // 5. Configura o cabeçalho para as próximas requisições não precisarem recarregar a página
                http.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                // 6. Joga o usuário para o painel
                router.push('/home');

                return response.data;
            } catch (error) {
                console.error("Falha na autenticação:", error);
                // Joga o erro para a tela (LoginView.vue) mostrar o alerta vermelho
                throw error; 
            }
        },

        // Lê o localStorage de verdade e avisa o Vue
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