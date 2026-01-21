<script setup>
import { ref, onMounted } from 'vue';
import draggable from 'vuedraggable';
import { useAuthStore } from '../stores/auth';
import http from '../services/http'; 
import { notify, confirmAction } from '@/utils/alert';

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
    const response = await http.get('/kanban');
    columns.value = response.data;
  } catch (error) {
    handleError(error);
  }
};

const createColumn = async () => {
  if (!newColumnTitle.value.trim()) return;
  try {
    await http.post('/columns', { title: newColumnTitle.value });
    newColumnTitle.value = '';
    await fetchKanban();
  } catch (error) { handleError(error); }
};

const deleteCard = async (id) => {
  const confirmed = await confirmAction(
    'Apagar Tarefa?',
    'Cuidado: Esta ação não pode ser desfeita.'
  );
  if (!confirmed) return;

  try {
    await http.delete(`/cards/${id}`);
    notify('success', 'Tarefa apagada com sucesso.');
    await fetchKanban();
  } catch (error){
    notify('error', 'Não foi possível apagar a tarefa.');
    handleError(error);
  }
};

const deleteColumn = async (id) => {
  const confirmed = await confirmAction(
    'Apagar Coluna?',
    'Cuidado: Todas as tarefas dentro desta coluna também serão perdidas para sempre.'
  );
  if (!confirmed) return;

  try {
    await http.delete(`/columns/${id}`);
    notify('success', 'Coluna apagada com sucesso.');
    await fetchKanban();
  } catch (error){
    notify('error', 'Não foi possível apagar a coluna.');
    handleError(error);
  }
};

