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
              START
            </v-btn>

            <!-- Google Sign-In Button -->
            <div id="google-signin-button" class="mt-4"></div>

            <!-- Secondary actions row -->
             <div class="d-flex justify-center gap-6 mt-2">
    <v-btn variant="text" class="retro-link" @click="goToRegister">
      <v-icon start>mdi-account-plus</v-icon>
      Need an account?
    </v-btn>
    
    <v-btn variant="text" class="retro-link" @click="goToForgotPassword">
      <v-icon start>mdi-lock-reset</v-icon>
      Forgot password?
    </v-btn>

            </div>
          </v-card-actions>
        </v-card>
      </v-form>
    </div>
  </v-container>
  <v-snackbar v-model="showSnackbar" :color="snackbarColor" timeout="3000"> {{ snackbarMessage }} </v-snackbar>

</template>



//* Script


<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const googleClientId = ref(import.meta.env.VITE_GOOGLE_CLIENT_ID || '')

// Snackbar state
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const isLoginEnabled = computed(() =>
  email.value && password.value
)

// Handle Google Sign-In response
const handleCredentialResponse = async (response) => {
  loading.value = true
  console.log('Google Sign-In response received:', response)
  
  try {
    const res = await axios.post('/auth/google', {
      token: response.credential
    })
    
    console.log('Google auth response:', res.data)
    
    successMessage.value = 'Successfully logged in with Google!'
    snackbarMessage.value = 'Successfully logged in with Google!'
    snackbarColor.value = 'success'
    showSnackbar.value = true
    
    // Check if user has a character, redirect accordingly
    const hasCharacter = res.data.has_character
    const redirectPath = hasCharacter ? '/game' : '/character-creation'
    setTimeout(() => {
      console.log('Redirecting to ' + redirectPath)
      router.push(redirectPath)
    }, 1500)
  } catch (error) {
    console.error('Google authentication error:', error)
    const errorMsg = error.response?.data?.message || error.message || 'Google login failed'
    errorMessage.value = errorMsg
    snackbarMessage.value = errorMsg
    snackbarColor.value = 'error'
    showSnackbar.value = true
  } finally {
    loading.value = false
  }
}

// Make handleCredentialResponse globally available for Google Sign-In callback
window.handleCredentialResponse = handleCredentialResponse

// Initialize Google Sign-In
onMounted(() => {
  console.log('Mounting Home.vue, Google Client ID:', googleClientId.value)
  
  // Check if script already exists
  if (window.google?.accounts?.id) {
    console.log('Google library already loaded')
    initializeGoogleSignIn()
    return
  }
  
  // Load the Google Sign-In script
  const script = document.createElement('script')
  script.src = 'https://accounts.google.com/gsi/client'
  script.async = true
  script.defer = true
  script.onload = () => {
    console.log('Google Sign-In script loaded')
    initializeGoogleSignIn()
  }
  script.onerror = () => {
    console.error('Failed to load Google Sign-In script')
  }
  document.head.appendChild(script)
})

const initializeGoogleSignIn = () => {
  try {
    if (!window.google?.accounts?.id) {
      console.error('Google Sign-In library not available')
      return
    }
    
    if (!googleClientId.value) {
      console.error('Google Client ID not configured')
      return
    }
    
    console.log('Initializing Google Sign-In with client ID:', googleClientId.value)
    
    window.google.accounts.id.initialize({
      client_id: googleClientId.value,
      callback: handleCredentialResponse,
      auto_select: false
    })
    
    const buttonElement = document.getElementById('google-signin-button')
    if (buttonElement) {
      console.log('Rendering Google Sign-In button')
      window.google.accounts.id.renderButton(
        buttonElement,
        { 
          theme: 'filled_blue',
          size: 'large',
          text: 'signin_with'
        }
      )
    } else {
      console.error('Google Sign-In button element not found')
    }
  } catch (error) {
    console.error('Error initializing Google Sign-In:', error)
  }
}

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

    // Check if user has a character, redirect accordingly
    const hasCharacter = response.data.has_character
    const redirectPath = hasCharacter ? '/game' : '/character-creation'
    setTimeout(() => {
      router.push(redirectPath)
    }, 1000)
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
  router.push({ path: '/create' })
}

const goToForgotPassword = () => {
  router.push({ path: '/forgot-password' })
}
</script>

<style scoped>
@keyframes beat {

  0%,
  100% {
    transform: scale(1);
    text-shadow: 2px 2px #000, 0 0 10px #00ff66;
  }

  50% {
    transform: scale(1.15);
    text-shadow: 4px 4px #000, 0 0 20px #00ff66;
  }
}

.retro-form {
  width: 600px;
}

.retro-card {
  background: #ececec8c !important;
  border-radius: 20px !important;
  font-family: 'Press Start 2P', monospace;
}

.retro-title {
  width: 1500px;
  font-family: "Press Start 2P", cursive;
  font-size: 90px;
  color: #53f06a;
  margin-bottom: 40px;
  text-shadow: 6px 6px #ffffff;
  animation: beat 1s infinite;
}

/* Inputs */
.retro-input input {
  font-family: 'Press Start 2P', monospace;
  color: #89df00;
}

::v-deep(.v-field) {
  border-radius: 0 !important;
  border: 3px solid #048519;
  background-color: #82f582;
}

.retro-input label {
  font-size: 10px;
}

.retro-btn {
  font-family: 'Press Start 2P', monospace;
  background: #008516 !important;
  color: #000 !important;
  border: 3px solid #000000;
  border-radius: 5px !important;
  padding: 4px 28px;
  box-shadow: 4px 4px #000;
}

.retro-btn:hover {
  transform: translate(2px, 2px);
  box-shadow: 2px 2px #000;
}

/* Disabled state */
.retro-btn:disabled {
  background: #555 !important;
  color: #222 !important;
}

.retro-link {
  margin-top: 25px;
  font-family: 'Press Start 2P', monospace;
  font-size: 12px;
  color: #000000;
  cursor: pointer;
  text-shadow: 2px 2px #f7fff9;
}

.retro-link:hover {
  color: #82f582;
}

#google-signin-button {
  display: flex;
  justify-content: center;
  margin: 10px 0;
}

::v-deep(#g_id_signin > div) {
  margin: 0 auto !important;
}
</style>
