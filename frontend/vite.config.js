import { fileURLToPath, URL } from 'node:url'
import { defineConfig, loadEnv } from 'vite' // Importante: loadEnv
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
  // Carrega as variáveis do arquivo .env (se existir)
  const env = loadEnv(mode, process.cwd(), '')

  return {
    plugins: [vue()],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url))
      }
    },
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      hmr: {
        protocol: 'ws',
        // Se existir VITE_SERVER_IP no .env, usa ele.
        // Se não existir (no WSL), usa 'localhost'.
        host: env.VITE_SERVER_IP || 'localhost', 
        clientPort: 5173,
      },
      watch: {
        usePolling: true,
      }
    }
  }
})