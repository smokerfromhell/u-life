<template>
  <!-- Galaxy Background Screen -->
  <div class="galaxy-screen relative min-h-screen overflow-hidden"
    style="background: linear-gradient(135deg, #0d0d1a 0%, #1a0a2e 30%, #0a1a2e 50%, #0a1a1a 70%, #0d0d1a 100%);">
    
    <!-- Galaxy Starfield -->
    <div class="starfield pointer-events-none fixed inset-0 z-0">
      <div v-for="n in 80" :key="n" class="star absolute rounded-full"
        :style="getStarStyle(n)">
      </div>
    </div>
    
    <!-- Nebula Clouds -->
    <div class="nebula pointer-events-none fixed inset-0 z-5"></div>
    
    <!-- Grid Lines -->
    <div class="grid-lines pointer-events-none fixed inset-0 z-10"></div>
    
    <!-- Scanlines -->
    <div class="scanlines pointer-events-none fixed inset-0 z-25"></div>
    
    <!-- Main Container -->
    <v-container fluid class="relative z-20 flex items-center justify-center min-h-screen px-3 py-6">
      <div class="w-full max-w-sm flex flex-col items-center">
        
        <!-- Game Title - BIG -->
        <div class="title-wrapper mb-8 text-center">
          <div class="title-container inline-block">
            <h1 class="game-title relative uppercase whitespace-nowrap" 
              style="font-family: 'Press Start 2P', monospace; font-size: clamp(2.5rem, 12vw, 8rem); text-align: center; display: block;">
              <span class="title-u">U</span><span class="title-colon">:</span><span class="glitch-title" data-text="LIFE">LIFE</span><span class="title-exclaim">!</span>
            </h1>
          </div>
          
          <p class="tagline mt-4 text-cyan-300 text-center text-[10px] md:text-[12px] uppercase tracking-[0.35em]"
            style="font-family: 'VT323', monospace; font-size: 18px;">
            ◇ Play Your Life ◇
          </p>
        </div>

        <!-- Login Card - Galaxy Theme -->
        <v-form class="w-full" @submit.prevent="login">
          <div class="galaxy-card relative">
            <!-- Card Glow -->
            <div class="card-glow absolute inset-0 rounded-2xl blur-3xl"></div>
            
            <!-- Card Background -->
            <div class="card-bg absolute inset-0 rounded-2xl"></div>
            
            <!-- Card Border Gradient -->
            <div class="card-border absolute inset-0 rounded-2xl"></div>

            <!-- Card Header -->
            <div class="card-header relative pt-6 pb-4 px-6 text-center">
              <!-- Logo - Centered -->
              <div class="logo-wrapper mb-4 flex justify-center">
                <v-img src="/css/images/ulife1.png" alt="U:LIFE" max-width="55" contain class="logo-img" />
              </div>
              
              <h2 class="card-title text-white text-lg font-medium tracking-wide"
                style="font-family: 'VT323', monospace; font-size: 24px;">
                Welcome Back, Player
              </h2>
              <p class="text-white/50 text-xs mt-1" style="font-family: 'VT323', monospace; font-size: 14px;">
                Continue your journey
              </p>
            </div>

            <!-- Card Body -->
            <div class="card-body relative px-6 pb-6">
