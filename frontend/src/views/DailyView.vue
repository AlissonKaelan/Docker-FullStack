<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import http from '../services/http';
import { confirmAction, notify } from '@/utils/alert'; // Se tiver alerts configurados

const tasks = ref([]);
const newTask = ref('');
const isNewTaskRecurring = ref(false); // Checkbox rápido na criação
const editingId = ref(null); // ID da tarefa sendo editada
const editTitle = ref('');   // Texto temporário da edição

// --- API ---
const fetchTasks = async () => {
  try {
    const response = await http.get('/daily');
    tasks.value = response.data;
  } catch (e) { console.error(e); }
};

const addTask = async () => {
  if (!newTask.value.trim()) return;
  try {
    const response = await http.post('/daily', { 
        title: newTask.value,
        is_recurring: isNewTaskRecurring.value 
    });
    tasks.value.unshift(response.data);
    newTask.value = '';
    // Não reseta o checkbox de recorrente pois o usuário pode querer adicionar várias
  } catch (e) { console.error(e); }
};

const toggleTask = async (task) => {
  try {
    await http.put(`/daily/${task.id}`, { is_completed: task.is_completed });
    // Reordena visualmente
    tasks.value.sort((a, b) => a.is_completed - b.is_completed);
  } catch (e) { console.error(e); }
};

const toggleRecurring = async (task) => {
    task.is_recurring = !task.is_recurring;
    try {
        await http.put(`/daily/${task.id}`, { is_recurring: task.is_recurring });
    } catch (e) { task.is_recurring = !task.is_recurring; }
};

const deleteTask = async (id) => {
  try {
    await http.delete(`/daily/${id}`);
    tasks.value = tasks.value.filter(t => t.id !== id);
  } catch (e) { console.error(e); }
};

// --- MODO EDIÇÃO ---
const startEdit = (task) => {
    editingId.value = task.id;
    editTitle.value = task.title;
    // Foca no input automaticamente
    nextTick(() => {
        const input = document.getElementById(`edit-input-${task.id}`);
        if(input) input.focus();
    });
};

const saveEdit = async () => {
    if (!editingId.value) return;
    try {
        await http.put(`/daily/${editingId.value}`, { title: editTitle.value });
        const task = tasks.value.find(t => t.id === editingId.value);
        if (task) task.title = editTitle.value;
        editingId.value = null;
    } catch (e) { console.error(e); }
};

const cancelEdit = () => {
    editingId.value = null;
    editTitle.value = '';
};

// --- RESETAR DIA ---
const resetDay = async () => {
    if(!confirm("Isso apagará tarefas comuns concluídas e desmarcará as recorrentes. Continuar?")) return;
    try {
        await http.post('/daily/reset');
        await fetchTasks(); // Recarrega a lista limpa
    } catch (e) { console.error(e); }
};

// --- COMPUTED ---
const progress = computed(() => {
  if (tasks.value.length === 0) return 0;
  const completed = tasks.value.filter(t => t.is_completed).length;
  return Math.round((completed / tasks.value.length) * 100);
});

onMounted(() => fetchTasks());
</script>

