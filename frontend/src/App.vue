<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <RouterView />
</template>

<style>
:root {
  /* TEMA CLARO */
  --bg-primary: #f3f4f6;
  --bg-secondary: #ffffff;
  --text-primary: #1f2937;
  --text-secondary: #6b7280;
  --border-color: #e5e7eb;
  --input-bg: #ffffff;
  --shadow-color: rgba(0, 0, 0, 0.1);
  --accent-color: #4F46E5;
}

/* TEMA ESCURO (Midnight Blue) */
.dark {
  --bg-primary: #0f172a; /* Azul Escuro Profundo (Fundo da tela) */
  --bg-secondary: #1e293b; /* Azul Petróleo (Cards) */
  --text-primary: #f1f5f9;
  --text-secondary: #94a3b8;
  --border-color: #334155;
  --input-bg: #0f172a;
  --shadow-color: rgba(0, 0, 0, 0.5);
  --accent-color: #818cf8;
}

/* --- CORREÇÃO DO FUNDO BRANCO --- */
body {
  margin: 0; /* Remove margem padrão do navegador */
  padding: 0;
  width: 100%;
  min-height: 100vh;
  
  /* Aqui está o segredo: O corpo do site assume a cor da variável */
  background-color: var(--bg-primary); 
  color: var(--text-primary);
  
  font-family: 'Segoe UI', sans-serif;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* Garante que o container raiz do Vue também estique e pegue a cor */
#app {
  min-height: 100vh;
  width: 100%;
  background-color: var(--bg-primary);
}

/* Garante que o alerta fique na frente de qualquer modal */
.swal2-container {
  z-index: 99999 !important;
}
</style>