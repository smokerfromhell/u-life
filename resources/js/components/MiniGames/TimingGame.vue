<template>
  <v-card class="timing-game" max-width="500" :loading="loading">
    <v-card-title class="text-center">
      <v-icon size="24" class="mr-2">mdi-target</v-icon>
      Timing Challenge
    </v-card-title>
    
    <v-card-text class="text-center">
      <div class="instructions mb-4">
        Hit the target when it crosses the center line!
      </div>
      
      <!-- Round info -->
      <div class="round-info mb-4">
        <v-chip color="primary" variant="outlined">
          Round: {{ currentRound }}/{{ gameData.rounds }}
        </v-chip>
        <v-chip color="success" variant="outlined" class="ml-2">
          Score: {{ totalScore }}
        </v-chip>
      </div>
      
      <!-- Timing game area -->
      <div class="timing-area" v-if="isPlaying">
        <div class="target-line"></div>
        <div 
          class="target"
          :style="{ 
            left: targetPosition + '%',
            width: targetSize + 'px'
          }"
        ></div>
        <div class="hit-zone" :class="{ 'active': canHit }"></div>
      </div>
      
      <!-- Hit indicator -->
      <div v-if="showHitResult" class="hit-result mt-4">
        <v-alert
          :type="lastHitSuccess ? 'success' : 'error'"
          variant="tonal"
        >
          {{ lastHitSuccess ? 'Perfect Hit!' : 'Missed!' }}
        </v-alert>
      </div>
      
      <!-- Start button -->
      <v-btn
        v-if="!isPlaying && !gameComplete"
        color="primary"
        size="large"
        @click="startGame"
        class="mt-4"
      >
        <v-icon start>mdi-play</v-icon>
        Start Game
      </v-btn>
      
      <!-- Instructions -->
      <div v-if="!isPlaying && !gameComplete" class="instructions-text mt-4">
        <p>Click or press SPACE when the target crosses the green line!</p>
      </div>
      
      <!-- Result -->
      <div v-if="gameComplete" class="result-display mt-4">
        <v-alert
          :type="score >= 70 ? 'success' : score >= 50 ? 'warning' : 'error'"
          variant="tonal"
        >
          <div class="text-h6">Score: {{ score }}%</div>
          <div>{{ resultMessage }}</div>
        </v-alert>
      </div>
    </v-card-text>
    
    <v-card-actions v-if="gameComplete">
      <v-btn
        color="primary"
        block
        @click="submitResult"
      >
        Continue
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  gameData: {
    type: Object,
    required: true
  },
  characterId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['complete'])

const loading = ref(false)
const isPlaying = ref(false)
const gameComplete = ref(false)
const currentRound = ref(0)
const totalScore = ref(0)
const targetPosition = ref(0)
const canHit = ref(false)
const showHitResult = ref(false)
const lastHitSuccess = ref(false)

let animationFrame = null
let direction = 1
let speed = 2

const targetSize = computed(() => props.gameData.target_size || 40)

const score = computed(() => {
  if (currentRound.value === 0) return 0
  return Math.round((totalScore.value / currentRound.value))
})

const resultMessage = computed(() => {
  if (score.value >= 90) return 'Perfect timing! Amazing reflexes!'
  if (score.value >= 70) return 'Great timing! Well done!'
  if (score.value >= 50) return 'Good timing!'
  return 'Keep practicing your timing!'
})

function startGame() {
  currentRound.value = 0
  totalScore.value = 0
  isPlaying.value = true
  gameComplete.value = false
  
  speed = (props.gameData.speed || 1) * 0.5
  
  nextRound()
}

function nextRound() {
  if (currentRound.value >= props.gameData.rounds) {
    endGame()
    return
  }
  
  currentRound.value++
  targetPosition.value = 0
  direction = 1
  showHitResult.value = false
  canHit.value = false
  
  // Start animation
  animateTarget()
}

function animateTarget() {
  targetPosition.value += speed * direction
  
  // Bounce at edges
  if (targetPosition.value >= 95) {
    direction = -1
  } else if (targetPosition.value <= 0) {
    direction = 1
  }
  
  // Check if in hit zone (center area)
  canHit.value = targetPosition.value >= 45 && targetPosition.value <= 55
  
  if (isPlaying.value && !showHitResult.value) {
    animationFrame = requestAnimationFrame(animateTarget)
  }
}

function handleHit() {
  if (!isPlaying.value || showHitResult.value) return
  
  // Cancel animation
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
  
  // Calculate score based on distance from center
  const distance = Math.abs(targetPosition.value - 50)
  let roundScore = 0
  
  if (distance <= 5) {
    roundScore = 100
    lastHitSuccess.value = true
  } else if (distance <= 10) {
    roundScore = 80
    lastHitSuccess.value = true
  } else if (distance <= 20) {
    roundScore = 50
    lastHitSuccess.value = false
  } else {
    roundScore = 0
    lastHitSuccess.value = false
  }
  
  totalScore.value += roundScore
  showHitResult.value = true
  canHit.value = false
  
  // Next round after delay
  setTimeout(() => {
    nextRound()
  }, 1500)
}

