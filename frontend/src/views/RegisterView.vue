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
        <h1>Junte-se a nós</h1>
        <p class="tagline">Crie sua conta e comece a organizar sua vida.</p>
      </div>
    </div>

    <div class="right-panel">
      <div class="auth-card">
        <div class="auth-header">
          <h2>Criar Conta</h2>
          <p>Preencha os dados abaixo.</p>
        </div>

        <form @submit.prevent="handleRegister">
          <div class="form-group">
            <label>Nome Completo</label>
            <input v-model="form.name" type="text" placeholder="Seu nome" required />
          </div>

          <div class="form-group">
            <label>E-mail</label>
            <input v-model="form.email" type="email" placeholder="seu@email.com" required />
          </div>

          <div class="form-group">
            <label>Senha</label>
            <div class="password-wrapper">
              <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Mínimo 8 caracteres" required />
              <button type="button" class="eye-btn" @click="showPassword = !showPassword">
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label>Confirmar Senha</label>
            <div class="password-wrapper">
               <input v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" placeholder="Repita a senha" required />
            </div>
          </div>

          <div v-if="errorMessage" class="error-msg">{{ errorMessage }}</div>

          <button type="submit" :disabled="loading" class="btn-primary">
            {{ loading ? 'Criando conta...' : 'Registrar' }}
          </button>
        </form>

        <div class="auth-footer">
          Já tem conta? <router-link to="/login">Faça Login</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'; // Adicionei onMounted/onUnmounted
import { useRouter } from 'vue-router';
import http from '../services/http';

const router = useRouter();
const form = ref({ name: '', email: '', password: '', password_confirmation: '' });
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');

// --- LÓGICA DO CARROSSEL ---
const currentImageIndex = ref(0);
const images = [
  'https://images.unsplash.com/photo-1497215728101-856f4ea42174?q=80&w=1920&auto=format&fit=crop', 
  'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=1920&auto=format&fit=crop', 
  'https://images.unsplash.com/photo-1542831371-29b0f74f9713?q=80&w=1920&auto=format&fit=crop'
];
let intervalId;

// Inicia a rotação das imagens ao carregar a tela
onMounted(() => {
  intervalId = setInterval(() => {
    currentImageIndex.value = (currentImageIndex.value + 1) % images.length;
  }, 5000); // Troca a cada 5 segundos
});

// Limpa a memória quando sair da tela
onUnmounted(() => {
  clearInterval(intervalId);
});

const handleRegister = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const response = await http.post('/register', form.value);
    const token = response.data.token;
    if (token) {
        localStorage.setItem('token', token);
        http.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        router.push('/');
    } else { router.push('/login'); }
  } catch (error) {
    console.error(error);
    errorMessage.value = error.response?.data?.message || 'Erro ao registrar.';
  } finally { loading.value = false; }
};
</script>

<style scoped>
.split-screen { display: flex; min-height: 100vh; font-family: 'Segoe UI', sans-serif; background-color: white; }

/* LADO ESQUERDO */
.left-panel { 
  flex: 1; 
  position: relative; /* Necessário para posicionar o overlay */
  display: flex; align-items: center; justify-content: center; 
  color: white; padding: 40px; overflow: hidden; 
  /* Fallback se a imagem não carregar */
  background: #10b981; 
}

/* Imagens */
.bg-image {
  position: absolute; top: 0; left: 0; width: 100%; height: 100%;
  background-size: cover; background-position: center;
  opacity: 0; transition: opacity 1.5s ease-in-out; z-index: 1;
}
.bg-image.active { opacity: 1; }

/* OVERLAY VERDE (MUDANÇA AQUI) */
.overlay {
  position: absolute; top: 0; left: 0; width: 100%; height: 100%;
  /* Gradiente Verde semi-transparente */
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.9) 0%, rgba(5, 150, 105, 0.85) 100%);
  z-index: 2; /* Fica acima da imagem */
}

/* Texto acima de tudo */
.brand-content { z-index: 3; max-width: 400px; text-align: center; position: relative; }
.brand-content h1 { font-size: 3rem; margin-bottom: 1rem; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
.tagline { font-size: 1.2rem; opacity: 0.95; margin-bottom: 2rem; }

/* LADO DIREITO */
.right-panel { flex: 1; display: flex; align-items: center; justify-content: center; background-color: #f9fafb; }
.auth-card { width: 100%; max-width: 400px; padding: 40px; }
.auth-header { margin-bottom: 2rem; }
.auth-header h2 { font-size: 2rem; color: #1f2937; margin-bottom: 0.5rem; }
.auth-header p { color: #6b7280; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; }

.password-wrapper { position: relative; display: flex; align-items: center; }
.password-wrapper input { padding-right: 40px; }
.eye-btn { position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: #6b7280; display: flex; align-items: center; padding: 0; }
.eye-btn:hover { color: #10b981; }

input { width: 100%; padding: 0.9rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; outline: none; transition: border 0.2s; box-sizing: border-box; }
input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }

/* BOTÃO VERDE */
.btn-primary { width: 100%; background-color: #10b981; color: white; padding: 1rem; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem; transition: background 0.2s; }
.btn-primary:hover { background-color: #059669; }

.error-msg { color: #dc2626; background: #fee2e2; padding: 0.8rem; border-radius: 6px; margin-bottom: 1rem; }
.auth-footer { margin-top: 1.5rem; text-align: center; color: #6b7280; }
.auth-footer a { color: #10b981; text-decoration: none; font-weight: 600; }

@media (max-width: 768px) { .split-screen { flex-direction: column; } .left-panel { padding: 30px; text-align: center; min-height: 250px; } }
</style>