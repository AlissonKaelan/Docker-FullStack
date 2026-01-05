<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import draggable from 'vuedraggable';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const columns = ref([]);
const newCardTitle = ref('');
const newColumnTitle = ref(''); 
const errorMessage = ref('');

// --- ESTADO DO MODAL ---
const showModal = ref(false);
const selectedCard = ref(null);
const newSubtaskTitle = ref('');

// Cores
const getStatusColor = (slug) => {
  if (!slug) return 'status-gray';
  switch(slug) {
    case 'todo': return 'status-blue';
    case 'doing': return 'status-orange';
    case 'done': return 'status-green';
    default: return 'status-purple';
  }
};

// --- API ACTIONS ---

const fetchKanban = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/kanban');
    columns.value = response.data;
  } catch (error) {
    handleError(error);
  }
};

const createColumn = async () => {
  if (!newColumnTitle.value.trim()) return;
  try {
    await axios.post('http://localhost:8000/api/columns', { title: newColumnTitle.value });
    newColumnTitle.value = '';
    await fetchKanban();
  } catch (error) { handleError(error); }
};

const deleteColumn = async (id) => {
  if(!confirm('Tem certeza? Todos os cards desta coluna serão apagados.')) return;
  try {
    await axios.delete(`http://localhost:8000/api/columns/${id}`);
    await fetchKanban();
  } catch (error) { handleError(error); }
};

const createCard = async () => {
  if (!newCardTitle.value.trim()) return;
  try {
    await axios.post('http://localhost:8000/api/cards', {
      title: newCardTitle.value,
      column_id: columns.value[0].id
    });
    newCardTitle.value = '';
    await fetchKanban();
  } catch (error) { handleError(error); }
};

const onCardDrop = async (event, columnId) => {
  if (event.added || event.moved) {
    const item = event.added ? event.added.element : event.moved.element;
    // Precisamos saber a nova posição, mas quem decide a porcentagem é o back
    const newIndex = event.added ? event.added.newIndex : event.moved.newIndex;
    
    try {
      await axios.put(`http://localhost:8000/api/cards/${item.id}`, {
        column_id: columnId,
        order_index: newIndex + 1
      });
      
      // Recarrega o quadro para mostrar a nova porcentagem e subtarefas concluídas
      await fetchKanban(); 

    } catch (error) { 
        console.error(error); 
        // Se der erro, recarrega para voltar a posição original
        fetchKanban();
    }
  }
};

// --- MODAL & LÓGICA ---

const openCardModal = (card) => {
  selectedCard.value = card; 
  showModal.value = true;
};

const updateCardDetails = async () => {
  if (!selectedCard.value) return;
  try {
    await axios.put(`http://localhost:8000/api/cards/${selectedCard.value.id}`, {
      title: selectedCard.value.title,
      description: selectedCard.value.description,
      percentage: selectedCard.value.percentage
    });
    
    // MUDANÇA: Sempre recarrega para aplicar a regra de mover coluna (0%, 1-99%, 100%)
    await fetchKanban();

  } catch (error) { handleError(error); }
};

const addSubtask = async () => {
  if (!newSubtaskTitle.value.trim()) return;
  try {
    const response = await axios.post('http://localhost:8000/api/subtasks', {
      card_id: selectedCard.value.id,
      title: newSubtaskTitle.value
    });
    if (!selectedCard.value.subtasks) selectedCard.value.subtasks = [];
    selectedCard.value.subtasks.push(response.data);
    newSubtaskTitle.value = '';
  } catch (error) { handleError(error); }
};

const updateSubtaskStatus = async (subtask) => {
  try {
    // Enviamos exatamente o valor que está no checkbox (true ou false)
    await axios.put(`http://localhost:8000/api/subtasks/${subtask.id}`, {
      is_completed: subtask.is_completed
    });
  } catch (error) { 
    handleError(error);
    // Se der erro, revertemos o checkbox visualmente
    subtask.is_completed = !subtask.is_completed; 
  }
};

