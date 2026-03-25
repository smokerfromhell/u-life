<template>
  <v-card class="qte-game" max-width="500" :loading="loading">
    <v-card-title class="game-title text-center">
      <v-icon size="28" class="game-icon mr-2">mdi-gesture-tap</v-icon>
      <span class="title-text">Quick Time Event!</span>
    </v-card-title>
    
    <v-card-text class="text-center game-content">
      <div class="instructions mb-4">
        <v-icon size="16" class="mr-1">mdi-star-shooting</v-icon>
        Press the buttons in the correct sequence!
        <v-icon size="16" class="ml-1">mdi-star-shooting</v-icon>
      </div>
      
      <!-- Timer - Galaxy styled -->
      <v-progress-linear
        v-if="isPlaying"
        :model-value="timeRemaining"
        :color="timeWarning ? 'warning' : 'primary'"
        height="10"
        rounded
        class="galaxy-progress mb-4"
      ></v-progress-linear>
      
      <!-- Current sequence display -->
      <div class="sequence-display mb-4">
        <div class="sequence-label mb-2">
          <v-icon size="18" class="mr-1">mdi-eye</v-icon>
          <span class="label-text">Watch: </span>
        </div>
        <div class="sequence-buttons">
          <span 
            v-for="(btn, idx) in gameData.sequence" 
            :key="'target-' + idx"
            class="sequence-btn"
            :class="{ 'completed': idx < currentIndex, 'current': idx === currentIndex && isShowingSequence }"
          >
            {{ btn }}
          </span>
        </div>
        
        <div class="player-sequence mt-3" v-if="playerSequence.length > 0">
          <div class="sequence-label mb-2">
            <v-icon size="18" class="mr-1">mdi-hand-pointing-right</v-icon>
            <span class="label-text">Your input: </span>
          </div>
          <div class="sequence-buttons">
            <span 
              v-for="(btn, idx) in playerSequence" 
              :key="'player-' + idx"
              class="sequence-btn"
              :class="{ 'correct': correctSequence[idx] === true, 'incorrect': correctSequence[idx] === false }"
            >
              {{ btn }}
            </span>
          </div>
        </div>
      </div>
      
      <!-- Button pad -->
      <div class="button-pad" v-if="isPlaying && !isShowingSequence">
        <v-btn
          v-for="btn in buttons"
          :key="btn"
          size="x-large"
          :color="pressedBtn === btn ? 'primary' : 'grey-darken-2'"
          class="mx-1 mb-2 game-btn"
          @mousedown="handleButtonPress(btn)"
          @touchstart.prevent="handleButtonPress(btn)"
        >
          {{ btn }}
        </v-btn>
      </div>
      
      <!-- Start/Restart button -->
      <v-btn
        v-if="!isPlaying && !gameComplete"
        color="primary"
        size="large"
        @click="startGame"
        class="mt-4 start-btn"
      >
        <v-icon start>mdi-play</v-icon>
        Start Game
      </v-btn>
      
      <!-- Result display -->
      <div v-if="gameComplete" class="result-display mt-4">
        <v-alert
          :type="score >= 70 ? 'success' : score >= 50 ? 'warning' : 'error'"
          variant="tonal"
          class="galaxy-alert"
        >
          <div class="score-display text-h6">
            <v-icon size="22" class="mr-2">mdi-star</v-icon>
            Score: {{ score }}%
            <v-icon size="22" class="ml-2">mdi-star</v-icon>
          </div>
          <div class="result-message">{{ resultMessage }}</div>
        </v-alert>
      </div>
    </v-card-text>
    
    <v-card-actions v-if="gameComplete" class="galaxy-actions">
      <v-btn
        color="primary"
        block
        class="continue-btn"
        @click="submitResult"
      >
        <v-icon start>mdi-check</v-icon>
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
const isShowingSequence = ref(true)
const gameComplete = ref(false)
const currentIndex = ref(0)
const playerSequence = ref([])
const correctSequence = ref([])
const pressedBtn = ref(null)
const timeRemaining = ref(100)
const timeWarning = ref(false)

const buttons = ['↑', '↓', '←', '→', 'A', 'B']

let timerInterval = null

const score = computed(() => {
  if (playerSequence.value.length === 0) return 0
  
  const correct = correctSequence.value.filter(c => c === true).length
  const total = props.gameData.sequence.length
  
  return Math.round((correct / total) * 100)
})

