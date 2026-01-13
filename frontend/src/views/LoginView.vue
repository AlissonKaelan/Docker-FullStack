<template>
  <div class="split-screen">
    <div class="left-panel">
      <div class="brand-content">
        <h1>Docker FullStack</h1>
        <p class="tagline">Gerencie Projetos e Finanças em um só lugar.</p>
        <ul class="features">
          <li>✅ Kanban Dinâmico</li>
          <li>✅ Controle Financeiro</li>
          <li>✅ Segurança Total</li>
        </ul>
      </div>
    </div>

    <div class="right-panel">
      <div class="auth-card">
        <div class="auth-header">
          <h2>Bem-vindo de volta</h2>
          <p>Entre com suas credenciais para acessar.</p>
        </div>

        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label>E-mail</label>
            <input v-model="email" type="email" placeholder="exemplo@email.com" required />
          </div>

          <div class="form-group">
            <label>Senha</label>
            <div class="password-wrapper">
              <input 
                v-model="password" 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="••••••••" 
                required 
              />
              <button type="button" class="eye-btn" @click="showPassword = !showPassword">
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
          </div>

          <div v-if="errorMessage" class="error-msg">{{ errorMessage }}</div>

          <button type="submit" :disabled="loading" class="btn-primary">
            {{ loading ? 'Acessando...' : 'Entrar' }}
          </button>
        </form>

        <div class="auth-footer">
          Não tem conta? <router-link to="/register">Crie gratuitamente</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import http from '../services/http';

const router = useRouter();
const email = ref('');
const password = ref('');
const showPassword = ref(false); // Controle do olhinho
const loading = ref(false);
const errorMessage = ref('');

onMounted(() => { localStorage.removeItem('token'); });

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const response = await http.post('/login', { email: email.value, password: password.value });
    const token = response.data.token;
    localStorage.setItem('token', token);
    http.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    router.push('/');
  } catch (error) {
    console.error(error);
    errorMessage.value = 'E-mail ou senha incorretos.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* REUTILIZANDO O DESIGN DO LOGIN ANTERIOR */
.split-screen { display: flex; min-height: 100vh; font-family: 'Segoe UI', sans-serif; background-color: white; }
.left-panel { flex: 1; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); display: flex; align-items: center; justify-content: center; color: white; padding: 40px; position: relative; overflow: hidden; }
.left-panel::before, .left-panel::after { content: ''; position: absolute; background: rgba(255,255,255, 0.1); border-radius: 50%; }
.left-panel::before { top: -10%; left: -10%; width: 300px; height: 300px; }
.left-panel::after { bottom: -10%; right: -10%; width: 400px; height: 400px; }
.brand-content { z-index: 10; max-width: 400px; }
.brand-content h1 { font-size: 3rem; margin-bottom: 1rem; font-weight: 800; }
.tagline { font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; }
.features { list-style: none; padding: 0; }
.features li { font-size: 1.1rem; margin-bottom: 10px; opacity: 0.9; }

.right-panel { flex: 1; display: flex; align-items: center; justify-content: center; background-color: #f9fafb; }
.auth-card { width: 100%; max-width: 400px; padding: 40px; }
.auth-header { margin-bottom: 2rem; }
.auth-header h2 { font-size: 2rem; color: #1f2937; margin-bottom: 0.5rem; }
.auth-header p { color: #6b7280; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; }

/* --- NOVO CSS DO INPUT DE SENHA --- */
.password-wrapper { position: relative; display: flex; align-items: center; }
.password-wrapper input { padding-right: 40px; } /* Espaço para o ícone */
.eye-btn {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  cursor: pointer;
  color: #6b7280;
  display: flex;
  align-items: center;
  padding: 0;
}
.eye-btn:hover { color: #4F46E5; }

/* INPUT PADRÃO */
input { width: 100%; padding: 0.9rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; outline: none; transition: border 0.2s; box-sizing: border-box; }
input:focus { border-color: #4F46E5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

.btn-primary { width: 100%; background-color: #4F46E5; color: white; padding: 1rem; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem; transition: background 0.2s; }
.btn-primary:hover { background-color: #4338ca; }
.error-msg { color: #dc2626; background: #fee2e2; padding: 0.8rem; border-radius: 6px; margin-bottom: 1rem; }
.auth-footer { margin-top: 1.5rem; text-align: center; color: #6b7280; }
.auth-footer a { color: #4F46E5; text-decoration: none; font-weight: 600; }

@media (max-width: 768px) { .split-screen { flex-direction: column; } .left-panel { padding: 30px; text-align: center; } .features { display: none; } }
</style>