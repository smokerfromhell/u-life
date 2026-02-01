import { createApp } from 'vue';
import App from './App.vue'
import vuetify from './vuetify';
import router from './router';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

createApp(App).use(router).use(vuetify).mount('#app');

