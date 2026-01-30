import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import './bootstrap';

const app = createApp(App);
const pinia = createPinia();

// Import activation store to setup interceptors globally before mount
import { useActivationStore } from '@/stores/activation';

app.use(pinia);
app.use(router);

// Setup Global Axio Interceptors for Activation Check
const activationStore = useActivationStore();
activationStore.setupInterceptors();

app.mount('#app');
