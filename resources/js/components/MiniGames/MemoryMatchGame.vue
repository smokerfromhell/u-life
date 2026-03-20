<template>
  <v-card class="memory-game" max-width="600" :loading="loading">
    <v-card-title class="text-center">
      <v-icon size="24" class="mr-2">mdi-brain</v-icon>
      Memory Match
    </v-card-title>
    
    <v-card-text class="text-center">
      <div class="instructions mb-4">
        Find all matching pairs! Time: {{ formatTime(elapsedTime) }}
      </div>
      
      <!-- Stats -->
      <div class="game-stats mb-4">
        <v-chip class="ma-1" color="primary" variant="outlined">
          Pairs: {{ matchedPairs }}/{{ gameData.pairs }}
        </v-chip>
        <v-chip class="ma-1" color="warning" variant="outlined">
          Moves: {{ moves }}
        </v-chip>
      </div>
      
      <!-- Timer bar -->
      <v-progress-linear
        v-if="isPlaying && gameData.time_limit"
        :model-value="timeRemaining"
        color="primary"
        height="8"
        rounded
        class="mb-4"
      ></v-progress-linear>
      
      <!-- Cards grid -->
      <div 
        class="cards-grid" 
        :style="{ gridTemplateColumns: `repeat(${gridSize}, 1fr)` }"
      >
        <div
          v-for="(card, idx) in cards"
          :key="idx"
          class="memory-card"
          :class="{ 
            'flipped': card.flipped, 
            'matched': card.matched,
            'disabled': isProcessing
          }"
          @click="flipCard(idx)"
        >
          <div class="card-inner">
            <div class="card-front">?</div>
            <div class="card-back">{{ card.symbol }}</div>
          </div>
        </div>
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
import { ref, computed, onUnmounted } from 'vue'

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
const isProcessing = ref(false)
const gameComplete = ref(false)
const cards = ref([])
const flippedCards = ref([])
const matchedPairs = ref(0)
const moves = ref(0)
const elapsedTime = ref(0)
const timeRemaining = ref(100)

let timerInterval = null

const gridSize = computed(() => props.gameData.grid_size || 4)

const score = computed(() => {
  if (matchedPairs.value === 0) return 0
  
  // Calculate score based on pairs found, moves, and time
  const pairs = props.gameData.pairs || 8
  const baseScore = (matchedPairs.value / pairs) * 100
  
  // Penalize for too many moves
  const optimalMoves = pairs * 2
  const movePenalty = Math.max(0, (moves.value - optimalMoves) / optimalMoves) * 30
  
  // Penalize for time (if time limit exists)
  let timePenalty = 0
  if (gameData.time_limit && elapsedTime.value > 0) {
    timePenalty = Math.min(20, (elapsedTime.value / (props.gameData.time_limit / 1000)) * 20)
  }
  
  return Math.max(0, Math.round(baseScore - movePenalty - timePenalty))
})

const resultMessage = computed(() => {
  if (score.value >= 90) return 'Perfect memory! Amazing!'
  if (score.value >= 70) return 'Great memory! Well done!'
  if (score.value >= 50) return 'Good job! Keep practicing!'
  return 'Memory needs work. Try again!'
})

function startGame() {
  // Initialize cards
  const symbols = [...props.gameData.cards]
  cards.value = symbols.map((symbol, idx) => ({
    symbol,
    flipped: false,
    matched: false,
    originalIndex: idx
  }))
  
  // Shuffle cards
  shuffleArray(cards.value)
  
  isPlaying.value = true
  gameComplete.value = false
  flippedCards.value = []
  matchedPairs.value = 0
  moves.value = 0
  elapsedTime.value = 0
  timeRemaining.value = 100
  
  // Start timer
  startTimer()
}

function shuffleArray(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [array[i], array[j]] = [array[j], array[i]]
  }
}

function startTimer() {
  if (!props.gameData.time_limit) return
  
  const totalTime = props.gameData.time_limit
  const interval = 100
  
  timerInterval = setInterval(() => {
    elapsedTime.value += interval
    timeRemaining.value = 100 - (elapsedTime.value / totalTime * 100)
    
    if (timeRemaining.value <= 0) {
      endGame()
    }
  }, interval)
}

function flipCard(idx) {
  if (isProcessing.value || !isPlaying.value) return
  if (cards.value[idx].flipped || cards.value[idx].matched) return
  if (flippedCards.value.length >= 2) return
  
  // Flip the card
  cards.value[idx].flipped = true
  flippedCards.value.push(idx)
  
  if (flippedCards.value.length === 2) {
    moves.value++
    checkMatch()
  }
}

function checkMatch() {
  isProcessing.value = true
  
  const [firstIdx, secondIdx] = flippedCards.value
  const firstCard = cards.value[firstIdx]
  const secondCard = cards.value[secondIdx]
  
  if (firstCard.symbol === secondCard.symbol) {
    // Match found!
    setTimeout(() => {
      firstCard.matched = true
      secondCard.matched = true
      matchedPairs.value++
      flippedCards.value = []
      isProcessing.value = false
      
      // Check if game is complete
      if (matchedPairs.value >= props.gameData.pairs) {
        endGame()
      }
    }, 500)
  } else {
    // No match - flip back
    setTimeout(() => {
      firstCard.flipped = false
      secondCard.flipped = false
      flippedCards.value = []
      isProcessing.value = false
    }, 1000)
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

function formatTime(ms) {
  const seconds = Math.floor(ms / 1000)
  const minutes = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${minutes}:${secs.toString().padStart(2, '0')}`
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
      game_type: 'memory_match',
      score: score.value,
      game_data: {
        ...props.gameData,
        moves: moves.value,
        time: elapsedTime.value
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

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})
</script>

<style scoped>
.memory-game {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.cards-grid {
  display: grid;
  gap: 10px;
  max-width: 400px;
  margin: 0 auto;
}

.memory-card {
  aspect-ratio: 1;
  perspective: 1000px;
  cursor: pointer;
}

.memory-card.disabled {
  cursor: not-allowed;
}

.card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  text-align: center;
  transition: transform 0.6s;
  transform-style: preserve-3d;
}

.memory-card.flipped .card-inner,
.memory-card.matched .card-inner {
  transform: rotateY(180deg);
}

.card-front, .card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-size: 28px;
}

.card-front {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: bold;
}

.card-back {
  background: white;
  transform: rotateY(180deg);
  font-size: 32px;
}

.memory-card.matched .card-back {
  background: #4caf50;
}

.game-stats {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
}
</style>
