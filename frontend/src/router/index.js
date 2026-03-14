import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Importar os componentes
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import KanbanBoard from '../components/KanbanBoard.vue'
import FinanceView from '../views/FinanceView.vue' 
import HomeView from '../views/HomeView.vue'
import DailyView from '../views/DailyView.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/home',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: true }
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView
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
    },
    {
      path: '/daily',
      name: 'daily',
      component: DailyView
    }
  ]
})

// --- O GUARDIÃO DA ROTA ---
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  // 1. PRIMEIRO DE TUDO: Sincroniza o Pinia com o localStorage atual
  authStore.checkToken();

  // 2. Agora, toma a decisão com base na verdade absoluta
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login'); // Bloqueia e manda pro login
  } 
  // 3. Bônus: Se tentar ir pro login já estando logado, manda pra home
  else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/home');
  } 
  else {
    next(); // Passagem livre
  }
})

export default router