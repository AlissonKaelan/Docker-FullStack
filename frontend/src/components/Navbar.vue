<template>
  <header class="hero-header">
    <div class="hero-content">
      <div class="user-info">
        <h1>Olá, {{ userName || 'Visitante' }}!</h1>
        <p>{{ subtitle }}</p>
      </div>
      
      <div class="header-actions">
        <button @click="toggleTheme" class="btn-theme" :title="isDark ? 'Mudar para Claro' : 'Mudar para Escuro'">
          {{ isDark ? '☀️' : '🌙' }}
        </button>
        
        <button @click="handleLogout" class="btn-logout">
          Sair
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';

// Define as propriedades que este componente vai receber do "Pai" (ex: HomeView)
defineProps({
  userName: {
    type: String,
    default: 'Visitante'
  },
  subtitle: {
    type: String,
    default: 'Bem-vindo ao sistema'
  }
});

const authStore = useAuthStore();
const isDark = ref(false);

// Lógica do Tema isolada aqui!
const toggleTheme = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.body.classList.add('dark');
    localStorage.setItem('theme', 'dark');
  } else {
    document.body.classList.remove('dark');
    localStorage.setItem('theme', 'light');
  }
};

onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    isDark.value = true;
    document.body.classList.add('dark');
  }
});

// Logout centralizado
const handleLogout = () => {
    authStore.logout(); 
};
</script>

<style scoped>
/* Apenas o CSS do Header veio para cá */
.hero-header {
  background: linear-gradient(135deg, var(--accent-color) 0%, #7C3AED 100%);
  padding: 60px 20px 100px 20px;
  color: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.hero-content {
  max-width: 1000px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;
}
.hero-content h1 { margin: 0; font-size: 2rem; font-weight: 700; }
.hero-content p { margin: 5px 0 0 0; opacity: 0.9; font-size: 1.1rem; }

.header-actions { display: flex; gap: 15px; align-items: center; }

.btn-theme {
  background: rgba(255, 255, 255, 0.2); border: none; font-size: 1.2rem; cursor: pointer;
  padding: 8px 12px; border-radius: 50%; transition: 0.2s;
}
.btn-theme:hover { background: rgba(255, 255, 255, 0.4); transform: rotate(15deg); }

.btn-logout {
  background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); color: white;
  padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;
}
.btn-logout:hover { background: rgba(255, 255, 255, 0.3); transform: translateY(-1px); }
</style>