<!-- Input Fields -->
              <div class="inputs space-y-2.5">
                <!-- Email -->
                <div class="input-wrapper">
                  <v-text-field 
                    v-model="email" 
                    label="Email Address"
                    variant="outlined"
                    density="comfortable" 
                    class="galaxy-input"
                    hide-details="auto"
                    bg-color="transparent"
                  >
                    <template #prepend-inner>
                      <v-icon size="small" class="input-icon">mdi-email-outline</v-icon>
                    </template>
                  </v-text-field>
                </div>

                <!-- Password -->
                <div class="input-wrapper">
                  <v-text-field 
                    v-model="password" 
                    :type="showPassword ? 'text' : 'password'" 
                    label="Password"
                    variant="outlined"
                    density="comfortable" 
                    class="galaxy-input"
                    hide-details="auto"
                    bg-color="transparent"
                  >
                    <template #prepend-inner>
                      <v-icon size="small" class="input-icon">mdi-lock-outline</v-icon>
                    </template>
                    <template #append-inner>
                      <v-icon @click="showPassword = !showPassword" class="eye-icon cursor-pointer">
                        {{ showPassword ? 'mdi-eye' : 'mdi-eye-off' }}
                      </v-icon>
                    </template>
                  </v-text-field>
                </div>
              </div>

              <!-- Remember & Forgot -->
              <div class="flex items-center justify-between mt-2 mb-4">
                <v-checkbox
                  v-model="rememberMe"
                  label="Remember me"
                  density="compact"
                  hide-details
                  class="remember-checkbox"
                  color="#00ffcc"
                ></v-checkbox>
                <v-btn variant="text" class="forgot-btn" @click="goToForgotPassword">
                  Forgot?
                </v-btn>
              </div>

              <!-- Login Button -->
              <v-btn 
                :disabled="!isLoginEnabled || loading" 
                class="login-btn w-full py-5"
                type="submit"
              >
                <span class="flex items-center justify-center gap-2 w-full text-center">
                  <v-icon size="small">mdi-rocket-launch</v-icon>
                  {{ loading ? 'Loading...' : 'Start Living' }}
                </span>
              </v-btn>

