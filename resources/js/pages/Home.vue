<template>
    <v-container class="w-full bg-amber-500" >
       <v-form class="w-1/8 mx-auto my-12 bg-amber-50  ">
        <v-card class=" py-4 shadow-lg rounded-x1">
            <v-fade-transition>
                <v-card-title class=" font-bold text-ceter text-h6 text-primary">
                    U:Life Login
                </v-card-title>

            </v-fade-transition>
            <v-expand-transition>
                <v-fade-transition>
                    <div>
                         <v-card-text class="px-6 pb-0 space-y-4">
                        <v-text-field
                        v-model="email"
                        type="email"
                        label="Email"
                        variant="outlined"
                        density="comfortable">
                            
                        </v-text-field>
                         <v-text-field
                        v-model="password"
                        :type= "showPassword ? 'text'  : ' password'"
                        label="Password"
                        variant="outlined"
                        density="comfortable">
                            
                        </v-text-field>
                    </v-card-text>
                    <v-card-actions class="justify-between px-6 pb-6">
                        <v-btn 
                        :disabled="isLoginEnabled" 
                        variant="tonal"
                        color=""
                        type=" submit">
                        LOGIN
                        </v-btn>
                    </v-card-actions>
                    </div>
                   
                </v-fade-transition>
            </v-expand-transition>

        </v-card>
       </v-form>
     </v-container>
</template>
<script setup>
import { ref } from 'vue';
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const isLoginEnabled = ref(false);
</script>