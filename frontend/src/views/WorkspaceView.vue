<template>
  <div class="workspace-wrapper">
    <div class="main-content">
      
      <div class="header-actions">
        <div>
          <router-link to="/" class="back-btn">⬅ Voltar para Projetos</router-link>
          <h2 class="mt-4">{{ projectName }}</h2>
          <p class="text-gray-400 text-sm">Escolha qual módulo deseja acessar.</p>
        </div>
        
        <!-- O Botão de Convidar Membro que testamos -->
        <ManageWorkspaceModal 
          :workspaceId="Number($route.params.id)" 
          :currentWorkspaceName="projectName"
          @workspace-updated="(newName) => projectName = newName"
        />
      </div>

      <div class="modules-grid mt-8">
        
        <!-- Botão para o Kanban -->
        <div class="module-card kanban-theme" @click="goTo('kanban')">
          <div class="card-icon">📋</div>
          <div class="card-text">
            <h3>Kanban Board</h3>
            <p>Gerencie tarefas e fluxo de trabalho</p>
          </div>
          <span class="action-link">Acessar &rarr;</span>
        </div>

        <!-- Botão para o Financeiro -->
        <div class="module-card finance-theme" @click="goTo('finance')">
          <div class="card-icon">💰</div>
          <div class="card-text">
            <h3>Financeiro</h3>
            <p>Controle entradas, saídas e saldo</p>
          </div>
          <span class="action-link">Acessar &rarr;</span>
        </div>

        <!-- Botão para as Tarefas Diárias -->
        <div class="module-card daily-theme" @click="goTo('daily')">
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
import { useRouter, useRoute } from 'vue-router';
// 1. CORRIGIDA A IMPORTAÇÃO DO COMPONENTE NOVO
import ManageWorkspaceModal from '../components/ManageWorkspaceModal.vue';
import http from '@/services/http';

const router = useRouter();
const route = useRoute();
const projectName = ref('Painel do Projeto');

// 2. BUSCA INTELIGENTE SEM PRECISAR DE NOVA ROTA NO BACKEND
onMounted(async () => {
  try {
    const res = await http.get('/workspaces');
    // Filtra dentro do array retornado o workspace com o ID da URL
    const currentWorkspace = res.data.find(w => w.id === Number(route.params.id));
    
    if (currentWorkspace) {
      projectName.value = currentWorkspace.name; // Atualiza o título na tela
    }
  } catch (err) {
    console.error('Erro ao carregar nome do projeto', err);
  }
});

// Função que navega para o módulo mantendo o ID do workspace na URL
const goTo = (moduleName) => {
  router.push(`/workspace/${route.params.id}/${moduleName}`);
};
</script>

<style scoped>
/* Aproveitando o seu CSS global maravilhoso */
.workspace-wrapper { min-height: 100vh; background-color: var(--bg-primary); font-family: 'Segoe UI', sans-serif; padding: 40px 20px; transition: background 0.3s; }
.main-content { max-width: 1000px; margin: 0 auto; }

.header-actions { display: flex; justify-content: space-between; align-items: flex-end; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 20px;}
.header-actions h2 { margin: 0; font-size: 1.8rem; }
.back-btn { text-decoration: none; color: var(--text-secondary); font-weight: 600; padding: 8px 12px; background: var(--bg-secondary); border-radius: 6px; font-size: 0.9rem; border: 1px solid var(--border-color); transition: 0.2s; display: inline-block; }
.back-btn:hover { background: var(--border-color); color: var(--text-primary); }

.modules-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }

.module-card { background: var(--bg-secondary); border-radius: 16px; padding: 30px; cursor: pointer; box-shadow: 0 10px 15px -3px var(--shadow-color); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 20px; transition: all 0.3s ease; position: relative; overflow: hidden; }
.module-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px var(--shadow-color); }

.card-icon { font-size: 2.5rem; background: var(--bg-primary); width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
.card-text h3 { margin: 0 0 5px 0; color: var(--text-primary); font-size: 1.25rem; }
.card-text p { margin: 0; color: var(--text-secondary); font-size: 0.95rem; }

.action-link { position: absolute; bottom: 20px; right: 20px; font-weight: 600; font-size: 0.9rem; opacity: 0; transform: translateX(10px); transition: all 0.3s; }
.module-card:hover .action-link { opacity: 1; transform: translateX(0); }

.kanban-theme:hover { border-color: #8b5cf6; } .kanban-theme .card-icon { color: #8b5cf6; } .kanban-theme .action-link { color: #8b5cf6; }
.finance-theme:hover { border-color: #10b981; } .finance-theme .card-icon { color: #10b981; } .finance-theme .action-link { color: #10b981; }
.daily-theme:hover { border-color: #f59e0b; } .daily-theme .card-icon { color: #f59e0b; } .daily-theme .action-link { color: #f59e0b; }
</style>