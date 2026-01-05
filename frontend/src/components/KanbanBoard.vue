<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import draggable from 'vuedraggable';

const columns = ref([]);
const newCardTitle = ref('');
const errorMessage = ref(''); // Para mostrar erros na tela

// Cores baseadas no status
const getStatusColor = (slug) => {
  if (!slug) return 'status-gray'; // Proteção contra slug vazio
  switch(slug) {
    case 'todo': return 'status-blue';
    case 'doing': return 'status-orange';
    case 'done': return 'status-green';
    default: return 'status-gray';
  }
};

const fetchKanban = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/kanban');
    columns.value = response.data;
    console.log("Dados recebidos:", columns.value); // Olhe no F12
  } catch (error) {
    console.error('Erro API:', error);
    errorMessage.value = 'Erro ao conectar com a API: ' + error.message;
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
    } catch (error) {
      console.error(error);
    }
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
  } catch (error) {
    alert('Erro ao criar: ' + error.message);
  }
};

onMounted(() => {
  fetchKanban();
});
</script>

<template>
  <div class="container">
    
    <div v-if="errorMessage" style="background: red; color: white; padding: 10px; margin-bottom: 20px;">
      {{ errorMessage }}
    </div>

    <div class="add-task-bar">
      <input v-model="newCardTitle" @keyup.enter="createCard" placeholder="Nova tarefa..." />
      <button @click="createCard">Criar</button>
    </div>

    <div v-if="columns.length === 0 && !errorMessage">
        Carregando dados... (Verifique se o backend está rodando)
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
/* Estilos simplificados para garantir visualização */
.container { padding: 20px; font-family: sans-serif; background: #f4f4f4; min-height: 100vh; }
.add-task-bar { margin-bottom: 20px; display: flex; gap: 10px; }
.add-task-bar input { padding: 10px; flex: 1; }
.add-task-bar button { padding: 10px 20px; cursor: pointer; background: blue; color: white; border: none; }

.board { display: flex; gap: 20px; overflow-x: auto; align-items: flex-start; }
.column { background: #e0e0e0; min-width: 300px; border-radius: 8px; }

.column-header { padding: 15px; background: white; border-top: 5px solid gray; display: flex; justify-content: space-between; align-items: center; border-radius: 8px 8px 0 0; }
.column-header h3 { margin: 0; font-size: 16px; }
.badge { background: #ccc; padding: 2px 8px; border-radius: 10px; font-size: 12px; }

/* Cores das Bordas */
.status-blue { border-top-color: #3b82f6; }
.status-orange { border-top-color: #f59e0b; }
.status-green { border-top-color: #10b981; }

.column-body { padding: 10px; min-height: 100px; }
.card { background: white; padding: 10px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); cursor: grab; }
</style>