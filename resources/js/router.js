import { createRouter, createWebHistory} from "vue-router";
import Home from './pages/Home.vue';
import CharacterCreation from './pages/CharacterCreation.vue';
import Create from './pages/Create.vue';
import Game from './pages/Game.vue';
import ForgotPassword from './pages/ForgotPassword.vue';
import ResetPassword from './pages/ResetPassword.vue';
import ProfessionalDashboard from './pages/ProfessionalDashboard.vue';
import UserAgreement from './pages/UserAgreement.vue';
import PrivacyPolicy from './pages/PrivacyPolicy.vue';
const routes = [
        { path: '/', redirect: '/home' },
        {path: '/home', component: Home},
        {path: '/character-creation', component: CharacterCreation},
        {path: '/create', component: Create},
        {path: '/game', component: Game},
        {path: '/forgot-password', component: ForgotPassword},
        {path: '/reset-password', component: ResetPassword},
        {path: '/professional-dashboard', component: ProfessionalDashboard},
        {path: '/user-agreement', component: UserAgreement},
        {path: '/privacy-policy', component: PrivacyPolicy},


];

export default createRouter({
    history: createWebHistory(),
    routes,
});
