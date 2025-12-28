<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const boards = ref([]);
const loading = ref(true);
const error = ref(null);

// Boa prática: URL baseada no ambiente
const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const fetchBoards = async () => {
  try {
    const response = await axios.get(`${apiUrl}/boards`);
    boards.value = response.data;
  } catch (err) {
    error.value = "Erro ao carregar quadros. O servidor está rodando?";
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchBoards();
});
</script>

<template>
  <div class="container">
    <h2 class="title">Meus Projetos Kanban</h2>

    <div v-if="loading" class="loading">Carregando...</div>

    <div v-else-if="error" class="error">{{ error }}</div>

    <div v-else-if="boards.length === 0" class="empty">
      Você ainda não tem nenhum quadro.
    </div>

    <div v-else class="board-grid">
      <div v-for="board in boards" :key="board.id" class="board-card">
        <h3>{{ board.name }}</h3>
        <div class="meta">
          <span>📅 {{ new Date(board.created_at).toLocaleDateString() }}</span>
          <span>📋 {{ board.columns.length }} colunas</span>
        </div>
        <button class="btn-open">Abrir Quadro</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container { padding: 40px; max-width: 1200px; margin: 0 auto; font-family: 'Segoe UI', sans-serif; }
.title { color: #2d3748; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }

.board-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }

.board-card {
  background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s;
}
.board-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
.board-card h3 { margin: 0 0 10px 0; color: #1a202c; font-size: 1.2rem; }

.meta { display: flex; justify-content: space-between; font-size: 0.85rem; color: #718096; margin-bottom: 15px; }

.btn-open {
  width: 100%; background-color: #4299e1; color: white; border: none; padding: 10px;
  border-radius: 6px; cursor: pointer; font-weight: bold;
}
.btn-open:hover { background-color: #3182ce; }

.loading, .empty, .error { text-align: center; padding: 40px; color: #718096; font-size: 1.1rem; }
.error { color: #e53e3e; }
</style>