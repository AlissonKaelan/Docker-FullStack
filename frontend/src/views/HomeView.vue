<template>
  <div class="home-wrapper">
    <header class="hero-header">
      <div class="hero-content">
        <div class="user-info">
          <h1>Olá, {{ user?.name || 'Visitante' }}!</h1>
          <p>Bem-vindo ao seu Hub de Produtividade</p>
        </div>
        
        <div class="header-actions">
          <button @click="toggleTheme" class="btn-theme" :title="isDark ? 'Mudar para Claro' : 'Mudar para Escuro'">
            {{ isDark ? '☀️' : '🌙' }}
          </button>
          
          <button @click="handleLogout" class="btn-logout">
            Sair
          </button>
        </div>
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
const isDark = ref(false);

// Lógica do Tema
const toggleTheme = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.body.classList.add('dark');
    localStorage.setItem('theme', 'dark');
  } else {
    document.body.classList.remove('dark');
    localStorage.setItem('theme', 'light');
  }
};

onMounted(async () => {
  // Carrega preferência salva
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    isDark.value = true;
    document.body.classList.add('dark');
  }

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
.home-wrapper { min-height: 100vh; background-color: var(--bg-primary); font-family: 'Segoe UI', sans-serif; transition: background 0.3s; }

/* HERO HEADER */
.hero-header {
  background: linear-gradient(135deg, var(--accent-color) 0%, #7C3AED 100%);
  padding: 60px 20px 100px 20px;
  color: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.hero-content {
  max-width: 1000px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;
}
.hero-content h1 { margin: 0; font-size: 2rem; font-weight: 700; }
.hero-content p { margin: 5px 0 0 0; opacity: 0.9; font-size: 1.1rem; }

.header-actions { display: flex; gap: 15px; align-items: center; }

/* BOTÕES HEADER */
.btn-theme {
  background: rgba(255, 255, 255, 0.2); border: none; font-size: 1.2rem; cursor: pointer;
  padding: 8px 12px; border-radius: 50%; transition: 0.2s;
}
.btn-theme:hover { background: rgba(255, 255, 255, 0.4); transform: rotate(15deg); }

.btn-logout {
  background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); color: white;
  padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;
}
.btn-logout:hover { background: rgba(255, 255, 255, 0.3); transform: translateY(-1px); }

/* CONTENT */
.main-content { max-width: 1000px; margin: -60px auto 0 auto; padding: 0 20px; }
.modules-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; }

/* CARDS COM VARIÁVEIS DE COR */
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

/* THEMES (Mantive as cores de destaque fixas pois são identidade visual) */
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