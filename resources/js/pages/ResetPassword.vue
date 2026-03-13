<template>
  <div
    class="galaxy-screen relative min-h-screen overflow-hidden"
    style="background: linear-gradient(135deg, #0d0d1a 0%, #1a0a2e 30%, #0a1a2e 50%, #0a1a1a 70%, #0d0d1a 100%);"
  >
    <div class="starfield pointer-events-none fixed inset-0 z-0">
      <div v-for="n in 80" :key="`star-${n}`" class="star absolute rounded-full" :style="getStarStyle(n)"></div>
    </div>
    <div class="nebula pointer-events-none fixed inset-0 z-5"></div>
    <div class="grid-lines pointer-events-none fixed inset-0 z-10"></div>
    <div class="scanlines pointer-events-none fixed inset-0 z-25"></div>

    <v-container fluid class="relative z-20 flex items-center justify-center min-h-screen px-3 py-6">
      <div class="w-full max-w-sm flex flex-col items-center">
        <div class="title-wrapper mb-8 text-center">
          <div class="title-container inline-block">
            <h1
              class="game-title relative uppercase whitespace-nowrap"
              style="font-family: 'Press Start 2P', monospace; font-size: clamp(2.2rem, 10vw, 5.4rem); text-align: center; display: block;"
            >
              <span class="title-u">U</span><span class="title-colon">:</span><span class="glitch-title" data-text="LIFE">LIFE</span><span class="title-exclaim">!</span>
            </h1>
          </div>
          <p
            class="tagline mt-4 text-cyan-300 text-center text-[10px] md:text-[12px] uppercase tracking-[0.35em]"
            style="font-family: 'VT323', monospace; font-size: 18px;"
          >
            Set New Password
          </p>
        </div>

        <v-form class="w-full" @submit.prevent="resetPassword">
          <div class="galaxy-card relative">
            <div class="card-glow absolute inset-0 rounded-2xl blur-3xl"></div>
            <div class="card-bg absolute inset-0 rounded-2xl"></div>
            <div class="card-border absolute inset-0 rounded-2xl"></div>

            <div class="card-header relative pt-6 pb-4 px-6 text-center">
              <div class="logo-wrapper mb-4 flex justify-center">
                <v-img src="/css/images/ulife1.png" alt="U:LIFE" max-width="55" contain class="logo-img" />
              </div>
              <h2 class="card-title text-white text-lg font-medium tracking-wide" style="font-family: 'VT323', monospace; font-size: 24px;">
                Reset Password
              </h2>
              <p class="text-white/50 text-xs mt-1" style="font-family: 'VT323', monospace; font-size: 14px;">
                Choose a strong password to continue
              </p>
            </div>

            <div class="card-body relative px-6 pb-6">
              <div class="space-y-3">
                <div class="input-wrapper">
                  <v-text-field
                    v-model="email"
                    label="Email Address"
                    variant="outlined"
                    density="comfortable"
                    class="galaxy-input"
                    type="email"
                    hide-details="auto"
                    bg-color="transparent"
                    :disabled="loading"
                  >
                    <template #prepend-inner>
                      <v-icon size="small" class="input-icon">mdi-email-outline</v-icon>
                    </template>
                  </v-text-field>
                </div>

                <div class="input-wrapper">
                  <v-text-field
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    label="New Password"
                    variant="outlined"
                    density="comfortable"
                    class="galaxy-input"
                    hide-details="auto"
                    bg-color="transparent"
                    :disabled="loading"
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

                <div class="input-wrapper">
                  <v-text-field
                    v-model="confirmPassword"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    label="Confirm Password"
                    variant="outlined"
                    density="comfortable"
                    class="galaxy-input"
                    hide-details="auto"
                    bg-color="transparent"
                    :disabled="loading"
                  >
                    <template #prepend-inner>
                      <v-icon size="small" class="input-icon">mdi-lock-check-outline</v-icon>
                    </template>
                    <template #append-inner>
                      <v-icon @click="showConfirmPassword = !showConfirmPassword" class="eye-icon cursor-pointer">
                        {{ showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off' }}
                      </v-icon>
                    </template>
                  </v-text-field>
                </div>

                <v-btn :disabled="!canReset || loading" class="login-btn w-full py-5" type="submit">
                  <span class="flex items-center justify-center gap-2 w-full text-center">
                    <v-icon size="small">mdi-lock-reset</v-icon>
                    {{ loading ? 'Resetting...' : 'Reset Password' }}
                  </span>
                </v-btn>
              </div>

              <div class="mt-5 text-center">
                <v-btn variant="text" class="back-link px-1" :disabled="loading" @click="goToHome">
                  <v-icon start size="16">mdi-arrow-left</v-icon>
                  Back to Login
                </v-btn>
              </div>
            </div>
          </div>
        </v-form>
      </div>
    </v-container>

    <v-snackbar
      v-model="showSnackbar"
      location="center"
      rounded="xl"
      timeout="4500"
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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const seededRandom = (seed) => {
  const x = Math.sin(seed) * 10000
  return x - Math.floor(x)
}

const getStarStyle = (n) => {
  const colors = [
    '#00ffcc',
    '#ff00ff',
    '#00ccff',
    '#8b5cf6',
    '#ffffff',
    '#00d4aa',
  ]

  const color = colors[n % colors.length]
  const left = seededRandom(n * 17.13) * 100
  const top = seededRandom(n * 31.77) * 100
  const size = seededRandom(n * 7.91) * 2.6 + 1
  const duration = seededRandom(n * 11.03) * 5 + 3
  const delay = seededRandom(n * 23.41) * 4
  const opacity = seededRandom(n * 5.37) * 0.55 + 0.25

  return {
    left: `${left}%`,
    top: `${top}%`,
    width: `${size}px`,
    height: `${size}px`,
    background: color,
    boxShadow: `0 0 ${size * 2}px ${color}`,
    opacity,
    animationDuration: `${duration}s`,
    animationDelay: `${delay}s`,
  }
}

// Get token from URL query params
const token = ref(route.query.token || '')

const canReset = computed(() => {
  return email.value && password.value && confirmPassword.value && password.value === confirmPassword.value && password.value.length >= 5
})

const resetPassword = async () => {
  loading.value = true

  try {
    const response = await axios.post('/reset-password', {
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
      token: token.value
    })

    snackbarMessage.value = response.data.message || 'Password reset successfully!'
    snackbarColor.value = 'success'
    showSnackbar.value = true

    setTimeout(() => {
      router.push('/home')
    }, 2000)
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Failed to reset password'
    snackbarMessage.value = errorMsg
    snackbarColor.value = 'error'
    showSnackbar.value = true
  } finally {
    loading.value = false
  }
}

const goToHome = () => {
  router.push('/home')
}
</script>

<style scoped>
.galaxy-screen {
  color: #e0e0e0;
  min-height: 100vh;
}

.star {
  animation: twinkle 4s ease-in-out infinite, drift 20s linear infinite;
}

@keyframes twinkle {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 1; }
}

@keyframes drift {
  0% { transform: translateY(0) translateX(0); }
  100% { transform: translateY(30px) translateX(20px); }
}

.nebula {
  background:
    radial-gradient(ellipse at 20% 20%, rgba(139, 92, 246, 0.18) 0%, transparent 40%),
    radial-gradient(ellipse at 80% 80%, rgba(0, 212, 170, 0.10) 0%, transparent 40%),
    radial-gradient(ellipse at 60% 40%, rgba(0, 206, 209, 0.10) 0%, transparent 35%),
    radial-gradient(ellipse at 40% 70%, rgba(255, 0, 255, 0.08) 0%, transparent 35%);
  animation: nebula-drift 30s ease-in-out infinite;
}

@keyframes nebula-drift {
  0%, 100% { transform: translateX(0) translateY(0); opacity: 0.8; }
  25% { transform: translateX(20px) translateY(-10px); opacity: 1; }
  50% { transform: translateX(-10px) translateY(20px); opacity: 0.9; }
  75% { transform: translateX(-20px) translateY(-15px); opacity: 1; }
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
  0%, 90%, 100% { transform: translate(0); opacity: 1; }
  92% { transform: translate(-3px, 1px); opacity: 0.8; }
  94% { transform: translate(3px, -1px); opacity: 0.9; }
  96% { transform: translate(-2px, 2px); opacity: 0.7; }
}

@keyframes glitch-1 {
  0%, 85%, 100% { transform: translate(0); opacity: 0; }
  90% { transform: translate(-4px, 1px); opacity: 0.8; }
}

@keyframes glitch-2 {
  0%, 85%, 100% { transform: translate(0); opacity: 0; }
  92% { transform: translate(4px, -1px); opacity: 0.8; }
}

.title-exclaim {
  color: #00ffcc;
  text-shadow: 0 0 30px #00ffcc, 4px 4px 0 #004444;
}

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

.logo-img {
  filter: drop-shadow(0 0 12px rgba(0, 255, 204, 0.6)) drop-shadow(0 0 24px rgba(138, 43, 226, 0.3));
}

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
  border-color: rgba(138, 43, 226, 0.5) !important;
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

.login-btn {
  font-family: 'VT323', monospace !important;
  font-size: 18px !important;
  font-weight: 500;
  letter-spacing: 2px;
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
}

.login-btn:disabled {
  background: #2a2a40 !important;
  color: #555577 !important;
  box-shadow: none;
}

.back-link {
  font-size: 14px !important;
  color: #00ffcc !important;
  text-transform: none;
  font-weight: 500;
  font-family: 'VT323', monospace;
}

.back-link:hover {
  text-decoration: underline;
  text-shadow: 0 0 10px #00ffcc;
}

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
</style>