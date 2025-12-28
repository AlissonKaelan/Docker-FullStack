import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0',      // Permite acesso de fora do container
    port: 5173, 
    hmr: {
      host: 'localhost',  // Força o Hot Reload a procurar o localhost
    },
    watch: {
      usePolling: true,   // OBRIGATÓRIO no Docker/Linux para atualizar arquivos
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  }
})