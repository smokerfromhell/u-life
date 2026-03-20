<template>
  <v-dialog v-model="dialogVisible" persistent max-width="600" :fullscreen="isMobile">
    <v-card class="mini-game-wrapper">
      <v-card-title class="game-title">
        <v-icon class="mr-2">{{ gameIcon }}</v-icon>
        {{ gameTitle }}
      </v-card-title>
      
      <v-card-text class="pa-4">
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
.mini-game-wrapper {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
}

.game-title {
  background: rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
