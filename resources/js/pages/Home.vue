<template>
  <v-container class="retro-screen flex items-center justify-center min-h-screen">
    <v-form class="retro-form">
      <v-card class="retro-card pa-12">
        <h1 class="retro-title text-center">
          WELCOME TO U:LIFE!
        </h1>

        <v-card-text class="space-y-6">
          <v-text-field v-model="email" label="EMAIL" variant="outlined" density="comfortable" class="retro-input" />

          <v-text-field v-model="password" :type="showPassword ? 'text' : 'password'" label="PASSWORD"
            variant="outlined" density="comfortable" class="retro-input">
            <template #append-inner>
              <v-icon @click="showPassword = !showPassword" class="cursor-pointer retro-icon">
                {{ showPassword ? 'mdi-eye' : 'mdi-eye-off' }}
              </v-icon>
            </template>
          </v-text-field>
        </v-card-text>

        <v-card-actions class="flex flex-col items-center gap-4 mt-6">
          <!-- Login -->
          <v-btn :disabled="!isLoginEnabled || loading" class="retro-btn" type="submit" @click.prevent="login">
            ▶ START
          </v-btn>

          <!-- Create Account -->

          <v-btn class="retro-link" @click="goToRegister">
            + CREATE ACCOUNT
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-container>
</template>

//* Script


<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const isLoginEnabled = computed(() =>
  email.value && password.value
)

const login = async () => {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await axios.post('login', {
      email: email.value,
      password: password.value
    })
    successMessage.value = response.data.message
    router.push('/character-creation') // or wherever you want after login
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}

const goToRegister = () => {
  router.push('/register')
}
</script>
