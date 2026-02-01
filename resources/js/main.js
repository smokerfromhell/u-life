import { createApp } from 'vue';
import App from './App.vue'
import vuetify from './vuetify';
import router from './router';
import 'vuetify/styles';

createApp(App).use(router).use(vuetify).mount('#app');

