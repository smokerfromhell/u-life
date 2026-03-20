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
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.question-card {
  background: rgba(255, 255, 255, 0.05);
}

.option-btn {
  text-transform: none;
  justify-content: flex-start;
  padding-left: 20px;
}

.progress-info {
  display: flex;
  justify-content: center;
}
</style>
