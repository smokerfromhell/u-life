import { createRouter, createWebHistory} from "vue-router";
import Home from './pages/Home.vue';
import CharacterCreation from './pages/CharacterCreation.vue';
import Create from './pages/Create.vue';
import Game from './pages/Game.vue';
const routes = [
        { path: '/', redirect: '/home' },
        {path: '/home', component: Home},
        {path: '/character-creation', component: CharacterCreation},
        {path: '/create', component: Create},
        {path: '/game', component: Game},


];

export default createRouter({
    history: createWebHistory(),
    routes,
});