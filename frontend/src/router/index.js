import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// ==========================================
// IMPORTAÇÃO DE TODAS AS TELAS E COMPONENTES
// ==========================================
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import HomeView from '../views/HomeView.vue'
import ProfileView from '../views/ProfileView.vue'
import WorkspaceView from '../views/WorkspaceView.vue'
import FinanceView from '../views/FinanceView.vue' 
import DailyView from '../views/DailyView.vue'
import KanbanBoard from '../components/KanbanBoard.vue' 

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // --- ROTAS GLOBAIS ---
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
      component: ProfileView,
      meta: { requiresAuth: true } 
    },  

    // --- 1. A ROTA DO PAINEL DO PROJETO ---
    {
      path: '/workspace/:id',
      name: 'workspace.dashboard',
      component: WorkspaceView,
      meta: { requiresAuth: true }
    },

    // --- 2. AS ROTAS DOS MÓDULOS ---
    {
      path: '/workspace/:id/kanban',
      name: 'workspace.kanban',
      component: KanbanBoard, 
      meta: { requiresAuth: true } 
    },
    {
      path: '/workspace/:id/finance',
      name: 'workspace.finance',
      component: FinanceView,
      meta: { requiresAuth: true } 
    },
    {
      path: '/workspace/:id/daily',
      name: 'workspace.daily',
      component: DailyView,
      meta: { requiresAuth: true } 
    },
    
    // --- REDIRECIONAMENTO PADRÃO ---
    {
      path: '/',
      redirect: '/home'
    }
  ]
})

// ==========================================
// O GUARDIÃO DA ROTA (MIDDLEWARE FRONTEND)
// ==========================================
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  // Tenta puxar o token do localStorage pro estado
  authStore.checkToken();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    // Se a rota exige login e não tem usuário, chuta pro login
    next('/login'); 
  } 
  else if (to.path === '/login' && authStore.isAuthenticated) {
    // Se tentar acessar a tela de login já estando logado, joga pra home
    next('/home');
  } 
  else {
    // Caso contrário, deixa passar normalmente
    next(); 
  }
})

export default router