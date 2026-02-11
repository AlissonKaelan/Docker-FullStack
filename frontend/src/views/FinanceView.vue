<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import http from '../services/http'; 
import { notify, confirmAction } from '@/utils/alert';
import Swal from 'sweetalert2';
import FinanceChart from '../components/FinanceChart.vue'; 
import CategoryManager from '../components/CategoryManager.vue'; 

const transactions = ref([]);
const categories = ref([]); 
const showCategoryModal = ref(false);

const amountDisplay = ref('');
const balance = ref({ income: 0, expense: 0, balance: 0 });
const editingId = ref(null);

// --- DATA ---
const currentDate = ref(new Date());
const currentMonth = computed(() => currentDate.value.getMonth() + 1);
const currentYear = computed(() => currentDate.value.getFullYear());
const formattedCurrentDate = computed(() => {
  return currentDate.value.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
});

const changeMonth = (offset) => {
    const newDate = new Date(currentDate.value);
    newDate.setDate(1);
    newDate.setMonth(newDate.getMonth() + offset);
    currentDate.value = newDate;
};

// Observa mudança de data para recarregar dados
watch(currentDate, () => fetchData());

// --- FORM ---
const form = ref({ 
    description: '', 
    amount: '', 
    type: 'expense', // Padrão
    transaction_date: new Date().toISOString().split('T')[0],
    installments: 1,
    category_id: '' 
});

// Computed para filtrar categorias com segurança
const filteredCategories = computed(() => {
    if (!categories.value || !Array.isArray(categories.value)) return [];
    return categories.value.filter(c => c.type === form.value.type);
});

// Quando muda o TIPO (Entrada/Saída), limpa a categoria selecionada para evitar erro
watch(() => form.value.type, () => {
    form.value.category_id = '';
});

const formatCurrencyInput = (event) => {
  let value = event.target.value.replace(/\D/g, "");
  const floatValue = (parseFloat(value) / 100);
  form.value.amount = isNaN(floatValue) ? 0 : floatValue;
  amountDisplay.value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(form.value.amount);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const [year, month, day] = dateString.split('T')[0].split('-');
  return `${day}/${month}`;
};

