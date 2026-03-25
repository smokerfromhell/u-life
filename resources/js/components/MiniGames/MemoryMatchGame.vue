<template>
  <v-card class="memory-game" max-width="600" :loading="loading">
    <v-card-title class="game-title text-center">
      <v-icon size="28" class="game-icon mr-2">mdi-brain</v-icon>
      <span class="title-text">Memory Match</span>
    </v-card-title>
    
    <v-card-text class="text-center game-content">
      <div class="instructions mb-4">
        <v-icon size="16" class="mr-1">mdi-star-shooting</v-icon>
        Find all matching pairs!
        <v-icon size="16" class="ml-1">mdi-clock-outline</v-icon>
        Time: {{ formatTime(elapsedTime) }}
      </div>
      
      <!-- Stats -->
      <div class="game-stats mb-4">
        <v-chip class="ma-1 stat-chip" color="primary" variant="outlined">
          <v-icon start size="16">mdi-link</v-icon>
          Pairs: {{ matchedPairs }}/{{ gameData.pairs }}
        </v-chip>
        <v-chip class="ma-1 stat-chip" color="warning" variant="outlined">
          <v-icon start size="16">mdi-gesture-tap</v-icon>
          Moves: {{ moves }}
        </v-chip>
      </div>
      
      <!-- Timer bar - galaxy styled -->
      <v-progress-linear
        v-if="isPlaying && gameData.time_limit"
        :model-value="timeRemaining"
        color="primary"
        height="8"
        rounded
        class="galaxy-progress mb-4"
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
            <div class="card-front">
              <v-icon size="24" class="star-icon">mdi-star-four-points</v-icon>
            </div>
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
        class="mt-4 start-btn"
      >
        <v-icon start>mdi-play</v-icon>
        Start Game
      </v-btn>
      
      <!-- Result -->
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
  if (props.gameData.time_limit && elapsedTime.value > 0) {
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
  background: transparent !important;
}

.instructions {
  color: #b0b0b0;
  font-size: 14px;
  text-shadow: 0 0 5px rgba(0, 255, 204, 0.3);
}

/* Galaxy styled cards grid */
.cards-grid {
  display: grid;
  gap: 12px;
  max-width: 400px;
  margin: 0 auto;
  padding: 15px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 16px;
  border: 1px solid rgba(0, 255, 204, 0.2);
}

/* Memory card container */
.memory-card {
  aspect-ratio: 1;
  perspective: 1000px;
  cursor: pointer;
}

.memory-card:hover:not(.disabled):not(.matched) .card-inner {
  box-shadow: 0 0 20px rgba(0, 255, 204, 0.4);
}

.memory-card.disabled {
  cursor: not-allowed;
}

/* Card inner with 3D flip */
.card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  text-align: center;
  transition: transform 0.6s;
  transform-style: preserve-3d;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.memory-card.flipped .card-inner,
.memory-card.matched .card-inner {
  transform: rotateY(180deg);
}

/* Card front (hidden side with cosmic design) */
.card-front, .card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 28px;
}

/* Galaxy themed card front - shows a constellation pattern */
.card-front {
  background: linear-gradient(135deg, #1a0a2e 0%, #2d1b4e 50%, #0a1a2e 100%);
  border: 2px solid rgba(155, 89, 182, 0.4);
  color: #9b59b6;
  font-weight: bold;
  position: relative;
  overflow: hidden;
}

.card-front::before {
  content: '✦';
  font-size: 32px;
  opacity: 0.6;
  text-shadow: 0 0 10px rgba(155, 89, 182, 0.8);
}

.card-front::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 20%, rgba(155, 89, 182, 0.2) 0%, transparent 30%),
    radial-gradient(circle at 80% 80%, rgba(0, 255, 204, 0.1) 0%, transparent 30%);
  pointer-events: none;
}

/* Card back (revealed side with symbol) */
.card-back {
  background: linear-gradient(135deg, #0d0d1a 0%, #1a1a2e 50%, #16213e 100%);
  transform: rotateY(180deg);
  font-size: 32px;
  border: 2px solid rgba(0, 255, 204, 0.4);
  color: #00ffcc;
  text-shadow: 0 0 10px rgba(0, 255, 204, 0.6);
}

/* Matched card - cosmic glow effect */
.memory-card.matched .card-back {
  background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #1b5e20 100%);
  border-color: rgba(0, 255, 204, 0.8);
  box-shadow: 0 0 25px rgba(0, 255, 204, 0.6), inset 0 0 20px rgba(0, 255, 204, 0.2);
  animation: matchedGlow 1s ease-out;
}

@keyframes matchedGlow {
  0% { transform: rotateY(180deg) scale(1.2); }
  50% { transform: rotateY(180deg) scale(1.1); }
  100% { transform: rotateY(180deg) scale(1); }
}

/* Game stats styling */
.game-stats {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 8px;
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
</style>
