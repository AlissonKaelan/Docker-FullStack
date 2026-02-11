<script setup>
import { ref, onMounted } from 'vue';
import http from '../services/http';
import { notify, confirmAction } from '@/utils/alert'; // <--- IMPORTANTE: Importar confirmAction

const props = defineProps(['isOpen']);
const emit = defineEmits(['close', 'refresh']);

const categories = ref([]);
const newCat = ref({ name: '', color: '#3b82f6', type: 'expense' });

const fetchCategories = async () => {
  try {
    const { data } = await http.get('/categories');
    categories.value = data;
  } catch (e) { console.error(e); }
};

const addCategory = async () => {
  if (!newCat.value.name) return notify('warning', 'Nome obrigatório');
  try {
    await http.post('/categories', newCat.value);
    notify('success', 'Categoria criada!');
    newCat.value.name = ''; 
    // Mantém a cor e o tipo para facilitar cadastros em sequência
    fetchCategories(); 
    emit('refresh');   
  } catch (e) { notify('error', 'Erro ao criar'); }
};

// --- CORREÇÃO DO ALERTA ---
const deleteCategory = async (id) => {
  // Usa o SweetAlert padronizado do projeto
  const confirmed = await confirmAction(
      'Excluir Categoria?', 
      'Isso removerá a categoria, mas manterá os lançamentos antigos sem categoria.'
  );
  
  if (!confirmed) return;

  try {
    await http.delete(`/categories/${id}`);
    notify('success', 'Categoria excluída!');
    fetchCategories();
    emit('refresh');
  } catch (e) { notify('error', 'Erro ao excluir'); }
};

onMounted(() => fetchCategories());
</script>

<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Gerenciar Categorias</h3>
        <button @click="$emit('close')" class="close-btn">×</button>
      </div>

      <div class="modal-body">
        <div class="add-row">
          <input v-model="newCat.name" placeholder="Nome (ex: Salário)" class="input-tiny" />
          
          <select 
            v-model="newCat.type" 
            class="input-tiny"
            :class="newCat.type === 'income' ? 'income-select' : 'expense-select'"
          >
            <option value="expense">🔴 Saída</option>
            <option value="income">🟢 Entrada</option>
          </select>

          <input type="color" v-model="newCat.color" class="input-color" title="Escolher Cor" />
          
          <button @click="addCategory" class="btn-add">+</button>
        </div>

        <hr class="divider">

        <div class="cat-list">
          <div v-if="categories.length === 0" class="empty-msg">Nenhuma categoria criada.</div>
          
          <div v-for="cat in categories" :key="cat.id" class="cat-item">
            <div class="cat-info">
              <span class="color-dot" :style="{ background: cat.color }"></span>
              <span class="cat-name">{{ cat.name }}</span>
              <span class="type-tag" :class="cat.type">
                {{ cat.type === 'income' ? 'Entrada' : 'Saída' }}
              </span>
            </div>
            <button @click="deleteCategory(cat.id)" class="btn-del" title="Excluir">🗑️</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay { 
  position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
  background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; z-index: 2000; 
  backdrop-filter: blur(2px);
}
.modal-content { 
  background: var(--bg-secondary); width: 450px; max-width: 90%; 
  border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); overflow: hidden; 
  border: 1px solid var(--border-color); color: var(--text-primary);
}
.modal-header { 
  padding: 15px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; 
  background: var(--bg-primary); 
}
.modal-header h3 { margin: 0; font-size: 1.1rem; }
.close-btn { background: none; border: none; cursor: pointer; font-size: 1.5rem; color: var(--text-secondary); transition: 0.2s; }
.close-btn:hover { color: #ef4444; transform: rotate(90deg); }

.modal-body { padding: 20px; }

/* Formulário */
.add-row { display: flex; gap: 10px; margin-bottom: 15px; align-items: center; }
.input-tiny { 
  flex: 1; padding: 8px; border-radius: 6px; border: 1px solid var(--border-color); 
  background: var(--input-bg); color: var(--text-primary); outline: none; font-size: 0.9rem;
}
.input-color { 
  width: 35px; height: 35px; border: none; cursor: pointer; background: transparent; padding: 0;
}
.btn-add { 
  background: var(--accent-color); color: white; border: none; width: 40px; height: 35px; 
  border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 1.2rem; transition: 0.2s;
}
.btn-add:hover { filter: brightness(1.1); }

/* Cores do Select */
.income-select { border-color: #10b981; color: #10b981; font-weight: 600; }
.expense-select { border-color: #ef4444; color: #ef4444; font-weight: 600; }

.divider { border: 0; border-top: 1px solid var(--border-color); margin-bottom: 15px; }

/* Lista */
.cat-list { max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.cat-item { 
  display: flex; justify-content: space-between; align-items: center; 
  padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-primary);
}
.cat-info { display: flex; align-items: center; gap: 10px; color: var(--text-primary); }
.cat-name { font-weight: 500; }
.color-dot { width: 12px; height: 12px; border-radius: 50%; border: 1px solid rgba(0,0,0,0.1); }

.type-tag { font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; font-weight: bold; text-transform: uppercase; }
.type-tag.income { background: #dcfce7; color: #166534; }
.type-tag.expense { background: #fee2e2; color: #991b1b; }

.btn-del { background: none; border: none; cursor: pointer; opacity: 0.6; transition: 0.2s; font-size: 1.1rem; }
.btn-del:hover { opacity: 1; transform: scale(1.1); filter: hue-rotate(320deg); }
.empty-msg { text-align: center; color: var(--text-secondary); font-style: italic; font-size: 0.9rem; }
</style>