const formatMoney = (value) => {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

// --- API ---
const fetchCategories = async () => {
  try {
    const { data } = await http.get('/categories');
    categories.value = data || []; // Garante que seja array
  } catch (e) { console.error(e); }
};

const fetchData = async () => {
  try {
    const params = { month: currentMonth.value, year: currentYear.value };
    const tResponse = await http.get('/transactions', { params });
    transactions.value = tResponse.data;
    const bResponse = await http.get('/balance', { params });
    balance.value = bResponse.data;
  } catch (error) { console.error(error); }
};

const editTransaction = (transaction) => {
  editingId.value = transaction.id;
  form.value.description = transaction.description;
  form.value.amount = parseFloat(transaction.amount);
  form.value.type = transaction.type;
  form.value.transaction_date = transaction.transaction_date.split('T')[0];
  form.value.installments = 1; 
  form.value.category_id = transaction.category_id || ''; 
  amountDisplay.value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(form.value.amount);
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const deleteTransaction = async (item) => {
  if (!item.batch_id) {
      if (await confirmAction('Excluir transação?')) {
           try {
               await http.delete(`/transactions/${item.id}`);
               notify('success', 'Removido!');
               fetchData();
           } catch (e) { notify('error', 'Erro ao excluir'); }
      }
      return;
  }
  const result = await Swal.fire({
      title: 'Item Parcelado', text: "Como deseja excluir?", icon: 'question',
      showDenyButton: true, showCancelButton: true,
      confirmButtonText: 'Excluir TODAS', denyButtonText: 'Apenas esta', cancelButtonText: 'Cancelar',
      confirmButtonColor: '#ef4444', denyButtonColor: '#f59e0b'
  });
  if (result.isDismissed) return;
  try {
      const params = { delete_all: result.isConfirmed };
      await http.delete(`/transactions/${item.id}`, { params });
      notify('success', result.isConfirmed ? 'Série removida!' : 'Parcela removida!');
      fetchData();
      if (editingId.value === item.id) cancelEdit();
  } catch (error) { notify('error', 'Erro ao excluir.'); }
};

const cancelEdit = () => {
  editingId.value = null;
  form.value.description = ''; form.value.amount = ''; amountDisplay.value = '';
  form.value.type = 'expense'; form.value.installments = 1; form.value.category_id = '';
  form.value.transaction_date = new Date().toISOString().split('T')[0];
};

const saveTransaction = async () => {
  if(!form.value.description || !form.value.amount) { 
      notify('warning', 'Preencha os campos!'); return; 
  }
  try {
    if (editingId.value) {
        const originalItem = transactions.value.find(t => t.id === editingId.value);
        let updateAll = false;
        if (originalItem && originalItem.batch_id) {
            const result = await Swal.fire({
                title: 'Editar Parcelamento', text: "Aplicar para todas?", icon: 'question',
                showDenyButton: true, showCancelButton: true, confirmButtonText: 'Sim, todas', denyButtonText: 'Não, só esta', cancelButtonText: 'Cancelar'
            });
            if (result.isDismissed) return;
            updateAll = result.isConfirmed;
        }
        await http.put(`/transactions/${editingId.value}`, { ...form.value, update_all: updateAll });
        notify('success', 'Atualizado!');
    } else {
        await http.post('/transactions', form.value);
        notify('success', 'Criado!'); 
    }
    cancelEdit();
    fetchData(); 
  } catch (error) { notify('error', 'Erro ao salvar.'); }
};

const getCategoryColor = (catId) => {
    const cat = categories.value.find(c => c.id === catId);
    return cat ? cat.color : 'transparent';
};

const getCategoryName = (catId) => {
    const cat = categories.value.find(c => c.id === catId);
    return cat ? cat.name : '';
};

onMounted(() => { 
    fetchData(); 
    fetchCategories(); 
});
</script>

<template>
  <div class="finance-wrapper">
    <CategoryManager 
        :isOpen="showCategoryModal" 
        @close="showCategoryModal = false" 
        @refresh="fetchCategories" 
    />

    <div class="page-header">
        <div class="header-left">
            <router-link to="/" class="back-btn">⬅ Voltar</router-link>
            <h1>Gestão Financeira</h1>
        </div>
        <div class="month-selector">
            <button @click="changeMonth(-1)" class="nav-btn">◀</button>
            <span class="current-month">{{ formattedCurrentDate }}</span>
            <button @click="changeMonth(1)" class="nav-btn">▶</button>
        </div>
    </div>

    <div class="dashboard-area">
        <div class="cards-column">
            <div class="stat-card income">
                <div class="icon-circle">⬇</div>
                <div><h3>Entradas</h3><p>{{ formatMoney(balance.income) }}</p></div>
            </div>
            <div class="stat-card expense">
                <div class="icon-circle">⬆</div>
                <div><h3>Saídas</h3><p>{{ formatMoney(balance.expense) }}</p></div>
            </div>
            <div class="stat-card total" :class="balance.balance >= 0 ? 'pos' : 'neg'">
                <div class="icon-circle">💰</div>
                <div><h3>Saldo Mês</h3><p>{{ formatMoney(balance.balance) }}</p></div>
            </div>
        </div>
        <div class="chart-column">
            <h3>Visão Geral</h3>
            <FinanceChart :income="balance.income" :expense="balance.expense" />
        </div>
    </div>

    <div class="form-section">
      <div class="form-header">
        <h3>{{ editingId ? 'Editar Lançamento' : 'Novo Lançamento' }}</h3>
      </div>
      <div class="form-body">
        <div class="input-grid">
           <input v-model="form.description" placeholder="Descrição (Ex: Mercado)" class="input-modern full-desc" />
           
           <input type="text" :value="amountDisplay" @input="formatCurrencyInput" placeholder="R$ 0,00" class="input-modern" />
           
           <select v-model="form.type" class="input-modern">
             <option value="income">Entrada</option>
             <option value="expense">Saída</option>
           </select>

           <div class="select-group">
               <select v-model="form.category_id" class="input-modern">
                   <option value="">Sem Categoria</option>
                   <option 
                        v-for="cat in filteredCategories" 
                        :key="cat.id" 
                        :value="cat.id"
                   >
                        {{ cat.name }}
                   </option>
               </select>
               <button @click="showCategoryModal = true" class="btn-config" title="Gerenciar Categorias">⚙️</button>
           </div>

           <select v-if="form.type === 'expense' && !editingId" v-model="form.installments" class="input-modern">
             <option :value="1">À Vista (1x)</option>
             <option v-for="i in 11" :key="i" :value="i+1">{{ i+1 }}x</option>
           </select>
           
           <input v-model="form.transaction_date" type="date" class="input-modern" />
        </div>
        
        <div class="form-actions">
           <button v-if="editingId" @click="cancelEdit" class="btn-cancel">Cancelar</button>
           <button @click="saveTransaction" class="btn-save">{{ editingId ? 'Atualizar' : 'Adicionar' }}</button>
        </div>
      </div>
    </div>

    <div class="transactions-list">
        <TransitionGroup name="list">
            <div v-for="item in transactions" :key="item.id" class="transaction-row">
                <div class="t-date">
                    <span class="day">{{ formatDate(item.transaction_date).split('/')[0] }}</span>
                    <span class="month">{{ formatDate(item.transaction_date).split('/')[1] }}</span>
                </div>
                
                <div class="t-desc">
                    <div class="desc-top">
                        <strong>{{ item.description }}</strong>
                        <span 
                            v-if="item.category_id" 
                            class="cat-badge" 
                            :style="{ backgroundColor: getCategoryColor(item.category_id) }"
                            :title="getCategoryName(item.category_id)"
                        ></span>
                    </div>
                    <small>
                        {{ item.type === 'income' ? 'Entrada' : 'Saída' }}
                        <span v-if="item.batch_id" title="Parcelado" style="margin-left:5px">💳</span>
                    </small>
                </div>

                <div class="t-amount" :class="item.type">
                    {{ item.type === 'income' ? '+' : '-' }} {{ formatMoney(item.amount) }}
                </div>
                <div class="t-actions">
                    <button @click="editTransaction(item)" class="action-btn edit">✏️</button>
                    <button @click="deleteTransaction(item)" class="action-btn delete">🗑️</button>
                </div>
            </div>
        </TransitionGroup>
    </div>
  </div>
</template>

<style scoped>
.finance-wrapper { 
    max-width: 900px; margin: 0 auto; padding: 40px 20px; 
    font-family: 'Segoe UI', sans-serif; color: var(--text-primary); 
    background-color: var(--bg-primary); min-height: 100vh; transition: background 0.3s;
}

/* LAYOUT DO FORMULÁRIO (Grid Melhorado) */
.form-section { background: var(--bg-secondary); border-radius: 12px; box-shadow: 0 10px 15px -3px var(--shadow-color); margin-bottom: 40px; overflow: hidden; border: 1px solid var(--border-color); }
.form-header { background: var(--bg-primary); padding: 15px 20px; border-bottom: 1px solid var(--border-color); }
.form-header h3 { margin: 0; font-size: 1rem; color: var(--text-primary); }
.form-body { padding: 20px; }

/* Grid de Inputs */
.input-grid { 
    display: grid; 
    grid-template-columns: 1fr 1fr; /* 2 colunas por padrão */
    gap: 15px; margin-bottom: 20px; 
}
.full-desc { grid-column: 1 / -1; } /* Descrição ocupa linha inteira */

.input-modern { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; transition: border 0.2s; box-sizing: border-box; background-color: var(--input-bg); color: var(--text-primary); }
.input-modern:focus { border-color: var(--accent-color); box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }

.select-group { display: flex; gap: 5px; width: 100%; align-items: center; }
.select-group select { flex: 1; } /* Select expande */
.btn-config { 
    background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary);
    border-radius: 8px; cursor: pointer; width: 40px; height: 38px; font-size: 1.2rem;
    display: flex; align-items: center; justify-content: center; transition: 0.2s;
}
.btn-config:hover { background: var(--border-color); transform: rotate(45deg); }

.form-actions { display: flex; justify-content: flex-end; gap: 10px; }
.btn-save { background: var(--accent-color); color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
.btn-save:hover { filter: brightness(1.1); }
.btn-cancel { background: var(--border-color); color: var(--text-secondary); padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; }
.btn-cancel:hover { background: #d1d5db; color: #374151; }

/* DASHBOARD */
.dashboard-area { display: flex; gap: 20px; margin-bottom: 40px; }
.cards-column { flex: 2; display: flex; flex-direction: column; gap: 15px; }
.chart-column { 
    flex: 1; background: var(--bg-secondary); border: 1px solid var(--border-color); 
    border-radius: 12px; padding: 20px; display: flex; flex-direction: column; align-items: center; 
    box-shadow: 0 4px 6px var(--shadow-color);
}
.stat-card { background: var(--bg-secondary); padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px var(--shadow-color); display: flex; align-items: center; gap: 15px; border: 1px solid var(--border-color); flex: 1; }
.stat-card h3 { margin: 0; font-size: 0.9rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }
.stat-card p { margin: 5px 0 0 0; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.icon-circle { width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.income .icon-circle { background: #dcfce7; color: #166534; } .income p { color: #10b981; }
.expense .icon-circle { background: #fee2e2; color: #991b1b; } .expense p { color: #ef4444; }
.total.pos .icon-circle { background: #dbeafe; color: #1e40af; } .total.pos p { color: #3b82f6; }
.total.neg .icon-circle { background: #fef9c3; color: #854d0e; } .total.neg p { color: #f59e0b; }

/* HEADER */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
.header-left { display: flex; align-items: center; gap: 20px; } .page-header h1 { margin: 0; font-size: 1.8rem; }
.month-selector { display: flex; align-items: center; gap: 15px; background: var(--bg-secondary); padding: 5px 10px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 2px 4px var(--shadow-color); }
.current-month { font-weight: bold; font-size: 1.1rem; text-transform: capitalize; min-width: 140px; text-align: center; }
.nav-btn { background: transparent; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-secondary); padding: 5px 10px; transition: 0.2s; }
.nav-btn:hover { color: var(--accent-color); transform: scale(1.2); }
.back-btn { text-decoration: none; color: var(--text-secondary); font-weight: 600; padding: 5px 10px; background: var(--bg-secondary); border-radius: 6px; font-size: 0.9rem; transition: 0.2s; border: 1px solid var(--border-color); }
.back-btn:hover { background: var(--border-color); color: var(--text-primary); }

/* LISTA */
.transaction-row { display: flex; align-items: center; background: var(--bg-secondary); padding: 15px; border-radius: 10px; margin-bottom: 10px; border: 1px solid var(--border-color); transition: transform 0.1s; }
.transaction-row:hover { transform: scale(1.01); border-color: var(--accent-color); box-shadow: 0 4px 6px var(--shadow-color); }
.t-date { display: flex; flex-direction: column; align-items: center; background: var(--bg-primary); padding: 5px 10px; border-radius: 6px; margin-right: 15px; min-width: 40px; }
.t-date .day { font-weight: bold; font-size: 1.1rem; color: var(--text-primary); }
.t-date .month { font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; }
.t-desc { flex: 1; display: flex; flex-direction: column; }
.desc-top { display: flex; align-items: center; gap: 8px; }
.cat-badge { width: 10px; height: 10px; border-radius: 50%; display: inline-block; box-shadow: 0 0 2px rgba(0,0,0,0.3); }
.t-desc strong { color: var(--text-primary); }
.t-desc small { color: var(--text-secondary); }
.t-amount { font-weight: 700; font-size: 1.1rem; margin-right: 20px; }
.t-amount.income { color: #10b981; } .t-amount.expense { color: #ef4444; }
.t-actions { display: flex; gap: 5px; }
.action-btn { background: none; border: none; cursor: pointer; font-size: 1.1rem; padding: 5px; border-radius: 4px; transition: background 0.2s; opacity: 0.6; color: var(--text-secondary); }
.action-btn:hover { background: var(--bg-primary); opacity: 1; transform: scale(1.1); color: var(--text-primary); }
.empty-state { text-align: center; color: var(--text-secondary); padding: 40px; font-style: italic; }
.list-enter-active, .list-leave-active { transition: all 0.4s ease; } .list-enter-from, .list-leave-to { opacity: 0; transform: translateX(30px); }

/* RESPONSIVIDADE */
@media (max-width: 768px) { 
    .dashboard-area { flex-direction: column-reverse; } 
    .chart-column { min-height: 300px; } 
    .input-grid { grid-template-columns: 1fr; } /* No mobile, 1 coluna */
}
</style>