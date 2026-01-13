<script setup>
/* ... MANTENHA TODO O SEU SCRIPT (LÓGICA) ORIGINAL AQUI ... */
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { notify, confirmAction } from '@/utils/alert';

const transactions = ref([]);
const amountDisplay = ref('');
const form = ref({ description: '', amount: '', type: 'expense', transaction_date: new Date().toISOString().split('T')[0] });
const balance = ref({ income: 0, expense: 0, balance: 0 });
const editingId = ref(null);

const formatCurrencyInput = (event) => {
  let value = event.target.value.replace(/\D/g, "");
  const floatValue = (parseFloat(value) / 100);
  form.value.amount = floatValue;
  amountDisplay.value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(floatValue);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

const formatMoney = (value) => {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const fetchBalance = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/balance');
    balance.value = response.data;
  } catch (error) { console.error(error); }
};

const fetchTransactions = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/transactions');
    transactions.value = response.data;
  } catch (error) { console.error(error); }
};

const editTransaction = (transaction) => {
  editingId.value = transaction.id;
  form.value.description = transaction.description;
  form.value.amount = parseFloat(transaction.amount);
  form.value.type = transaction.type;
  form.value.transaction_date = transaction.transaction_date.split('T')[0];
  amountDisplay.value = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(form.value.amount);
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const deleteTransaction = async (id) => {
  if (!await confirmAction('Tem certeza?', 'Essa ação não pode ser desfeita.')) return;
  try {
    await axios.delete(`http://localhost:8000/api/transactions/${id}`);
    notify('success', 'Excluído!');
    await fetchTransactions();
    await fetchBalance();
    if (editingId.value === id) cancelEdit();
  } catch (error) { notify('error', 'Erro ao excluir.'); }
};

const cancelEdit = () => {
  editingId.value = null;
  form.value.description = '';
  form.value.amount = '';
  amountDisplay.value = '';
  form.value.type = 'expense';
};

const saveTransaction = async () => {
  if(!form.value.description || !form.value.amount) { alert("Preencha os campos!"); return; }
  try {
    if (editingId.value) {
      await axios.put(`http://localhost:8000/api/transactions/${editingId.value}`, form.value);
      notify('success', 'Atualizado!');
    } else {
      await axios.post('http://localhost:8000/api/transactions', form.value);
      notify('success', 'Criado!');
    }
    cancelEdit();
    await fetchTransactions();
    await fetchBalance();
  } catch (error) { notify('error', 'Erro ao salvar.'); }
};

onMounted(() => { fetchTransactions(); fetchBalance(); });
</script>

<template>
  <div class="finance-wrapper">
    <div class="page-header">
        <router-link to="/" class="back-btn">⬅ Voltar</router-link>
        <h1>Gestão Financeira</h1>
    </div>

    <div class="dashboard-grid">
      <div class="stat-card income">
        <div class="icon-circle">⬇</div>
        <div>
            <h3>Entradas</h3>
            <p>{{ formatMoney(balance.income) }}</p>
        </div>
      </div>
      <div class="stat-card expense">
        <div class="icon-circle">⬆</div>
        <div>
            <h3>Saídas</h3>
            <p>{{ formatMoney(balance.expense) }}</p>
        </div>
      </div>
      <div class="stat-card total" :class="balance.balance >= 0 ? 'pos' : 'neg'">
        <div class="icon-circle">💰</div>
        <div>
            <h3>Saldo Total</h3>
            <p>{{ formatMoney(balance.balance) }}</p>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-header">
        <h3>{{ editingId ? 'Editar Lançamento' : 'Novo Lançamento' }}</h3>
      </div>
      <div class="form-body">
        <div class="input-grid">
           <input v-model="form.description" placeholder="Descrição (Ex: Freelance)" class="input-modern" />
           <input type="text" :value="amountDisplay" @input="formatCurrencyInput" placeholder="R$ 0,00" class="input-modern" />
           <select v-model="form.type" class="input-modern">
             <option value="income">Entrada</option>
             <option value="expense">Saída</option>
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
        <div v-if="transactions.length === 0" class="empty-state">Nenhum lançamento ainda.</div>
        
        <div v-for="item in transactions" :key="item.id" class="transaction-row">
            <div class="t-date">
                <span class="day">{{ formatDate(item.transaction_date).split('/')[0] }}</span>
                <span class="month">{{ formatDate(item.transaction_date).split('/')[1] }}</span>
            </div>
            <div class="t-desc">
                <strong>{{ item.description }}</strong>
                <small>{{ item.type === 'income' ? 'Entrada' : 'Saída' }}</small>
            </div>
            <div class="t-amount" :class="item.type">
                {{ item.type === 'income' ? '+' : '-' }} {{ formatMoney(item.amount) }}
            </div>
            <div class="t-actions">
                <button @click="editTransaction(item)" class="action-btn edit">✏️</button>
                <button @click="deleteTransaction(item.id)" class="action-btn delete">🗑️</button>
            </div>
        </div>
    </div>
  </div>
</template>

<style scoped>
.finance-wrapper { max-width: 900px; margin: 0 auto; padding: 40px 20px; font-family: 'Segoe UI', sans-serif; color: #1f2937; }

.page-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
.page-header h1 { margin: 0; font-size: 1.8rem; }
.back-btn { text-decoration: none; color: #6b7280; font-weight: 600; padding: 5px 10px; background: #e5e7eb; border-radius: 6px; font-size: 0.9rem; transition: 0.2s; }
.back-btn:hover { background: #d1d5db; color: #1f2937; }

/* DASHBOARD */
.dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
.stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; border: 1px solid #f3f4f6; }
.stat-card h3 { margin: 0; font-size: 0.9rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-card p { margin: 5px 0 0 0; font-size: 1.5rem; font-weight: 700; }

.icon-circle { width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

.income .icon-circle { background: #dcfce7; color: #166534; }
.income p { color: #166534; }

.expense .icon-circle { background: #fee2e2; color: #991b1b; }
.expense p { color: #991b1b; }

.total.pos .icon-circle { background: #dbeafe; color: #1e40af; }
.total.pos p { color: #1e40af; }
.total.neg .icon-circle { background: #fef9c3; color: #854d0e; }
.total.neg p { color: #854d0e; }

/* FORM */
.form-section { background: white; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom: 40px; overflow: hidden; }
.form-header { background: #f9fafb; padding: 15px 20px; border-bottom: 1px solid #e5e7eb; }
.form-header h3 { margin: 0; font-size: 1rem; color: #374151; }
.form-body { padding: 20px; }
.input-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px; }
@media (max-width: 768px) { .input-grid { grid-template-columns: 1fr; } }

.input-modern { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; }
.input-modern:focus { border-color: #4F46E5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }
.form-actions { display: flex; justify-content: flex-end; gap: 10px; }
.btn-save { background: #4F46E5; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
.btn-save:hover { background: #4338ca; }
.btn-cancel { background: #9ca3af; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; }

/* LISTA */
.transaction-row { display: flex; align-items: center; background: white; padding: 15px; border-radius: 10px; margin-bottom: 10px; border: 1px solid #f3f4f6; transition: transform 0.1s; }
.transaction-row:hover { transform: scale(1.01); border-color: #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }

.t-date { display: flex; flex-direction: column; align-items: center; background: #f3f4f6; padding: 5px 10px; border-radius: 6px; margin-right: 15px; min-width: 40px; }
.t-date .day { font-weight: bold; font-size: 1.1rem; color: #374151; }
.t-date .month { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; }

.t-desc { flex: 1; display: flex; flex-direction: column; }
.t-desc small { color: #9ca3af; }

.t-amount { font-weight: 700; font-size: 1.1rem; margin-right: 20px; }
.t-amount.income { color: #10b981; }
.t-amount.expense { color: #ef4444; }

.t-actions { display: flex; gap: 5px; }
.action-btn { background: none; border: none; cursor: pointer; font-size: 1.1rem; padding: 5px; border-radius: 4px; transition: background 0.2s; }
.action-btn:hover { background: #f3f4f6; }
.empty-state { text-align: center; color: #9ca3af; padding: 40px; font-style: italic; }
</style>