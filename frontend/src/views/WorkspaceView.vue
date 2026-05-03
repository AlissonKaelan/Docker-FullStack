<template>
  <div class="min-h-screen bg-gray-900 text-gray-100 font-sans">
    
    <!-- Header Principal -->
    <header class="border-b border-gray-800 bg-gray-900 px-6 py-4 flex items-center justify-between shadow-sm">
      <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Arena AK <span class="text-emerald-500">| Backend</span></h1>
        <p class="text-sm text-gray-400 mt-1">Gerenciamento de Tarefas e Gastos Compartilhados</p>
      </div>
      
      <!-- AQUI ENTRA O SEU MODAL! -->
      <!-- Passamos o ID 1 fixo por enquanto apenas para testar -->
      <InviteMemberModal :workspaceId="1" />
    </header>

    <!-- Área de Trabalho (Onde o Kanban vai ficar) -->
    <main class="p-6">
      <div class="flex gap-6 overflow-x-auto pb-4">
        
        <!-- Esqueleto das Colunas do Kanban -->
        <div class="w-80 flex-shrink-0 bg-gray-800 rounded-lg p-4 border border-gray-700">
          <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4 border-b border-gray-700 pb-2">A Fazer</h3>
          <div class="bg-gray-700/50 p-3 rounded text-sm text-gray-300 border border-gray-600 border-dashed text-center">
            Nenhuma tarefa ainda
          </div>
        </div>

        <div class="w-80 flex-shrink-0 bg-gray-800 rounded-lg p-4 border border-gray-700">
          <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4 border-b border-gray-700 pb-2">Em Progresso</h3>
          <!-- Tarefas entrarão aqui -->
        </div>

      </div>
    </main>

  </div>
</template>

<script setup>
import InviteMemberModal from '../components/InviteMemberModal.vue';

// Isso captura o ID da URL (ex: /workspace/1)
const props = defineProps({
  id: {
    type: String,
    required: true
  }
});
</script>