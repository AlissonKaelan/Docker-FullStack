<template>
  <div class="home-container">
    
    <header class="top-bar">
      <div>
        <h3>Olá, {{ user?.name || 'Visitante' }}!</h3>
        <span class="welcome">Bem-vindo ao seu Hub</span>
      </div>
      <button @click="handleLogout" class="btn-danger">Sair</button>
    </header>

    <div class="modules-grid">
      
      <div class="module-card kanban" @click="$router.push('/kanban')">
        <div class="icon">📋</div>
        <h3>Kanban Board</h3>
        <p class="status">Gerencie suas tarefas</p>
        <span class="link">Acessar Quadro &rarr;</span>
      </div>

      <div class="module-card finance" @click="$router.push('/finance')">
        <div class="icon">💰</div>
        <h3>Financeiro</h3>
        <p class="status">Controle gastos</p>
        <span class="link">Ver Extrato &rarr;</span>
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
    const response = await http.get('/user'); // Pega dados do usuário logado
    user.value = response.data;
  } catch (error) {
    console.error("Erro ao carregar usuário", error);
  }
});

const handleLogout = async () => {
  try {
    await http.post('/logout');
  } catch (error) {
    console.error("Erro no logout", error);
  } finally {
    localStorage.removeItem('token');
    router.push('/login');
  }
};
</script>

<style scoped>
.home-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 40px 20px;
  font-family: 'Segoe UI', sans-serif;
  background: #f0f2f5;
  min-height: 100vh;
}

/* --- ESTILOS DO CABEÇALHO --- */
.top-bar { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    background: white; 
    padding: 20px 30px; 
    border-radius: 12px; 
    margin-bottom: 40px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
}
.welcome { color: #666; font-size: 0.9rem; display: block; margin-top: 5px; }
h3 { margin: 0; color: #333; font-size: 1.5rem; }

/* Botão Sair */
.btn-danger { 
    background: #ffe5e5; 
    color: #d32f2f; 
    border: none; 
    padding: 10px 20px; 
    border-radius: 8px; 
    cursor: pointer; 
    font-weight: 600; 
    transition: 0.2s; 
}
.btn-danger:hover { background: #ffcccc; }

/* --- GRID DOS MÓDULOS --- */
.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
}

.module-card {
  background: white;
  border-radius: 16px;
  padding: 40px 30px;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  border: 1px solid #f0f0f0;
  text-align: center;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.module-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.icon { font-size: 3.5rem; margin-bottom: 20px; }
.module-card h3 { color: #2c3e50; margin-bottom: 10px; font-size: 1.3rem; }

.status {
  font-size: 1rem;
  color: #888;
  margin-bottom: 25px;
}

.link {
  color: #3b82f6;
  font-weight: bold;
  margin-top: auto; /* Empurra para o fundo */
  font-size: 0.9rem;
}

/* Bordas coloridas no topo */
.module-card.kanban { border-top: 6px solid #8b5cf6; } /* Roxo */
.module-card.finance { border-top: 6px solid #10b981; } /* Verde */
</style>