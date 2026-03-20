<template>
  <v-card class="qte-game" max-width="500" :loading="loading">
    <v-card-title class="text-center">
      <v-icon size="24" class="mr-2">mdi-gesture-tap</v-icon>
      Quick Time Event!
    </v-card-title>
    
    <v-card-text class="text-center">
      <div class="instructions mb-4">
        Press the buttons in the correct sequence!
      </div>
      
      <!-- Timer -->
      <v-progress-linear
        v-if="isPlaying"
        :model-value="timeRemaining"
        :color="timeWarning ? 'warning' : 'primary'"
        height="10"
        rounded
        class="mb-4"
      ></v-progress-linear>
      
      <!-- Current sequence display -->
      <div class="sequence-display mb-4">
        <div class="target-sequence mb-2">
          <span class="text-grey">Watch: </span>
          <span 
            v-for="(btn, idx) in gameData.sequence" 
            :key="'target-' + idx"
            class="sequence-btn"
            :class="{ 'completed': idx < currentIndex, 'current': idx === currentIndex && isShowingSequence }"
          >
            {{ btn }}
          </span>
        </div>
        
        <div class="player-sequence" v-if="playerSequence.length > 0">
          <span class="text-grey">Your input: </span>
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
        class="mt-4"
      >
        <v-icon start>mdi-play</v-icon>
        Start Game
      </v-btn>
      
      <!-- Result display -->
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
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.sequence-btn {
  display: inline-block;
  padding: 8px 12px;
  margin: 2px;
  background: #333;
  border-radius: 8px;
  font-size: 18px;
  font-weight: bold;
  min-width: 40px;
  transition: all 0.2s;
}

.sequence-btn.completed {
  background: #4caf50;
  color: white;
}

.sequence-btn.current {
  background: #ff9800;
  color: white;
  animation: pulse 0.5s infinite;
}

.sequence-btn.correct {
  background: #4caf50;
  color: white;
}

.sequence-btn.incorrect {
  background: #f44336;
  color: white;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.button-pad {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  max-width: 300px;
  margin: 0 auto;
}

.game-btn {
  min-width: 60px;
  min-height: 60px;
  font-size: 24px;
  font-weight: bold;
}

.game-btn:hover {
  transform: scale(1.05);
}
</style>
