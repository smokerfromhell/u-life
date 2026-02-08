<template>
  <v-container class="retro-screen flex items-center justify-center min-h-screen">
    <v-form class="retro-form">
      <v-card class="retro-card pa-12">
        <h1 class="retro-title text-center">
          WELCOME TO U:LIFE!    
        </h1>

        <v-card-text class="space-y-6">
          <v-text-field
            v-model="email"
            label="EMAIL"
            variant="outlined"
            density="comfortable"
            class="retro-input"
          />

          <v-text-field
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            label="PASSWORD"
            variant="outlined"
            density="comfortable"
            class="retro-input"
          >
            <template #append-inner>
              <v-icon
                @click="showPassword = !showPassword"
                class="cursor-pointer retro-icon"
              >
                {{ showPassword ? 'mdi-eye' : 'mdi-eye-off' }}
              </v-icon>
            </template>
          </v-text-field>
        </v-card-text>

        <v-card-actions class="flex flex-col items-center gap-4 mt-6">
          <!-- Login -->
          <v-btn
            :disabled="isLoginEnabled"
            class="retro-btn"
            type="submit"
          >
            ▶ START
          </v-btn>

          <!-- Create Account -->

          <v-btn
            class="retro-link"
            @click="goToRegister"
            
          >
          + CREATE ACCOUNT
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-container>
</template>

/* SCRIPT */


<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoginEnabled = ref(false)

const goToRegister = () => {
  router.push('/register')
}
</script>

<style scoped>
.retro-screen {
  background-image: url('public/css/images/login-bacg.jpg');
  background-size: cover;
  background-position: center;
}
</style>
