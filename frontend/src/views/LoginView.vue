<template>
  <div class="split-screen">
    <div class="left-panel">
      <div 
        v-for="(img, index) in images" 
        :key="index"
        class="bg-image"
        :style="{ backgroundImage: `url(${img})` }"
        :class="{ active: currentImageIndex === index }"
      ></div>
      
      <div class="overlay"></div>

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
                <span v-if="showPassword">🙈</span>
                <span v-else>👁️</span>
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
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import http from '../services/http'; 
import axios from 'axios';

const router = useRouter();
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');

// --- LÓGICA DO CARROSSEL (Mantém igual) ---
const currentImageIndex = ref(0);
const images = [
  'https://images.unsplash.com/photo-1497215728101-856f4ea42174?q=80&w=1920&auto=format&fit=crop', 
  'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=1920&auto=format&fit=crop', 
  'https://images.unsplash.com/photo-1542831371-29b0f74f9713?q=80&w=1920&auto=format&fit=crop'  
];
let intervalId;

onMounted(() => { 
  localStorage.removeItem('token');
  intervalId = setInterval(() => {
    currentImageIndex.value = (currentImageIndex.value + 1) % images.length;
  }, 5000);
});

onUnmounted(() => clearInterval(intervalId));

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    // 1. CORREÇÃO CRÍTICA: 
    // Usamos o axios puro com a URL COMPLETA (sem /api) para pegar o cookie.
    // Isso evita o erro 404.
    await axios.get('http://192.168.1.44:8000/sanctum/csrf-cookie', {
        withCredentials: true // Obrigatório para o cookie ser salvo
    });

    // 2. Agora fazemos o login usando a instância configurada (que aponta para /api)
    const response = await http.post('/login', { email: email.value, password: password.value });
    
    // 3. Sucesso
    const token = response.data.token;
    localStorage.setItem('token', token);
    http.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    
    router.push('/');

  } catch (error) {
    console.error("Erro no Login:", error);
    if (error.response && error.response.status === 422) {
        errorMessage.value = 'E-mail ou senha incorretos.';
    } else {
        errorMessage.value = 'Erro de conexão. Verifique se o servidor está rodando.';
    }
  } finally {
    loading.value = false;
  }
};
</script>
<style scoped>
.split-screen { display: flex; min-height: 100vh; font-family: 'Segoe UI', sans-serif; background-color: var(--bg-primary); }

/* --- PAINEL ESQUERDO COM IMAGENS --- */
.left-panel { 
  flex: 1; 
  position: relative; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  color: white; 
  padding: 40px; 
  overflow: hidden; 
}

/* Imagens de fundo sobrepostas */
.bg-image {
  position: absolute; top: 0; left: 0; width: 100%; height: 100%;
  background-size: cover; background-position: center;
  opacity: 0; transition: opacity 1.5s ease-in-out; z-index: 1;
}
.bg-image.active { opacity: 1; }

/* Overlay Escuro (Gradiente) para o texto aparecer bem */
.overlay {
  position: absolute; top: 0; left: 0; width: 100%; height: 100%;
  background: linear-gradient(135deg, rgba(79, 70, 229, 0.9) 0%, rgba(124, 58, 237, 0.8) 100%);
  z-index: 2;
}

.brand-content { z-index: 3; max-width: 450px; text-align: center; }
.brand-content h1 { font-size: 3.5rem; margin-bottom: 1rem; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
.tagline { font-size: 1.3rem; opacity: 0.95; margin-bottom: 2rem; }
.features { list-style: none; padding: 0; text-align: left; display: inline-block; }
.features li { font-size: 1.1rem; margin-bottom: 12px; opacity: 0.9; text-shadow: 0 1px 2px rgba(0,0,0,0.2); }

/* --- PAINEL DIREITO --- */
.right-panel { flex: 1; display: flex; align-items: center; justify-content: center; background-color: var(--bg-primary); transition: background-color 0.3s; }
.auth-card { width: 100%; max-width: 400px; padding: 40px; }

.auth-header { margin-bottom: 2rem; }
.auth-header h2 { font-size: 2rem; color: var(--text-primary); margin-bottom: 0.5rem; }
.auth-header p { color: var(--text-secondary); }

.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary); }

.password-wrapper { position: relative; display: flex; align-items: center; }
.password-wrapper input { padding-right: 40px; }
.eye-btn { position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: var(--text-secondary); }

input { 
  width: 100%; padding: 0.9rem; 
  border: 1px solid var(--border-color); 
  background-color: var(--input-bg);
  color: var(--text-primary);
  border-radius: 8px; font-size: 1rem; outline: none; transition: all 0.2s; box-sizing: border-box; 
}
input:focus { border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

.btn-primary { 
  width: 100%; background-color: var(--accent-color); color: white; padding: 1rem; 
  border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem; transition: filter 0.2s; 
}
.btn-primary:hover { filter: brightness(110%); }

.error-msg { color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 0.8rem; border-radius: 6px; margin-bottom: 1rem; }
.auth-footer { margin-top: 1.5rem; text-align: center; color: var(--text-secondary); }
.auth-footer a { color: var(--accent-color); text-decoration: none; font-weight: 600; }

@media (max-width: 768px) { .split-screen { flex-direction: column; } .left-panel { padding: 40px 20px; min-height: 300px; } }
</style>