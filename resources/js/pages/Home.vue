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

    <!-- Bottom Left Links - How to Play, User Agreement, Privacy Policy -->
    <div class="fixed bottom-4 left-4 z-50 flex flex-col gap-2">
      <v-btn 
        variant="text" 
        size="small" 
        class="footer-link-btn"
        @click="showHowToPlay = true"
      >
        <v-icon left size="small">mdi-help-circle-outline</v-icon>
        How to Play
      </v-btn>
      <div class="flex gap-3 ml-1">
        <router-link to="/user-agreement" class="footer-link text-cyan-300/60 hover:text-cyan-300">
          User Agreement
        </router-link>
        <span class="text-cyan-300/40">|</span>
        <router-link to="/privacy-policy" class="footer-link text-cyan-300/60 hover:text-cyan-300">
          Privacy Policy
        </router-link>
      </div>
    </div>

    <!-- Bottom Right - Contact / Feedback -->
    <div class="fixed bottom-4 right-4 z-50">
      <v-btn 
        variant="text" 
        size="small" 
        class="footer-link-btn"
        @click="showFeedbackDialog = true"
      >
        <v-icon left size="small">mdi-message-text-outline</v-icon>
        Contact / Feedback
      </v-btn>
    </div>
  </div>

  <!-- Retro Pixel Feedback Dialog - Similar to How to Play -->
  <v-dialog v-model="showFeedbackDialog" max-width="800" max-height="90vh" persistent rounded="0" content-class="feedback-pixel-dialog">
    <v-card class="retro-pixel-feedback" style="min-height: 550px; max-height: 90vh; overflow-y: auto; image-rendering: pixelated;">
      <!-- CRT Glow Overlay -->
      <div class="fbp-glow" aria-hidden="true"></div>
      <div class="fbp-scanlines" aria-hidden="true"></div>
      <div class="fbp-vignette" aria-hidden="true"></div>
      
      <!-- Header -->
      <v-card-title class="fbp-title">
        <div style="font-size: clamp(1.5rem, 4vw, 2.5rem); line-height: 1.1; padding: 16px 0; text-align: center; width: 100%;">
          SEND FEEDBACK
          <div style="font-size: 2.5rem; margin: 0.2em 0; animation: fbp-bounce 2s infinite;">💬</div>
        </div>
      </v-card-title>
      
      <v-card-subtitle class="fbp-subtitle mb-4 text-center">
        <span>TELL US WHAT YOU THINK</span>
        <div style="font-size: 2rem; margin-top: 8px;">⭐</div>
      </v-card-subtitle>
      
      <!-- Form Content -->
      <v-card-text class="fbp-form-container">
        <div class="feedback-form-grid">
          <div class="form-field-card" style="--field-delay: 0s">
            <div class="field-icon">👤</div>
            <div class="field-title">NAME (OPTIONAL)</div>
            <v-text-field
              v-model="feedbackName"
              variant="underlined"
              density="compact"
              hide-details
              placeholder="Your name"
              class="pixel-input"
            ></v-text-field>
          </div>
          
          <div class="form-field-card" style="--field-delay: 0.1s">
            <div class="field-icon">📧</div>
            <div class="field-title">EMAIL (OPTIONAL)</div>
            <v-text-field
              v-model="feedbackEmail"
              type="email"
              variant="underlined"
              density="compact"
              hide-details
              placeholder="your@email.com"
              class="pixel-input"
            ></v-text-field>
          </div>
          
          <div class="form-field-card full-width" style="--field-delay: 0.2s">
            <div class="field-icon">🏷️</div>
            <div class="field-title">FEEDBACK TYPE</div>
            <v-select
              v-model="feedbackType"
              :items="['Bug Report', 'Feature Suggestion', 'General Feedback', 'Other']"
              variant="underlined"
              density="compact"
              hide-details
              class="pixel-input"
            ></v-select>
          </div>
          
          <div class="form-field-card full-width" style="--field-delay: 0.3s">
            <div class="field-icon">💭</div>
            <div class="field-title">YOUR MESSAGE *</div>
            <v-textarea
              v-model="feedbackMessage"
              variant="underlined"
              rows="6"
              auto-grow
              hide-details
              placeholder="Share your thoughts about U:LIFE..."
              class="pixel-input"
            ></v-textarea>
          </div>
        </div>
        
        <div class="fbp-tip mt-6 p-4">
          <span class="tip-icon">💡</span>
          <span class="tip-text">Your feedback helps us improve the game!</span>
        </div>
      </v-card-text>
      
      <!-- Actions -->
      <v-card-actions class="fbp-actions justify-center pb-6">
        <v-btn 
          variant="outlined"
          color="grey-darken-2"
          class="fbp-cancel-btn mr-4"
          @click="showFeedbackDialog = false"
        >
          <v-icon left>mdi-close</v-icon>
          Cancel
        </v-btn>
        <v-btn 
          size="x-large"
          variant="elevated"
          color="amber-darken-2"
          class="fbp-send-btn"
          :loading="feedbackLoading"
          @click="submitFeedback"
        >
          <v-icon left>mdi-send</v-icon>
          Send Feedback
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- How to Play Dialog -->
  <v-dialog v-model="showHowToPlay" max-width="800" max-height="90vh" persistent rounded="0" content-class="howtoplay-dialog-content">
    <v-card class="retro-pixel-howtoplay" style="min-height: 550px; max-height: 90vh; overflow-y: auto; image-rendering: pixelated;">
      <!-- CRT Glow Overlay -->
      <div class="htp-glow" aria-hidden="true"></div>
      <div class="htp-scanlines" aria-hidden="true"></div>
      <div class="htp-vignette" aria-hidden="true"></div>
      
      <!-- Header -->
      <v-card-title class="htp-title">
        <div style="font-size: clamp(1.5rem, 4vw, 2.5rem); line-height: 1.1; padding: 16px 0; text-align: center; width: 100%;">
          HOW TO PLAY
          <div style="font-size: 2.5rem; margin: 0.2em 0; animation: htp-bounce 2s infinite;">🎮</div>
        </div>
      </v-card-title>
      
      <v-card-subtitle class="htp-subtitle mb-4 text-center">
        <span>MASTER THE GAME OF LIFE</span>
        <div style="font-size: 2rem; margin-top: 8px;">⭐</div>
      </v-card-subtitle>
      
      <!-- Steps Grid -->
      <v-card-text class="htp-steps-container">
        <div class="steps-grid">
          <div class="step-card" style="--step-delay: 0s">
            <div class="step-number">1</div>
            <div class="step-icon">👤</div>
            <div class="step-title">CREATE CHARACTER</div>
            <div class="step-desc">Choose name, gender & age group!</div>
          </div>
          <div class="step-card" style="--step-delay: 0.1s">
            <div class="step-number">2</div>
            <div class="step-icon">🎯</div>
            <div class="step-title">MAKE DECISIONS</div>
            <div class="step-desc">Each choice impacts your stats!</div>
          </div>
          <div class="step-card" style="--step-delay: 0.2s">
            <div class="step-number">3</div>
            <div class="step-icon">📊</div>
            <div class="step-title">MANAGE STATS</div>
            <div class="step-desc">Balance Health, Happiness, Intelligence & Wealth!</div>
          </div>
          <div class="step-card" style="--step-delay: 0.3s">
            <div class="step-number">4</div>
            <div class="step-icon">🎂</div>
            <div class="step-title">AGE & EVENTS</div>
            <div class="step-desc">Experience life events from childhood to retirement!</div>
          </div>
          <div class="step-card" style="--step-delay: 0.4s">
            <div class="step-number">5</div>
            <div class="step-icon">💼</div>
            <div class="step-title">BUILD CAREER</div>
            <div class="step-desc">Choose your profession path wisely!</div>
          </div>
          <div class="step-card" style="--step-delay: 0.5s">
            <div class="step-number">6</div>
            <div class="step-icon">✨</div>
            <div class="step-title">TALENTS & SKILLS</div>
            <div class="step-desc">Discover unique talents and skills!</div>
          </div>
          <div class="step-card" style="--step-delay: 0.6s">
            <div class="step-number">7</div>
            <div class="step-icon">📈</div>
            <div class="step-title">TRACK PROGRESS</div>
            <div class="step-desc">View analytics to see your journey!</div>
          </div>
          <div class="step-card" style="--step-delay: 0.7s">
            <div class="step-number">8</div>
            <div class="step-icon">🔄</div>
            <div class="step-title">PLAY AGAIN</div>
            <div class="step-desc">Try different choices and discover possibilities!</div>
          </div>
        </div>
        
        <div class="htp-tip mt-6 p-4">
          <span class="tip-icon">💡</span>
          <span class="tip-text">TIP: Every choice creates a unique story!</span>
        </div>
      </v-card-text>
      
      <!-- Close Button -->
      <v-card-actions class="htp-actions justify-center pb-6">
        <v-btn 
          size="x-large"
          variant="elevated"
          color="amber-darken-2"
          class="htp-start-btn"
          @click="showHowToPlay = false"
          style="font-size: 1.2rem; padding: 16px 50px; min-width: 220px;"
        >
          <v-icon left>mdi-play</v-icon>
          LET'S PLAY!
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>


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

  <!-- Normal Player Consent Dialog -->
  <v-dialog v-model="normalConsentDialog" max-width="520" rounded="xl">
    <v-card class="guest-dialog-card">
      <v-card-title class="guest-dialog-title text-center">
        <span class="glitch-title" data-text="SHARE YOUR DATA?">SHARE YOUR DATA?</span>
      </v-card-title>
      <v-card-text class="guest-dialog-text text-center">
        <p class="mb-3">Help improve U:LIFE!</p>
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
          @click="startNormalConsent(false)"
        >
          Play Private
        </v-btn>
        <v-btn
          variant="elevated"
          color="#00ffcc"
          class="guest-share-btn"
          :disabled="loading"
          @click="startNormalConsent(true)"
        >
          Play & Share
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- User Agreement / Terms of Service Dialog - NEW -->
  <v-dialog v-model="showTermsDialog" persistent fullscreen hide-overlay>
    <v-card class="terms-dialog-card" style="height: 100vh; backdrop-filter: blur(5px);">
      <!-- Pixel Scanlines -->
      <div class="terms-scanlines absolute inset-0 pointer-events-none z-10"></div>
      
      <div class="terms-container h-full flex flex-col">
        <!-- Header -->
        <div class="terms-header p-6 border-b border-white/10 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <v-icon size="32" color="#00ffcc" class="terms-icon">mdi-file-document-check-outline</v-icon>
            <span class="glitch-title terms-title" data-text="TERMS OF SERVICE" style="font-family: 'Press Start 2P', monospace; font-size: 1.4rem;">TERMS OF SERVICE</span>
          </div>
          <v-btn icon @click="showTermsDialog = false" size="small" variant="text" class="close-btn">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
        
        <!-- Scrollable Content -->
        <div class="terms-content flex-1 overflow-y-auto p-6 space-y-4 text-sm leading-relaxed" style="font-family: 'VT323', monospace; font-size: 14px; scrollbar-width: thin;">
          <div>
            <h3 class="text-lg font-bold mb-3 text-cyan-300" style="font-family: 'Press Start 2P', monospace;">1. Introduction</h3>
            <p>Welcome to U:LIFE! These Terms of Service ("Terms") govern your access to and use of U:LIFE, a life simulation game. By accessing or using the Service, you agree to be bound by these Terms.</p>
          </div>
          
          <div>
            <h3 class="text-lg font-bold mb-3 text-cyan-300" style="font-family: 'Press Start 2P', monospace;">2. Eligibility</h3>
            <p>You must be at least 13 years old to use U:LIFE. By using the Service, you represent that you meet this requirement.</p>
          </div>
          
          <div>
            <h3 class="text-lg font-bold mb-3 text-cyan-300" style="font-family: 'Press Start 2P', monospace;">3. Account & Guest Mode</h3>
            <p>Keep your account secure. Guest mode is anonymous. You may opt-in to share gameplay data via consent prompts.</p>
          </div>
          
          <div>
            <h3 class="text-lg font-bold mb-3 text-cyan-300" style="font-family: 'Press Start 2P', monospace;">4. User Conduct</h3>
            <p>Do not cheat, harass, or upload harmful content. We may terminate violating accounts.</p>
          </div>
          
          <div>
            <h3 class="text-lg font-bold mb-3 text-cyan-300" style="font-family: 'Press Start 2P', monospace;">5. Data Privacy</h3>
            <p>Guest data is anonymous if private. Shared data helps improve simulations (aggregated, no personal IDs). See our <a href="#" class="terms-link hover:underline">Privacy Policy</a>.</p>
          </div>
          
          <div>
            <h3 class="text-lg font-bold mb-3 text-cyan-300" style="font-family: 'Press Start 2P', monospace;">6. Intellectual Property</h3>
            <p>U:LIFE and its simulations are proprietary. You may not copy or reverse-engineer.</p>
          </div>
          
          <div>
            <h3 class="text-lg font-bold mb-3 text-cyan-300" style="font-family: 'Press Start 2P', monospace;">7. Disclaimers</h3>
            <p>U:LIFE is fiction/entertainment. Not real life/professional advice. Life outcomes vary.</p>
          </div>
          
          <div>
            <h3 class="text-lg font-bold mb-3 text-cyan-300" style="font-family: 'Press Start 2P', monospace;">8. Termination</h3>
            <p>We may suspend/terminate access for violations. You may delete your account anytime.</p>
          </div>
          
          <div>
            <h3 class="text-lg font-bold mb-3 text-cyan-300" style="font-family: 'Press Start 2P', monospace;">9. Governing Law</h3>
            <p>These Terms governed by laws of [Your Jurisdiction].</p>
          </div>
          
          <div>
            <p class="text-xs text-white/60 mb-4">Last updated: {{ new Date().toLocaleDateString() }}</p>
            <v-checkbox 
              v-model="termsAccepted" 
              label="I agree to the Terms of Service" 
              color="#00ffcc"
              hide-details
              density="compact"
              class="terms-checkbox"
            ></v-checkbox>
          </div>
        </div>
        
        <!-- Footer Actions -->
        <div class="terms-footer p-6 border-t border-white/10 bg-black/20">
          <div class="flex gap-3 justify-end">
            <v-btn variant="outlined" color="white" @click="showTermsDialog = false" :disabled="loading" size="large">
              Decline
            </v-btn>
            <v-btn 
              color="#00ffcc" 
              variant="elevated" 
              @click="acceptTerms" 
              :disabled="!termsAccepted || loadingTerms"
              size="large"
              :loading="loadingTerms"
            >
              Accept & Continue
            </v-btn>
          </div>
        </div>
      </div>
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

        <div class="space-y-6">
          <v-text-field
            v-model="proRequest.name"
            label="Full Name *"
            variant="outlined"
            density="comfortable"
            class="galaxy-input"
            hide-details="auto"
            bg-color="transparent"
            :disabled="loading"
          />

          <v-text-field
            v-model="proRequest.email"
            label="Email Address *"
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

          <v-text-field
            v-model="proRequest.desired_password"
            :type="showPassword ? 'text' : 'password'"
            label="Desired Password *"
            variant="outlined"
            density="comfortable"
            class="galaxy-input"
            hide-details="auto"
            bg-color="transparent"
            :disabled="loading"
          >
            <template #append-inner>
              <v-icon @click="showPassword = !showPassword" class="cursor-pointer pa-1">
                {{ showPassword ? 'mdi-eye-off' : 'mdi-eye' }}
              </v-icon>
            </template>
          </v-text-field>

          <v-text-field
            v-model="proRequest.confirm_password"
            :type="showConfirmPassword ? 'text' : 'password'"
            label="Confirm Password *"
            variant="outlined"
            density="comfortable"
            class="galaxy-input"
            hide-details="auto"
            bg-color="transparent"
            :disabled="loading"
          >
            <template #append-inner>
              <v-icon @click="showConfirmPassword = !showConfirmPassword" class="cursor-pointer pa-1">
                {{ showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye' }}
              </v-icon>
            </template>
          </v-text-field>

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

  <!-- Welcome Back Popup - Retro Pixel Design -->
  <v-dialog v-model="showWelcomeDialog" max-width="520" rounded="xl">
    <v-card class="welcome-dialog-card">
      <!-- Pixel Scanlines Overlay -->
      <div class="welcome-scanlines absolute inset-0 pointer-events-none z-10"></div>
      
      <v-card-title class="welcome-dialog-title text-center relative z-20">
        <span class="welcome-glitch-title" data-text="WELCOME">{{ welcomeMessage }}</span>
      </v-card-title>
      
      <v-card-text class="welcome-dialog-text text-center relative z-20">
        <div class="welcome-pixel-art mb-4 mx-auto">
          <div class="pixel-heart"></div>
        </div>
        <p class="welcome-subtitle">Your life simulation awaits...</p>
      </v-card-text>
      
      <v-card-actions class="justify-center pb-6 px-6 relative z-20">
        <v-btn
          variant="elevated"
          color="#00ffcc"
          class="welcome-enter-btn"
          @click="closeWelcome"
        >
          Enter U:LIFE
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
const normalConsentDialog = ref(false)
const professionalDialog = ref(false)
const showHowToPlay = ref(false)

// Feedback dialog state
const showFeedbackDialog = ref(false)
const feedbackName = ref('')
const feedbackEmail = ref('')
const feedbackType = ref('General Feedback')
const feedbackMessage = ref('')
const feedbackLoading = ref(false)
const proRequest = ref({
  name: '',
  email: '',
  organization: '',
  message: '',
  desired_password: '',
  confirm_password: '',
})

const showProPassword = ref(false)
const showConfirmPassword = ref(false)

// User Agreement state - NEW
const showTermsDialog = ref(false)
const termsAccepted = ref(false)
const loadingTerms = ref(false)

// Welcome popup state
const showWelcomeDialog = ref(false)

const currentUser = ref(null)
const welcomeHasCharacter = ref(false)
const welcomeMessage = computed(() => {
  if (!currentUser.value) return ''
  const name = currentUser.value.name || 'Player'
  if (currentUser.value.is_guest) {
    return `Welcome, Guest${'###'.repeat(Math.floor(Math.random() * 3) + 1)}!`
  }
  return `Welcome back, ${name}!`
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
    
    // Set welcome data
    currentUser.value = res.data.user
    welcomeHasCharacter.value = res.data.has_character

    if (currentUser.value.is_guest) {
      showWelcomeDialog.value = true
    } else {
      normalConsentDialog.value = true
    }
    
    successMessage.value = 'Successfully logged in with Google!'
    snackbarMessage.value = 'Successfully logged in with Google!'
    snackbarColor.value = 'success'
    showSnackbar.value = true
    
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

// Check terms acceptance on mount
const checkTermsAcceptance = () => {
  const accepted = localStorage.getItem('ulife_terms_accepted_v1')
  if (accepted !== 'true') {
    showTermsDialog.value = true
  }
}

// Submit Feedback
const submitFeedback = async () => {
  if (!feedbackMessage.value.trim()) {
    snackbarMessage.value = 'Please enter your message'
    snackbarColor.value = 'error'
    showSnackbar.value = true
    return
  }
  
  feedbackLoading.value = true
  
  try {
    await axios.post('/api/feedback', {
      name: feedbackName.value,
      email: feedbackEmail.value,
      type: feedbackType.value,
      message: feedbackMessage.value
    })
    
    snackbarMessage.value = 'Thank you! Your feedback has been sent.'
    snackbarColor.value = 'success'
    showSnackbar.value = true
    
    // Reset form
    feedbackName.value = ''
    feedbackEmail.value = ''
    feedbackType.value = 'General Feedback'
    feedbackMessage.value = ''
    showFeedbackDialog.value = false
  } catch (error) {
    snackbarMessage.value = error.response?.data?.message || 'Failed to send feedback'
    snackbarColor.value = 'error'
    showSnackbar.value = true
  } finally {
    feedbackLoading.value = false
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

    // Set welcome data
    currentUser.value = response.data.user
    welcomeHasCharacter.value = response.data.has_character

    if (currentUser.value.is_guest) {
      showWelcomeDialog.value = true
    } else {
      normalConsentDialog.value = true
    }

    snackbarMessage.value = 'Successfully logged in!'
    snackbarColor.value = 'success'
    showSnackbar.value = true

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

    // Set welcome data
    currentUser.value = response.data.user
    welcomeHasCharacter.value = response.data.has_character

    snackbarMessage.value = shareConsent
      ? 'Guest mode started (sharing enabled).'
      : 'Guest mode started (private).'
    snackbarColor.value = 'success'
    showSnackbar.value = true
    showWelcomeDialog.value = true

  } catch (error) {
    const errorMsg = error.response?.data?.message || error.message || 'Failed to start guest mode'
    snackbarMessage.value = errorMsg
    snackbarColor.value = 'error'
    showSnackbar.value = true
  } finally {
    loading.value = false
  }
}

const startNormalConsent = async (shareConsent) => {
  loading.value = true
  try {
    const response = await axios.post('/api/consent', {
      share_consent: shareConsent
    })

    normalConsentDialog.value = false

    snackbarMessage.value = shareConsent
      ? 'Data sharing enabled.'
      : 'Private mode enabled.'
    snackbarColor.value = 'success'
    showSnackbar.value = true
    showWelcomeDialog.value = true

  } catch (error) {
    const errorMsg = error.response?.data?.message || error.message || 'Failed to set consent'
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
  if (proRequest.value.desired_password !== proRequest.value.confirm_password) {
    snackbarMessage.value = 'Passwords do not match'
    snackbarColor.value = 'error'
    showSnackbar.value = true
    return
  }

  loading.value = true
  try {
    const res = await axios.post('/api/professional-account-requests', {
      name: proRequest.value.name,
      email: proRequest.value.email,
      desired_password: proRequest.value.desired_password,
      desired_password_confirmation: proRequest.value.confirm_password,
      organization: proRequest.value.organization || null,
      message: proRequest.value.message || null,
    })

    professionalDialog.value = false
    proRequest.value = { name: '', email: '', organization: '', message: '', desired_password: '', confirm_password: '' }

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

const closeWelcome = () => {
  showWelcomeDialog.value = false
  if (currentUser.value && welcomeHasCharacter.value !== null) {
    const redirectPath = welcomeHasCharacter.value ? '/game' : '/character-creation'
    setTimeout(() => {
      router.push(redirectPath)
    }, 300)
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
   FOOTER LINKS
   ============================================ */
.footer-link-btn {
  font-family: 'VT323', monospace !important;
  font-size: 14px !important;
  color: #67e8f9 !important;
  text-transform: none !important;
  letter-spacing: 0.5px !important;
  opacity: 0.7;
  transition: all 0.3s ease;
}

.footer-link-btn:hover {
  opacity: 1;
  color: #22d3ee !important;
}

.footer-link {
  font-family: 'VT323', monospace;
  font-size: 14px;
  text-decoration: none;
  transition: all 0.3s ease;
}

.footer-link:hover {
  text-decoration: underline;
}

/* ============================================
   RETRO PIXEL FEEDBACK DIALOG
   ============================================ */
/* ============================================
   RETRO PIXEL FEEDBACK DIALOG - LIKE HOW TO PLAY
   ============================================ */
.feedback-pixel-dialog {
  background: transparent !important;
  box-shadow: none !important;
}

.retro-pixel-feedback {
  background: 
    linear-gradient(170deg, #1a1a0a 0%, #0f0f05 50%, #080805 100%),
    #111;
  border: 4px solid #ffaa00 !important;
  box-shadow: 
    inset 0 0 0 2px rgba(255,255,255,0.1),
    0 0 0 2px #ffaa00,
    0 40px 120px rgba(255,170,0,0.35),
    0 0 80px rgba(255,170,0,0.25),
    inset 0 0 40px rgba(0,0,0,0.8);
  font-family: 'Press Start 2P', monospace !important;
  text-rendering: optimizeSpeed;
  image-rendering: pixelated;
  position: relative;
  overflow: hidden;
}

/* Glow Effects */
.fbp-glow {
  position: absolute;
  inset: -50px;
  background: 
    radial-gradient(ellipse at 30% 20%, rgba(255,200,50,0.25) 0%, transparent 50%),
    radial-gradient(ellipse at 70% 70%, rgba(255,150,0,0.2) 0%, transparent 60%),
    radial-gradient(circle at center, rgba(255,180,0,0.15) 0%, transparent 70%);
  filter: blur(40px);
  z-index: 0;
  pointer-events: none;
  animation: fbp-glow-pulse 3s ease-in-out infinite;
}

@keyframes fbp-glow-pulse {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.05); }
}

.fbp-scanlines {
  position: absolute;
  inset: 0;
  background: 
    repeating-linear-gradient(0deg, rgba(255,180,0,0.06), rgba(255,180,0,0.06) 1px, transparent 1px, transparent 2px),
    repeating-linear-gradient(90deg, rgba(200,150,0,0.04), rgba(200,150,0,0.04) 2px, transparent 2px, transparent 4px);
  z-index: 1;
  pointer-events: none;
  mix-blend-mode: overlay;
}

.fbp-vignette {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at center, transparent 40%, rgba(0,0,0,0.6) 85%);
  z-index: 1;
  pointer-events: none;
}

/* Title */
.fbp-title {
  position: relative;
  z-index: 2;
  text-align: center;
  justify-content: center;
  padding: 20px 16px 10px !important;
  background: linear-gradient(180deg, rgba(255,180,0,0.15), transparent);
  color: #ffcc00 !important;
  text-shadow: 
    0 0 20px #ffaa00,
    3px 0 0 #000, -3px 0 0 #ffaa00,
    0 3px 0 #000, 0 -3px 0 #ffaa00,
    2px 2px 0 rgba(255,170,0,0.5) !important;
  animation: fbp-title-glow 2s ease-in-out infinite alternate;
}

@keyframes fbp-title-glow {
  from { text-shadow: 0 0 20px #ffaa00, 3px 0 0 #000, -3px 0 0 #ffaa00, 0 3px 0 #000, 0 -3px 0 #ffaa00; }
  to { text-shadow: 0 0 40px #ffdd00, 3px 0 0 #000, -3px 0 0 #ffaa00, 0 3px 0 #000, 0 -3px 0 #ffaa00, 0 0 60px rgba(255,200,0,0.5); }
}

@keyframes fbp-bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.fbp-subtitle {
  position: relative;
  z-index: 2;
  font-size: 0.75rem !important;
  color: #ffcc66 !important;
  text-shadow: 2px 2px 0 #000;
  letter-spacing: 2px;
  background: transparent !important;
}

/* Form */
.fbp-form-container {
  position: relative;
  z-index: 2;
  padding: 10px 20px 15px !important;
}

.feedback-form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.form-field-card {
  background: linear-gradient(145deg, rgba(30,25,10,0.9), rgba(20,15,5,0.95));
  border: 3px solid #ffaa00;
  border-radius: 0;
  padding: 16px 12px;
  text-align: center;
  position: relative;
  overflow: hidden;
  animation: field-card-appear 0.6s ease-out backwards;
  animation-delay: var(--field-delay, 0s);
  box-shadow: 
    inset 0 0 20px rgba(255,170,0,0.1),
    0 4px 15px rgba(0,0,0,0.5),
    0 0 0 1px rgba(255,170,0,0.3);
  transition: all 0.3s ease;
}

.form-field-card:hover {
  transform: translateY(-5px);
  border-color: #ffcc00;
  box-shadow: 
    inset 0 0 30px rgba(255,200,0,0.15),
    0 8px 25px rgba(0,0,0,0.6),
    0 0 20px rgba(255,170,0,0.3),
    0 0 0 2px rgba(255,200,0,0.5);
}

.form-field-card.full-width {
  grid-column: 1 / -1;
}

@keyframes field-card-appear {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.field-icon {
  font-size: 2rem;
  margin: 8px 0;
  animation: icon-bounce 2s infinite;
  animation-delay: var(--field-delay, 0s);
}

@keyframes icon-bounce {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.field-title {
  font-size: 0.55rem;
  color: #ffcc00;
  margin-bottom: 12px;
  text-shadow: 2px 2px 0 #000;
  line-height: 1.4;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.pixel-input :deep(.v-field) {
  background: rgba(20,15,5,0.8) !important;
  border: 2px solid #cc8800 !important;
  border-radius: 0 !important;
}

.pixel-input :deep(.v-label) {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.65rem !important;
  color: #ffaa00 !important;
  text-shadow: 1px 1px 0 #000;
}

.pixel-input :deep(.v-field__input) {
  font-family: 'VT323', monospace !important;
  font-size: 1rem !important;
  color: #ffdd88 !important;
  text-shadow: 1px 1px 0 #000;
}

.pixel-input :deep(.v-field--focused) {
  border-color: #ffcc00 !important;
  box-shadow: 0 0 20px rgba(255,170,0,0.4) !important;
}

/* Tip */
.fbp-tip {
  background: linear-gradient(90deg, rgba(255,180,0,0.1), rgba(255,150,0,0.15), rgba(255,180,0,0.1));
  border: 2px solid #ffaa00;
  border-radius: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 12px 18px;
  box-shadow: 
    inset 0 0 15px rgba(255,170,0,0.1),
    0 0 15px rgba(255,170,0,0.2);
  margin: 0 20px;
}

.tip-icon {
  font-size: 1.5rem;
  animation: tip-shine 2s infinite;
}

@keyframes tip-shine {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.1); }
}

.tip-text {
  font-size: 0.55rem;
  color: #ffdd66;
  text-shadow: 1px 1px 0 #000;
  line-height: 1.4;
  letter-spacing: 0.5px;
}

/* Actions */
.fbp-actions {
  position: relative;
  z-index: 2;
  background: linear-gradient(180deg, rgba(20,15,5,0.9), rgba(10,5,0,0.95));
  padding: 16px 24px 20px;
  display: flex;
  justify-content: center;
  gap: 12px;
}

.fbp-cancel-btn {
  font-family: 'Press Start 2P', monospace !important;
  border: 2px solid #aa7700 !important;
  color: #ffcc66 !important;
  text-shadow: 1px 1px 0 #000;
  transition: all 0.2s ease;
  border-radius: 0 !important;
}

.fbp-cancel-btn:hover {
  background: rgba(255,170,0,0.2) !important;
  border-color: #ffaa00 !important;
  transform: translateY(-2px);
}

.fbp-send-btn {
  font-family: 'Press Start 2P', monospace !important;
  text-rendering: optimizeSpeed;
  background: linear-gradient(180deg, #ffcc00 0%, #ff9900 50%, #ff7700 100%) !important;
  color: #1a0a00 !important;
  border: 3px solid #ffdd00 !important;
  box-shadow: 
    0 6px 0 #aa5500,
    0 8px 20px rgba(255,170,0,0.4),
    inset 0 2px 0 rgba(255,255,255,0.4),
    inset 0 -2px 0 rgba(0,0,0,0.2) !important;
  transition: all 0.15s ease !important;
  animation: fbp-btn-pulse 2s infinite;
  border-radius: 0 !important;
  min-width: 200px;
  font-size: 0.75rem !important;
}

@keyframes fbp-btn-pulse {
  0%, 100% { box-shadow: 0 6px 0 #aa5500, 0 8px 20px rgba(255,170,0,0.4), inset 0 2px 0 rgba(255,255,255,0.4), inset 0 -2px 0 rgba(0,0,0,0.2); }
  50% { box-shadow: 0 6px 0 #aa5500, 0 12px 30px rgba(255,170,0,0.6), inset 0 2px 0 rgba(255,255,255,0.4), inset 0 -2px 0 rgba(0,0,0,0.2); }
}

.fbp-send-btn:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 
    0 9px 0 #aa5500,
    0 15px 35px rgba(255,170,0,0.5),
    inset 0 2px 0 rgba(255,255,255,0.5),
    inset 0 -2px 0 rgba(0,0,0,0.2) !important;
}

.fbp-send-btn:active:not(:disabled) {
  transform: translateY(3px);
  box-shadow: 
    0 3px 0 #aa5500,
    0 5px 15px rgba(255,170,0,0.3),
    inset 0 2px 0 rgba(255,255,255,0.3),
    inset 0 2px 3px rgba(0,0,0,0.3) !important;
}

/* Mobile */
@media (max-width: 600px) {
  .feedback-form-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .fbp-actions {
    flex-direction: column;
    gap: 12px;
  }
  
  .fbp-send-btn {
    width: 100%;
  }
}

/* ============================================
   RETRO PIXEL HOW TO PLAY DIALOG
   ============================================ */
.howtoplay-dialog-content {
  background: transparent !important;
  box-shadow: none !important;
}

.htp-glow {
  position: absolute;
  inset: -50px;
  background: 
    radial-gradient(ellipse at 30% 20%, rgba(255,200,50,0.25) 0%, transparent 50%),
    radial-gradient(ellipse at 70% 70%, rgba(255,150,0,0.2) 0%, transparent 60%),
    radial-gradient(circle at center, rgba(255,180,0,0.15) 0%, transparent 70%);
  filter: blur(40px);
  z-index: 0;
  pointer-events: none;
  animation: htp-glow-pulse 3s ease-in-out infinite;
}

@keyframes htp-glow-pulse {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.05); }
}

.htp-scanlines {
  position: absolute;
  inset: 0;
  background: 
    repeating-linear-gradient(0deg, rgba(255,180,0,0.06), rgba(255,180,0,0.06) 1px, transparent 1px, transparent 2px),
    repeating-linear-gradient(90deg, rgba(200,150,0,0.04), rgba(200,150,0,0.04) 2px, transparent 2px, transparent 4px);
  z-index: 1;
  pointer-events: none;
  mix-blend-mode: overlay;
}

.htp-vignette {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at center, transparent 40%, rgba(0,0,0,0.6) 85%);
  z-index: 1;
  pointer-events: none;
}

.retro-pixel-howtoplay {
  background: 
    linear-gradient(170deg, #1a1a0a 0%, #0f0f05 50%, #080805 100%),
    #111;
  border: 4px solid #ffaa00 !important;
  box-shadow: 
    inset 0 0 0 2px rgba(255,255,255,0.1),
    0 0 0 2px #ffaa00,
    0 40px 120px rgba(255,170,0,0.35),
    0 0 80px rgba(255,170,0,0.25),
    inset 0 0 40px rgba(0,0,0,0.8);
  font-family: 'Press Start 2P', monospace !important;
  text-rendering: optimizeSpeed;
  image-rendering: pixelated;
  position: relative;
  overflow: hidden;
}

.htp-title {
  position: relative;
  z-index: 2;
  text-align: center;
  justify-content: center;
  padding: 20px 16px 10px !important;
  background: linear-gradient(180deg, rgba(255,180,0,0.15), transparent);
  color: #ffcc00 !important;
  text-shadow: 
    0 0 20px #ffaa00,
    3px 0 0 #000, -3px 0 0 #ffaa00,
    0 3px 0 #000, 0 -3px 0 #ffaa00,
    2px 2px 0 rgba(255,170,0,0.5) !important;
  animation: htp-title-glow 2s ease-in-out infinite alternate;
}

@keyframes htp-title-glow {
  from { text-shadow: 0 0 20px #ffaa00, 3px 0 0 #000, -3px 0 0 #ffaa00, 0 3px 0 #000, 0 -3px 0 #ffaa00; }
  to { text-shadow: 0 0 40px #ffdd00, 3px 0 0 #000, -3px 0 0 #ffaa00, 0 3px 0 #000, 0 -3px 0 #ffaa00, 0 0 60px rgba(255,200,0,0.5); }
}

@keyframes htp-bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.htp-subtitle {
  position: relative;
  z-index: 2;
  font-size: 0.75rem !important;
  color: #ffcc66 !important;
  text-shadow: 2px 2px 0 #000;
  letter-spacing: 2px;
  background: transparent !important;
}

.htp-steps-container {
  position: relative;
  z-index: 2;
  padding: 10px 20px 15px !important;
}

.steps-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
}

.step-card {
  background: linear-gradient(145deg, rgba(30,25,10,0.9), rgba(20,15,5,0.95));
  border: 3px solid #ffaa00;
  border-radius: 0;
  padding: 12px 10px;
  text-align: center;
  position: relative;
  overflow: hidden;
  animation: step-card-appear 0.6s ease-out backwards;
  animation-delay: var(--step-delay, 0s);
  box-shadow: 
    inset 0 0 20px rgba(255,170,0,0.1),
    0 4px 15px rgba(0,0,0,0.5),
    0 0 0 1px rgba(255,170,0,0.3);
  transition: all 0.3s ease;
}

.step-card:hover {
  transform: translateY(-5px);
  border-color: #ffcc00;
  box-shadow: 
    inset 0 0 30px rgba(255,200,0,0.15),
    0 8px 25px rgba(0,0,0,0.6),
    0 0 20px rgba(255,170,0,0.3),
    0 0 0 2px rgba(255,200,0,0.5);
}

@keyframes step-card-appear {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.step-number {
  position: absolute;
  top: 4px;
  left: 6px;
  font-size: 0.5rem;
  color: #ffaa00;
  text-shadow: 1px 1px 0 #000;
}

.step-icon {
  font-size: 1.8rem;
  margin: 6px 0;
  animation: icon-bounce 2s infinite;
  animation-delay: var(--step-delay, 0s);
}

@keyframes icon-bounce {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.step-title {
  font-size: 0.5rem;
  color: #ffcc00;
  margin: 6px 0 4px;
  text-shadow: 2px 2px 0 #000;
  line-height: 1.4;
}

.step-desc {
  font-size: 0.4rem;
  color: #ccbb99;
  line-height: 1.5;
  text-shadow: 1px 1px 0 #000;
}

.htp-tip {
  background: linear-gradient(90deg, rgba(255,180,0,0.1), rgba(255,150,0,0.15), rgba(255,180,0,0.1));
  border: 2px solid #ffaa00;
  border-radius: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 10px 16px;
  box-shadow: 
    inset 0 0 15px rgba(255,170,0,0.1),
    0 0 15px rgba(255,170,0,0.2);
}

.tip-icon {
  font-size: 1.3rem;
  animation: tip-shine 2s infinite;
}

@keyframes tip-shine {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.1); }
}

.tip-text {
  font-size: 0.5rem;
  color: #ffdd66;
  text-shadow: 1px 1px 0 #000;
  line-height: 1.4;
}

.htp-actions {
  position: relative;
  z-index: 2;
  background: linear-gradient(180deg, rgba(20,15,5,0.9), rgba(10,5,0,0.95));
  padding: 16px 24px 20px;
  display: flex;
  justify-content: center;
}

.htp-start-btn {
  font-family: 'Press Start 2P', monospace !important;
  text-rendering: optimizeSpeed;
  background: linear-gradient(180deg, #ffcc00 0%, #ff9900 50%, #ff7700 100%) !important;
  color: #1a0a00 !important;
  border: 3px solid #ffdd00 !important;
  box-shadow: 
    0 6px 0 #aa5500,
    0 8px 20px rgba(255,170,0,0.4),
    inset 0 2px 0 rgba(255,255,255,0.4),
    inset 0 -2px 0 rgba(0,0,0,0.2) !important;
  transition: all 0.15s ease !important;
  animation: btn-pulse 2s infinite;
}

@keyframes btn-pulse {
  0%, 100% { box-shadow: 0 6px 0 #aa5500, 0 8px 20px rgba(255,170,0,0.4), inset 0 2px 0 rgba(255,255,255,0.4), inset 0 -2px 0 rgba(0,0,0,0.2); }
  50% { box-shadow: 0 6px 0 #aa5500, 0 12px 30px rgba(255,170,0,0.6), inset 0 2px 0 rgba(255,255,255,0.4), inset 0 -2px 0 rgba(0,0,0,0.2); }
}

.htp-start-btn:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 
    0 9px 0 #aa5500,
    0 15px 35px rgba(255,170,0,0.5),
    inset 0 2px 0 rgba(255,255,255,0.5),
    inset 0 -2px 0 rgba(0,0,0,0.2) !important;
}

.htp-start-btn:active:not(:disabled) {
  transform: translateY(3px);
  box-shadow: 
    0 3px 0 #aa5500,
    0 5px 15px rgba(255,170,0,0.3),
    inset 0 2px 0 rgba(255,255,255,0.3),
    inset 0 2px 3px rgba(0,0,0,0.3) !important;
}

/* Mobile Responsive */
@media (max-width: 600px) {
  .steps-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }
  
  .step-card {
    padding: 10px 6px;
  }
  
  .step-icon {
    font-size: 1.4rem;
  }
  
  .step-title {
    font-size: 0.45rem;
  }
  
  .step-desc {
    font-size: 0.35rem;
  }
}

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
   WELCOME DIALOG - RETRO PIXEL DESIGN
   ============================================ */
.welcome-dialog-card {
  background: linear-gradient(180deg, rgba(15, 10, 35, 0.98) 0%, rgba(5, 5, 20, 0.98) 100%) !important;
  border: 2px solid rgba(0, 255, 204, 0.4) !important;
  border-radius: 24px !important;
  box-shadow: 
    0 35px 100px rgba(0, 0, 0, 0.7),
    0 0 60px rgba(0, 255, 204, 0.25),
    inset 0 1px 0 rgba(255, 255, 255, 0.08);
  overflow: hidden;
  position: relative;
  animation: welcome-bounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes welcome-bounce {
  0% { transform: scale(0.3) rotate(-3deg); opacity: 0; }
  50% { transform: scale(1.05); }
  70% { transform: scale(0.98) rotate(1deg); }
  100% { transform: scale(1) rotate(0); opacity: 1; }
}

.welcome-scanlines {
  background: repeating-linear-gradient(
    90deg,
    transparent,
    transparent 2px,
    rgba(0, 255, 204, 0.03) 2px,
    rgba(0, 255, 204, 0.03) 4px
  );
  mix-blend-mode: screen;
}

.welcome-dialog-title {
  font-family: 'Press Start 2P', monospace !important;
  font-size: clamp(1.1rem, 4vw, 1.4rem) !important;
  padding: 24px 20px 16px !important;
  color: #ffffff !important;
  text-shadow: 
    3px 3px 0 #000,
    0 0 20px #ff00ff,
    0 0 40px #00ffcc;
  position: relative;
}

.welcome-glitch-title {
  position: relative;
  animation: welcome-glitch 3s infinite;
  display: block;
}

.welcome-glitch-title::before,
.welcome-glitch-title::after {
  content: attr(data-text);
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.welcome-glitch-title::before {
  animation: welcome-glitch-cyan 3s infinite;
  color: #00ffcc;
  left: -1px;
  clip-path: polygon(0 0, 100% 0, 100% 33%, 0 33%);
}

.welcome-glitch-title::after {
  animation: welcome-glitch-magenta 3s infinite;
  color: #ff00ff;
  left: 1px;
  clip-path: polygon(0 67%, 100% 67%, 100% 100%, 0 100%);
}

@keyframes welcome-glitch {
  0%, 85%, 100% { transform: translate(0); }
  87% { transform: translate(-2px, 1px); }
  89% { transform: translate(2px, -1px); }
  91% { transform: translate(-1px, 2px); }
}

@keyframes welcome-glitch-cyan {
  0%, 94% { opacity: 0.4; transform: translateX(1px); }
  97% { opacity: 1; transform: translateX(-1px); }
}

@keyframes welcome-glitch-magenta {
  0%, 94% { opacity: 0.4; transform: translateX(-1px); }
  97% { opacity: 1; transform: translateX(1px); }
}

.welcome-dialog-text {
  color: rgba(255, 255, 255, 0.9) !important;
  font-family: 'VT323', monospace !important;
  padding: 0 24px 20px !important;
  font-size: 1.1rem;
}

.welcome-pixel-art {
  width: 64px;
  height: 64px;
}

.pixel-heart {
  width: 100%;
  height: 100%;
  background: 
    conic-gradient(from 45deg, transparent 0deg 90deg, #ff69b4 90deg 180deg, transparent 180deg 270deg, #ff1493 270deg);
  mask: 
    radial-gradient(circle closest-side at 30% 30%, #ff69b4 10%, transparent 11%),
    radial-gradient(circle closest-side at 70% 30%, #ff69b4 10%, transparent 11%),
    radial-gradient(circle closest-side at 50% 60%, #ff1493 20%, transparent 21%);
  mask-composite: exclude;
  animation: heart-beat 1.5s ease-in-out infinite;
  filter: drop-shadow(0 0 12px rgba(255, 105, 180, 0.6));
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
}

@keyframes heart-beat {
  0%, 100% { transform: scale(1); }
  14% { transform: scale(1.1); }
  28% { transform: scale(1); }
  42% { transform: scale(1.05); }
  70% { transform: scale(1); }
}

.welcome-subtitle {
  font-size: 0.95rem !important;
  color: rgba(0, 255, 204, 0.95) !important;
  text-shadow: 0 0 10px rgba(0, 255, 204, 0.5);
  margin-top: 8px !important;
}

.welcome-enter-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.75rem !important;
  letter-spacing: 0.1em !important;
  min-width: 160px !important;
  padding: 12px 24px !important;
  border-radius: 12px !important;
  box-shadow: 
    0 8px 25px rgba(0, 255, 204, 0.3),
    0 0 30px rgba(255, 0, 255, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
  transition: all 0.25s ease;
}

.welcome-enter-btn:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 
    0 12px 35px rgba(0, 255, 204, 0.4),
    0 0 40px rgba(255, 0, 255, 0.3);
}

.welcome-enter-btn:active {
  transform: translateY(0) scale(0.98);
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