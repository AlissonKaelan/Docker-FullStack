<script setup>
import { computed } from 'vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';

// Registra os elementos necessários do Chart.js
ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
  income: { type: Number, required: true },
  expense: { type: Number, required: true }
});

// Configuração dos Dados
const chartData = computed(() => ({
  labels: ['Entradas', 'Saídas'],
  datasets: [
    {
      backgroundColor: ['#10b981', '#ef4444'], // Verde e Vermelho
      borderColor: ['#059669', '#b91c1c'],
      borderWidth: 1,
      data: [props.income, props.expense]
    }
  ]
}));

// Configuração Visual (Opções)
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: '#9ca3af', // Cor do texto da legenda (cinza para ficar bom no dark/light)
        font: { family: "'Segoe UI', sans-serif", size: 12 }
      }
    }
  }
};
</script>

<template>
  <div class="chart-container">
    <div v-if="income === 0 && expense === 0" class="no-data">
        <span>😴 Sem dados</span>
    </div>
    <Doughnut v-else :data="chartData" :options="chartOptions" />
  </div>
</template>

<style scoped>
.chart-container {
  position: relative;
  height: 250px; /* Altura fixa para não quebrar o layout */
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.no-data {
    color: var(--text-secondary);
    font-size: 0.9rem;
    border: 2px dashed var(--border-color);
    border-radius: 50%;
    width: 150px;
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>