<template>
  <v-container fluid class="retro-screen d-flex align-center justify-center"
    style="height: 100vh; background-image: url('/css/images/login-bacg.jpg'); background-size: cover; background-position: center;">
    <div class="w-full max-w-md d-flex flex-col align-center">
      <!-- Title above the form, outside the card -->
      <h1 class="retro-title text-center mb-6">
        WELCOME TO U:LIFE!
      </h1>

      <!-- Form card -->
      <v-form class="retro-form w-full">
        <v-card class="retro-card pa-12" elevation="12">

          <!-- Logo above inputs -->
          <div class="d-flex justify-center mb-6">
            <v-img src="/css/images/ulife1.png" alt="U:LIFE Logo" max-width="70" contain />
          </div>

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
            <v-btn :disabled="!isLoginEnabled || loading" class="retro-btn" type="submit" @click.prevent="login">
              ▶ START
            </v-btn>

            <v-btn class="retro-link" @click="goToRegister">
              + CREATE ACCOUNT
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </div>
  </v-container>
  <v-snackbar v-model="showSnackbar" :color="snackbarColor" timeout="3000"> {{ snackbarMessage }} </v-snackbar>

</template>



//* Script


<script setup>
import { ref, computed} from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Snackbar state
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

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

    // Show success popup
    snackbarMessage.value = 'Successfully logged in!'
    snackbarColor.value = 'success'
    showSnackbar.value = true

    router.push('/character-creation')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Login failed'

    // Show error popup
    snackbarMessage.value = 'Invalid email or password'
    snackbarColor.value = 'error'
    showSnackbar.value = true
  } finally {
    loading.value = false
  }
}

const goToRegister = () => {
  router.push('/create')
}
</script>
