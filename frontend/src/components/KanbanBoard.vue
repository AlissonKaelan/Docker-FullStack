<script setup>
import { ref, onMounted } from 'vue';
import draggable from 'vuedraggable';
import { useAuthStore } from '../stores/auth';
// MUDANÇA CRÍTICA: Usamos nosso serviço http configurado, não o axios puro
import http from '../services/http'; 

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
    // URL simplificada (http já tem a base)
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

const deleteColumn = async (id) => {
  if(!confirm('Tem certeza? Todos os cards desta coluna serão apagados.')) return;
  try {
    await http.delete(`/columns/${id}`);
    await fetchKanban();
  } catch (error) { handleError(error); }
};

const createCard = async () => {
  if (!newCardTitle.value.trim()) return;
  // Proteção: Se não houver colunas, não tenta criar card
  if (columns.value.length === 0) {
      alert("Crie uma coluna antes de adicionar tarefas!");
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
        order: newIndex + 1 // Ajustado para 'order' conforme backend
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
  // Clona o objeto para evitar reatividade imediata sem salvar
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
    // Atualiza o fundo também
    await fetchKanban();
  } catch (error) { handleError(error); }
};

const updateSubtaskStatus = async (subtask) => {
  try {
    await http.put(`/subtasks/${subtask.id}`, {
      is_completed: subtask.is_completed
    });
    // Atualiza o kanban principal para refletir a contagem na lista
    await fetchKanban();
  } catch (error) { 
    handleError(error);
    subtask.is_completed = !subtask.is_completed; 
  }
};

const handleError = (error) => {
  console.error(error);
  if (error.response && error.response.status === 401) {
    // Se der 401, o http.js já vai redirecionar, mas garantimos aqui
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
        <router-link to="/" class="back-btn">⬅ Voltar</router-link>
        <h1>Gestão de Tarefas</h1>
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
          <button @click="showModal = false" class="close-btn">×</button>
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
/* GERAL */
.kanban-container { padding: 40px; max-width: 1400px; margin: 0 auto; font-family: 'Segoe UI', sans-serif; background: #f3f4f6; min-height: 100vh; }
.page-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
.page-header h1 { margin: 0; font-size: 1.8rem; color: #1f2937; }
.back-btn { text-decoration: none; color: #6b7280; font-weight: 600; padding: 8px 12px; background: white; border-radius: 6px; font-size: 0.9rem; transition: 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.back-btn:hover { background: #e5e7eb; color: #1f2937; }

/* CONTROLES */
.controls-area { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
.input-group { display: flex; gap: 10px; flex: 1; min-width: 300px; background: white; padding: 10px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

.input-modern { flex: 1; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; transition: border 0.2s; }
.input-modern:focus { border-color: #4F46E5; }
.full-width { width: 100%; box-sizing: border-box; }

button { border: none; border-radius: 8px; cursor: pointer; font-weight: 600; padding: 10px 20px; transition: 0.2s; white-space: nowrap; }
.btn-primary { background: #4F46E5; color: white; }
.btn-primary:hover { background: #4338ca; }
.btn-secondary { background: #8b5cf6; color: white; }
.btn-secondary:hover { background: #7c3aed; }
.btn-small { padding: 2px 8px; background: transparent; color: #9ca3af; font-size: 20px; }
.btn-small:hover { color: #ef4444; }

/* BOARD */
.board { display: flex; overflow-x: auto; gap: 25px; padding-bottom: 20px; align-items: flex-start; height: calc(100vh - 200px); }

.column { 
    background: #f9fafb; min-width: 320px; width: 320px; border-radius: 12px; display: flex; flex-direction: column; 
    border: 1px solid #e5e7eb; max-height: 100%; 
}

.column-header { padding: 15px; background: white; border-bottom: 1px solid #e5e7eb; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; border-top: 4px solid transparent; }
.header-text { display: flex; align-items: center; gap: 10px; }
.header-text h3 { margin: 0; font-size: 1rem; color: #374151; font-weight: 700; }
.badge { background: #e5e7eb; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; color: #4b5563; }

.column-body { padding: 15px; overflow-y: auto; flex: 1; min-height: 100px; }

/* Status Colors */
.status-blue { border-top-color: #3b82f6; }
.status-orange { border-top-color: #f59e0b; }
.status-green { border-top-color: #10b981; }
.status-purple { border-top-color: #8b5cf6; }

/* CARDS */
.card { background: white; padding: 15px; margin-bottom: 12px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); cursor: grab; border: 1px solid transparent; transition: all 0.2s; }
.card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-color: #4F46E5; }
.card:active { cursor: grabbing; }
.card-title { font-weight: 600; color: #1f2937; margin-bottom: 8px; font-size: 0.95rem; }
.card-meta { display: flex; gap: 8px; flex-wrap: wrap; }
.meta-tag { background: #eff6ff; color: #3b82f6; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.meta-tag.done { background: #ecfdf5; color: #10b981; }

/* MODAL */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; backdrop-filter: blur(2px); }
.modal-content { background: white; width: 600px; max-width: 90%; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden; }
.modal-header { padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f9fafb; }
.modal-header h2 { margin: 0; font-size: 1.25rem; color: #1f2937; }
.close-btn { background: none; border: none; font-size: 1.5rem; color: #6b7280; cursor: pointer; padding: 0; }
.modal-body { padding: 30px; max-height: 70vh; overflow-y: auto; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #374151; font-size: 0.9rem; }

.slider { -webkit-appearance: none; width: 100%; height: 6px; border-radius: 3px; background: #e5e7eb; outline: none; background-image: linear-gradient(#4F46E5, #4F46E5); background-repeat: no-repeat; }
.slider::-webkit-slider-thumb { -webkit-appearance: none; height: 18px; width: 18px; border-radius: 50%; background: #4F46E5; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2); border: 2px solid white; transition: transform 0.1s; }
.slider::-webkit-slider-thumb:hover { transform: scale(1.1); }

.subtasks-section { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
.subtasks-section h4 { margin: 0 0 15px 0; color: #4b5563; font-size: 1rem; }
.subtask-item { display: flex; align-items: center; gap: 12px; padding: 8px 0; }
.subtask-item input[type="checkbox"] { width: 18px; height: 18px; accent-color: #10b981; cursor: pointer; }
.completed-text { text-decoration: line-through; color: #9ca3af; }
.add-subtask { margin-top: 15px; }

.error-banner { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
</style>