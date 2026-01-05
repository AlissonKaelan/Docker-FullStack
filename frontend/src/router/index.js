import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Importar os componentes (vamos criar a LoginView já já)
import LoginView from '../views/LoginView.vue'
import KanbanBoard from '../components/KanbanBoard.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView
    },
    {
      path: '/kanban',
      name: 'kanban',
      component: KanbanBoard,
      meta: { requiresAuth: true } // <--- ETIQUETA DE PROTEÇÃO
    },
    {
      // Redireciona a raiz para o kanban (ou login se não tiver logado)
      path: '/',
      redirect: '/kanban'
    }
  ]
})

// --- O GUARDIÃO DA ROTA ---
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  // Se a rota precisa de auth e o usuário NÃO está logado
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login'); // Chuta para o login
  } else {
    // Verifica se o token está configurado no axios ao trocar de rota
    authStore.checkToken();
    next(); // Pode passar
  }
})

export default router