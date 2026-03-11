<template>
  <v-container
    fluid
    class="reset-screen d-flex align-center justify-center"
    style="height: 100vh;
      background-image: url('/css/images/login-bacg.jpg');
      background-size: cover;
      background-position: center;">

    <div class="w-full max-w-md flex flex-col items-center">

      <h1 class="retro-title text-center mb-8">
        RESET PASSWORD
      </h1>

      <v-form class="retro-form w-full">
        <v-card class="retro-card pa-12" elevation="12">

          <div class="d-flex justify-center mb-6">
            <v-img src="/css/images/ulife1.png" alt="U:LIFE Logo" max-width="80" contain />
          </div>

          <!-- Before sending -->
          <v-card-text v-if="!resetLink" class="space-y-6">
            <p class="text-center retro-link mb-4">
              Enter your email address and we'll send you a link to reset your password.
            </p>
            <v-text-field 
              v-model="email" 
              label="EMAIL" 
              variant="outlined" 
              density="comfortable" 
              class="retro-input"
              type="email"
            />
          </v-card-text>

          <!-- After sending - show the link -->
          <v-card-text v-if="resetLink" class="space-y-4">
            <p class="text-center retro-link mb-4" style="font-size: 14px; color: #555;">
              Your password reset link is ready! Click or copy it to reset your password.
            </p>
            <v-card class="bg-yellow-100 pa-4 mb-4">
              <p class="text-wrap" style="word-break: break-all; font-size: 12px; font-family: monospace;">
                {{ resetLink }}
              </p>
            </v-card>
          </v-card-text>

          <v-card-actions class="flex flex-col items-center gap-4 mt-6">
            <!-- Before sending -->
            <template v-if="!resetLink">
              <v-btn 
                :disabled="!isEmailValid || loading" 
                class="retro-btn" 
                type="submit" 
                @click.prevent="sendResetLink"
              >
                SEND LINK
              </v-btn>
            </template>

            <!-- After sending -->
            <template v-else>
              <v-btn 
                class="retro-btn" 
                @click="copyToClipboard"
              >
                <v-icon start>mdi-content-copy</v-icon>
                COPY LINK
              </v-btn>
              <v-btn 
                class="retro-btn" 
                @click="openResetLink"
              >
                <v-icon start>mdi-open-in-new</v-icon>
                OPEN LINK
              </v-btn>
            </template>

            <v-btn variant="text" class="retro-link" @click="goToHome">
              <v-icon start size="16">mdi-arrow-left</v-icon>
              Back to Login
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </div>
  </v-container>
  <v-snackbar v-model="showSnackbar" :color="snackbarColor" timeout="5000"> 
    {{ snackbarMessage }} 
  </v-snackbar>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const email = ref('')
const loading = ref(false)
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')
const resetLink = ref('')

const isEmailValid = computed(() => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return email.value && emailRegex.test(email.value)
})

const sendResetLink = async () => {
  loading.value = true

  try {
    const response = await axios.post('/forgot-password', {
      email: email.value
    })

    console.log('Reset link response:', response.data)
    
    // Store the reset link from response
    resetLink.value = response.data.reset_link || ''
    
    snackbarMessage.value = response.data.message || 'Password reset link generated!'
    snackbarColor.value = 'success'
    showSnackbar.value = true
  } catch (error) {
    console.error('Error sending reset link:', error)
    const errorMsg = error.response?.data?.message || 'Failed to send reset link'
    snackbarMessage.value = errorMsg
    snackbarColor.value = 'error'
    showSnackbar.value = true
  } finally {
    loading.value = false
  }
}

const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(resetLink.value)
    snackbarMessage.value = 'Link copied to clipboard!'
    snackbarColor.value = 'success'
    showSnackbar.value = true
  } catch (error) {
    snackbarMessage.value = 'Failed to copy link'
    snackbarColor.value = 'error'
    showSnackbar.value = true
  }
}

const openResetLink = () => {
  window.open(resetLink.value, '_blank')
}

const goToHome = () => {
  router.push('/home')
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
  font-size: 70px;
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

.retro-btn:disabled {
  background: #555 !important;
  color: #222 !important;
}

.retro-link {
  margin-top: 10px;
  font-family: 'Press Start 2P', monospace;
  font-size: 12px;
  color: #000000;
  cursor: pointer;
  text-shadow: 2px 2px #f7fff9;
}

.retro-link:hover {
  color: #82f582;
}
</style>