<!-- Divider -->
              <div class="divider mt-5 flex items-center gap-3">
                <div class="flex-1 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                <span class="text-white/40 text-[10px]" style="font-family: 'Press Start 2P', monospace;">OR</span>
                <div class="flex-1 h-px bg-gradient-to-l from-transparent via-white/20 to-transparent"></div>
              </div>

              <!-- Google Sign-In -->
              <div class="google-wrapper mt-4 flex justify-center">
                <div id="google-signin-button"></div>
              </div>

              <!-- Guest Mode -->
              <div class="mt-4">
                <v-btn
                  class="guest-btn w-full"
                  variant="outlined"
                  :disabled="loading"
                  prepend-icon="mdi-incognito"
                  @click="guestDialog = true"
                >
                  Play as Guest
                </v-btn>
                <p
                  class="text-white/40 text-[11px] mt-2 text-center"
                  style="font-family: 'VT323', monospace;"
                >
                  No account needed. Choose your privacy.
                </p>
              </div>

              <!-- Create Account -->
              <div class="signup-link mt-5 text-center">
                <span class="text-white/50 text-sm">New player? </span>
                <v-btn variant="text" class="create-btn px-1" @click="goToRegister">
                  Create account
                </v-btn>
              </div>

              <!-- Professional Access Request -->
              <div class="mt-2 text-center">
                <v-btn
                  variant="text"
                  class="professional-link px-1"
                  :disabled="loading"
                  prepend-icon="mdi-chart-box-outline"
                  @click="openProfessionalRequest"
                >
                  Request Analytics Dashboard Access
                </v-btn>
                <div class="text-white/40 text-[11px]" style="font-family: 'VT323', monospace;">
                  For professionals. Approved by Super Admin.
                </div>
              </div>
            </div>
          </div>
        </v-form>

        <!-- Footer -->
        <div class="footer mt-5 text-center">
          <p class="version text-cyan-400/50 text-[7px] uppercase tracking-widest" 
            style="font-family: 'Press Start 2P', monospace;">
            v1.0 • Galaxy Edition
          </p>
        </div>
      </div>
    </v-container>
  </div>

  <!-- Snackbar -->
  <v-snackbar
    v-model="showSnackbar"
    location="center"
    rounded="xl"
    timeout="3500"
    class="floating-snackbar"
    :class="snackbarColor === 'error' ? 'floating-snackbar--error' : 'floating-snackbar--success'"
  >
    <div class="toast-content">
      <v-icon class="toast-icon" size="20">
        {{ snackbarColor === 'error' ? 'mdi-alert-circle-outline' : 'mdi-check-circle-outline' }}
      </v-icon>
      <div class="toast-text">{{ snackbarMessage }}</div>
    </div>
  </v-snackbar>

  <!-- Guest Mode Dialog -->
  <v-dialog v-model="guestDialog" max-width="520" rounded="xl">
    <v-card class="guest-dialog-card">
      <v-card-title class="guest-dialog-title text-center">
        <span class="glitch-title" data-text="GUEST MODE">GUEST MODE</span>
      </v-card-title>
      <v-card-text class="guest-dialog-text text-center">
        <p class="mb-3">Play without an account.</p>
        <p class="text-white/60 text-sm" style="font-family: 'VT323', monospace; font-size: 16px;">
          Share your gameplay data (choices + stat changes) to help improve the game?
        </p>
      </v-card-text>
      <v-card-actions class="justify-center gap-3 pb-6 px-6">
        <v-btn
          variant="outlined"
          color="white"
          class="guest-private-btn"
          :disabled="loading"
          @click="startGuest(false)"
        >
          Play Private
        </v-btn>
        <v-btn
          variant="elevated"
          color="#00ffcc"
          class="guest-share-btn"
          :disabled="loading"
          @click="startGuest(true)"
        >
          Play & Share
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- Professional Account Request Dialog -->
  <v-dialog v-model="professionalDialog" max-width="520" rounded="xl">
    <v-card class="guest-dialog-card">
      <v-card-title class="guest-dialog-title text-center">
        <span class="glitch-title" data-text="PRO ACCESS">PRO ACCESS</span>
      </v-card-title>
      <v-card-text class="guest-dialog-text">
        <div class="text-center mb-4">
          Request access to the analytics dashboard. Your account can only be created/approved by the Super Admin.
        </div>

        <div class="space-y-3">
          <v-text-field
            v-model="proRequest.name"
            label="Full Name"
            variant="outlined"
            density="comfortable"
            class="galaxy-input"
            hide-details="auto"
            bg-color="transparent"
            :disabled="loading"
          />

          <v-text-field
            v-model="proRequest.email"
            label="Email Address"
            variant="outlined"
            density="comfortable"
            class="galaxy-input"
            hide-details="auto"
            bg-color="transparent"
            :disabled="loading"
          />

          <v-text-field
            v-model="proRequest.organization"
            label="Organization (optional)"
            variant="outlined"
            density="comfortable"
            class="galaxy-input"
            hide-details="auto"
            bg-color="transparent"
            :disabled="loading"
          />

          <v-textarea
            v-model="proRequest.message"
            label="Message (optional)"
            variant="outlined"
            density="comfortable"
            class="galaxy-input"
            hide-details="auto"
            bg-color="transparent"
            rows="3"
            auto-grow
            :disabled="loading"
          />
        </div>
      </v-card-text>
      <v-card-actions class="justify-center gap-3 pb-6 px-6">
        <v-btn
          variant="outlined"
          color="white"
          class="guest-private-btn"
          :disabled="loading"
          @click="professionalDialog = false"
        >
          Cancel
        </v-btn>
        <v-btn
          variant="elevated"
          color="#00ffcc"
          class="guest-share-btn"
          :disabled="loading"
          @click="submitProfessionalRequest"
        >
          Submit Request
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
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
const rememberMe = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const googleClientId = ref(import.meta.env.VITE_GOOGLE_CLIENT_ID || '')

// Snackbar state
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')
const guestDialog = ref(false)
const professionalDialog = ref(false)
const proRequest = ref({
  name: '',
  email: '',
  organization: '',
  message: '',
})

const isLoginEnabled = computed(() =>
  email.value && password.value
)

// Generate galaxy star positions with varied colors
const getStarStyle = (n) => {
  const colors = [
    '#00ffcc', // cyan
    '#ff00ff', // magenta/purple
    '#00ff88', // green
    '#ffcc00', // yellow
    '#88ccff', // light blue
    '#ff88ff', // pink
    '#ffffff', // white
    '#aaff00', // lime
  ]
  const color = colors[n % colors.length]
  const left = Math.random() * 100
  const top = Math.random() * 100
  const size = Math.random() * 3 + 1
  const duration = Math.random() * 5 + 3
  const delay = Math.random() * 4
  const opacity = Math.random() * 0.5 + 0.3
  return `left: ${left}%; top: ${top}%; width: ${size}px; height: ${size}px; background: ${color}; box-shadow: 0 0 ${size * 2}px ${color}; opacity: ${opacity}; animation-duration: ${duration}s; animation-delay: ${delay}s;`
}

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
          theme: 'dark',
          size: 'large',
          width: '240',
          text: 'continue_with',
          shape: 'rectangular'
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

