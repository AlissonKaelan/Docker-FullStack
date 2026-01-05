<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import draggable from 'vuedraggable';
import { useAuthStore } from '../stores/auth'; // Importamos a loja de Autenticação

const auth = useAuthStore(); // Acesso aos dados do usuário (auth.user.name)
const columns = ref([]);
const newCardTitle = ref('');
const errorMessage = ref('');

// Cores baseadas no status
const getStatusColor = (slug) => {
  if (!slug) return 'status-gray';
  switch(slug) {
    case 'todo': return 'status-blue';
    case 'doing': return 'status-orange';
    case 'done': return 'status-green';
    default: return 'status-gray';
  }
};

const fetchKanban = async () => {
  try {
    // Agora o Axios já vai com o Token automaticamente!
    const response = await axios.get('http://localhost:8000/api/kanban');
    columns.value = response.data;
  } catch (error) {
    console.error('Erro API:', error);
    if (error.response && error.response.status === 401) {
      errorMessage.value = 'Sessão expirada. Faça login novamente.';
      auth.logout(); // Chuta pro login se o token for inválido
    } else {
      errorMessage.value = 'Erro ao conectar com a API: ' + error.message;
    }
  }
};

const onCardDrop = async (event, columnId) => {
  if (event.added || event.moved) {
    const item = event.added ? event.added.element : event.moved.element;
    const newIndex = event.added ? event.added.newIndex : event.moved.newIndex;
    try {
      await axios.put(`http://localhost:8000/api/cards/${item.id}`, {
        column_id: columnId,
        order_index: newIndex + 1
      });
    } catch (error) { console.error(error); }
  }
};

const createCard = async () => {
  if (!newCardTitle.value.trim()) return;
  try {
    await axios.post('http://localhost:8000/api/cards', {
      title: newCardTitle.value,
      column_id: columns.value[0].id
    });
    newCardTitle.value = '';
    fetchKanban();
  } catch (error) { alert('Erro ao criar: ' + error.message); }
};

onMounted(() => {
  fetchKanban();
});
</script>

<template>
  <div class="container">
    
    <div class="top-bar">
      <div class="user-info">
        <span>Olá, <strong>{{ auth.user?.name }}</strong> 👋</span>
      </div>
      <button @click="auth.logout()" class="logout-btn">Sair</button>
    </div>

    <div v-if="errorMessage" class="error-banner">
      {{ errorMessage }}
    </div>

    <div class="add-task-bar">
      <input v-model="newCardTitle" @keyup.enter="createCard" placeholder="➕ Nova tarefa..." />
      <button @click="createCard">Adicionar</button>
    </div>

    <div class="board">
      <div v-for="column in columns" :key="column.id" class="column">
        <div class="column-header" :class="getStatusColor(column.slug)">
          <h3>{{ column.title }}</h3>
          <span class="badge">{{ column.cards ? column.cards.length : 0 }}</span>
        </div>
        <draggable 
          v-model="column.cards" 
          group="kanban-cards" 
          item-key="id"
          class="column-body"
          @change="(event) => onCardDrop(event, column.id)"
        >
          <template #item="{ element }">
            <div class="card">{{ element.title }}</div>
          </template>
        </draggable>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container { padding: 20px; font-family: sans-serif; background: #f4f4f4; min-height: 100vh; }

/* Barra de Topo */
.top-bar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 10px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.user-info { color: #555; }
.logout-btn { background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: bold; }
.logout-btn:hover { background: #dc2626; }

.error-banner { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 20px; }

/* Resto do CSS (Mantido igual) */
.add-task-bar { margin-bottom: 20px; display: flex; gap: 10px; }
.add-task-bar input { padding: 10px; flex: 1; border: 1px solid #ddd; border-radius: 6px; }
.add-task-bar button { padding: 10px 20px; cursor: pointer; background: #3b82f6; color: white; border: none; border-radius: 6px; }

.board { display: flex; gap: 20px; overflow-x: auto; align-items: flex-start; padding-bottom: 20px; }
.column { background: #e5e7eb; min-width: 300px; border-radius: 8px; }
.column-header { padding: 15px; background: white; border-top: 5px solid gray; display: flex; justify-content: space-between; align-items: center; border-radius: 8px 8px 0 0; }
.column-header h3 { margin: 0; font-size: 16px; }
.badge { background: #f3f4f6; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: bold; }
.status-blue { border-top-color: #3b82f6; }
.status-orange { border-top-color: #f59e0b; }
.status-green { border-top-color: #10b981; }
.column-body { padding: 10px; min-height: 100px; }
.card { background: white; padding: 10px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); cursor: grab; }
</style>