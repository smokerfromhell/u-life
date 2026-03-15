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
        USER AGREEMENT
      </h1>
      
      <!-- Content Card -->
      <v-card class="content-card mx-auto" max-width="900">
        <v-card-text class="pa-6">
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">1. ACCEPTANCE OF TERMS</h2>
            <p class="section-text">
              By accessing and playing ULIFE ("the Game"), you accept and agree to be bound by the terms and provisions of this User Agreement. If you do not agree to these terms, please do not use the Game.
            </p>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">2. USE LICENSE</h2>
            <p class="section-text">
              Permission is granted to temporarily use the Game for personal, non-commercial use only. This is the grant of a license, not a transfer of title, and under this license you may not:
            </p>
            <ul class="section-list">
              <li>Modify or copy the materials</li>
              <li>Use the materials for any commercial purpose</li>
              <li>Transfer the materials to another person</li>
              <li>Attempt to reverse engineer any software contained in the Game</li>
            </ul>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">3. USER ACCOUNT</h2>
            <p class="section-text">
              When creating an account, you must provide accurate and complete information. You are responsible for maintaining the confidentiality of your account and password. You agree to accept responsibility for all activities that occur under your account.
            </p>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">4. GAME RULES AND CONDUCT</h2>
            <p class="section-text">
              You agree not to use the Game in any way that:
            </p>
            <ul class="section-list">
              <li>Violates any applicable laws or regulations</li>
              <li>Infringes upon the rights of any third party</li>
              <li>Contains viruses, worms, or other harmful components</li>
              <li>Interferes with the proper working of the Game</li>
            </ul>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">5. INTELLECTUAL PROPERTY</h2>
            <p class="section-text">
              The Game and its original content, features, and functionality are owned by ULIFE and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.
            </p>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">6. DISCLAIMER OF WARRANTIES</h2>
            <p class="section-text">
              THE GAME IS PROVIDED ON AN "AS IS" BASIS. ULIFE MAKES NO WARRANTIES, EXPRESS OR IMPLIED, AND HEREBY DISCLAIMS ALL IMPLIED WARRANTIES INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, OR NON-INFRINGEMENT.
            </p>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">7. LIMITATION OF LIABILITY</h2>
            <p class="section-text">
              IN NO EVENT SHALL ULIFE BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING WITHOUT LIMITATION, LOSS OF PROFITS, DATA, USE, GOODWILL, OR OTHER INTANGIBLE LOSSES.
            </p>
          </div>
          
          <div class="content-section mb-6">
            <h2 class="section-title mb-3">8. CHANGES TO TERMS</h2>
            <p class="section-text">
              ULIFE reserves the right, at its sole discretion, to modify or replace these terms at any time. What constitutes a material change will be determined at our sole discretion.
            </p>
          </div>
          
          <div class="content-section mb-0">
            <h2 class="section-title mb-3">9. CONTACT US</h2>
            <p class="section-text">
              If you have any questions about this User Agreement, please contact us through the game's support channels.
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
            I Agree - Back to Home
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
