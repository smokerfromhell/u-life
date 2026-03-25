<template>
  <v-card class="choice-chain-game" max-width="600" :loading="loading">
    <v-card-title class="text-center">
      <v-icon size="24" class="mr-2">mdi-chat-question</v-icon>
      Quick Decisions!
    </v-card-title>
    
    <v-card-text class="text-center">
      <div class="instructions mb-4">
        Answer quickly! Make {{ questions.length }} decisions.
      </div>
      
      <!-- Progress -->
      <div class="progress-info mb-4">
        <v-chip color="primary" variant="outlined">
          Question: {{ currentQuestionIndex + 1 }}/{{ questions.length }}
        </v-chip>
        <v-chip color="success" variant="outlined" class="ml-2">
          Correct: {{ correctCount }}
        </v-chip>
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
      
      <!-- Question -->
      <div v-if="currentQuestion && !gameComplete" class="question-display mb-4">
        <v-card variant="outlined" class="pa-4 question-card">
          <div class="text-h6 mb-4">{{ currentQuestion.question }}</div>
          
          <v-btn
            v-for="(option, idx) in currentQuestion.options"
            :key="idx"
            block
            :color="selectedAnswer === idx ? 'primary' : 'grey-darken-2'"
            class="mb-2 option-btn"
            :disabled="answerSelected"
            @click="selectAnswer(idx)"
          >
            {{ option }}
          </v-btn>
        </v-card>
        
        <!-- Feedback -->
        <v-alert
          v-if="showFeedback"
          :type="lastAnswerCorrect ? 'success' : 'error'"
          variant="tonal"
          class="mt-4"
        >
          {{ lastAnswerCorrect ? '✓ Correct!' : '✗ Wrong!' }}
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
      
      <!-- Result -->
      <div v-if="gameComplete" class="result-display mt-4">
        <v-alert
          :type="score >= 70 ? 'success' : score >= 50 ? 'warning' : 'error'"
          variant="tonal"
        >
          <div class="text-h6">Score: {{ score }}%</div>
          <div>Correct: {{ correctCount }}/{{ questions.length }}</div>
          <div class="mt-2">{{ resultMessage }}</div>
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
const gameComplete = ref(false)
const questions = ref([])
const currentQuestionIndex = ref(0)
const correctCount = ref(0)
const selectedAnswer = ref(null)
const answerSelected = ref(false)
const showFeedback = ref(false)
const lastAnswerCorrect = ref(false)
const timeRemaining = ref(100)
const timeWarning = ref(false)

let timerInterval = null

const currentQuestion = computed(() => {
  if (questions.value.length === 0) return null
  return questions.value[currentQuestionIndex.value] || null
})

const score = computed(() => {
  if (questions.value.length === 0) return 0
  return Math.round((correctCount.value / questions.value.length) * 100)
})

const resultMessage = computed(() => {
  if (score.value >= 90) return 'Outstanding decision making!'
  if (score.value >= 70) return 'Great choices! Well done!'
  if (score.value >= 50) return 'Good decisions!'
  return 'Room for improvement in decision making.'
})

function startGame() {
  questions.value = [...props.gameData.questions]
  currentQuestionIndex.value = 0
  correctCount.value = 0
  selectedAnswer.value = null
  answerSelected.value = false
  showFeedback.value = false
  isPlaying.value = true
  gameComplete.value = false
  timeRemaining.value = 100
  
  startQuestionTimer()
}

function startQuestionTimer() {
  const timePerQuestion = props.gameData.time_per_question || 8000
  const interval = 50
  const decrement = (interval / timePerQuestion) * 100
  
  if (timerInterval) clearInterval(timerInterval)
  
  timerInterval = setInterval(() => {
    timeRemaining.value -= decrement
    timeWarning.value = timeRemaining.value < 30
    
    if (timeRemaining.value <= 0) {
      // Time's up - treat as wrong answer
      handleTimeout()
    }
  }, interval)
}

function selectAnswer(idx) {
  if (answerSelected.value) return
  
  selectedAnswer.value = idx
  answerSelected.value = true
  
  const isCorrect = idx === currentQuestion.value.correct
  lastAnswerCorrect.value = isCorrect
  
  if (isCorrect) {
    correctCount.value++
  }
  
  showFeedback.value = true
  
  // Wait then move to next question
  setTimeout(() => {
    nextQuestion()
  }, 1500)
}

function handleTimeout() {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
  
  lastAnswerCorrect.value = false
  showFeedback.value = true
  answerSelected.value = true
  
  setTimeout(() => {
    nextQuestion()
  }, 1500)
}

function nextQuestion() {
  showFeedback.value = false
  selectedAnswer.value = null
  answerSelected.value = false
  
  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++
    timeRemaining.value = 100
    startQuestionTimer()
  } else {
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
  
  fetch(`/api/characters/${props.characterId}/mini-game`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      game_type: 'choice_chain',
      score: score.value,
      game_data: {
        ...props.gameData,
        correct_count: correctCount.value
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
.choice-chain-game {
  background: transparent !important;
}

.instructions {
  color: #b0b0b0;
  font-size: 14px;
  text-shadow: 0 0 5px rgba(0, 255, 204, 0.3);
}

/* Galaxy styled question card */
.question-card {
  background: linear-gradient(135deg, rgba(26, 10, 46, 0.6) 0%, rgba(13, 13, 26, 0.8) 100%) !important;
  border: 1px solid rgba(0, 255, 204, 0.3) !important;
  border-radius: 12px !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3), inset 0 0 30px rgba(0, 255, 204, 0.05);
}

.question-card .text-h6 {
  color: #00ffcc !important;
  text-shadow: 0 0 10px rgba(0, 255, 204, 0.4);
}

/* Galaxy styled option buttons - holographic effect */
.option-btn {
  text-transform: none;
  justify-content: flex-start;
  padding-left: 20px;
  background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4a 100%) !important;
  border: 1px solid rgba(0, 255, 204, 0.3) !important;
  color: #e0e0e0 !important;
  transition: all 0.2s ease;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.option-btn:hover:not(:disabled) {
  border-color: rgba(0, 255, 204, 0.7) !important;
  box-shadow: 0 0 20px rgba(0, 255, 204, 0.3), 0 4px 15px rgba(0, 0, 0, 0.3);
  transform: translateX(5px);
}

.option-btn:active:not(:disabled) {
  background: linear-gradient(135deg, #2d2d4a 0%, #3d3d5a 100%) !important;
  box-shadow: 0 0 25px rgba(0, 255, 204, 0.5);
}

/* Progress info styling */
.progress-info {
  display: flex;
  justify-content: center;
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
