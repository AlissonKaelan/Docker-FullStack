import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Importar os componentes
import LoginView from '../views/LoginView.vue'
import KanbanBoard from '../components/KanbanBoard.vue'
import FinanceView from '../views/FinanceView.vue' 
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView
    },
    {
      path: '/home',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: true }
    },
    {
      path: '/kanban',
      name: 'kanban',
      component: KanbanBoard,
      meta: { requiresAuth: true }
    },
    // --- NOVA ROTA INSERIDA AQUI NA LISTA ÚNICA ---
    {
      path: '/finance',
      name: 'finance',
      component: FinanceView, // Usa o componente importado acima
      meta: { requiresAuth: true }
    },
    {
      path: '/',
      redirect: '/home'
    }
  ]
})

// --- O GUARDIÃO DA ROTA ---
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login');
  } else {
    authStore.checkToken();
    next();
  }
})

export default router