const handleError = (error) => {
  console.error(error);
  if (error.response && error.response.status === 401) {
    auth.logout();
  } else {
    errorMessage.value = 'Erro: ' + (error.response?.data?.message || error.message);
  }
};

onMounted(() => {
  fetchKanban();
});
</script>

<template>
  <div class="container">
    <div class="top-bar">
      <div class="user-info">
        <h3>🚀 Painel de Projetos</h3>
        <span class="welcome">Usuário: <strong>{{ auth.user?.name }}</strong></span>
      </div>
      <button @click="auth.logout()" class="btn-danger">Sair</button>
    </div>

    <div v-if="errorMessage" class="error-banner">{{ errorMessage }}</div>

    <div class="controls-area">
      <div class="input-group">
        <input v-model="newCardTitle" @keyup.enter="createCard" placeholder="Nova tarefa..." />
        <button @click="createCard" class="btn-primary">Adicionar Tarefa</button>
      </div>
      <div class="input-group">
        <input v-model="newColumnTitle" @keyup.enter="createColumn" placeholder="Nova Coluna (Ex: Provas)..." />
        <button @click="createColumn" class="btn-secondary">Criar Coluna</button>
      </div>
    </div>

    <div class="board">
      <div v-for="column in columns" :key="column.id" class="column">
        
        <div class="column-header" :class="getStatusColor(column.slug)">
          <div class="header-text">
            <h3>{{ column.title }}</h3>
            <span class="badge">{{ column.cards ? column.cards.length : 0 }}</span>
          </div>
          <button v-if="!['todo','doing','done'].includes(column.slug)" @click="deleteColumn(column.id)" class="btn-small">×</button>
        </div>

        <draggable 
          v-model="column.cards" 
          group="kanban-cards" 
          item-key="id"
          class="column-body"
          @change="(event) => onCardDrop(event, column.id)"
        >
          <template #item="{ element }">
            <div class="card" @click="openCardModal(element)">
              <div class="card-title">{{ element.title }}</div>
              <div class="card-meta">
                <span v-if="element.percentage > 0" class="meta-tag" :class="{'done': element.percentage === 100}">
                  {{ element.percentage }}%
                </span>
                <span v-if="element.subtasks && element.subtasks.length > 0" class="meta-tag">
                  📋 {{ element.subtasks.filter(t => t.is_completed).length }}/{{ element.subtasks.length }}
                </span>
              </div>
            </div>
          </template>
        </draggable>
      </div>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Editar Tarefa</h2>
          <button @click="showModal = false" class="close-btn">×</button>
        </div>

        <div class="modal-body" v-if="selectedCard">
          <div class="form-group">
            <label>Título</label>
            <input v-model="selectedCard.title" @change="updateCardDetails" class="full-width" />
          </div>

          <div class="form-group">
            <label>Progresso: {{ selectedCard.percentage }}%</label>
            <input 
              type="range" min="0" max="100" step="10" 
              v-model="selectedCard.percentage" 
              @change="updateCardDetails"
              class="slider"
              :style="{backgroundSize: selectedCard.percentage + '% 100%'}" 
            />
            <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                <small v-if="selectedCard.percentage === 0" style="color: #3b82f6; font-weight: bold;">A Fazer</small>
                <small v-else-if="selectedCard.percentage < 100" style="color: #f59e0b; font-weight: bold;">Em Progresso</small>
                <small v-else style="color: #10b981; font-weight: bold;">Concluído</small>
            </div>
          </div>

          <div class="form-group">
            <label>Descrição</label>
            <textarea v-model="selectedCard.description" @change="updateCardDetails" placeholder="Detalhes..." rows="3"></textarea>
          </div>

          <div class="subtasks-section">
            <h4>Subtarefas</h4>
            <div class="subtask-list">
              <div v-for="sub in selectedCard.subtasks" :key="sub.id" class="subtask-item">
                <input type="checkbox" v-model="sub.is_completed" @change="updateSubtaskStatus(sub)" />
                <span :class="{ 'completed-text': sub.is_completed }">{{ sub.title }}</span>
              </div>
            </div>
            <div class="add-subtask">
              <input v-model="newSubtaskTitle" @keyup.enter="addSubtask" placeholder="+ Subtarefa" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* GERAL */
