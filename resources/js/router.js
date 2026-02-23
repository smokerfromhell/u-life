import { createRouter, createWebHistory} from "vue-router";
import Home from './pages/Home.vue';
import CharacterCreation from './pages/CharacterCreation.vue';
import Create from './pages/Create.vue';
const routes = [
        {path: '/home', component: Home},
        {path: '/character-creation', component: CharacterCreation},
        {path: '/create', component: Create},
];

export default createRouter({
    history: createWebHistory(),
    routes,
});