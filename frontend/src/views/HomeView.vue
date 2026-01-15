<template>
  <div class="home-wrapper">
    <header class="hero-header">
      <div class="hero-content">
        <div class="user-info">
          <h1>Olá, {{ user?.name || 'Visitante' }}!</h1>
          <p>Bem-vindo ao seu Hub de Produtividade</p>
        </div>
        <button @click="handleLogout" class="btn-logout">
          Sair
        </button>
      </div>
    </header>

    <div class="main-content">
      <div class="modules-grid">
        
        <div class="module-card kanban-theme" @click="$router.push('/kanban')">
          <div class="card-icon">📋</div>
          <div class="card-text">
            <h3>Kanban Board</h3>
            <p>Gerencie tarefas e fluxo de trabalho</p>
          </div>
          <span class="action-link">Acessar &rarr;</span>
        </div>

        <div class="module-card finance-theme" @click="$router.push('/finance')">
          <div class="card-icon">💰</div>
          <div class="card-text">
            <h3>Financeiro</h3>
            <p>Controle entradas, saídas e saldo</p>
          </div>
          <span class="action-link">Acessar &rarr;</span>
        </div>

        <div class="module-card daily-theme" @click="$router.push('/daily')">
          <div class="card-icon">☀️</div>
          <div class="card-text">
            <h3>Atividades Diárias</h3>
            <p>Checklist rápido para seu dia</p>
          </div>
          <span class="action-link">Acessar &rarr;</span>
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
const user = ref(null);

onMounted(async () => {
  try {
    const response = await http.get('/user');
    user.value = response.data;
  } catch (error) { console.error(error); }
});

const handleLogout = async () => {
  try { await http.post('/logout'); } catch (e) {} 
  finally {
    localStorage.removeItem('token');
    router.push('/login');
  }
};
</script>

<style scoped>
.home-wrapper { min-height: 100vh; background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif; }

/* HERO HEADER */
.hero-header {
  background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
  padding: 60px 20px 100px 20px; /* Padding extra embaixo para os cards subirem */
  color: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.hero-content {
  max-width: 1000px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;
}
.hero-content h1 { margin: 0; font-size: 2rem; font-weight: 700; }
.hero-content p { margin: 5px 0 0 0; opacity: 0.9; font-size: 1.1rem; }

.btn-logout {
  background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); color: white;
  padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;
}
.btn-logout:hover { background: rgba(255, 255, 255, 0.3); transform: translateY(-1px); }

/* MAIN CONTENT */
.main-content { max-width: 1000px; margin: -60px auto 0 auto; padding: 0 20px; }

.modules-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; }

.module-card {
  background: white; border-radius: 16px; padding: 30px; cursor: pointer;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid transparent;
  display: flex; align-items: center; gap: 20px; transition: all 0.3s ease; position: relative; overflow: hidden;
}

.module-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }

.card-icon {
  font-size: 2.5rem; background: #f3f4f6; width: 70px; height: 70px;
  display: flex; align-items: center; justify-content: center; border-radius: 12px;
}

.card-text h3 { margin: 0 0 5px 0; color: #1f2937; font-size: 1.25rem; }
.card-text p { margin: 0; color: #6b7280; font-size: 0.95rem; }

.action-link {
  position: absolute; bottom: 20px; right: 20px; font-weight: 600; font-size: 0.9rem; opacity: 0; transform: translateX(10px); transition: all 0.3s;
}
.module-card:hover .action-link { opacity: 1; transform: translateX(0); }

/* THEMES */
.kanban-theme:hover { border-color: #8b5cf6; }
.kanban-theme .card-icon { color: #8b5cf6; background: #f5f3ff; }
.kanban-theme .action-link { color: #8b5cf6; }

.finance-theme:hover { border-color: #10b981; }
.finance-theme .card-icon { color: #10b981; background: #ecfdf5; }
.finance-theme .action-link { color: #10b981; }

.daily-theme:hover { border-color: #f59e0b; }
.daily-theme .card-icon { color: #f59e0b; background: #fef3c7; }
.daily-theme .action-link { color: #f59e0b; }
</style>