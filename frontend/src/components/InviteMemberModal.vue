<template>
  <div>
    <!-- Botão de Trigger (Gatilho) -->
    <button 
      @click="isOpen = true" 
      class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-md shadow-md transition-colors"
    >
      + Convidar Membro
    </button>

    <!-- O Modal em si (Headless UI) -->
    <Dialog :open="isOpen" @close="fecharModal" class="relative z-50">
      
      <!-- Overlay (O fundo escuro desfocado) -->
      <div class="fixed inset-0 bg-black/75 backdrop-blur-sm" aria-hidden="true" />

      <!-- Container de alinhamento -->
      <div class="fixed inset-0 flex w-screen items-center justify-center p-4">
        
        <!-- O Painel do Modal (A caixinha) -->
        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-xl bg-gray-800 border border-gray-700 p-6 text-left align-middle shadow-2xl transition-all">
          
          <DialogTitle as="h3" class="text-lg font-bold leading-6 text-gray-100 flex items-center gap-2">
            <!-- Ícone simples SVG de usuário -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
              <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
            </svg>
            Convidar para o Workspace
          </DialogTitle>
          
          <p class="mt-2 text-sm text-gray-400">
            Adicione um novo operador ao projeto. Ele precisará usar este e-mail para acessar.
          </p>

          <!-- Formulário -->
          <form @submit.prevent="enviarConvite" class="mt-4 space-y-4">
            
            <!-- Input de E-mail -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-300 mb-1">E-mail do Operador</label>
              <input 
                id="email" 
                v-model="email" 
                type="email" 
                required
                class="w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors"
                placeholder="amigo@exemplo.com"
              />
            </div>

            <!-- Select de Nível de Acesso -->
            <div>
              <label for="role" class="block text-sm font-medium text-gray-300 mb-1">Nível de Acesso</label>
              <select 
                id="role" 
                v-model="role"
                class="w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              >
                <option value="viewer">Leitor (Apenas visualiza)</option>
                <option value="editor">Editor (Pode mover tarefas/gastos)</option>
              </select>
            </div>

            <!-- Botões de Ação -->
            <div class="mt-6 flex justify-end gap-3">
              <button 
                type="button" 
                @click="fecharModal"
                class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition-colors"
              >
                Cancelar
              </button>
              
              <button 
                type="submit" 
                :disabled="isLoading"
                class="inline-flex justify-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isLoading">Enviando...</span>
                <span v-else>Enviar Convite</span>
              </button>
            </div>
          </form>

        </DialogPanel>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import http from '@/services/http'; // Usa o SEU axios configurado com o Token!

// Estado do Componente
const isOpen = ref(false);
const isLoading = ref(false);
const email = ref('');
const role = ref('viewer');

// Prop que recebe o ID do projeto atual
const props = defineProps({
  workspaceId: {
    type: Number,
    required: true
  }
});

const fecharModal = () => {
  isOpen.value = false;
  email.value = '';
  role.value = 'viewer';
};

const enviarConvite = async () => {
  isLoading.value = true;
  
  try {
    const response = await http.post(`/workspaces/${props.workspaceId}/members`, {
      email: email.value,
      role: role.value
    });
    
    // Aqui você pode colocar uma biblioteca de Toast no futuro (ex: Vue Toastification)
    alert('Operador adicionado com sucesso!');
    fecharModal();
    
  } catch (error) {
    if (error.response?.status === 422) {
      alert('E-mail não encontrado no sistema ou formato inválido.');
    } else {
      alert('Erro ao enviar o convite. Tente novamente.');
    }
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};
</script>