.container { padding: 20px; font-family: 'Segoe UI', sans-serif; background: #f0f2f5; min-height: 100vh; }
h3 { margin: 0; }

.top-bar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.welcome { color: #666; margin-left: 10px; }

.controls-area { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
.input-group { display: flex; gap: 8px; flex: 1; min-width: 300px; }
.input-group input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; }
.input-group input:focus { border-color: #3b82f6; }

button { border: none; border-radius: 8px; cursor: pointer; font-weight: 600; padding: 10px 16px; transition: 0.2s; }
.btn-primary { background: #3b82f6; color: white; }
.btn-secondary { background: #8b5cf6; color: white; }
.btn-danger { background: #ef4444; color: white; }
.btn-small { padding: 2px 8px; background: transparent; color: #999; font-size: 18px; }

/* --- BOARD (LAYOUT GRID/WRAP) --- */
.board { 
    display: flex; 
    flex-wrap: wrap; /* AQUI: Permite quebrar linha */
    gap: 20px; 
    align-items: flex-start; 
    padding-bottom: 30px; 
    /* Removemos o overflow-x: auto forçado */
}

.column { 
    background: #f8f9fa; 
    min-width: 300px; 
    max-width: 320px; /* Limita largura para caberem vários */
    flex: 1 1 300px; /* Cresce e diminui responsivamente */
    border-radius: 12px; 
    display: flex; 
    flex-direction: column; 
    border: 1px solid #e5e7eb; 
    max-height: 80vh; 
}

.column-header { padding: 15px; background: white; border-top: 4px solid #ccc; display: flex; justify-content: space-between; align-items: center; border-radius: 12px 12px 0 0; border-bottom: 1px solid #eee; }
.header-text { display: flex; gap: 10px; align-items: center; }
.badge { background: #f3f4f6; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; font-weight: bold; }
.column-body { padding: 10px; overflow-y: auto; flex: 1; min-height: 100px; }

/* CORES */
.status-blue { border-top-color: #3b82f6; }
.status-orange { border-top-color: #f59e0b; }
.status-green { border-top-color: #10b981; }
.status-purple { border-top-color: #8b5cf6; }

/* CARD */
.card { background: white; padding: 12px; margin-bottom: 10px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); cursor: pointer; border: 1px solid #f0f0f0; transition: transform 0.1s; }
.card:hover { transform: translateY(-2px); border-color: #3b82f6; }
.card-title { font-weight: 500; color: #374151; margin-bottom: 8px; }
.card-meta { display: flex; gap: 5px; font-size: 0.75rem; }
.meta-tag { background: #eef2ff; color: #4f46e5; padding: 2px 6px; border-radius: 4px; }
.meta-tag.done { background: #dcfce7; color: #166534; }

/* MODAL */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { background: white; width: 500px; max-width: 90%; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden; animation: fadeIn 0.2s ease; }
.modal-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f9fafb; }
.modal-body { padding: 20px; max-height: 70vh; overflow-y: auto; }
.close-btn { background: none; color: #666; font-size: 24px; padding: 0 10px; }

.form-group { margin-bottom: 15px; }
.form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: #374151; font-size: 0.9rem; }
.full-width { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; resize: vertical; }

.slider { -webkit-appearance: none; width: 100%; height: 8px; border-radius: 5px; background: #e5e7eb; outline: none; background-image: linear-gradient(#3b82f6, #3b82f6); background-repeat: no-repeat; }
.slider::-webkit-slider-thumb { -webkit-appearance: none; height: 20px; width: 20px; border-radius: 50%; background: #2563eb; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }

.subtasks-section { margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px; }
.subtask-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f9f9f9; }
.completed-text { text-decoration: line-through; color: #9ca3af; }
.add-subtask input { width: 100%; padding: 8px; margin-top: 10px; border: 1px dashed #ccc; border-radius: 6px; }

@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>