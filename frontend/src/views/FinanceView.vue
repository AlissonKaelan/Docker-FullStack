<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { notify, confirmAction } from '@/utils/alert';

const transactions = ref([]);

// Variável separada para mostrar o valor formatado no Input "R$ 0,00"
const amountDisplay = ref('');

const form = ref({
  description: '',
  amount: '', // Aqui guardamos o número puro (ex: 5000.00) para enviar pro banco
  type: 'expense',
  transaction_date: new Date().toISOString().split('T')[0]
});

const balance = ref({
  income: 0,
  expense: 0,
  balance: 0
});

const editingId = ref(null); // Se for null, é criação. Se tiver um ID, é edição.

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

const fetchBalance = async () => {
  try {
    // Chama a rota nova que criamos no Laravel (Backend)
    // Lembre-se: O Backend faz a matemática pesada (SUM) e retorna só o resultado.
    const response = await axios.get('http://localhost:8000/api/balance');
    
    // Atualiza a memória do Vue com os dados frescos
    balance.value = response.data;
    
  } catch (error) {
    console.error("Erro ao atualizar saldo:", error);
  }
};

// 1. PREPARAR EDIÇÃO (Ao clicar no lápis)
const editTransaction = (transaction) => {
  // Passamos o ID para a variável de controle
  editingId.value = transaction.id;
  
  // Preenchemos o formulário com os dados da transação clicada
  form.value.description = transaction.description;
  form.value.amount = parseFloat(transaction.amount); // Garante que seja número
  form.value.type = transaction.type;
  
  // Tratamento da Data: O input type="date" precisa do formato YYYY-MM-DD
  // O banco manda YYYY-MM-DDTHH:mm... então pegamos só a primeira parte
  form.value.transaction_date = transaction.transaction_date.split('T')[0];
  
  // Atualiza a máscara visual do dinheiro
  amountDisplay.value = new Intl.NumberFormat('pt-BR', {
    style: 'currency', currency: 'BRL'
  }).format(form.value.amount);
  
  // UX: Rola a página para o topo (para o usuário ver o formulário)
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const deleteTransaction = async (id) => {
  const confirmed = await confirmAction(
      'Tem certeza?', 
      'Você não poderá reverter esta exclusão!'
  );
  // 1. Confirmação (UX)
  if (!confirmed) return;

  try {
    // 2. Chamada API
    await axios.delete(`http://localhost:8000/api/transactions/${id}`);
    notify('success', 'Item excluído com sucesso!'); // Feedback visual
    // 3. Atualização
    await fetchTransactions(); // Remove da lista
    await fetchBalance();      // Recalcula o saldo (Importante!)
    
    // Se estivesse editando esse item, cancela a edição para não dar erro
    if (editingId.value === id) {
      cancelEdit();
    }

  } catch (error) {
    notify('error', 'Erro ao excluir item.');
  }
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

// 2. CANCELAR EDIÇÃO
const cancelEdit = () => {
  editingId.value = null;
  form.value.description = '';
  form.value.amount = '';
  amountDisplay.value = '';
  form.value.type = 'expense'; // Volta ao padrão
};

// 3. MUDANÇA NO SALVAR (Agora ele decide: Cria ou Atualiza?)
const saveTransaction = async () => {
  if(!form.value.description || !form.value.amount) {
    alert("Preencha todos os campos!");
    return;
  }

  try {
    if (editingId.value) {
      // --- MODO EDIÇÃO (PUT) ---
      // Passamos o ID na URL
      await axios.put(`http://localhost:8000/api/transactions/${editingId.value}`, form.value);
      notify('success', 'Transação atualizada com sucesso!');
    } else {
      // --- MODO CRIAÇÃO (POST) ---
      await axios.post('http://localhost:8000/api/transactions', form.value);
      notify('success', 'Transação criada com sucesso!');
    }
    
    // Limpeza (Reset)
    cancelEdit(); 
    
    // Atualiza tudo
    await fetchTransactions();
    await fetchBalance();
    
  } catch (error) {
    notify('error', 'Erro ao salvar: ' + (error.response?.data?.message || error.message));
  }
};

onMounted(() => {
  fetchTransactions();
  fetchBalance();
});
</script>

<template>
  <div class="finance-container">
    <router-link to="/home" class="back-link">⬅ Voltar ao Início</router-link>
    <h1>💰 Minhas Finanças</h1>

    <div class="dashboard">
      <div class="card income-card">
        <h3>Entradas</h3>
        <p>{{ formatMoney(balance.income) }}</p>
      </div>
      <div class="card expense-card">
        <h3>Saídas</h3>
        <p>{{ formatMoney(balance.expense) }}</p>
      </div>
      <div class="card total-card" :class="balance.balance >= 0 ? 'positive' : 'negative'">
        <h3>Saldo Atual</h3>
        <p>{{ formatMoney(balance.balance) }}</p>
      </div>
    </div>

    <div class="form-card">
      <h3>{{ editingId ? 'Editar Transação' : 'Nova Transação' }}</h3>
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

        <div class="actions">
            <button v-if="editingId" @click="cancelEdit" class="btn-cancel">Cancelar</button>
            <button @click="saveTransaction" class="btn-save">
                {{ editingId ? 'Atualizar' : 'Salvar' }}
            </button>
        </div>
      </div>
    </div>

    <hr>

    <p v-if="transactions.length === 0">Nenhuma transação encontrada.</p>
    
    <ul v-else class="transaction-list">
      <li v-for="item in transactions" :key="item.id" class="transaction-item">
        
        <div class="left-side" style="display: flex; align-items: center; gap: 15px;">
            <span class="date">{{ formatDate(item.transaction_date) }}</span>
            <div class="details">
              <strong>{{ item.description }}</strong>
            </div>
        </div>

        <div class="right-side">
            <span class="value" :class="item.type">
              {{ formatMoney(item.amount) }}
            </span>
            <button @click="editTransaction(item)" class="btn-icon" title="Editar">✏️</button>
            <button @click="deleteTransaction(item.id)" class="btn-icon delete-btn" title="Excluir">🗑️</button>
        </div>

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
.dashboard {
  display: flex;
  gap: 20px; /* Espaço entre os cards */
  margin-bottom: 30px;
  flex-wrap: wrap; /* Se a tela for pequena (celular), quebra linha */
}

/* Estilo Base do Card */
.card {
  flex: 1; /* Faz todos terem a mesma largura */
  padding: 20px;
  border-radius: 12px; /* Bordas arredondadas */
  background: white;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Sombra suave */
  min-width: 200px;
}

.card h3 {
  margin: 0 0 10px 0;
  font-size: 0.9rem;
  color: #666; /* Cinza para o título */
  text-transform: uppercase;
  letter-spacing: 1px;
}

.card p {
  margin: 0;
  font-size: 1.8rem; /* Fonte grande para o dinheiro */
  font-weight: bold;
}

/* Cores Específicas */
.income-card p { color: #10b981; } /* Verde Esmeralda */
.expense-card p { color: #ef4444; } /* Vermelho Perigo */

/* Saldo: Fundo colorido para destaque */
.total-card {
  color: white; /* Texto branco */
}
.total-card.positive { background: #3b82f6; } /* Azul se estiver positivo */
.total-card.negative { background: #ef4444; } /* Vermelho se estiver devendo */
.total-card.positive p, .total-card.negative p { color: white; } /* Garante texto branco */
.total-card h3 { color: rgba(255,255,255, 0.8); } /* Título levemente transparente */
/* Container dos botões do form */
.actions { display: flex; gap: 10px; }

/* Botão Cancelar */
.btn-cancel { background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; }

/* Botão Ícone (Lápis) */
.right-side { display: flex; align-items: center; gap: 15px; }
.btn-icon { background: none; border: none; cursor: pointer; font-size: 1.2rem; transition: transform 0.2s; }
.btn-icon:hover { transform: scale(1.2); }
.btn-icon { 
  background: none; 
  border: none; 
  cursor: pointer; 
  font-size: 1.2rem; 
  transition: transform 0.2s; 
  margin-left: 5px; /* Espacinho entre os botões */
}

.btn-icon:hover { transform: scale(1.2); }

/* Cor específica para o delete no hover */
.delete-btn:hover {
  filter: hue-rotate(140deg); /* Truque CSS para mudar cor do emoji ou use color: red se fosse ícone de fonte */
  cursor: pointer;
}
.back-link {
  display: inline-block;
  margin-bottom: 10px;
  color: #666;
  text-decoration: none;
  font-size: 0.9rem;
}
.back-link:hover { color: #3b82f6; text-decoration: underline; }
</style>