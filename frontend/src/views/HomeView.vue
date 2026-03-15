<template>
  <div class="home-wrapper">
    <Navbar 
      :userName="user?.name" 
      subtitle="Bem-vindo ao seu Hub de Produtividade" 
    />

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
import http from '../services/http';
// Importamos o componente Navbar
import Navbar from '../components/Navbar.vue';

const user = ref(null);

onMounted(async () => {
  try {
    const response = await http.get('/user');
    user.value = response.data;
  } catch (error) { 
    console.error(error); 
  }
});
</script>

<style scoped>
/* O CSS restante: apenas coisas relacionadas ao Wrapper e aos Cards */
.home-wrapper { min-height: 100vh; background-color: var(--bg-primary); font-family: 'Segoe UI', sans-serif; transition: background 0.3s; }

.main-content { max-width: 1000px; margin: -60px auto 0 auto; padding: 0 20px; }
.modules-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; }

.module-card {
  background: var(--bg-secondary); 
  border-radius: 16px; padding: 30px; cursor: pointer;
  box-shadow: 0 10px 15px -3px var(--shadow-color); 
  border: 1px solid var(--border-color);
  display: flex; align-items: center; gap: 20px; transition: all 0.3s ease; position: relative; overflow: hidden;
}

.module-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px var(--shadow-color); }

.card-icon {
  font-size: 2.5rem; background: var(--bg-primary); width: 70px; height: 70px;
  display: flex; align-items: center; justify-content: center; border-radius: 12px;
}

.card-text h3 { margin: 0 0 5px 0; color: var(--text-primary); font-size: 1.25rem; }
.card-text p { margin: 0; color: var(--text-secondary); font-size: 0.95rem; }

.action-link {
  position: absolute; bottom: 20px; right: 20px; font-weight: 600; font-size: 0.9rem; opacity: 0; transform: translateX(10px); transition: all 0.3s;
}
.module-card:hover .action-link { opacity: 1; transform: translateX(0); }

.kanban-theme:hover { border-color: #8b5cf6; }
.kanban-theme .card-icon { color: #8b5cf6; } 
.kanban-theme .action-link { color: #8b5cf6; }

.finance-theme:hover { border-color: #10b981; }
.finance-theme .card-icon { color: #10b981; }
.finance-theme .action-link { color: #10b981; }

.daily-theme:hover { border-color: #f59e0b; }
.daily-theme .card-icon { color: #f59e0b; }
.daily-theme .action-link { color: #f59e0b; }
</style>