<template>
  <div class="daily-container">
    <div class="header-area">
      <router-link to="/" class="back-link">⬅ Voltar</router-link>
      <h1>☀️ Minhas Atividades</h1>
      
      <div class="header-actions">
          <p class="subtitle">Foco no hoje.</p>
          <button @click="resetDay" class="btn-reset" title="Apaga concluídos e reseta recorrentes">
            🔄 Iniciar Novo Dia
          </button>
      </div>
    </div>

    <div class="progress-container">
        <div class="progress-bar" :style="{ width: progress + '%' }"></div>
        <span class="progress-text">{{ progress }}% Concluído</span>
    </div>

    <div class="input-area">
      <div class="input-wrapper">
          <input 
            v-model="newTask" 
            @keyup.enter="addTask" 
            placeholder="Nova tarefa..." 
            class="main-input"
          />
          <label class="recurrence-toggle" :class="{active: isNewTaskRecurring}" title="Tornar hábito diário">
            <input type="checkbox" v-model="isNewTaskRecurring" style="display: none;">
            🔁
          </label>
      </div>
      <button @click="addTask" class="btn-add">+</button>
    </div>

    <div class="task-list">
      <div v-if="tasks.length === 0" class="empty-state">
        Tudo limpo! Adicione tarefas ou descanse. 🍃
      </div>

      <div 
        v-for="task in tasks" 
        :key="task.id" 
        class="task-item" 
        :class="{ 'completed': task.is_completed, 'recurring': task.is_recurring }"
      >
        <div class="checkbox-wrapper">
             <input type="checkbox" v-model="task.is_completed" @change="toggleTask(task)" class="custom-checkbox">
        </div>
        
        <div class="content-wrapper">
            <input 
                v-if="editingId === task.id"
                :id="`edit-input-${task.id}`"
                v-model="editTitle"
                @keyup.enter="saveEdit"
                @keyup.esc="cancelEdit"
                @blur="saveEdit"
                class="edit-input"
            />
            <span v-else class="task-title" @dblclick="startEdit(task)">
                {{ task.title }}
                <span v-if="task.is_recurring" class="habit-badge" title="Hábito Diário">🔁</span>
            </span>
        </div>
        
        <div class="actions">
            <button @click="toggleRecurring(task)" class="btn-icon" :class="{active: task.is_recurring}" title="Tornar Recorrente">
                🔁
            </button>
            <button @click="startEdit(task)" class="btn-icon" title="Editar">✏️</button>
            <button @click="deleteTask(task.id)" class="btn-icon delete" title="Excluir">🗑️</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.daily-container { max-width: 700px; margin: 0 auto; padding: 40px 20px; font-family: 'Segoe UI', sans-serif; background: #f3f4f6; min-height: 100vh; }

/* HEADER */
.header-area { margin-bottom: 30px; }
.header-area h1 { color: #1f2937; margin: 10px 0; font-size: 2rem; }
.header-actions { display: flex; justify-content: space-between; align-items: center; }
.subtitle { color: #6b7280; margin: 0; }
.back-link { color: #6b7280; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
.back-link:hover { color: #4F46E5; }

.btn-reset {
    background: #e0e7ff; color: #4338ca; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: 0.2s;
}
.btn-reset:hover { background: #c7d2fe; }

/* PROGRESSO */
.progress-container { background: #e5e7eb; height: 25px; border-radius: 12px; position: relative; overflow: hidden; margin-bottom: 30px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }
.progress-bar { background: linear-gradient(90deg, #4F46E5, #7C3AED); height: 100%; transition: width 0.5s ease; border-radius: 12px; }
.progress-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.8rem; font-weight: bold; color: #374151; mix-blend-mode: multiply; }

/* INPUT */
.input-area { display: flex; gap: 10px; margin-bottom: 30px; }
.input-wrapper { flex: 1; position: relative; display: flex; align-items: center; }
.main-input { width: 100%; padding: 15px; padding-right: 40px; border-radius: 12px; border: 2px solid transparent; box-shadow: 0 4px 6px rgba(0,0,0,0.05); font-size: 1.1rem; outline: none; transition: 0.2s; box-sizing: border-box; }
.main-input:focus { border-color: #4F46E5; }

.recurrence-toggle { position: absolute; right: 10px; cursor: pointer; opacity: 0.3; transition: 0.2s; font-size: 1.2rem; user-select: none; }
.recurrence-toggle:hover { opacity: 0.7; }
.recurrence-toggle.active { opacity: 1; transform: scale(1.1); }

.btn-add { background: #4F46E5; color: white; border: none; width: 50px; border-radius: 12px; font-size: 1.5rem; cursor: pointer; transition: 0.2s; }
.btn-add:hover { background: #4338ca; transform: scale(1.05); }

/* LISTA (FLEXBOX CORRIGIDO) */
.task-list { display: flex; flex-direction: column; gap: 10px; }

.task-item { 
    background: white; padding: 12px 15px; border-radius: 12px; 
    display: flex; align-items: center; gap: 15px; /* GAP resolve a sobreposição */
    box-shadow: 0 2px 4px rgba(0,0,0,0.03); transition: all 0.2s; border: 1px solid transparent;
}
.task-item:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.05); }

/* Destaque para Recorrentes */
.task-item.recurring { border-left: 4px solid #8b5cf6; }

/* CHECKBOX */
.checkbox-wrapper { display: flex; align-items: center; }
.custom-checkbox { width: 20px; height: 20px; cursor: pointer; accent-color: #10b981; }

/* CONTEÚDO */
.content-wrapper { flex: 1; display: flex; align-items: center; overflow: hidden; }
.task-title { font-size: 1.1rem; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.habit-badge { font-size: 0.8rem; margin-left: 5px; opacity: 0.6; }
.edit-input { width: 100%; padding: 5px; font-size: 1.1rem; border: 1px solid #4F46E5; border-radius: 4px; outline: none; }

/* AÇÕES */
.actions { display: flex; gap: 5px; opacity: 0; transition: 0.2s; }
.task-item:hover .actions { opacity: 1; }

.btn-icon { background: none; border: none; cursor: pointer; opacity: 0.4; transition: 0.2s; font-size: 1rem; padding: 5px; }
.btn-icon:hover { opacity: 1; transform: scale(1.2); }
.btn-icon.active { opacity: 1; color: #8b5cf6; }
.btn-icon.delete:hover { filter: hue-rotate(140deg); } /* Vermelho */

/* ESTADO CONCLUÍDO */
.task-item.completed { background: #f9fafb; opacity: 0.7; }
.task-item.completed .task-title { text-decoration: line-through; color: #9ca3af; }

.empty-state { text-align: center; color: #9ca3af; margin-top: 40px; font-size: 1.1rem; }
</style>