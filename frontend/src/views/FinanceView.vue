<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const transactions = ref([]);

// Variável separada para mostrar o valor formatado no Input "R$ 0,00"
const amountDisplay = ref('');

const form = ref({
  description: '',
  amount: '', // Aqui guardamos o número puro (ex: 5000.00) para enviar pro banco
  type: 'expense',
  transaction_date: new Date().toISOString().split('T')[0]
});

// --- 1. MÁSCARA DE MOEDA (Input) ---
const formatCurrencyInput = (event) => {
  let value = event.target.value;
  
  // 1. Remove tudo que não é número (Limpeza)
  value = value.replace(/\D/g, "");
  
  // 2. Divide por 100 para considerar os centavos
  const floatValue = (parseFloat(value) / 100);

  // 3. Guarda o valor numérico puro para enviar pro Backend
  form.value.amount = floatValue;

  // 4. Formata visualmente para o usuário (R$ 5.000,00)
  amountDisplay.value = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(floatValue);
};

// --- 2. FORMATADORES DE EXIBIÇÃO (Lista) ---

// Formata data: 2026-01-06 -> 06/01/2026
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  // timeZone: 'UTC' é importante para não mostrar o dia anterior por causa do fuso!
  return date.toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

// Formata dinheiro: 5000.00 -> R$ 5.000,00
const formatMoney = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
};

// --- API ---
const fetchTransactions = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/transactions');
    transactions.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar:", error);
  }
};

const saveTransaction = async () => {
  if(!form.value.description || !form.value.amount) {
    alert("Preencha todos os campos!");
    return;
  }

  try {
    // Enviamos o form.value.amount (que é número puro)
    await axios.post('http://localhost:8000/api/transactions', form.value);
    
    // Limpar formulário
    form.value.description = '';
    form.value.amount = '';
    amountDisplay.value = ''; // Limpa o visual também
    
    await fetchTransactions();
  } catch (error) {
    alert("Erro ao salvar: " + (error.response?.data?.message || error.message));
  }
};

onMounted(() => {
  fetchTransactions();
});
</script>

<template>
  <div class="finance-container">
    <h1>💰 Minhas Finanças</h1>

    <div class="form-card">
      <h3>Nova Transação</h3>
      <div class="inputs-row">
        
        <input 
          v-model="form.description" 
          placeholder="Descrição (Ex: Salário)" 
          class="input-field"
        />

        <input 
          type="text"
          :value="amountDisplay"
          @input="formatCurrencyInput"
          placeholder="R$ 0,00" 
          class="input-field"
        />

        <select v-model="form.type" class="input-field">
          <option value="income">🟢 Entrada</option>
          <option value="expense">🔴 Saída</option>
        </select>

        <input 
          v-model="form.transaction_date" 
          type="date" 
          class="input-field"
        />

        <button @click="saveTransaction" class="btn-save">Salvar</button>
      </div>
    </div>

    <hr>

    <p v-if="transactions.length === 0">Nenhuma transação encontrada.</p>
    
    <ul v-else class="transaction-list">
      <li v-for="item in transactions" :key="item.id" class="transaction-item">
        
        <span class="date">{{ formatDate(item.transaction_date) }}</span>
        
        <div class="details">
          <strong>{{ item.description }}</strong>
        </div>

        <span class="value" :class="item.type">
          {{ formatMoney(item.amount) }}
        </span>
      </li>
    </ul>

  </div>
</template>

<style scoped>
/* (Mantenha o CSS anterior e adicione/atualize estes) */

.transaction-list { list-style: none; padding: 0; }
.transaction-item { 
  display: flex; 
  align-items: center; 
  justify-content: space-between; 
  padding: 15px; 
  border-bottom: 1px solid #eee; 
  background: white; 
  margin-bottom: 5px;
  border-radius: 4px;
}

.date { color: #888; font-size: 0.9rem; min-width: 100px; }
.details { flex: 1; text-align: left; padding-left: 10px; }

.value { font-weight: bold; font-size: 1.1rem; }
.value.income { color: #10b981; } /* Verde */
.value.expense { color: #ef4444; } /* Vermelho */

/* Estilos de Form (os mesmos de antes) */
.finance-container { padding: 20px; max-width: 800px; margin: 0 auto; }
.form-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
.inputs-row { display: flex; gap: 10px; flex-wrap: wrap; }
.input-field { padding: 12px; border: 1px solid #ddd; border-radius: 6px; flex: 1; min-width: 150px; }
.btn-save { background: #3b82f6; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; }
.btn-save:hover { background: #2563eb; }
</style>