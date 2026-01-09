<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth'; // 1. Importar a Store de Autenticação

const router = useRouter();
const auth = useAuthStore(); // 2. Inicializar a Store

const balance = ref(0);
const pendingTaskCount = ref(0); // Mudamos o nome para "Tarefas Pendentes"

const fetchDashboardData = async () => {
  try {
    // Nota: O nome do usuário já vem do auth.user, não precisa buscar de novo

    // --- 1. BUSCAR SALDO ---
    const financeRes = await axios.get('http://localhost:8000/api/balance');
    balance.value = financeRes.data.balance;

    // --- 2. BUSCAR TAREFAS PENDENTES ---
    const kanbanRes = await axios.get('http://localhost:8000/api/kanban'); // Rota correta que traz colunas + cards
    
    let count = 0;
    
    kanbanRes.data.forEach(column => {
        // LÓGICA DE OURO: Só conta se a coluna NÃO for 'done' (Concluído)
        if (column.slug !== 'done') {
            count += column.cards ? column.cards.length : 0;
        }
    });
    
    pendingTaskCount.value = count;

  } catch (error) {
    console.error("Erro ao carregar dashboard:", error);
    if (error.response && error.response.status === 401) {
        auth.logout(); // Se o token venceu, desloga
    }
  }
};

const formatMoney = (value) => {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const goTo = (route) => {
  router.push(route);
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<template>
  <div class="home-container">
    
    <div class="top-bar">
      <div class="user-info">
        <h3>🏠 Início</h3>
        <span class="welcome">Olá, <strong>{{ auth.user?.name }}</strong></span>
      </div>
      <button @click="auth.logout()" class="btn-danger">Sair</button>
    </div>

    <div class="modules-grid">
      
      <div class="module-card kanban" @click="goTo('/kanban')">
        <div class="icon">📋</div>
        <h3>Gestão de Tarefas</h3>
        <p class="status">
            <strong>{{ pendingTaskCount }}</strong> tarefas pendentes
        </p>
        <span class="link">Acessar Kanban &rarr;</span>
      </div>

      <div class="module-card finance" @click="goTo('/finance')">
        <div class="icon">💰</div>
        <h3>Controle Financeiro</h3>
        <p class="status" :class="balance >= 0 ? 'text-green' : 'text-red'">
            Saldo: <strong>{{ formatMoney(balance) }}</strong>
        </p>
        <span class="link">Acessar Finanças &rarr;</span>
      </div>

    </div>
  </div>
</template>

<style scoped>
.home-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px; /* Ajustei padding */
  font-family: 'Segoe UI', sans-serif;
  background: #f0f2f5; /* Fundo cinza claro para combinar */
  min-height: 100vh;
}

/* --- ESTILOS DO CABEÇALHO (Copiados do Kanban para padronizar) --- */
.top-bar { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    background: white; 
    padding: 15px 25px; 
    border-radius: 12px; 
    margin-bottom: 40px; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
}
.welcome { color: #666; margin-left: 10px; }
h3 { margin: 0; }

/* Botão Sair */
.btn-danger { 
    background: #ef4444; 
    color: white; 
    border: none; 
    padding: 10px 16px; 
    border-radius: 8px; 
    cursor: pointer; 
    font-weight: 600; 
    transition: 0.2s; 
}
.btn-danger:hover { background: #dc2626; }

/* --- GRID DOS MÓDULOS --- */
.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
}

.module-card {
  background: white;
  border-radius: 16px;
  padding: 30px;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  border: 1px solid #eee;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.module-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.icon { font-size: 3rem; margin-bottom: 15px; }
.module-card h3 { color: #34495e; margin-bottom: 10px; }

.status {
  font-size: 1.1rem;
  color: #666;
  margin-bottom: 20px;
  background: #f8f9fa;
  padding: 10px;
  border-radius: 8px;
  display: inline-block;
}

.link {
  display: block;
  color: #3b82f6;
  font-weight: bold;
  margin-top: auto;
}

/* Cores de Status */
.text-green { color: #10b981; }
.text-red { color: #ef4444; }

/* Bordas coloridas */
.module-card.kanban { border-top: 5px solid #8b5cf6; } /* Roxo para combinar com o Kanban */
.module-card.finance { border-top: 5px solid #10b981; } /* Verde para dinheiro */
</style>