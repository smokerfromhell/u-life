<template>
  <v-dialog v-model="dialogVisible" persistent max-width="600" :fullscreen="isMobile">
    <v-card class="mini-game-wrapper galaxy-dialog">
      <!-- Star field background -->
      <div class="game-starfield">
        <div v-for="n in 30" :key="'star-' + n" class="game-star" :class="{ 'twinkle': n % 3 === 0 }"></div>
      </div>
      
      <!-- Nebula overlay -->
      <div class="game-nebula"></div>
      
      <v-card-title class="game-title">
        <v-icon class="mr-2 cosmic-icon">{{ gameIcon }}</v-icon>
        <span class="game-title-text">{{ gameTitle }}</span>
      </v-card-title>
      
      <v-card-text class="pa-4 game-content">
        <component
          :is="gameComponent"
          :game-data="gameData"
          :character-id="characterId"
          @complete="handleGameComplete"
          ref="gameRef"
        />
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, defineAsyncComponent } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  gameType: {
    type: String,
    required: true,
    validator: (value) => ['qte', 'memory_match', 'choice_chain', 'timing'].includes(value)
  },
  gameData: {
    type: Object,
    required: true
  },
  characterId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'complete'])

const dialogVisible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const gameRef = ref(null)

const gameComponent = computed(() => {
  const components = {
    qte: defineAsyncComponent(() => import('./QTEGame.vue')),
    memory_match: defineAsyncComponent(() => import('./MemoryMatchGame.vue')),
    choice_chain: defineAsyncComponent(() => import('./ChoiceChainGame.vue')),
    timing: defineAsyncComponent(() => import('./TimingGame.vue'))
  }
  return components[props.gameType] || null
})

const gameTitle = computed(() => {
  const titles = {
    qte: 'Quick Time Event',
    memory_match: 'Memory Match',
    choice_chain: 'Quick Decisions',
    timing: 'Timing Challenge'
  }
  return titles[props.gameType] || 'Mini Game'
})

const gameIcon = computed(() => {
  const icons = {
    qte: 'mdi-gesture-tap',
    memory_match: 'mdi-brain',
    choice_chain: 'mdi-chat-question',
    timing: 'mdi-target'
  }
  return icons[props.gameType] || 'mdi-gamepad-variant'
})

const isMobile = computed(() => {
  return window.innerWidth < 600
})

function handleGameComplete(result) {
  emit('complete', result)
  dialogVisible.value = false
}
</script>

<style scoped>
.galaxy-dialog {
  position: relative;
  overflow: hidden;
  background: linear-gradient(180deg, #0d0d1a 0%, #1a0a2e 50%, #0a1a2e 100%) !important;
  border: 1px solid rgba(0, 255, 204, 0.2) !important;
  box-shadow: 
    0 0 40px rgba(155, 89, 182, 0.3),
    0 0 80px rgba(0, 255, 204, 0.1),
    inset 0 0 60px rgba(0, 0, 0, 0.5) !important;
}

.game-starfield {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 0;
}

.game-star {
  position: absolute;
  width: 2px;
  height: 2px;
  background: #fff;
  border-radius: 50%;
  opacity: 0.6;
}

.game-star:nth-child(1) { top: 5%; left: 10%; width: 1px; height: 1px; }
.game-star:nth-child(2) { top: 15%; left: 25%; width: 2px; height: 2px; }
.game-star:nth-child(3) { top: 8%; left: 45%; width: 1px; height: 1px; }
.game-star:nth-child(4) { top: 25%; left: 60%; width: 2px; height: 2px; }
.game-star:nth-child(5) { top: 12%; left: 75%; width: 1px; height: 1px; }
.game-star:nth-child(6) { top: 30%; left: 85%; width: 2px; height: 2px; }
.game-star:nth-child(7) { top: 40%; left: 15%; width: 1px; height: 1px; }
.game-star:nth-child(8) { top: 50%; left: 35%; width: 2px; height: 2px; }
.game-star:nth-child(9) { top: 35%; left: 55%; width: 1px; height: 1px; }
.game-star:nth-child(10) { top: 55%; left: 70%; width: 2px; height: 2px; }
.game-star:nth-child(11) { top: 45%; left: 90%; width: 1px; height: 1px; }
.game-star:nth-child(12) { top: 65%; left: 20%; width: 2px; height: 2px; }
.game-star:nth-child(13) { top: 70%; left: 40%; width: 1px; height: 1px; }
.game-star:nth-child(14) { top: 60%; left: 60%; width: 2px; height: 2px; }
.game-star:nth-child(15) { top: 75%; left: 80%; width: 1px; height: 1px; }
.game-star:nth-child(16) { top: 80%; left: 10%; width: 2px; height: 2px; }
.game-star:nth-child(17) { top: 85%; left: 30%; width: 1px; height: 1px; }
.game-star:nth-child(18) { top: 90%; left: 50%; width: 2px; height: 2px; }
.game-star:nth-child(19) { top: 88%; left: 70%; width: 1px; height: 1px; }
.game-star:nth-child(20) { top: 95%; left: 85%; width: 2px; height: 2px; }
.game-star:nth-child(21) { top: 20%; left: 5%; width: 1px; height: 1px; }
.game-star:nth-child(22) { top: 38%; left: 95%; width: 2px; height: 2px; }
.game-star:nth-child(23) { top: 58%; left: 5%; width: 1px; height: 1px; }
.game-star:nth-child(24) { top: 78%; left: 95%; width: 2px; height: 2px; }
.game-star:nth-child(25) { top: 5%; left: 95%; width: 1px; height: 1px; }
.game-star:nth-child(26) { top: 48%; left: 48%; width: 2px; height: 2px; }
.game-star:nth-child(27) { top: 22%; left: 88%; width: 1px; height: 1px; }
.game-star:nth-child(28) { top: 68%; left: 28%; width: 2px; height: 2px; }
.game-star:nth-child(29) { top: 82%; left: 58%; width: 1px; height: 1px; }
.game-star:nth-child(30) { top: 2%; left: 58%; width: 2px; height: 2px; }

.game-star.twinkle {
  animation: starTwinkle 2s ease-in-out infinite;
}

@keyframes starTwinkle {
  0%, 100% { opacity: 0.4; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.5); }
}

.game-nebula {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 1;
  background: 
    radial-gradient(ellipse at 20% 20%, rgba(155, 89, 182, 0.15) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 80%, rgba(0, 255, 204, 0.1) 0%, transparent 50%),
    radial-gradient(ellipse at 50% 50%, rgba(255, 0, 255, 0.05) 0%, transparent 70%);
}

.game-title {
  position: relative;
  z-index: 2;
  background: linear-gradient(90deg, rgba(0, 255, 204, 0.15) 0%, rgba(155, 89, 182, 0.15) 100%) !important;
  border-bottom: 1px solid rgba(0, 255, 204, 0.3) !important;
  color: #00ffcc !important;
  text-shadow: 0 0 10px rgba(0, 255, 204, 0.5);
}

.cosmic-icon {
  color: #00ffcc !important;
  filter: drop-shadow(0 0 6px rgba(0, 255, 204, 0.8));
}

.game-title-text {
  font-weight: 600;
  letter-spacing: 1px;
}

.game-content {
  position: relative;
  z-index: 2;
}
</style>
