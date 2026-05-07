<template>
  <div>
    <!-- Botão de Trigger -->
    <button 
      @click="abrirModal" 
      class="px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-600 text-gray-200 text-sm font-semibold rounded-md shadow-sm transition-colors flex items-center gap-2"
    >
      ⚙️ Gerenciar Projeto
    </button>

    <Dialog :open="isOpen" @close="fecharModal" class="relative z-50">
      <div class="fixed inset-0 bg-black/75 backdrop-blur-sm" aria-hidden="true" />

      <div class="fixed inset-0 flex w-screen items-center justify-center p-4">
        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-xl bg-gray-800 border border-gray-700 p-6 text-left align-middle shadow-2xl transition-all">
          
          <DialogTitle as="h3" class="text-lg font-bold leading-6 text-gray-100 flex justify-between items-center mb-4">
            <span>Gestão do Workspace</span>
            <button @click="fecharModal" class="text-gray-400 hover:text-red-500 text-xl">&times;</button>
          </DialogTitle>

          <!-- Abas (Tabs) -->
          <div class="flex border-b border-gray-700 mb-4">
            <button @click="activeTab = 'members'" :class="activeTab === 'members' ? 'border-emerald-500 text-emerald-500' : 'border-transparent text-gray-400'" class="flex-1 py-2 border-b-2 font-medium text-sm transition-colors">
              👥 Membros
            </button>
            <button @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'border-emerald-500 text-emerald-500' : 'border-transparent text-gray-400'" class="flex-1 py-2 border-b-2 font-medium text-sm transition-colors">
              ✏️ Detalhes
            </button>
          </div>

          <!-- ABA 1: MEMBROS -->
          <div v-if="activeTab === 'members'" class="space-y-4">
            
            <!-- Formulário de Convite (Só aparece para Admin) -->
            <form v-if="isAdmin" @submit.prevent="enviarConvite" class="flex gap-2 mb-4 bg-gray-900 p-3 rounded-lg border border-gray-700">
              <input v-model="newMember.email" type="email" required placeholder="E-mail do usuário" class="flex-1 rounded-md border border-gray-600 bg-gray-800 px-3 py-1.5 text-sm text-gray-100 focus:border-emerald-500 focus:outline-none" />
              <select v-model="newMember.role" class="rounded-md border border-gray-600 bg-gray-800 px-2 py-1.5 text-sm text-gray-100 focus:border-emerald-500 focus:outline-none">
                <option value="viewer">Leitor</option>
                <option value="editor">Editor</option>
                <option value="admin">Admin</option>
              </select>
              <button type="submit" :disabled="isLoading" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-md text-sm font-bold transition-colors">+</button>
            </form>

            <div v-else class="text-sm text-yellow-500 mb-4 bg-yellow-500/10 p-2 rounded border border-yellow-500/20">
              ⚠️ Apenas administradores podem convidar novas pessoas.
            </div>

            <!-- Lista de Membros -->
            <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
              <div v-for="member in members" :key="member.id" class="flex justify-between items-center p-3 bg-gray-700/30 rounded-lg border border-gray-700">
                <div class="flex flex-col">
                  <span class="text-gray-200 text-sm font-semibold">{{ member.name }} <span v-if="member.id === currentUser?.id" class="text-xs text-emerald-500 font-normal">(Você)</span></span>
                  <span class="text-gray-400 text-xs">{{ member.email }}</span>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-xs font-bold px-2 py-1 rounded" :class="getRoleColor(member.pivot.role)">
                    {{ member.pivot.role.toUpperCase() }}
                  </span>
                  <!-- Botão de Lixeira (Só aparece se o usuário logado for admin e não for ele mesmo) -->
                  <button v-if="isAdmin && member.id !== currentUser?.id" @click="removerMembro(member.id)" class="text-gray-500 hover:text-red-500 transition-colors" title="Remover Membro">
                    🗑️
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- ABA 2: CONFIGURAÇÕES -->
          <div v-if="activeTab === 'settings'" class="space-y-4">
            <form @submit.prevent="atualizarNomeWorkspace">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1">Nome do Projeto</label>
                <input v-model="workspaceName" type="text" :disabled="!isAdmin" class="w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:border-emerald-500 focus:outline-none disabled:opacity-50" />
              </div>
              <div class="flex justify-end">
                <button v-if="isAdmin" type="submit" :disabled="isLoading" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-md text-sm font-bold transition-colors">
                  Salvar Alterações
                </button>
                <p v-else class="text-sm text-yellow-500">Apenas admins podem renomear.</p>
              </div>
            </form>
          </div>

        </DialogPanel>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import http from '@/services/http';
import { notify, confirmAction } from '@/utils/alert';

const props = defineProps({
  workspaceId: { type: Number, required: true },
  currentWorkspaceName: { type: String, default: '' } // Recebe o nome atual para preencher o input
});
const emit = defineEmits(['workspace-updated']); // Avisa a tela pai que o nome mudou

const isOpen = ref(false);
const activeTab = ref('members');
const isLoading = ref(false);

const members = ref([]);
const currentUser = ref(null);
const workspaceName = ref('');

const newMember = ref({ email: '', role: 'viewer' });

// Computa se o usuário logado é admin deste projeto
const isAdmin = computed(() => {
  if (!currentUser.value || members.value.length === 0) return false;
  const me = members.value.find(m => m.id === currentUser.value.id);
  return me && me.pivot.role === 'admin';
});

const getRoleColor = (role) => {
  if (role === 'admin') return 'bg-purple-500/20 text-purple-400';
  if (role === 'editor') return 'bg-blue-500/20 text-blue-400';
  return 'bg-gray-500/20 text-gray-400';
};

const abrirModal = async () => {
  workspaceName.value = props.currentWorkspaceName;
  isOpen.value = true;
  await carregarDados();
};

const fecharModal = () => { isOpen.value = false; };

const carregarDados = async () => {
  try {
    // Busca quem é o usuário logado
    const userRes = await http.get('/user');
    currentUser.value = userRes.data;

    // Busca a lista de membros do projeto
    const memRes = await http.get(`/workspaces/${props.workspaceId}/members`);
    members.value = memRes.data;
  } catch (error) {
    console.error(error);
  }
};

const enviarConvite = async () => {
  isLoading.value = true;
  try {
    const res = await http.post(`/workspaces/${props.workspaceId}/members`, newMember.value);
    notify('success', res.data.message);
    newMember.value.email = '';
    newMember.value.role = 'viewer';
    await carregarDados(); // Recarrega a lista
  } catch (error) {
    notify('error', error.response?.data?.message || 'Erro ao convidar.');
  } finally {
    isLoading.value = false;
  }
};

const removerMembro = async (userId) => {
  const confirmed = await confirmAction('Remover membro?', 'Ele perderá acesso a este painel.');
  if (!confirmed) return;

  try {
    const res = await http.delete(`/workspaces/${props.workspaceId}/members/${userId}`);
    notify('success', res.data.message);
    await carregarDados();
  } catch (error) {
    notify('error', error.response?.data?.message || 'Erro ao remover.');
  }
};

const atualizarNomeWorkspace = async () => {
  isLoading.value = true;
  try {
    const res = await http.put(`/workspaces/${props.workspaceId}`, { name: workspaceName.value });
    notify('success', 'Nome atualizado!');
    emit('workspace-updated', workspaceName.value); // Pede pra tela de trás atualizar o título
  } catch (error) {
    notify('error', 'Erro ao renomear.');
  } finally {
    isLoading.value = false;
  }
};
</script>