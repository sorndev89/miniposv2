import './bootstrap'; // Ensures bootstrap.js (axios setup) runs first
import 'flowbite'; // Import Flowbite JavaScript

import { createApp } from 'vue';
import { createPinia } from 'pinia'; // Import createPinia
import App from './App.vue';
import router from './router'; // Import your router instance
import { useAuthStore } from './stores/auth'; // Import your auth store
import Pagination from './components/Pagination.vue';

const app = createApp(App);
const pinia = createPinia(); // Create Pinia instance
app.component('Pagination', Pagination);
app.use(pinia); // Use Pinia first
app.use(router); // Then use Vue Router

// Now that Pinia is installed and the app is created, we can safely access stores.
// Initialize the auth store to set the axios authorization header if a token already exists.
const authStore = useAuthStore();
authStore.initialize();


app.mount('#app');