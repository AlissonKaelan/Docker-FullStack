<template>
  <div class="profile-wrapper">
    <div class="page-header">
      <div class="header-left">
        <router-link to="/" class="back-btn">⬅ Voltar</router-link>
        <h1>Meu Perfil</h1>
      </div>
    </div>

    <div class="form-section">
      <div class="form-header">
        <h3>Atualizar Informações</h3>
      </div>
      <div class="form-body">
        <form @submit.prevent="updateProfile">
          <div class="input-grid">
            <div class="form-group">
              <label>Nome Completo</label>
              <input v-model="form.name" type="text" class="input-modern" required />
            </div>

            <div class="form-group">
              <label>E-mail</label>
              <input v-model="form.email" type="email" class="input-modern" required />
            </div>

            <div class="form-group full-desc">
              <label>Nova Senha <span class="text-xs text-gray-500 font-normal">(Deixe em branco para não alterar)</span></label>
              <input v-model="form.password" type="password" placeholder="••••••••" class="input-modern" />
            </div>
          </div>

          <div class="form-actions mt-6">
            <button type="submit" :disabled="loading" class="btn-save">
              {{ loading ? 'Salvando...' : 'Salvar Alterações' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import http from '@/services/http';
import { notify } from '@/utils/alert';

const form = ref({ name: '', email: '', password: '' });
const loading = ref(false);

const loadUser = async () => {
  try {
    const { data } = await http.get('/user');
    form.value.name = data.name;
    form.value.email = data.email;
  } catch (error) {
    console.error('Erro ao carregar perfil', error);
  }
};

const updateProfile = async () => {
  loading.value = true;
  try {
    // Envia apenas a senha se o usuário digitou algo
    const payload = { 
      name: form.value.name, 
      email: form.value.email 
    };
    
    if (form.value.password) {
      payload.password = form.value.password;
    }

    await http.put('/user', payload);
    notify('success', 'Perfil atualizado com sucesso!');
    form.value.password = ''; // Limpa o campo de senha por segurança
  } catch (error) {
    if (error.response?.status === 422) {
      const erros = error.response.data.errors;
      const primeiroCampo = Object.keys(erros)[0];
      notify('error', erros[primeiroCampo][0]);
    } else {
      notify('error', 'Erro ao atualizar o perfil.');
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => loadUser());
</script>

<style scoped>
/* Reaproveitando os estilos da sua tela de Finanças/Kanban */
.profile-wrapper { max-width: 800px; margin: 0 auto; padding: 40px 20px; font-family: 'Segoe UI', sans-serif; color: var(--text-primary); background-color: var(--bg-primary); min-height: 100vh; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.header-left { display: flex; align-items: center; gap: 20px; } 
.header-left h1 { margin: 0; font-size: 1.8rem; }
.back-btn { text-decoration: none; color: var(--text-secondary); font-weight: 600; padding: 8px 12px; background: var(--bg-secondary); border-radius: 6px; font-size: 0.9rem; border: 1px solid var(--border-color); transition: 0.2s; }
.back-btn:hover { background: var(--border-color); color: var(--text-primary); }

.form-section { background: var(--bg-secondary); border-radius: 12px; box-shadow: 0 10px 15px -3px var(--shadow-color); overflow: hidden; border: 1px solid var(--border-color); }
.form-header { background: var(--bg-primary); padding: 15px 20px; border-bottom: 1px solid var(--border-color); }
.form-header h3 { margin: 0; font-size: 1rem; color: var(--text-primary); }
.form-body { padding: 30px 20px; }

.input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.full-desc { grid-column: 1 / -1; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary); }
.input-modern { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; background-color: var(--input-bg); color: var(--text-primary); transition: 0.2s; box-sizing: border-box; }
.input-modern:focus { border-color: var(--accent-color); }

.form-actions { display: flex; justify-content: flex-end; }
.btn-save { background: var(--accent-color); color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.2s; }
.btn-save:hover { filter: brightness(1.1); transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; }

@media (max-width: 768px) { .input-grid { grid-template-columns: 1fr; } }
</style>