const createCard = async () => {
  if (!newCardTitle.value.trim()) return;
  if (columns.value.length === 0) {
      notify('warning', "Crie uma coluna antes de adicionar tarefas!");
      return;
  }
  
  try {
    await http.post('/cards', {
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
    const newIndex = event.added ? event.added.newIndex : event.moved.newIndex;
    
    try {
      await http.put(`/cards/${item.id}`, {
        column_id: columnId,
        order: newIndex + 1 
      });
      await fetchKanban(); 
    } catch (error) { 
        console.error(error); 
        fetchKanban();
    }
  }
};

// --- MODAL & LÓGICA ---

const openCardModal = (card) => {
  selectedCard.value = JSON.parse(JSON.stringify(card)); 
  showModal.value = true;
};

const updateCardDetails = async () => {
  if (!selectedCard.value) return;
  try {
    await http.put(`/cards/${selectedCard.value.id}`, {
      title: selectedCard.value.title,
      description: selectedCard.value.description,
      percentage: selectedCard.value.percentage
    });
    await fetchKanban();
  } catch (error) { handleError(error); }
};

const addSubtask = async () => {
  if (!newSubtaskTitle.value.trim()) return;
  try {
    const response = await http.post('/subtasks', {
      card_id: selectedCard.value.id,
      title: newSubtaskTitle.value
    });
    if (!selectedCard.value.subtasks) selectedCard.value.subtasks = [];
    selectedCard.value.subtasks.push(response.data);
    newSubtaskTitle.value = '';
    await fetchKanban();
  } catch (error) { handleError(error); }
};

const updateSubtaskStatus = async (subtask) => {
  try {
    await http.put(`/subtasks/${subtask.id}`, {
      is_completed: subtask.is_completed
    });
    await fetchKanban();
  } catch (error) { 
    handleError(error);
    subtask.is_completed = !subtask.is_completed; 
  }
};

const handleError = (error) => {
  console.error(error);
  if (error.response && error.response.status === 401) {
    errorMessage.value = "Sessão expirada.";
  } else {
    errorMessage.value = 'Erro: ' + (error.response?.data?.message || error.message);
  }
};

onMounted(() => {
  fetchKanban();
});
</script>

<template>
  <div class="kanban-container">
    <div class="page-header">
        <div class="header-left">
            <router-link to="/" class="back-btn">⬅ Voltar</router-link>
            <h1>Gestão de Tarefas</h1>
        </div>
    </div>

    <div v-if="errorMessage" class="error-banner">{{ errorMessage }}</div>

    <div class="controls-area">
      <div class="input-group">
        <input v-model="newCardTitle" @keyup.enter="createCard" placeholder="Nova tarefa..." class="input-modern" />
        <button @click="createCard" class="btn-primary">Adicionar Tarefa</button>
      </div>
      <div class="input-group">
        <input v-model="newColumnTitle" @keyup.enter="createColumn" placeholder="Nova Coluna..." class="input-modern" />
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
          <div class="header-actions">
              <button 
                  @click="deleteCard(selectedCard.id); showModal = false" 
                  class="btn-icon-danger" 
                  title="Excluir Tarefa"
              >
                🗑️
              </button>
              
              <button @click="showModal = false" class="close-btn">×</button>
          </div>
        </div>

        <div class="modal-body" v-if="selectedCard">
          <div class="form-group">
            <label>Título</label>
            <input v-model="selectedCard.title" @change="updateCardDetails" class="input-modern full-width" />
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
          </div>

          <div class="form-group">
            <label>Descrição</label>
            <textarea v-model="selectedCard.description" @change="updateCardDetails" placeholder="Detalhes..." rows="3" class="input-modern full-width"></textarea>
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
              <input v-model="newSubtaskTitle" @keyup.enter="addSubtask" placeholder="+ Adicionar subtarefa e Enter" class="input-modern full-width" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* APLICANDO AS VARIÁVEIS GLOBAIS PARA TEMA ESCURO */
.kanban-container { 
    padding: 20px; 
    max-width: 100%; 
    margin: 0 auto; 
    font-family: 'Segoe UI', sans-serif; 
    background: var(--bg-primary); /* Fundo dinâmico */
    min-height: 100vh; 
    transition: background-color 0.3s;
}

.page-header h1 { margin: 0; font-size: 1.8rem; color: var(--text-primary); }

.back-btn { 
    text-decoration: none; color: var(--text-secondary); font-weight: 600; 
    padding: 8px 12px; background: var(--bg-secondary); border-radius: 6px; 
    font-size: 0.9rem; transition: 0.2s; box-shadow: 0 1px 2px var(--shadow-color); border: 1px solid var(--border-color);
}
.back-btn:hover { background: var(--border-color); color: var(--text-primary); }

.controls-area { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
.input-group { display: flex; gap: 10px; flex: 1; min-width: 280px; background: var(--bg-secondary); padding: 10px; border-radius: 12px; box-shadow: 0 2px 4px var(--shadow-color); border: 1px solid var(--border-color); }

.input-modern { 
    flex: 1; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; transition: border 0.2s; 
    background-color: var(--input-bg); color: var(--text-primary);
}
.input-modern:focus { border-color: var(--accent-color); }
.full-width { width: 100%; box-sizing: border-box; }

button { border: none; border-radius: 8px; cursor: pointer; font-weight: 600; padding: 10px 20px; transition: 0.2s; white-space: nowrap; }
.btn-primary { background: var(--accent-color); color: white; }
.btn-primary:hover { filter: brightness(110%); }
.btn-secondary { background: #8b5cf6; color: white; }
.btn-secondary:hover { filter: brightness(110%); }
.btn-small { padding: 2px 8px; background: transparent; color: var(--text-secondary); font-size: 20px; }
.btn-small:hover { color: #ef4444; }

.board { 
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
    gap: 25px; padding-bottom: 40px; align-items: start; 
}

.column { 
    background: var(--bg-primary); /* Coluna levemente diferente do fundo */
    border: 1px solid var(--border-color);
    width: 100%; border-radius: 12px; display: flex; flex-direction: column; max-height: 85vh; 
}

.column-header { 
    padding: 15px; background: var(--bg-secondary); border-bottom: 1px solid var(--border-color); 
    border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; border-top: 4px solid transparent; 
}
.header-text h3 { margin: 0; font-size: 1rem; color: var(--text-primary); font-weight: 700; }
.badge { background: var(--border-color); padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; color: var(--text-secondary); }

.column-body { padding: 15px; overflow-y: auto; flex: 1; min-height: 100px; }
.column-body::-webkit-scrollbar { width: 6px; }
.column-body::-webkit-scrollbar-thumb { background-color: var(--border-color); border-radius: 3px; }

/* CORES DOS STATUS (MANTÉM CORES FIXAS) */
.status-blue { border-top-color: #3b82f6; }
.status-orange { border-top-color: #f59e0b; }
.status-green { border-top-color: #10b981; }
.status-purple { border-top-color: #8b5cf6; }

/* CARDS */
.card { 
    background: var(--bg-secondary); 
    padding: 15px; margin-bottom: 12px; border-radius: 8px; box-shadow: 0 1px 3px var(--shadow-color); 
    cursor: grab; border: 1px solid var(--border-color); transition: all 0.2s; 
}
.card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px var(--shadow-color); border-color: var(--accent-color); }
.card-title { font-weight: 600; color: var(--text-primary); margin-bottom: 8px; font-size: 0.95rem; }

/* MODAL */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: flex; justify-content: center; align-items: center; z-index: 1000; backdrop-filter: blur(2px); }
.modal-content { 
    background: var(--bg-secondary); 
    width: 600px; max-width: 90%; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); 
    overflow: hidden; max-height: 90vh; display: flex; flex-direction: column; border: 1px solid var(--border-color);
}
.modal-header { 
    padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-primary); 
}
.modal-header h2 { margin: 0; font-size: 1.25rem; color: var(--text-primary); }
.close-btn { background: none; border: none; font-size: 1.5rem; color: var(--text-secondary); cursor: pointer; padding: 0; }
.modal-body { padding: 30px; overflow-y: auto; flex: 1; color: var(--text-primary); }

.form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-primary); font-size: 0.9rem; }
.subtasks-section h4 { margin: 0 0 15px 0; color: var(--text-secondary); font-size: 1rem; }
.completed-text { text-decoration: line-through; color: var(--text-secondary); }
.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.btn-icon-danger {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    transition: transform 0.2s;
    opacity: 0.7;
}

.btn-icon-danger:hover {
    transform: scale(1.2);
    opacity: 1;
    filter: hue-rotate(140deg); /* Faz ficar avermelhado */
}
</style>