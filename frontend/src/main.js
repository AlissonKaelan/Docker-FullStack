import { createApp } from 'vue'
import { createPinia } from 'pinia' // Importar Pinia
import App from './App.vue'
import router from './router'

//import './assets/main.css'

const app = createApp(App)

app.use(createPinia()) // Ativar Pinia
app.use(router)

app.mount('#app')