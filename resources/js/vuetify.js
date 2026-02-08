import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import { aliases , mdi } from 'vuetify/iconsets/mdi';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

export default createVuetify ({
components,
directives,
icons: {
    defaultSet: 'mdi',
    aliases,
    sets: {mdi},

},
theme: {

    defaultTheme: 'light',
    
        themes: {
            light: {
                colors: {
                    background: '#98FB98',
                    surface: '#50C878',
                    inverted_primary:  '#1b620f',
                    button_primary:  '#F0FFF0',
                    text_primary:  '#46811b',
                    primary:  '#2E8b57',
                    secondary:  '#0A886B',
                    tertiary:  '50C878',
                    error: '#4a8516',
                    thead_background: '#91be4d',
                },
            },
            
           dark:{
                colors:{ 
                    background: '#121212',
                    surface: '#1e1e1e',
                    inverted_primary:  '#e5eef8',
                    button_primary:  '#1a4789',
                    text_primary:  '#e5eef8',
                    primary:  '#444444',
                    secondary:  '#03dac6',
                    tertiary:  '#b3cde0',
                    error: '#cf6679',
                    thead_background: '#0A0A0A',
                },
            }, 

        }, 
            
},
});