const startGuest = async (shareConsent) => {
  loading.value = true
  try {
    const response = await axios.post('/api/guest/start', {
      share_consent: shareConsent
    })

    guestDialog.value = false

    snackbarMessage.value = shareConsent
      ? 'Guest mode started (sharing enabled).'
      : 'Guest mode started (private).'
    snackbarColor.value = 'success'
    showSnackbar.value = true

    const hasCharacter = response.data.has_character
    router.push(hasCharacter ? '/game' : '/character-creation')
  } catch (error) {
    const errorMsg = error.response?.data?.message || error.message || 'Failed to start guest mode'
    snackbarMessage.value = errorMsg
    snackbarColor.value = 'error'
    showSnackbar.value = true
  } finally {
    loading.value = false
  }
}

const openProfessionalRequest = () => {
  proRequest.value = {
    name: proRequest.value.name || '',
    email: proRequest.value.email || email.value || '',
    organization: proRequest.value.organization || '',
    message: proRequest.value.message || '',
  }
  professionalDialog.value = true
}

const submitProfessionalRequest = async () => {
  loading.value = true
  try {
    const res = await axios.post('/api/professional-account-requests', {
      name: proRequest.value.name,
      email: proRequest.value.email,
      organization: proRequest.value.organization || null,
      message: proRequest.value.message || null,
    })

    professionalDialog.value = false

    snackbarMessage.value = res.data?.message || 'Request submitted'
    snackbarColor.value = 'success'
    showSnackbar.value = true
  } catch (error) {
    const errorMsg =
      error.response?.data?.message ||
      (error.response?.data?.errors
        ? Object.values(error.response.data.errors).flat().join(' ')
        : null) ||
      error.message ||
      'Failed to submit request'

    snackbarMessage.value = errorMsg
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
/* ============================================
   GALAXY BACKGROUND - MOVING STARS
   ============================================ */
.starfield {
  background: transparent;
}

.star {
  animation: twinkle 4s ease-in-out infinite, drift 20s linear infinite;
}

.star.shooting {
  animation: shooting-star 3s ease-in-out infinite;
}

.star.fast {
  animation: twinkle 3s ease-in-out infinite, drift-fast 15s linear infinite;
}

.star.slow {
  animation: twinkle 5s ease-in-out infinite, drift-slow 30s linear infinite;
}

@keyframes twinkle {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 1; }
}

@keyframes drift {
  0% { transform: translateY(0) translateX(0); }
  100% { transform: translateY(30px) translateX(20px); }
}

@keyframes drift-fast {
  0% { transform: translateY(0) translateX(0); }
  100% { transform: translateY(50px) translateX(30px); }
}

@keyframes drift-slow {
  0% { transform: translateY(0) translateX(0); }
  100% { transform: translateY(20px) translateX(10px); }
}

@keyframes shooting-star {
  0% {
    transform: translateX(0) translateY(0);
    opacity: 1;
  }
  70% {
    opacity: 1;
  }
  100% {
    transform: translateX(300px) translateY(300px);
    opacity: 0;
  }
}

.nebula {
  background: 
    radial-gradient(ellipse at 20% 20%, rgba(138, 43, 226, 0.15) 0%, transparent 40%),
    radial-gradient(ellipse at 80% 80%, rgba(0, 255, 136, 0.1) 0%, transparent 40%),
    radial-gradient(ellipse at 60% 40%, rgba(0, 206, 209, 0.1) 0%, transparent 35%),
    radial-gradient(ellipse at 40% 70%, rgba(255, 0, 255, 0.08) 0%, transparent 35%);
  animation: nebula-drift 30s ease-in-out infinite;
}

@keyframes nebula-drift {
  0%, 100% { 
    transform: translateX(0) translateY(0);
    opacity: 0.8;
  }
  25% { 
    transform: translateX(20px) translateY(-10px);
    opacity: 1;
  }
  50% { 
    transform: translateX(-10px) translateY(20px);
    opacity: 0.9;
  }
  75% { 
    transform: translateX(-20px) translateY(-15px);
    opacity: 1;
  }
}

.grid-lines {
  background: 
    linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
    linear-gradient(0deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
  background-size: 60px 60px;
  animation: grid-scroll 20s linear infinite;
}

@keyframes grid-scroll {
  0% { background-position: 0 0; }
  100% { background-position: 60px 60px; }
}

.scanlines {
  background: repeating-linear-gradient(
    0deg,
    rgba(0, 0, 0, 0.04),
    rgba(0, 0, 0, 0.04) 1px,
    transparent 1px,
    transparent 3px
  );
}

/* ============================================
   TITLE - BIG WITH GLITCH ON LIFE
   ============================================ */
.game-title {
  letter-spacing: 0.1em;
  line-height: 1.2;
}

.title-u {
  color: #00ffcc;
  text-shadow: 0 0 30px #00ffcc, 0 0 60px #00ffcc, 4px 4px 0 #004444;
}

.title-colon {
  color: #ffffff;
  text-shadow: 3px 3px 0 #000;
}

.glitch-title {
  position: relative;
  color: #ff00ff;
  text-shadow: 0 0 30px #ff00ff, 0 0 60px #ff00ff, 4px 4px 0 #440044;
  animation: glitch-effect 2.5s infinite;
  display: inline-block;
}

.glitch-title::before,
.glitch-title::after {
  content: attr(data-text);
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.glitch-title::before {
  color: #00ffcc;
  animation: glitch-1 2.5s infinite;
  clip-path: polygon(0 0, 100% 0, 100% 30%, 0 30%);
  left: -2px;
}

.glitch-title::after {
  color: #00ccff;
  animation: glitch-2 2.5s infinite;
  clip-path: polygon(0 70%, 100% 70%, 100% 100%, 0 100%);
  left: 2px;
}

@keyframes glitch-effect {
  0%, 90%, 100% { 
    transform: translate(0);
    opacity: 1;
  }
  92% { 
    transform: translate(-3px, 1px);
    opacity: 0.8;
  }
  94% { 
    transform: translate(3px, -1px);
    opacity: 0.9;
  }
  96% { 
    transform: translate(-2px, 2px);
    opacity: 0.7;
  }
}

@keyframes glitch-1 {
  0%, 85%, 100% { 
    transform: translate(0);
    opacity: 0;
  }
  90% { 
    transform: translate(-4px, 1px);
    opacity: 0.8;
  }
}

@keyframes glitch-2 {
  0%, 85%, 100% { 
    transform: translate(0);
    opacity: 0;
  }
  92% { 
    transform: translate(4px, -1px);
    opacity: 0.8;
  }
}

.title-exclaim {
  color: #00ffcc;
  text-shadow: 0 0 30px #00ffcc, 4px 4px 0 #004444;
  animation: exclaim-glow 2s ease-in-out infinite;
}

@keyframes exclaim-glow {
  0%, 100% { text-shadow: 0 0 30px #00ffcc, 4px 4px 0 #004444; }
  50% { text-shadow: 0 0 50px #00ffcc, 0 0 80px #00ffcc, 4px 4px 0 #004444; }
}

.tagline {
  animation: tagline-shimmer 5s ease-in-out infinite;
}

@keyframes tagline-shimmer {
  0%, 100% { opacity: 0.8; }
  50% { opacity: 1; }
}

/* ============================================
   GALAXY CARD
   ============================================ */
.galaxy-card {
  background: rgba(20, 15, 35, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.5),
    0 0 60px rgba(138, 43, 226, 0.1),
    0 0 60px rgba(0, 255, 204, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  overflow: hidden;
}

.card-glow {
  background: 
    radial-gradient(ellipse at 30% 20%, rgba(138, 43, 226, 0.2) 0%, transparent 50%),
    radial-gradient(ellipse at 70% 80%, rgba(0, 255, 204, 0.15) 0%, transparent 50%);
}

.card-bg {
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.95) 0%, rgba(15, 15, 30, 0.95) 100%);
}

.card-border {
  border: 1px solid transparent;
  background: 
    linear-gradient(135deg, rgba(138, 43, 226, 0.4) 0%, transparent 40%, transparent 60%, rgba(0, 255, 204, 0.3) 100%) border-box,
    linear-gradient(225deg, rgba(0, 255, 204, 0.3) 0%, transparent 40%, transparent 60%, rgba(138, 43, 226, 0.4) 100%) border-box;
  background-origin: border-box;
  background-clip: padding-box, border-box;
}

/* ============================================
   CARD HEADER
   ============================================ */
.logo-img {
  filter: drop-shadow(0 0 12px rgba(0, 255, 204, 0.6)) drop-shadow(0 0 24px rgba(138, 43, 226, 0.3));
}

.card-title {
  text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
}

/* ============================================
   INPUT FIELDS
   ============================================ */
.input-wrapper {
  position: relative;
}

.galaxy-input :deep(.v-field) {
  background: rgba(10, 10, 25, 0.7) !important;
  border: 1px solid rgba(138, 43, 226, 0.3) !important;
  border-radius: 12px !important;
  transition: all 0.25s ease;
}

.galaxy-input :deep(.v-field:hover) {
  border-color: rgba(138,226, 0 43, .5) !important;
  background: rgba(10, 10, 25, 0.85) !important;
}

.galaxy-input :deep(.v-field--focused) {
  border-color: #00ffcc !important;
  background: rgba(10, 10, 25, 0.95) !important;
  box-shadow: 0 0 0 3px rgba(0, 255, 204, 0.15), 0 0 20px rgba(0, 255, 204, 0.1);
}

.galaxy-input :deep(input),
.galaxy-input :deep(.v-label) {
  font-family: 'VT323', monospace;
  font-size: 16px;
  color: #e0e0e0 !important;
  letter-spacing: 1px;
}

.galaxy-input :deep(.v-label) {
  color: #8888aa !important;
  font-size: 15px;
}

.galaxy-input :deep(input::placeholder) {
  color: #555577 !important;
}

.input-icon {
  color: #8888aa !important;
}

.eye-icon {
  color: #8888aa !important;
  transition: all 0.2s ease;
}

.eye-icon:hover {
  color: #00ffcc !important;
  filter: drop-shadow(0 0 5px #00ffcc);
}

/* ============================================
   REMEMBER & FORGOT
   ============================================ */
.remember-checkbox :deep(.v-label) {
  color: #8888aa !important;
  font-family: 'VT323', monospace;
  font-size: 14px;
}

.forgot-btn {
  font-size: 12px !important;
  color: #00ffcc !important;
  text-transform: none;
  font-family: 'VT323', monospace;
}

.forgot-btn:hover {
  text-decoration: underline;
  text-shadow: 0 0 10px #00ffcc;
}

/* ============================================
   LOGIN BUTTON
   ============================================ */
.login-btn {
  font-family: 'VT323', monospace !important;
  font-size: 18px !important;
  font-weight: 500;
  letter-spacing: 2px;
  text-align: center !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 50%, #00d4aa 100%) !important;
  color: #ffffff !important;
  border-radius: 12px !important;
  box-shadow: 
    0 4px 20px rgba(139, 92, 246, 0.3),
    0 0 30px rgba(0, 212, 170, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
  transition: all 0.25s ease;
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 
    0 6px 30px rgba(139, 92, 246, 0.4),
    0 0 40px rgba(0, 212, 170, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

.login-btn:active:not(:disabled) {
  transform: translateY(0);
}

.login-btn:disabled {
  background: #2a2a40 !important;
  color: #555577 !important;
  box-shadow: none;
}

/* ============================================
   DIVIDER
   ============================================ */
.divider span {
  font-family: 'Press Start 2P', monospace;
}

/* ============================================
   GOOGLE BUTTON
   ============================================ */
.google-wrapper {
  min-height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}

#google-signin-button {
  display: flex !important;
  justify-content: center;
  width: 100%;
}

#google-signin-button :deep(div) {
  margin: 0 !important;
}

#google-signin-button :deep(.g_id_signin) {
  display: flex;
  justify-content: center;
}

#google-signin-button :deep(.g_id_signin > div) {
  transform: scale(0.92);
}

/* ============================================
   SIGNUP LINK
   ============================================ */
.create-btn {
  font-size: 14px !important;
  color: #00ffcc !important;
  text-transform: none;
  font-weight: 500;
  font-family: 'VT323', monospace;
}

.create-btn:hover {
  text-decoration: underline;
  text-shadow: 0 0 10px #00ffcc;
}

/* ============================================
   SNACKBAR
   ============================================ */
.snackbar-text {
  font-family: 'VT323', monospace;
  font-size: 16px;
}

.floating-snackbar {
  --toast-glow: 0, 255, 204;
}

.floating-snackbar--error {
  --toast-glow: 239, 68, 68;
}

.floating-snackbar :deep(.v-snackbar__wrapper) {
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.94) 0%, rgba(10, 10, 25, 0.92) 100%) !important;
  border: 1px solid rgba(255, 255, 255, 0.12) !important;
  box-shadow:
    0 25px 80px rgba(0, 0, 0, 0.55),
    0 0 45px rgba(var(--toast-glow), 0.18);
  backdrop-filter: blur(18px);
  max-width: min(560px, calc(100vw - 24px));
}

.floating-snackbar :deep(.v-snackbar__content) {
  padding: 14px 16px !important;
}

.toast-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  text-align: center;
  font-family: 'VT323', monospace;
  font-size: 16px;
  letter-spacing: 0.04em;
  color: rgba(255, 255, 255, 0.92);
}

.toast-icon {
  color: rgb(var(--toast-glow)) !important;
  filter: drop-shadow(0 0 14px rgba(var(--toast-glow), 0.25));
}

.toast-text {
  line-height: 1.25;
}

.login-btn :deep(.v-btn__content) {
  width: 100%;
  justify-content: center;
}

/* ============================================
   VERSION
   ============================================ */
.version {
  font-family: 'Press Start 2P', monospace;
}

/* ============================================
   GUEST MODE
   ============================================ */
.guest-btn {
  border-color: rgba(0, 255, 204, 0.35) !important;
  color: rgba(0, 255, 204, 0.95) !important;
  font-family: 'VT323', monospace !important;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.guest-btn:hover {
  border-color: rgba(0, 255, 204, 0.6) !important;
  box-shadow: 0 0 18px rgba(0, 255, 204, 0.18);
}

.guest-dialog-card {
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.96) 0%, rgba(15, 15, 30, 0.96) 100%) !important;
  border: 1px solid rgba(255, 255, 255, 0.10) !important;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.55), 0 0 45px rgba(0, 255, 204, 0.10);
  overflow: hidden;
}

.guest-dialog-title {
  font-family: 'Press Start 2P', monospace !important;
  color: #ffffff !important;
  padding-top: 18px !important;
}

.guest-dialog-text {
  color: rgba(255, 255, 255, 0.8) !important;
  font-family: 'VT323', monospace;
  font-size: 1.05rem;
}

.guest-private-btn,
.guest-share-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.7rem !important;
  letter-spacing: 0.08em !important;
  border-radius: 12px !important;
}

.guest-private-btn {
  border-color: rgba(255, 255, 255, 0.22) !important;
}

.guest-share-btn {
  color: #00110d !important;
  box-shadow: 0 10px 28px rgba(0, 255, 204, 0.18);
}

.professional-link {
  color: rgba(0, 255, 204, 0.95) !important;
  font-family: 'VT323', monospace !important;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 380px) {
  .galaxy-card {
    margin: 0 -4px;
  }
  
  .card-header {
    padding-top: 20px;
    padding-bottom: 12px;
  }
  
  .card-body {
    padding: 20px;
  }
}
</style>

