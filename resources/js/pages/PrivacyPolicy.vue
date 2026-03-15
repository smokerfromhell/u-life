<template>
  <!-- Galaxy Background -->
  <div class="galaxy-screen relative min-h-screen overflow-hidden"
    style="background: linear-gradient(135deg, #0d0d1a 0%, #1a0a2e 30%, #0a1a2e 50%, #0a1a1a 70%, #0d0d1a 100%);">
    
    <!-- Starfield -->
    <div class="starfield pointer-events-none fixed inset-0 z-0">
      <div v-for="n in 50" :key="n" class="star absolute rounded-full"
        :style="getStarStyle(n)">
      </div>
    </div>
    
    <!-- Content -->
    <v-container fluid class="relative z-10 px-4 py-8">
      <!-- Back Button -->
      <v-btn 
        variant="text" 
        class="back-btn mb-4"
        @click="$router.push('/home')"
      >
        <v-icon left>mdi-arrow-left</v-icon>
        Back to Home
      </v-btn>
      
      <!-- Title -->
      <h1 class="page-title text-center mb-8">
        PRIVACY POLICY
      </h1>
      
      <!-- Content Card -->
      <v-card class="content-card mx-auto" max-width="900">
        <v-card-text class="pa-6">
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">1. INTRODUCTION</h2>
            <p class="section-text">
              At ULIFE, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you play our game. Please read this privacy policy carefully.
            </p>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">2. INFORMATION WE COLLECT</h2>
            <p class="section-text">
              We may collect personal information that you voluntarily provide to us when you:
            </p>
            <ul class="section-list">
              <li>Register an account</li>
              <li>Play the game</li>
              <li>Contact us for support</li>
              <li>Participate in surveys or promotions</li>
            </ul>
            <p class="section-text mt-3">
              The personal information that we collect may include:
            </p>
            <ul class="section-list">
              <li>Email address</li>
              <li>Username and password</li>
              <li>Character data and game progress</li>
              <li>Device information</li>
            </ul>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">3. HOW WE USE YOUR INFORMATION</h2>
            <p class="section-text">
              We may use the information we collect from you when you play, register, or make a purchase to:
            </p>
            <ul class="section-list">
              <li>Create and manage your account</li>
              <li>Provide and maintain the Game</li>
              <li>Track your game progress and achievements</li>
              <li>Communicate with you about updates and promotions</li>
              <li>Respond to your comments and questions</li>
              <li>Analyze usage patterns to improve the Game</li>
            </ul>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">4. DATA SECURITY</h2>
            <p class="section-text">
              We implement appropriate technical and organizational security measures to protect your personal information. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.
            </p>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">5. THIRD-PARTY SERVICES</h2>
            <p class="section-text">
              The Game may contain links to third-party websites or services that are not owned or controlled by ULIFE. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party websites.
            </p>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">6. CHILDREN'S PRIVACY</h2>
            <p class="section-text">
              The Game is not intended for children under the age of 13. We do not knowingly collect personal information from children under 13. If you are a parent or guardian and believe your child has provided us with personal information, please contact us.
            </p>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">7. CHANGES TO THIS POLICY</h2>
            <p class="section-text">
              We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "last modified" date at the top.
            </p>
          </div>
          
          <div class="content-section mb-0">
            <h2 class="section-title mb-3">8. CONTACT US</h2>
            <p class="section-text">
              If you have any questions about this Privacy Policy, please contact us through the game's support channels.
            </p>
          </div>
        </v-card-text>
        
        <v-card-actions class="pa-6 pt-0">
          <v-spacer></v-spacer>
          <v-btn 
            color="amber-darken-2" 
            variant="flat"
            @click="$router.push('/home')"
          >
            I Understand - Back to Home
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-container>
  </div>
</template>

<script setup>
const getStarStyle = (n) => {
  const size = Math.random() * 2 + 1
  const left = Math.random() * 100
  const top = Math.random() * 100
  const duration = Math.random() * 3 + 2
  const delay = Math.random() * 2
  
  return {
    width: `${size}px`,
    height: `${size}px`,
    left: `${left}%`,
    top: `${top}%`,
    animation: `twinkle ${duration}s ease-in-out ${delay}s infinite`,
    opacity: Math.random() * 0.5 + 0.3
  }
}
</script>

<style scoped>
.galaxy-screen {
  min-height: 100vh;
}

.starfield {
  background: transparent;
}

.star {
  position: absolute;
  background: white;
  border-radius: 50%;
}

@keyframes twinkle {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.2); }
}

.back-btn {
  font-family: 'VT323', monospace !important;
  font-size: 18px !important;
  color: #67e8f9 !important;
  text-transform: none !important;
}

.back-btn:hover {
  color: #22d3ee !important;
}

.page-title {
  font-family: 'Press Start 2P', monospace !important;
  font-size: clamp(1.2rem, 4vw, 2rem) !important;
  color: #ffcc00 !important;
  text-shadow: 
    0 0 20px #ffaa00,
    3px 0 0 #000, -3px 0 0 #ffaa00,
    0 3px 0 #000, 0 -3px 0 #ffaa00;
}

.content-card {
  background: linear-gradient(170deg, rgba(26, 26, 10, 0.95), rgba(15, 15, 5, 0.98)) !important;
  border: 2px solid #ffaa00 !important;
  box-shadow: 
    0 0 30px rgba(255, 170, 0, 0.2),
    inset 0 0 20px rgba(0, 0, 0, 0.5) !important;
}

.section-title {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.75rem !important;
  color: #ffcc00 !important;
  text-shadow: 2px 2px 0 #000;
}

.section-text {
  font-family: 'VT323', monospace !important;
  font-size: 18px !important;
  line-height: 1.8 !important;
  color: #ccbb99 !important;
}

.section-list {
  font-family: 'VT323', monospace !important;
  font-size: 16px !important;
  line-height: 1.8 !important;
  color: #ccbb99 !important;
  margin-left: 20px;
  margin-top: 10px;
}

.section-list li {
  margin-bottom: 8px;
}

@media (max-width: 600px) {
  .page-title {
    font-size: 1rem !important;
  }
  
  .section-title {
    font-size: 0.6rem !important;
  }
  
  .section-text {
    font-size: 14px !important;
  }
}
</style>
