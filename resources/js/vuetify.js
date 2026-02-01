import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import { aliases , mdi } from 'vuetify/iconsets/mdi';

export default createVuetify ({
icons: {
    defaultSet: 'mdia',
    aliases,
    sets: {mdi},

},
theme: {

    defaultTheme: 'light',

},
});
