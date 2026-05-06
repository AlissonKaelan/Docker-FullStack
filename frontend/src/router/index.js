import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Importar os componentes
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import KanbanBoard from '../components/KanbanBoard.vue'
import FinanceView from '../views/FinanceView.vue' 
import HomeView from '../views/HomeView.vue'
import DailyView from '../views/DailyView.vue'
import WorkspaceView from '../views/WorkspaceView.vue'
import ProfileView from '../views/ProfileView.vue';

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
      path: '/profile',
      name: 'profile',
      component: ProfileView
    },  
    {
      path: '/kanban',
      name: 'kanban',
      component: KanbanBoard,
      meta: { requiresAuth: true }
    },
    {
      path: '/finance',
      name: 'finance',
      component: FinanceView, 
      meta: { requiresAuth: true }
    },
    // --- NOVA ROTA DE WORKSPACE ---
    {
      path: '/workspace/:id',
      name: 'workspace',
      component: WorkspaceView,
      props: true, // Crucial: Transforma o :id da URL em uma prop para a WorkspaceView
      meta: { requiresAuth: true } // Blindado pelo seu AuthStore!
    },
    {
      path: '/daily',
      name: 'daily',
      component: DailyView,
      // Se essa for uma tela interna da plataforma, lembre-se de colocar a meta de auth aqui também!
      // meta: { requiresAuth: true }
    },
    {
      path: '/',
      redirect: '/home'
    }
  ]
})

// --- O GUARDIÃO DA ROTA (Mantido intacto) ---
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  authStore.checkToken();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login'); 
  } 
  else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/home');
  } 
  else {
    next(); 
  }
})

export default router