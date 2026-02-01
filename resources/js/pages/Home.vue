<template>
    <v-container class="flex items-center justify-center min-h-screen">
        <v-form class="w-1/2">
            <v-card class="w-full pa-16">
                <v-fade-transition>
                    <h1 class="font-bold text-center text-5xl text-primary">
                        U:Life Login
                    </h1>

                </v-fade-transition>
                <v-expand-transition>
                    <v-fade-transition>
                        <div>
                            <v-card-text class="px-6 pb-0 space-y-4">
                                <v-text-field v-model="email" type="email" label="Email" variant="outlined"
                                    density="comfortable">

                                </v-text-field>
                                <v-text-field v-model="password" :type="showPassword ? 'text' : 'password'"
                                    label="Password" variant="outlined" density="comfortable">
                                    <template v-slot:append-inner>
                                        <v-icon
                                            @click="showPassword = !showPassword"
                                            class="cursor-pointer text-primary"
                                        >
                                            {{ showPassword ? 'mdi-eye' : 'mdi-eye-off' }}
                                        </v-icon>
                                    </template>
                                </v-text-field>
                            </v-card-text>
                            <v-card-actions class="d-flex justify-center px-6 pb-6">
                                <v-btn :disabled="isLoginEnabled" variant="tonal" color="button-primary" type="submit">
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