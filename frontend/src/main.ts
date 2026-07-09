import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { vuetify } from './plugins/vuetify'
import { initializeAuthState } from './services/authService'
import './styles/main.css'

initializeAuthState()

createApp(App).use(router).use(vuetify).mount('#app')