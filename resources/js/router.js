import { createRouter, createWebHistory} from "vue-router";
import Home from './pages/Home.vue';
import Register from './pages/Register.Vue';
import CharacterCreation from './pages/CharacterCreation.vue';
const routes = [
        {path: '/home', component: Home},
        {path: '/register', component: Register},
        {path: '/character-creation', component: CharacterCreation},
];

export default createRouter({
    history: createWebHistory(),
    routes,
});