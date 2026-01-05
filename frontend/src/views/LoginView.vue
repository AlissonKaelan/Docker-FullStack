<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const email = ref('');
const password = ref('');
const errorMessage = ref('');

const handleLogin = async () => {
    try {
        await auth.login(email.value, password.value);
    } catch (error) {
        errorMessage.value = 'Login falhou. Verifique suas credenciais.';
    }
};
</script>

<template>
    <div class="login-container">
        <div class="login-card">
            <h2>🔐 Acesso ao Sistema</h2>
            
            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <label>E-mail</label>
                    <input v-model="email" type="email" required placeholder="admin@teste.com" />
                </div>

                <div class="form-group">
                    <label>Senha</label>
                    <input v-model="password" type="password" required placeholder="******" />
                </div>

                <div v-if="errorMessage" class="error">{{ errorMessage }}</div>

                <button type="submit">Entrar</button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.login-container { display: flex; justify-content: center; align-items: center; height: 100vh; background: #2c3e50; }
.login-card { background: white; padding: 2rem; border-radius: 10px; width: 100%; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #333; margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1rem; }
label { display: block; margin-bottom: 0.5rem; color: #666; }
input { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
button { width: 100%; padding: 0.8rem; background: #42b983; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
button:hover { background: #3aa876; }
.error { color: red; text-align: center; margin-bottom: 1rem; font-size: 0.9rem; }
</style>