const resultMessage = computed(() => {
  if (score.value >= 90) return 'Perfect! You nailed it!'
  if (score.value >= 70) return 'Great job! Well done!'
  if (score.value >= 50) return 'Good performance!'
  if (score.value >= 30) return 'Not bad, but room for improvement.'
  return 'Better luck next time!'
})

function startGame() {
  isPlaying.value = true
  isShowingSequence.value = true
  gameComplete.value = false
  currentIndex.value = 0
  playerSequence.value = []
  correctSequence.value = []
  timeRemaining.value = 100
  
  // Show sequence first
  setTimeout(() => {
    isShowingSequence.value = false
    startTimer()
  }, props.gameData.time_limit || 2000)
}

function startTimer() {
  const totalTime = props.gameData.time_limit || 3000
  const interval = 50
  const decrement = (interval / totalTime) * 100
  
  timerInterval = setInterval(() => {
    timeRemaining.value -= decrement
    timeWarning.value = timeRemaining.value < 30
    
    if (timeRemaining.value <= 0) {
      endGame()
    }
  }, interval)
}

function handleButtonPress(btn) {
  if (!isPlaying.value || isShowingSequence.value || gameComplete.value) return
  
  pressedBtn.value = btn
  setTimeout(() => { pressedBtn.value = null }, 100)
  
  const expectedBtn = props.gameData.sequence[currentIndex.value]
  const isCorrect = btn === expectedBtn
  
  correctSequence.value.push(isCorrect)
  playerSequence.value.push(btn)
  currentIndex.value++
  
  if (currentIndex.value >= props.gameData.sequence.length) {
    endGame()
  }
}

function endGame() {
  isPlaying.value = false
  gameComplete.value = true
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
}

function submitResult() {
  loading.value = true
  
  // Call the API to submit the result
  fetch(`/api/characters/${props.characterId}/mini-game`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      game_type: 'qte',
      score: score.value,
      game_data: props.gameData
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
    // Still emit result even if API fails
    emit('complete', {
      score: score.value,
      effects: {},
      message: resultMessage.value,
      outcome: 'neutral'
    })
  })
}

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})
</script>

<style scoped>
.qte-game {
  background: transparent !important;
  border: 1px solid rgba(0, 255, 204, 0.2) !important;
}

/* Game title styling */
.game-title {
  background: linear-gradient(90deg, rgba(0, 255, 204, 0.1) 0%, rgba(155, 89, 182, 0.1) 100%) !important;
  border-bottom: 1px solid rgba(0, 255, 204, 0.3) !important;
  color: #00ffcc !important;
  padding: 16px !important;
}

.game-icon {
  color: #00ffcc !important;
  filter: drop-shadow(0 0 8px rgba(0, 255, 204, 0.8));
  animation: iconPulse 2s ease-in-out infinite;
}

@keyframes iconPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.title-text {
  font-weight: 600;
  letter-spacing: 1px;
  text-shadow: 0 0 10px rgba(0, 255, 204, 0.5);
}

/* Content area */
.game-content {
  padding: 20px !important;
}

