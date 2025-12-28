<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import draggable from 'vuedraggable'; // Importamos a lib

const columns = ref([]);

// Busca dados iniciais
const fetchKanban = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/kanban');
    columns.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar kanban:', error);
  }
};

// Função chamada SEMPRE que você soltar um cartão
const onCardDrop = async (event, columnId) => {
  // O evento nos diz qual item foi mexido
  if (event.added || event.moved) {
    const item = event.added ? event.added.element : event.moved.element;
    const newIndex = event.added ? event.added.newIndex : event.moved.newIndex;
    
    console.log(`Cartão ${item.title} movido para Coluna ${columnId} na posição ${newIndex}`);

    // Chama a API para salvar no banco
    try {
      await axios.put(`http://localhost:8000/api/cards/${item.id}`, {
        column_id: columnId,
        order_index: newIndex + 1 // +1 porque array começa em 0 mas banco gostamos de 1
      });
    } catch (error) {
      console.error('Erro ao salvar movimento:', error);
      alert('Erro ao salvar! O cartão vai voltar para o lugar original ao recarregar.');
    }
  }
};

onMounted(() => {
  fetchKanban();
});
</script>

<template>
  <div class="board">
    <div v-for="column in columns" :key="column.id" class="column">
      <div class="column-header">
        <h3>{{ column.title }}</h3>
        <span class="count">{{ column.cards.length }}</span>
      </div>

      <draggable 
        v-model="column.cards" 
        group="kanban-cards" 
        item-key="id"
        class="column-body"
        @change="(event) => onCardDrop(event, column.id)"
      >
        <template #item="{ element }">
          <div class="card">
            {{ element.title }}
          </div>
        </template>
      </draggable>

    </div>
  </div>
</template>

<style scoped>
/* Mesmos estilos de antes */
.board { display: flex; gap: 1rem; padding: 2rem; height: 100vh; background: #f3f4f6; }
.column { background: #e2e8f0; min-width: 300px; border-radius: 8px; padding: 1rem; display: flex; flex-direction: column; }
.column-header { display: flex; justify-content: space-between; margin-bottom: 1rem; font-weight: bold; color: #1e293b; }
.column-body { min-height: 100px; /* Importante para conseguir soltar em coluna vazia */ }
.card { background: white; padding: 1rem; border-radius: 6px; margin-bottom: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); cursor: grab; }
.card:active { cursor: grabbing; }
</style>