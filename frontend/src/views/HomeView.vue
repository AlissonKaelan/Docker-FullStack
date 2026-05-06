<template>
  <div class="home-wrapper">
    <Navbar 
      :userName="user?.name" 
      subtitle="Seus Projetos e Workspaces" 
    />

    <div class="main-content">
      <div class="header-actions">
        <h2>Meus Projetos</h2>
        <button @click="showCreateModal = true" class="btn-create">
          + Novo Projeto
        </button>
      </div>

      <!-- Loading e Empty State -->
      <div v-if="loading" class="text-center py-10 text-gray-500">Carregando...</div>
      
      <div v-else-if="workspaces.length === 0" class="empty-state">
        <div class="card-icon mb-4 mx-auto">🚀</div>
        <h3>Nenhum projeto encontrado</h3>
        <p>Crie seu primeiro Workspace para começar a organizar suas tarefas e finanças.</p>
      </div>

      <!-- Grid de Workspaces -->
      <div v-else class="modules-grid">
        <div 
          v-for="workspace in workspaces" 
          :key="workspace.id"
          class="module-card workspace-theme" 
          @click="$router.push(`/workspace/${workspace.id}`)"
        >
          <div class="card-icon">📁</div>
          <div class="card-text">
            <h3>{{ workspace.name }}</h3>
            <p>Acesse o painel completo</p>
          </div>
          <span class="action-link">Entrar &rarr;</span>
        </div>
      </div>
    </div>

    <!-- Modal Simples de Criação (Reaproveitando seu estilo) -->
    <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Criar Novo Projeto</h2>
          <button @click="showCreateModal = false" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="createWorkspace">
            <div class="form-group">
              <label>Nome do Projeto (Workspace)</label>
              <input 
                v-model="newWorkspaceName" 
                type="text" 
                placeholder="Ex: Arena AK - Freelance" 
                class="input-modern full-width"
                required
              />
            </div>
            <div class="form-actions mt-4 flex justify-end gap-3">
              <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-gray-500 hover:text-gray-700">Cancelar</button>
              <button type="submit" :disabled="isCreating" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-500 font-bold disabled:opacity-50">
                {{ isCreating ? 'Criando...' : 'Salvar Projeto' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import http from '@/services/http';
import Navbar from '../components/Navbar.vue';
import { notify } from '@/utils/alert';

const router = useRouter();
const user = ref(null);
const workspaces = ref([]);
const loading = ref(true);

// Controle do Modal de Criação
const showCreateModal = ref(false);
const newWorkspaceName = ref('');
const isCreating = ref(false);

const loadData = async () => {
  try {
    const userResponse = await http.get('/user');
    user.value = userResponse.data;

    const workspaceResponse = await http.get('/workspaces');
    workspaces.value = workspaceResponse.data;
  } catch (error) { 
    console.error('Erro ao carregar dados:', error); 
  } finally {
    loading.value = false;
  }
};

const createWorkspace = async () => {
  if (!newWorkspaceName.value.trim()) return;
  isCreating.value = true;
  
  try {
    const response = await http.post('/workspaces', { name: newWorkspaceName.value });
    notify('success', 'Projeto criado com sucesso!');
    
    // Adiciona na lista visualmente e fecha modal
    workspaces.value.push(response.data.workspace);
    showCreateModal.value = false;
    newWorkspaceName.value = '';
    
    // Opcional: Já redirecionar o usuário para o novo projeto
    // router.push(`/workspace/${response.data.workspace.id}`);
  } catch (error) {
    notify('error', 'Erro ao criar o projeto.');
    console.error(error);
  } finally {
    isCreating.value = false;
  }
};

onMounted(() => loadData());
</script>

<style scoped>
/* Aproveitando o seu CSS existente */
.home-wrapper { min-height: 100vh; background-color: var(--bg-primary); font-family: 'Segoe UI', sans-serif; transition: background 0.3s; }
.main-content { max-width: 1000px; margin: -60px auto 0 auto; padding: 0 20px; }

.header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; color: var(--text-primary); }
.header-actions h2 { margin: 0; font-size: 1.5rem; }
.btn-create { background: var(--accent-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.btn-create:hover { filter: brightness(1.1); transform: translateY(-2px); }

.modules-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; }
.module-card { background: var(--bg-secondary); border-radius: 16px; padding: 30px; cursor: pointer; box-shadow: 0 10px 15px -3px var(--shadow-color); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 20px; transition: all 0.3s ease; position: relative; overflow: hidden; }
.module-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px var(--shadow-color); }

.card-icon { font-size: 2.5rem; background: var(--bg-primary); width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
.card-text h3 { margin: 0 0 5px 0; color: var(--text-primary); font-size: 1.25rem; }
.card-text p { margin: 0; color: var(--text-secondary); font-size: 0.95rem; }

.action-link { position: absolute; bottom: 20px; right: 20px; font-weight: 600; font-size: 0.9rem; opacity: 0; transform: translateX(10px); transition: all 0.3s; }
.module-card:hover .action-link { opacity: 1; transform: translateX(0); }

/* Tema padrão do workspace */
.workspace-theme:hover { border-color: #3b82f6; }
.workspace-theme .card-icon { color: #3b82f6; } 
.workspace-theme .action-link { color: #3b82f6; }

.empty-state { text-align: center; padding: 60px 20px; background: var(--bg-secondary); border-radius: 16px; border: 1px dashed var(--border-color); color: var(--text-secondary); }

/* Estilos do Modal reutilizados do seu código */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(2px); }
.modal-content { background: var(--bg-secondary); width: 450px; max-width: 90%; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border: 1px solid var(--border-color); color: var(--text-primary); }
.modal-header { padding: 15px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-primary); }
.modal-header h2 { margin: 0; font-size: 1.1rem; }
.close-btn { background: none; border: none; cursor: pointer; font-size: 1.5rem; color: var(--text-secondary); }
.modal-body { padding: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; }
.input-modern { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-primary); outline: none; }
.input-modern:focus { border-color: var(--accent-color); }
</style>