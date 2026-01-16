<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import http from '../services/http';
import { confirmAction, notify } from '@/utils/alert';

const tasks = ref([]);
const newTask = ref('');
const isNewTaskRecurring = ref(false);
const editingId = ref(null);
const editTitle = ref('');

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
    notify('success', 'Atividade adicionada!');
  } catch (e) { console.error(e); }
};

const toggleTask = async (task) => {
  try {
    await http.put(`/daily/${task.id}`, { is_completed: task.is_completed });
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

const startEdit = (task) => {
    editingId.value = task.id;
    editTitle.value = task.title;
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

const resetDay = async () => {
    const confirmed = await confirmAction(
        'Iniciar Novo Dia?',
        'Tarefas concluídas serão apagadas e hábitos resetados.'
    );
    if(!confirmed) return;
    try {
        await http.post('/daily/reset');
        notify('success', 'Bom dia! Tudo pronto.');
        await fetchTasks();
    } catch (e) { notify('error', 'Erro ao resetar.'); }
};

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
      <div class="header-top">
          <router-link to="/" class="back-link">⬅ Voltar</router-link>
          <button @click="resetDay" class="btn-reset" title="Apaga concluídos e reseta recorrentes">
            🔄 Iniciar Novo Dia
          </button>
      </div>
      <h1>☀️ Minhas Atividades</h1>
      <p class="subtitle">Foco no hoje.</p>
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
            <button @click="toggleRecurring(task)" class="btn-icon" :class="{active: task.is_recurring}" title="Tornar Recorrente">🔁</button>
            <button @click="startEdit(task)" class="btn-icon" title="Editar">✏️</button>
            <button @click="deleteTask(task.id)" class="btn-icon delete" title="Excluir">🗑️</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* CONFIGURAÇÃO DO TEMA VIA VARIÁVEIS */
.daily-container { 
    max-width: 700px; margin: 0 auto; padding: 40px 20px; 
    font-family: 'Segoe UI', sans-serif; 
    background-color: var(--bg-primary); /* Fundo Dinâmico */
    min-height: 100vh; 
    transition: background-color 0.3s;
}

.header-area { margin-bottom: 30px; text-align: center; }
.header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.header-area h1 { color: var(--text-primary); margin: 5px 0; font-size: 2rem; }
.subtitle { color: var(--text-secondary); margin: 0; }

.back-link { 
    color: var(--text-secondary); text-decoration: none; font-weight: 600; font-size: 0.9rem; 
    padding: 8px 12px; background: var(--bg-secondary); border-radius: 6px; border: 1px solid var(--border-color);
}
.back-link:hover { color: var(--text-primary); border-color: var(--accent-color); }

.btn-reset {
    background: var(--bg-secondary); color: var(--accent-color); border: 1px solid var(--border-color);
    padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: 0.2s;
}
.btn-reset:hover { background: var(--bg-primary); border-color: var(--accent-color); }

/* PROGRESSO */
.progress-container { background: var(--border-color); height: 25px; border-radius: 12px; position: relative; overflow: hidden; margin-bottom: 30px; }
.progress-bar { background: linear-gradient(90deg, var(--accent-color), #7C3AED); height: 100%; transition: width 0.5s ease; border-radius: 12px; }
.progress-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.8rem; font-weight: bold; color: var(--text-primary); mix-blend-mode: exclusion; }

/* INPUT */
.input-area { display: flex; gap: 10px; margin-bottom: 30px; }
.input-wrapper { flex: 1; position: relative; display: flex; align-items: center; }

.main-input { 
    width: 100%; padding: 15px; padding-right: 40px; border-radius: 12px; 
    border: 1px solid var(--border-color); 
    background-color: var(--bg-secondary); color: var(--text-primary);
    font-size: 1.1rem; outline: none; transition: 0.2s; box-sizing: border-box; 
    box-shadow: 0 4px 6px var(--shadow-color);
}
.main-input:focus { border-color: var(--accent-color); }

.recurrence-toggle { position: absolute; right: 10px; cursor: pointer; opacity: 0.3; transition: 0.2s; font-size: 1.2rem; }
.recurrence-toggle:hover { opacity: 0.7; }
.recurrence-toggle.active { opacity: 1; transform: scale(1.1); filter: drop-shadow(0 0 2px var(--accent-color)); }

.btn-add { background: var(--accent-color); color: white; border: none; width: 50px; border-radius: 12px; font-size: 1.5rem; cursor: pointer; transition: 0.2s; }
.btn-add:hover { filter: brightness(1.1); }

/* LISTA */
.task-list { display: flex; flex-direction: column; gap: 10px; }

.task-item { 
    background: var(--bg-secondary); padding: 12px 15px; border-radius: 12px; 
    display: flex; align-items: center; gap: 15px; border: 1px solid var(--border-color);
    box-shadow: 0 2px 4px var(--shadow-color); transition: all 0.2s;
}
.task-item:hover { transform: translateY(-2px); border-color: var(--accent-color); }

/* Destaque para Recorrentes */
.task-item.recurring { border-left: 4px solid var(--accent-color); }

/* CHECKBOX */
.checkbox-wrapper { display: flex; align-items: center; }
.custom-checkbox { width: 20px; height: 20px; cursor: pointer; accent-color: var(--accent-color); }

/* CONTEÚDO */
.content-wrapper { flex: 1; display: flex; align-items: center; overflow: hidden; }
.task-title { font-size: 1.1rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.habit-badge { font-size: 0.8rem; margin-left: 5px; opacity: 0.6; }

.edit-input { 
    width: 100%; padding: 5px; font-size: 1.1rem; outline: none; border-radius: 4px;
    background: var(--input-bg); color: var(--text-primary); border: 1px solid var(--accent-color); 
}

/* AÇÕES */
.actions { display: flex; gap: 5px; opacity: 0; transition: 0.2s; }
.task-item:hover .actions { opacity: 1; }

.btn-icon { background: none; border: none; cursor: pointer; opacity: 0.4; transition: 0.2s; font-size: 1rem; padding: 5px; color: var(--text-secondary); }
.btn-icon:hover { opacity: 1; transform: scale(1.2); color: var(--text-primary); }
.btn-icon.active { opacity: 1; color: var(--accent-color); }
.btn-icon.delete:hover { color: #ef4444; filter: none; }

/* ESTADO CONCLUÍDO */
.task-item.completed { background: var(--bg-primary); opacity: 0.6; border-color: transparent; }
.task-item.completed .task-title { text-decoration: line-through; color: var(--text-secondary); }

.empty-state { text-align: center; color: var(--text-secondary); margin-top: 40px; font-size: 1.1rem; }
</style>