/* Instructions with stars */
.instructions {
  color: #9e9e9e;
  font-size: 14px;
  text-shadow: 0 0 5px rgba(0, 255, 204, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.instructions .v-icon {
  color: #9b59b6 !important;
  font-size: 16px !important;
}

/* Galaxy progress bar */
.galaxy-progress {
  background: rgba(0, 0, 0, 0.3) !important;
  border: 1px solid rgba(0, 255, 204, 0.2) !important;
}

.galaxy-progress .v-progress-linear__bar {
  background: linear-gradient(90deg, #00ffcc, #9b59b6) !important;
  box-shadow: 0 0 10px rgba(0, 255, 204, 0.5);
}

/* Sequence display */
.sequence-display {
  background: rgba(0, 0, 0, 0.2);
  padding: 15px;
  border-radius: 12px;
  border: 1px solid rgba(0, 255, 204, 0.15);
}

.sequence-label {
  display: flex;
  align-items: center;
  justify-content: center;
}

.sequence-label .v-icon {
  color: #9b59b6 !important;
}

.label-text {
  color: #b0b0b0;
  font-size: 13px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.sequence-buttons {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 4px;
}

.instructions {
  color: #b0b0b0;
  font-size: 14px;
  text-shadow: 0 0 5px rgba(0, 255, 204, 0.3);
}

/* Galaxy styled sequence buttons */
.sequence-btn {
  display: inline-block;
  padding: 10px 14px;
  margin: 3px;
  background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4a 100%);
  border: 1px solid rgba(0, 255, 204, 0.3);
  border-radius: 8px;
  font-size: 18px;
  font-weight: bold;
  min-width: 44px;
  transition: all 0.2s;
  color: #e0e0e0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.sequence-btn:hover {
  border-color: rgba(0, 255, 204, 0.6);
  box-shadow: 0 0 15px rgba(0, 255, 204, 0.3);
}

.sequence-btn.completed {
  background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
  border-color: rgba(76, 175, 80, 0.6);
  color: #a5d6a7;
  box-shadow: 0 0 10px rgba(76, 175, 80, 0.4);
}

.sequence-btn.current {
  background: linear-gradient(135deg, #e65100 0%, #ef6c00 100%);
  border-color: rgba(255, 152, 0, 0.8);
  color: #ffcc80;
  animation: cosmicPulse 0.5s infinite;
  box-shadow: 0 0 20px rgba(255, 152, 0, 0.5);
}

@keyframes cosmicPulse {
  0%, 100% { transform: scale(1); box-shadow: 0 0 15px rgba(255, 152, 0, 0.4); }
  50% { transform: scale(1.05); box-shadow: 0 0 25px rgba(255, 152, 0, 0.7); }
}

.sequence-btn.correct {
  background: linear-gradient(135deg, #00c853 0%, #00e676 100%);
  border-color: rgba(0, 255, 204, 0.8);
  color: #fff;
  animation: correctFlash 0.3s ease-out;
  box-shadow: 0 0 20px rgba(0, 255, 204, 0.6);
}

.sequence-btn.incorrect {
  background: linear-gradient(135deg, #c62828 0%, #d32f2f 100%);
  border-color: rgba(244, 67, 54, 0.8);
  color: #ffcdd2;
  animation: incorrectShake 0.3s ease-out;
  box-shadow: 0 0 15px rgba(244, 67, 54, 0.5);
}

@keyframes correctFlash {
  0% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

@keyframes incorrectShake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

/* Galaxy styled game buttons */
.button-pad {
  padding: 15px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 16px;
  border: 1px solid rgba(0, 255, 204, 0.2);
}

.game-btn {
  background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4a 100%) !important;
  border: 2px solid rgba(0, 255, 204, 0.4) !important;
  color: #00ffcc !important;
  font-weight: bold;
  font-size: 16px;
  transition: all 0.15s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.game-btn:hover {
  border-color: rgba(0, 255, 204, 0.9) !important;
  box-shadow: 0 0 25px rgba(0, 255, 204, 0.5), 0 4px 20px rgba(0, 0, 0, 0.4);
  transform: translateY(-2px);
}

.game-btn:active {
  transform: translateY(1px);
  box-shadow: 0 0 30px rgba(0, 255, 204, 0.7);
}

/* Start button */
.start-btn {
  background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4a 100%) !important;
  border: 2px solid rgba(0, 255, 204, 0.5) !important;
  color: #00ffcc !important;
  font-weight: 600;
  letter-spacing: 1px;
  box-shadow: 0 4px 20px rgba(0, 255, 204, 0.3);
}

.start-btn:hover {
  border-color: rgba(0, 255, 204, 0.9) !important;
  box-shadow: 0 0 30px rgba(0, 255, 204, 0.5), 0 6px 25px rgba(0, 0, 0, 0.4);
  transform: translateY(-2px);
}

/* Galaxy alert styling */
.galaxy-alert {
  background: rgba(0, 0, 0, 0.4) !important;
  border: 1px solid rgba(0, 255, 204, 0.3) !important;
  border-radius: 12px !important;
}

.score-display {
  color: #00ffcc !important;
  text-shadow: 0 0 15px rgba(0, 255, 204, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}

.score-display .v-icon {
  color: #ffd700 !important;
  filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8));
}

.result-message {
  color: #b0b0b0;
  font-size: 14px;
}

/* Card actions */
.galaxy-actions {
  background: linear-gradient(90deg, rgba(0, 255, 204, 0.05) 0%, rgba(155, 89, 182, 0.05) 100%) !important;
  border-top: 1px solid rgba(0, 255, 204, 0.2) !important;
  padding: 12px 16px !important;
}

/* Continue button */
.continue-btn {
  background: linear-gradient(135deg, #00ffcc 0%, #00bfa5 100%) !important;
  color: #0d0d1a !important;
  font-weight: 600;
  letter-spacing: 1px;
  box-shadow: 0 4px 20px rgba(0, 255, 204, 0.4);
}

.continue-btn:hover {
  box-shadow: 0 0 30px rgba(0, 255, 204, 0.6), 0 6px 25px rgba(0, 0, 0, 0.4);
  transform: translateY(-2px);
}
</style>
