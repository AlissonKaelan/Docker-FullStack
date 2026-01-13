<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>Criar Conta</h1>
        <p>Comece a organizar seus projetos</p>
      </div>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>Nome Completo</label>
          <input 
            v-model="form.name" 
            type="text" 
            placeholder="Seu nome" 
            required 
          />
        </div>

        <div class="form-group">
          <label>E-mail</label>
          <input 
            v-model="form.email" 
            type="email" 
            placeholder="seu@email.com" 
            required 
          />
        </div>

        <div class="form-group">
          <label>Senha</label>
          <input 
            v-model="form.password" 
            type="password" 
            placeholder="Mínimo 8 caracteres" 
            required 
          />
        </div>

        <div class="form-group">
          <label>Confirmar Senha</label>
          <input 
            v-model="form.password_confirmation" 
            type="password" 
            placeholder="Repita a senha" 
            required 
          />
        </div>

        <div v-if="errorMessage" class="error-msg">
          {{ errorMessage }}
        </div>

        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Criando...' : 'Registrar' }}
        </button>
      </form>

      <div class="auth-footer">
        Já tem conta? <router-link to="/login">Faça Login</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import http from '../services/http';

const router = useRouter();
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});
const loading = ref(false);
const errorMessage = ref('');

const handleRegister = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await http.post('http://localhost:8000/api/register', form.value);
    
    localStorage.setItem('token', response.data.token);
    router.push('/'); // Vai para Home
    
  } catch (error) {
    console.error(error);
    errorMessage.value = error.response?.data?.message || 'Erro ao registrar. Verifique os dados.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* FUNDO COM GRADIENTE MODERNO */
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  /* Gradiente elegante (Azul para Roxo) */
  background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  padding: 1rem;
}

/* O CARTÃO (Efeito de vidro sutil e sombra suave) */
.auth-card {
  background: rgba(255, 255, 255, 0.95); /* Branco com leve transparência */
  padding: 2.5rem;
  border-radius: 16px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
  backdrop-filter: blur(10px); /* Blur atrás do card */
}

/* Títulos */
.auth-header { text-align: center; margin-bottom: 2rem; }
.auth-header h1 { font-size: 2rem; color: #111827; margin-bottom: 0.5rem; font-weight: 700; }
.auth-header p { color: #6b7280; font-size: 0.95rem; }

/* Inputs */
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;}
.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  outline: none;
  font-size: 1rem;
  transition: all 0.2s;
  box-sizing: border-box;
}
.form-group input:focus {
  border-color: #4F46E5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Botão */
.btn-primary {
  width: 100%;
  background: linear-gradient(to right, #4F46E5, #7C3AED); /* Gradiente no botão também */
  color: white;
  padding: 0.875rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: opacity 0.2s;
}
.btn-primary:hover { opacity: 0.9; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }

/* Erro */
.error-msg {
  color: #b91c1c;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  padding: 0.75rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  text-align: center;
  font-size: 0.875rem;
}

/* Rodapé */
.auth-footer { margin-top: 1.5rem; text-align: center; font-size: 0.9rem; color: #4b5563; }
.auth-footer a { color: #4F46E5; text-decoration: none; font-weight: 600; transition: color 0.2s; }
.auth-footer a:hover { color: #7C3AED; }
</style>