function endGame() {
  isPlaying.value = false
  gameComplete.value = true
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
}

function handleKeydown(e) {
  if (e.code === 'Space') {
    e.preventDefault()
    handleHit()
  }
}

function submitResult() {
  loading.value = true
  
  fetch(`/api/characters/${props.characterId}/mini-game`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      game_type: 'timing',
      score: score.value,
      game_data: {
        ...props.gameData,
        total_score: totalScore.value
      }
    })
  })
  .then(res => res.json())
  .then(data => {
    loading.value = false
    emit('complete', {
      score: score.value,
      effects: data.effects || {},
      message: data.message || resultMessage.value,
      outcome: data.outcome || 'neutral'
    })
  })
  .catch(err => {
    loading.value = false
    emit('complete', {
      score: score.value,
      effects: {},
      message: resultMessage.value,
      outcome: 'neutral'
    })
  })
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
})
</script>

<style scoped>
.timing-game {
  background: transparent !important;
}

.instructions {
  color: #b0b0b0;
  font-size: 14px;
  text-shadow: 0 0 5px rgba(0, 255, 204, 0.3);
}

/* Galaxy styled timing area - cosmic track */
.timing-area {
  position: relative;
  height: 80px;
  background: linear-gradient(90deg, 
    rgba(0, 0, 0, 0.4) 0%, 
    rgba(26, 10, 46, 0.4) 50%, 
    rgba(0, 0, 0, 0.4) 100%);
  border-radius: 10px;
  overflow: hidden;
  margin: 20px 0;
  cursor: pointer;
  border: 1px solid rgba(0, 255, 204, 0.2);
  box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.5);
}

/* Cosmic target line - energy beam */
.target-line {
  position: absolute;
  left: 50%;
  top: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(180deg, 
    transparent 0%, 
    #00ffcc 20%, 
    #00ffcc 80%, 
    transparent 100%);
  transform: translateX(-50%);
  z-index: 2;
  box-shadow: 0 0 15px rgba(0, 255, 204, 0.8), 0 0 30px rgba(0, 255, 204, 0.4);
  animation: beamPulse 1.5s ease-in-out infinite;
}

@keyframes beamPulse {
  0%, 100% { opacity: 0.8; box-shadow: 0 0 15px rgba(0, 255, 204, 0.6); }
  50% { opacity: 1; box-shadow: 0 0 25px rgba(0, 255, 204, 1); }
}

/* Planet-styled target - glowing orb */
.target {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  height: 40px;
  background: radial-gradient(circle at 30% 30%, 
    #ffd54f 0%, 
    #ff9800 40%, 
    #e65100 80%, 
    #bf360c 100%);
  border-radius: 50%;
  transition: none;
  cursor: pointer;
  box-shadow: 
    0 0 20px rgba(255, 152, 0, 0.6),
    0 0 40px rgba(255, 152, 0, 0.3),
    inset -5px -5px 15px rgba(0, 0, 0, 0.3),
    inset 5px 5px 15px rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 213, 79, 0.5);
}

/* Cosmic hit zone - energy field */
.hit-zone {
  position: absolute;
  left: 45%;
  right: 45%;
  top: 0;
  bottom: 0;
  background: rgba(0, 255, 204, 0.1);
  transition: background 0.2s;
  border-left: 1px solid rgba(0, 255, 204, 0.2);
  border-right: 1px solid rgba(0, 255, 204, 0.2);
}

.hit-zone.active {
  background: rgba(0, 255, 204, 0.25);
  box-shadow: 0 0 20px rgba(0, 255, 204, 0.4);
  border-color: rgba(0, 255, 204, 0.6);
}

/* Round info styling */
.round-info {
  display: flex;
  justify-content: center;
  gap: 8px;
}

/* Instructions text */
.instructions-text {
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
  text-shadow: 0 0 5px rgba(0, 255, 204, 0.2);
}

/* Enhanced result display */
.result-display {
  padding: 10px;
  border-radius: 12px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(0, 255, 204, 0.2);
}

.result-display .text-h6 {
  color: #00ffcc;
  text-shadow: 0 0 10px rgba(0, 255, 204, 0.5);
  font-weight: 600;
}

/* Hit result animation */
.hit-result {
  animation: hitFlash 0.5s ease-out;
}

@keyframes hitFlash {
  0% { transform: scale(1.2); }
  100% { transform: scale(1); }
}
</style>
