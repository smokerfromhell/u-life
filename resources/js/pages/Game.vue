<template>
  <v-container fluid class="game-screen">
    <!-- Header Section -->
    <v-row class="mb-8">
      <v-col cols="12" md="4" lg="3">
        <div class="character-card pa-6 text-center h-100">
          <div class="avatar-container mb-4">
            <v-img :src="character.image" alt="player portrait" class="character-avatar" />
            <div class="avatar-glow"></div>
          </div>
          <h2 class="character-name mb-2">{{ character.name }}</h2>
          
          <!-- Age & Day Counter -->
          <div class="age-info mb-4">
            <span class="age-label">{{ character.ageGroup }} | Day {{ character.currentDay }}</span>
          </div>

          <v-btn
            variant="tonal"
            size="small"
            class="stats-btn mb-4"
            @click="toggleStats"
            prepend-icon="mdi-account"
          >
            Player Info
          </v-btn>

          <!-- Player Info Expansion Panel -->
          <v-expand-transition>
            <div v-if="showStats" class="w-full stats-panel mt-4" :key="statsPanelKey">
              <v-divider class="mb-4" />
              
              <!-- Profile Info -->
              <div class="stat-item mb-3">
                <span class="stat-label">Gender:</span>
                <span class="stat-value ml-2">{{ character.gender }}</span>
              </div>
              <div class="stat-item mb-3">
                <span class="stat-label">Age Group:</span>
                <span class="stat-value ml-2">{{ character.ageGroup }}</span>
              </div>
              <div class="stat-item mb-4">
                <span class="stat-label">Profession:</span>
                <span class="stat-value ml-2">{{ character.profession || 'None' }}</span>
              </div>
                  
              <!-- Stats Display -->
              <h4 class="stats-title mb-3">STATS</h4>
              <div v-for="(value, stat) in effectiveStats.visible" :key="stat" class="stat-bar-row mb-3">
                <div class="d-flex align-center gap-2">
                  <span class="stat-icon">{{ getStatIcon(stat) }}</span>
                  <span class="stat-label grow">{{ stat }}</span>
                  <span class="stat-value">{{ value }}%</span>
                </div>
                <v-progress-linear
                  :model-value="value"
                  :color="getStatColor(value)"
                  height="8"
                  class="mt-1"
                />
              </div>

              <v-divider class="my-4" />

              <!-- Skills -->
              <h4 class="stats-title mb-3">SKILLS</h4>
              <div class="skill-tags mb-4">
                <v-chip
                  v-for="skill in character.skills"
                  :key="skill.name"
                  class="skill-chip"
                  label
                  prepend-icon="mdi-star"
                >
                  {{ skill.name }}
                </v-chip>
              </div>

              <!-- Talents -->
              <h4 class="stats-title mb-3">TALENTS</h4>
              <div class="talent-tags">
                <v-chip
                  v-for="talent in character.talents"
                  :key="talent.name"
                  class="talent-chip"
                  label
                  prepend-icon="mdi-sparkles"
                >
                  {{ talent.name }}
                </v-chip>
              </div>
            </div>
          </v-expand-transition>
        </div>
      </v-col>

      <!-- Right Side: Actions and Game Info -->
      <v-col cols="12" md="8" lg="9" class="pl-md-4">
        <!-- Menu Bar -->
        <div class="d-flex justify-end gap-3 mb-4 flex-wrap">
          <v-btn
            variant="outlined"
            size="small"
            prepend-icon="mdi-pencil"
            @click="editProfile"
            class="menu-btn"
          >
            Edit Profile
          </v-btn>
          <v-btn
            variant="outlined"
            size="small"
            color="warning"
            prepend-icon="mdi-content-save"
            @click="saveGame"
            :loading="isSavingGame"
            class="menu-btn"
          >
            Save Game
          </v-btn>
          <v-btn
            variant="outlined"
            size="small"
            color="error"
            prepend-icon="mdi-logout"
            @click="logout"
            class="menu-btn"
          >
            Logout
          </v-btn>
        </div>

        <!-- Narration Box -->
        <v-card class="narration-card px-6 py-4 mb-4">
          <h4 class="narration-title mb-3">📜 GAME LOG</h4>
          <div class="narration-scroll">
            <p v-if="narrationHistory.length === 0" class="text-center text-muted">
              Start by selecting an event...
            </p>
            <p v-for="(entry, i) in narrationHistory" :key="i" class="narration-text mb-2">
              {{ entry }}
            </p>
          </div>
        </v-card>

        <!-- Action Buttons -->
        <v-row justify="center" class="mb-4">
          <v-col cols="12" sm="6" md="4">
            <v-btn
              block
              size="large"
              class="action-btn-primary"
              prepend-icon="mdi-dice-5"
              @click="randomEvent"
              :disabled="selectedEvent || loading"
            >
              🎲 Random Event
            </v-btn>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Event Sections - Horizontal Scroll Cards -->
    <v-row class="mb-6">
      <v-col cols="12">
        
        <!-- Daily Occurrences Section -->
        <div v-if="availableEvents.daily && availableEvents.daily.length > 0" class="mb-8">
          <h2 class="event-section-title mb-6">⭐ DAILY OCCURRENCES</h2>
          <div class="events-scroll-container">
            <div class="events-track">
              <div
                v-for="(event, index) in availableEvents.daily"
                :key="`daily-${index}`"
                class="event-card-wrapper"
              >
                <v-card
                  class="event-card"
                  :class="{ 'opacity-50 pointer-events-none': selectedEvent }"
                  @click="!selectedEvent && selectEvent(event)"
                >
                  <div class="card-image-container">
                    <v-img :src="event.image" height="160" cover class="card-image" />
                    <div class="card-overlay"></div>
                  </div>
                  <v-card-title class="event-card-title">{{ event.title }}</v-card-title>
                  <v-card-text class="event-card-text">
                    {{ event.description }}
                  </v-card-text>
                  <div class="card-badge">Daily</div>
                </v-card>
              </div>
            </div>
          </div>
        </div>

        <!-- Cultural Events Section -->
        <div v-if="availableEvents.cultural && availableEvents.cultural.length > 0" class="mb-8">
          <h2 class="event-section-title mb-6">🎭 CULTURAL EVENTS</h2>
          <div class="events-scroll-container">
            <div class="events-track">
              <div
                v-for="(event, index) in availableEvents.cultural"
                :key="`cultural-${index}`"
                class="event-card-wrapper"
              >
                <v-card
                  class="event-card"
                  :class="{ 'opacity-50 pointer-events-none': selectedEvent }"
                  @click="!selectedEvent && selectEvent(event)"
                >
                  <div class="card-image-container">
                    <v-img :src="event.image" height="160" cover class="card-image" />
                    <div class="card-overlay"></div>
                  </div>
                  <v-card-title class="event-card-title">{{ event.title }}</v-card-title>
                  <v-card-text class="event-card-text">
                    {{ event.description }}
                  </v-card-text>
                  <div class="card-badge cultural">Cultural</div>
                </v-card>
              </div>
            </div>
          </div>
        </div>

        <!-- Age-Specific Events Section -->
        <div v-if="availableEvents.ageSpecific && availableEvents.ageSpecific.length > 0" class="mb-8">
          <h2 class="event-section-title mb-6">🎯 YOUR STORY</h2>
          <div class="events-scroll-container">
            <div class="events-track">
              <div
                v-for="(event, index) in availableEvents.ageSpecific"
                :key="`ageSpecific-${index}`"
                class="event-card-wrapper"
              >
                <v-card
                  class="event-card"
                  :class="{ 'opacity-50 pointer-events-none': selectedEvent }"
                  @click="!selectedEvent && selectEvent(event)"
                >
                  <div class="card-image-container">
                    <v-img :src="event.image" height="160" cover class="card-image" />
                    <div class="card-overlay"></div>
                  </div>
                  <v-card-title class="event-card-title">{{ event.title }}</v-card-title>
                  <v-card-text class="event-card-text">
                    {{ event.description }}
                  </v-card-text>
                  <div class="card-badge story">Story</div>
                </v-card>
              </div>
            </div>
          </div>
        </div>

        <!-- Profession Events Section (Adults only) -->
        <div v-if="availableEvents.profession && availableEvents.profession.length > 0" class="mb-8">
          <h2 class="event-section-title mb-6">💼 PROFESSIONAL PATH</h2>
          <div class="events-scroll-container">
            <div class="events-track">
              <div
                v-for="(event, index) in availableEvents.profession"
                :key="`profession-${index}`"
                class="event-card-wrapper"
              >
                <v-card
                  class="event-card"
                  :class="{ 'opacity-50 pointer-events-none': selectedEvent }"
                  @click="!selectedEvent && selectEvent(event)"
                >
                  <div class="card-image-container">
                    <v-img :src="event.image" height="160" cover class="card-image" />
                    <div class="card-overlay"></div>
                  </div>
                  <v-card-title class="event-card-title">{{ event.title }}</v-card-title>
                  <v-card-text class="event-card-text">
                    {{ event.description }}
                  </v-card-text>
                  <div class="card-badge profession">Career</div>
                </v-card>
              </div>
            </div>
          </div>
        </div>

        <!-- Milestone Event Section -->
        <div v-if="availableEvents.milestone" class="mb-8">
          <h2 class="event-section-title mb-6">🌟 LIFE MILESTONE</h2>
          <div class="events-scroll-container">
            <div class="events-track justify-center">
              <div class="event-card-wrapper milestone-wrapper">
                <v-card
                  class="event-card milestone-card"
                  @click="showMilestone(availableEvents.milestone)"
                >
                  <div class="card-image-container">
                    <v-img src="/css/images/milestone.jpg" height="180" cover class="card-image" />
                    <div class="card-overlay"></div>
                  </div>
                  <v-card-title class="event-card-title">{{ availableEvents.milestone.title }}</v-card-title>
                  <v-card-text class="event-card-text">
                    {{ availableEvents.milestone.description }}
                  </v-card-text>
                  <div class="card-badge milestone">Milestone</div>
                </v-card>
              </div>
            </div>
          </div>
        </div>

        <!-- Game Over Section -->
        <div v-if="gameOver" class="mb-8">
          <h2 class="event-section-title mb-6 text-error">💀 GAME OVER</h2>
          <v-row justify="center">
            <v-col cols="12" sm="8" md="6">
              <v-card class="pa-6 text-center game-over-card">
                <h3 class="text-h5 mb-4 game-over-title">Your journey has ended.</h3>
                <p class="mb-4 game-over-text">You lived until Day {{ character.currentDay }} as a {{ character.ageGroup }}.</p>
                <v-btn color="primary" size="large" class="new-game-btn" @click="startNewGame">Start New Game</v-btn>
              </v-card>
            </v-col>
          </v-row>
        </div>
      </v-col>
    </v-row>


    <v-dialog v-model="showEventDialog" max-width="700" rounded="xl" @after-leave="onDialogClosed">
      <v-card v-if="selectedEvent" class="event-dialog">
        <div class="dialog-image-container">
          <v-img :src="selectedEvent.image" height="280" cover class="dialog-image" />
          <div class="dialog-overlay"></div>
          <div class="dialog-title-container">
            <v-card-title class="dialog-title">{{ selectedEvent.title }}</v-card-title>
          </div>
        </div>
        <v-card-text class="dialog-text px-6 py-4">
          <p class="text-body1 mb-6 text-center">{{ selectedEvent.description }}</p>
          
          <!-- Event Choices -->
          <div v-if="selectedEvent.choices && selectedEvent.choices.length > 0" class="choices-container">
            <p class="text-subtitle2 mb-4 text-center choices-title">How will you respond?</p>
            <div class="d-flex flex-column gap-3">
              <v-btn
                v-for="(choice, idx) in selectedEvent.choices"
                :key="idx"
                variant="outlined"
                size="large"
                class="choice-btn"
                @click="applyChoice(idx)"
                :disabled="applyingOutcome"
              >
                {{ choice.text || 'Accept' }}
              </v-btn>
            </div>
          </div>
        </v-card-text>
        <v-card-actions class="justify-center gap-3 pb-6">
          <v-btn
            variant="elevated"
            color="error"
            size="large"
            prepend-icon="mdi-stop"
            @click="closeEvent"
            :disabled="applyingOutcome"
            class="cancel-btn"
          >
            Cancel
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Loading Indicator -->
    <v-overlay v-model="loading" class="align-center justify-center">
      <v-progress-circular indeterminate size="64" color="success"></v-progress-circular>
    </v-overlay>

    <!-- Edit Profile Dialog -->
    <v-dialog v-model="editProfileDialog" max-width="500" rounded="xl">
      <v-card class="profile-dialog">
        <v-card-title class="text-center text-h5 dialog-title-main">Edit Profile</v-card-title>
        <v-card-text class="py-6">
          <div class="mb-4">
            <label class="text-subtitle2 mb-2 d-block">Character Name</label>
            <v-text-field
              v-model="editedCharacter.name"
              placeholder="Enter your character name"
              variant="outlined"
              dense
              :disabled="isSavingProfile"
            />
          </div>

          <div class="mb-4">
            <label class="text-subtitle2 mb-2 d-block">Gender</label>
            <v-select
              v-model="editedCharacter.gender"
              :items="['male', 'female', 'non-binary', 'transgender']"
              variant="outlined"
              dense
              :disabled="isSavingProfile"
            />
          </div>

          <div class="mb-4">
            <label class="text-subtitle2 mb-2 d-block">Age Group</label>
            <v-select
              v-model="editedCharacter.ageGroup"
              :items="['child', 'teenager', 'adult', 'old']"
              variant="outlined"
              dense
              :disabled="isSavingProfile"
            />
          </div>
        </v-card-text>
        <v-card-actions class="justify-center gap-3 pb-6">
          <v-btn
            variant="outlined"
            @click="closeEditProfile"
            :disabled="isSavingProfile"
          >
            Cancel
          </v-btn>
          <v-btn
            variant="elevated"
            color="success"
            prepend-icon="mdi-content-save"
            @click="saveProfile"
            :loading="isSavingProfile"
          >
            Save Changes
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>




<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const getRouteCharacterId = () => {
  const id = route.params.characterId
  if (Array.isArray(id)) return id[0] || null
  return id ?? null
}

const loading = ref(false)
const showStats = ref(false)
const selectedEvent = ref(null)
const showEventDialog = ref(false)
const applyingOutcome = ref(false)
const narrationHistory = ref([])
const statsPanelKey = ref(0)

const character = ref({
  id: null,
  name: "Loading...",
  image: "/css/images/player.jpg",
  gender: "Male",
  ageGroup: "Adult",
  currentDay: 1,
  skills: [],
  talents: []
})

const effectiveStats = ref({
  visible: {
    Health: 70,
    Charisma: 65,
    Burnout: 20,
    Wealth: 50,
    Happiness: 85
  }
})

const availableEvents = ref({
  daily: [],
  cultural: [],
  ageSpecific: [],
  profession: [],
  milestone: null
})

// Edit profile dialog state
const editProfileDialog = ref(false)
const editedCharacter = ref({
  name: "",
  gender: "male",
  ageGroup: "adult"
})
const isSavingProfile = ref(false)
const isSavingGame = ref(false)
const gameOver = ref(false)

// Fetch character and events on mount
onMounted(async () => {
  // First fetch the character data
  await fetchCharacter()
  
  // If we have a character loaded (not showing selection screen), fetch events
  if (character.value?.id) {
    await fetchEvents()
  }
})

/**
 * Fetch character data
 */
const fetchCharacter = async () => {
  try {
    loading.value = true

    let id = getRouteCharacterId()

    if (!id) {
      const listResponse = await fetch('/api/characters', {
        headers: {
          'Accept': 'application/json'
        }
      })

      if (listResponse.status === 401 || listResponse.status === 403 || listResponse.status === 419) {
        throw new Error('Unauthenticated')
      }
      if (!listResponse.ok) throw new Error('Failed to fetch characters')

      const characters = await listResponse.json()
      if (!Array.isArray(characters) || characters.length === 0) {
        throw new Error('No character found')
      }

      id = characters[characters.length - 1]?.id
    }

    if (!id) throw new Error('Missing character id')

    const response = await fetch(`/api/characters/${id}`, {
      headers: {
        'Accept': 'application/json'
      }
    })

    if (response.status === 401 || response.status === 403 || response.status === 419) {
      throw new Error('Unauthenticated')
    }
    
    if (!response.ok) throw new Error('Failed to fetch character')
    
    const data = await response.json()
    console.log('Character data fetched:', data)
    
    character.value = {
      id: data.id,
      name: data.name,
      image: "/css/images/player.jpg",
      gender: data.gender,
      ageGroup: data.age_group,
      currentDay: data.current_day || 1,
      skills: data.skills || [],
      talents: data.talents || [],
      stats: data.stats || {},
      hiddenStats: data.hidden_stats || {},
      effectiveStats: data.effective_stats || {}
    }

    console.log('Character state after fetch:', character.value)
    console.log('Hidden stats:', character.value.hiddenStats)

    // Update effective stats for display - use effective_stats from database if available
    updateEffectiveStats()
    narrationHistory.value.push(`Welcome, ${character.value.name}! Your adventure begins...`)
  } catch (error) {
    console.error('Error fetching character:', error)
    narrationHistory.value.push('Error loading character. Please refresh the page.')

    if (String(error?.message || '').includes('Unauthenticated')) {
      router.push('/home')
      return
    }

    if (String(error?.message || '').includes('No character found')) {
      router.push('/character-creation')
    }
  } finally {
    loading.value = false
  }
}

/**
 * Fetch available events for character
 */
const fetchEvents = async () => {
  try {
    loading.value = true

    const id = character.value?.id || getRouteCharacterId()
    if (!id) throw new Error('Missing character id')

    const response = await fetch(`/api/characters/${id}/events`, {
      headers: {
        'Accept': 'application/json'
      }
    })
    
    if (!response.ok) throw new Error('Failed to fetch events')
    
    const data = await response.json()
    availableEvents.value = data
  } catch (error) {
    console.error('Error fetching events:', error)
    narrationHistory.value.push('Error loading events.')
  } finally {
    loading.value = false
  }
}

/**
 * Select an event to view details
 */
const selectEvent = (event) => {
  selectedEvent.value = event
  showEventDialog.value = true
  const narration = `You encounter: "${event.title}". ${event.description}`
  narrationHistory.value.push(narration)
}

/**
 * Close event dialog
 */
const closeEvent = () => {
  showEventDialog.value = false
  selectedEvent.value = null
}

/**
 * Handle dialog close (including clicking outside)
 */
const onDialogClosed = () => {
  selectedEvent.value = null
}

/**
 * Apply a choice outcome to the character
 */
const applyChoice = async (choiceIndex) => {
  if (!selectedEvent.value) return
  
  try {
    applyingOutcome.value = true
    
    const choice = selectedEvent.value.choices[choiceIndex]
    const choiceText = choice?.text || 'Accept'
    
    const response = await fetch(`/api/characters/${character.value.id}/apply-event`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        event_type: selectedEvent.value.type,
        event_id: selectedEvent.value.id,
        choice_index: choiceIndex
      })
    })
    
    // Check if response is ok, if not throw detailed error
    if (!response.ok) {
      let errorMessage = 'Failed to apply event outcome'
      try {
        const errorData = await response.json()
        errorMessage = errorData.message || errorData.error || errorMessage
        console.error('Server error details:', errorData)
      } catch (e) {
        // Response might not be JSON
        console.error('Error response:', response.status, response.statusText)
      }
      throw new Error(errorMessage)
    }
    
    const data = await response.json()
    console.log('Apply choice response:', data)
    console.log('Character stats from response:', data.character?.stats)
    
    // Update character stats - create new object to trigger Vue reactivity
    if (data.character) {
      character.value.stats = { ...(data.character.stats || {}) }
      character.value.hiddenStats = { ...(data.character.hidden_stats || {}) }
      character.value.effectiveStats = { ...(data.character.effective_stats || {}) }
      console.log('Updated character effectiveStats:', character.value.effectiveStats)
      
      // Update effective stats from backend response
      updateEffectiveStats()
    }
    
    // Log the choice and effects
    narrationHistory.value.push(`You chose: "${choiceText}"`)
    if (data.effects) {
      const effectsText = Object.entries(data.effects)
        .map(([stat, value]) => `${value > 0 ? '+' : ''}${value} ${stat}`)
        .join(', ')
      narrationHistory.value.push(`Effects: ${effectsText}`)
    }
    
    // Close dialog and prepare for next round
    closeEvent()
    await new Promise(resolve => setTimeout(resolve, 500))
    await continueGame()
  } catch (error) {
    console.error('Error applying event outcome:', error)
    narrationHistory.value.push('Error applying event outcome.')
  } finally {
    applyingOutcome.value = false
  }
}

/**
 * Continue to next round - refresh events and increment day
 */
const continueGame = async () => {
  character.value.currentDay++
  narrationHistory.value.push(`Day ${character.value.currentDay} begins...`)
  
  // Clear selectedEvent to re-enable card clicking
  selectedEvent.value = null
  
  await fetchEvents()
}

/**
 * Get a random event from any type
 */
const randomEvent = async () => {
  try {
    loading.value = true
    const response = await fetch(`/api/characters/${character.value.id}/random-event`, {
      headers: {
        'Accept': 'application/json'
      }
    })
    
    if (!response.ok) throw new Error('Failed to fetch random event')
    
    const data = await response.json()
    if (data.event) {
      selectEvent(data.event)
    }
  } catch (error) {
    console.error('Error fetching random event:', error)
    narrationHistory.value.push('Error loading random event.')
  } finally {
    loading.value = false
  }
}

/**
 * Toggle stats panel visibility
 */
const toggleStats = () => {
  showStats.value = !showStats.value
}

/**
 * Update effective stats for display (use values from backend)
 */
const updateEffectiveStats = () => {
  // Use the effective_stats from the backend if available
  const backendStats = character.value.effectiveStats || {}
  
  console.log('Backend effective stats:', backendStats)
  
  // If we have effective stats from backend, use them directly
  if (Object.keys(backendStats).length > 0) {
    const newStats = {
      Health: backendStats.Health ?? 50,
      Charisma: backendStats.Charisma ?? 50,
      Burnout: backendStats.Burnout ?? 0,
      Wealth: backendStats.Wealth ?? 50,
      Happiness: backendStats.Happiness ?? 50
    }
    
    console.log('Using backend stats:', newStats)
    
    // Force Vue reactivity by creating a completely new object
    const updatedStats = JSON.parse(JSON.stringify(newStats))
    effectiveStats.value = { visible: updatedStats }
  } else {
    // Fallback: use hidden_stats from character if available
    const hiddenStats = character.value.hiddenStats || {}
    
    const newStats = {
      Health: hiddenStats.Health ?? 50,
      Charisma: hiddenStats.Charisma ?? 50,
      Burnout: hiddenStats.Burnout ?? 0,
      Wealth: hiddenStats.Wealth ?? 50,
      Happiness: hiddenStats.Happiness ?? 50
    }
    
    console.log('Using hidden stats:', newStats)
    
    const updatedStats = JSON.parse(JSON.stringify(newStats))
    effectiveStats.value = { visible: updatedStats }
  }
  
  // Force re-render of stats panel by incrementing key
  statsPanelKey.value++
  
  console.log('Effective stats after update:', effectiveStats.value.visible)
}

/**
 * Get icon for stat
 */
const getStatIcon = (stat) => {
  const icons = {
    Health: "❤️",
    Charisma: "✨",
    Burnout: "🔥",
    Wealth: "💰",
    Happiness: "😊"
  }
  return icons[stat] || "📊"
}

/**
 * Get color for stat based on value
 */
const getStatColor = (value) => {
  if (value > 70) return "success"
  if (value < 30) return "error"
  return "warning"
}

/**
 * Save game progress
 */
const saveGame = async () => {
  try {
    isSavingGame.value = true

    const response = await fetch(`/api/characters/${character.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        stats: character.value.stats,
        hidden_stats: character.value.hiddenStats,
        current_day: character.value.currentDay,
        effective_stats: effectiveStats.value.visible
      })
    })

    if (!response.ok) throw new Error('Failed to save game')

    narrationHistory.value.push("✓ Game saved successfully!")
  } catch (error) {
    console.error('Error saving game:', error)
    narrationHistory.value.push("✗ Error saving game: " + error.message)
  } finally {
    isSavingGame.value = false
  }
}

/**
 * Save stats to database immediately after event choice
 */
const saveStatsToDb = async () => {
  try {
    const response = await fetch(`/api/characters/${character.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        stats: character.value.stats,
        hidden_stats: character.value.hiddenStats,
        current_day: character.value.currentDay,
        effective_stats: effectiveStats.value.visible
      })
    })
    
    if (response.ok) {
      console.log('Stats saved to database after event')
      
      // Fetch the updated character from database to ensure UI is in sync
      const characterResponse = await fetch(`/api/characters/${character.value.id}`, {
        headers: { 'Accept': 'application/json' }
      })
      if (characterResponse.ok) {
        const data = await characterResponse.json()
        character.value.stats = data.stats || {}
        character.value.hiddenStats = data.hidden_stats || {}
        character.value.effectiveStats = data.effective_stats || {}
        updateEffectiveStats()
        console.log('Stats re-fetched from database:', character.value.stats)
      }
    }
  } catch (error) {
    console.error('Error saving stats to database:', error)
  }
}

/**
 * Edit profile handler - Open dialog
 */
const editProfile = () => {
  editedCharacter.value = {
    name: character.value.name,
    gender: character.value.gender,
    ageGroup: character.value.ageGroup
  }
  editProfileDialog.value = true
}

/**
 * Save profile changes
 */
const saveProfile = async () => {
  if (!editedCharacter.value.name || !editedCharacter.value.name.trim()) {
    alert("Please enter a character name!")
    return
  }

  try {
    isSavingProfile.value = true

    const response = await fetch(`/api/characters/${character.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name: editedCharacter.value.name,
        gender: editedCharacter.value.gender.toLowerCase(),
        age_group: editedCharacter.value.ageGroup.toLowerCase()
      })
    })

    if (!response.ok) throw new Error('Failed to save profile')

    const data = await response.json()
    character.value.name = data.character.name
    character.value.gender = data.character.gender
    character.value.ageGroup = data.character.age_group

    editProfileDialog.value = false
    narrationHistory.value.push("✓ Profile updated successfully!")
  } catch (error) {
    console.error('Error saving profile:', error)
    alert('Error saving profile: ' + error.message)
  } finally {
    isSavingProfile.value = false
  }
}

/**
 * Close edit profile dialog
 */
const closeEditProfile = () => {
  editProfileDialog.value = false
}

/**
 * Logout handler
 */
const logout = () => {
  window.location.href = '/logout'
}

/**
 * Show milestone event
 */
const showMilestone = (milestone) => {
  if (milestone) {
    narrationHistory.value.push(`🌟 ${milestone.title}: ${milestone.description}`)
  }
}

/**
 * Start new game
 */
const startNewGame = () => {
  gameOver.value = false
  router.push('/character-creation')
}
</script>




<style scoped>
/* Main Screen */
.game-screen {
  background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #0f1428 100%);
  min-height: 100vh;
  padding: 24px;
  font-family: 'Press Start 2P', monospace;
}

/* Character Card */
.character-card {
  background: linear-gradient(145deg, rgba(30, 35, 60, 0.9), rgba(15, 20, 40, 0.95)) !important;
  border: 3px solid #53f06a !important;
  border-radius: 16px !important;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(83, 240, 106, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.character-card:hover {
  border-color: #82f582 !important;
  box-shadow: 0 16px 48px rgba(83, 240, 106, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.15);
  transform: translateY(-4px);
}

/* Avatar Container with Glow */
.avatar-container {
  position: relative;
  display: inline-block;
}

.character-avatar {
  height: 140px;
  width: 140px;
  border: 4px solid #53f06a;
  border-radius: 50%;
  box-shadow: 0 0 30px rgba(83, 240, 106, 0.4), 0 0 60px rgba(83, 240, 106, 0.2);
  transition: all 0.3s ease;
  object-fit: cover;
}

.character-avatar:hover {
  transform: scale(1.05);
  box-shadow: 0 0 40px rgba(83, 240, 106, 0.5), 0 0 80px rgba(83, 240, 106, 0.3);
}

.avatar-glow {
  position: absolute;
  inset: -10px;
  background: radial-gradient(circle, rgba(83, 240, 106, 0.3), transparent 70%);
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
  pointer-events: none;
}

@keyframes pulse {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.1); }
}

.character-name {
  font-family: 'Press Start 2P', monospace;
  color: #53f06a;
  font-size: 16px;
  text-shadow: 0 0 20px rgba(83, 240, 106, 0.5), 2px 2px 0 rgba(0, 0, 0, 0.5);
  text-align: center;
}

.age-info {
  font-family: 'Press Start 2P', monospace;
  color: #82f582;
  font-size: 8px;
  text-transform: uppercase;
  letter-spacing: 2px;
  padding: 10px 16px;
  border: 2px solid #82f582;
  border-radius: 8px;
  text-align: center;
  background: rgba(83, 240, 106, 0.1);
}

.age-label {
  color: #82f582;
}

.stats-btn {
  margin-top: 24px;
  background: linear-gradient(135deg, #53f06a, #3ddc55) !important;
  color: #000 !important;
  font-family: 'Press Start 2P', monospace !important;
  border: 2px solid #000 !important;
  text-transform: uppercase;
  font-size: 9px !important;
  box-shadow: 0 4px 15px rgba(83, 240, 106, 0.4);
}

.stats-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(83, 240, 106, 0.5);
}

/* Stats Panel */
.stats-panel {
  background: rgba(0, 0, 0, 0.3);
  border-left: 3px solid #53f06a;
  padding: 16px;
  border-radius: 8px;
}

.stats-title {
  color: #53f06a;
  font-family: 'Press Start 2P', monospace;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.stat-item {
  display: flex;
  gap: 8px;
  color: #82f582;
}

.stat-label {
  font-family: 'Press Start 2P', monospace;
  font-size: 9px;
  color: #82f582;
}

.stat-value {
  font-family: 'Press Start 2P', monospace;
  font-size: 9px;
  color: #53f06a;
  font-weight: bold;
}

.stat-bar-row {
  background: rgba(0, 0, 0, 0.2);
  padding: 10px;
  border-radius: 6px;
  border-left: 3px solid #53f06a;
}

.stat-icon {
  font-size: 14px;
  min-width: 24px;
}

/* Chips for Skills and Talents */
.skill-tags,
.talent-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.skill-chip {
  background: linear-gradient(135deg, #53f06a, #3ddc55) !important;
  color: #000 !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 8px !important;
  border: 1px solid #000 !important;
}

.talent-chip {
  background: linear-gradient(135deg, #ffd700, #ffed4a) !important;
  color: #000 !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 8px !important;
  border: 1px solid #000 !important;
}

/* Menu Buttons */
.menu-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 8px !important;
}

/* Narration Card */
.narration-card {
  background: linear-gradient(145deg, rgba(20, 25, 45, 0.9), rgba(10, 15, 30, 0.95)) !important;
  border: 2px solid #82f582 !important;
  border-radius: 12px !important;
  backdrop-filter: blur(15px);
  height: 280px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.narration-title {
  color: #53f06a;
  font-family: 'Press Start 2P', monospace;
  font-size: 11px;
  text-transform: uppercase;
  text-shadow: 0 0 10px rgba(83, 240, 106, 0.5);
}

.narration-scroll {
  flex: 1;
  overflow-y: auto;
  padding-right: 8px;
}

.narration-text {
  color: #e0e0e0;
  font-family: 'Press Start 2P', monospace;
  font-size: 8px;
  line-height: 1.6;
  animation: fadeInText 0.3s ease;
  padding: 8px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  border-left: 2px solid #53f06a;
}

.narration-scroll::-webkit-scrollbar {
  width: 6px;
}

.narration-scroll::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.narration-scroll::-webkit-scrollbar-thumb {
  background: #53f06a;
  border-radius: 3px;
}

/* Event Section */
.event-section-title {
  font-family: 'Press Start 2P', monospace;
  color: #53f06a;
  font-size: 18px;
  text-align: center;
  text-shadow: 0 0 20px rgba(83, 240, 106, 0.5), 3px 3px 0 rgba(0, 0, 0, 0.5);
  margin-bottom: 24px;
  padding: 12px;
  background: linear-gradient(90deg, transparent, rgba(83, 240, 106, 0.1), transparent);
  border-radius: 8px;
}

/* Horizontal Scroll Container */
.events-scroll-container {
  overflow-x: auto;
  overflow-y: hidden;
  padding: 20px 0;
  margin: 0 -24px;
  padding-left: 24px;
  padding-right: 24px;
  scroll-behavior: smooth;
}

.events-scroll-container::-webkit-scrollbar {
  height: 8px;
}

.events-scroll-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

.events-scroll-container::-webkit-scrollbar-thumb {
  background: linear-gradient(90deg, #53f06a, #82f582);
  border-radius: 4px;
}

.events-track {
  display: flex;
  gap: 20px;
  padding-bottom: 10px;
}

.event-card-wrapper {
  flex: 0 0 280px;
  min-height: 320px;
}

.event-card {
  background: linear-gradient(145deg, rgba(30, 35, 55, 0.9), rgba(15, 20, 35, 0.95)) !important;
  border: 2px solid #82f582 !important;
  border-radius: 16px !important;
  overflow: hidden !important;
  cursor: pointer !important;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
  backdrop-filter: blur(15px);
  position: relative;
  height: 100%;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.event-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(83, 240, 106, 0.15), rgba(130, 245, 130, 0.05));
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: 1;
  pointer-events: none;
}

.event-card:hover::before {
  opacity: 1;
}

.event-card:hover {
  border-color: #53f06a !important;
  transform: translateY(-12px) scale(1.02);
  box-shadow: 0 20px 50px rgba(83, 240, 106, 0.3);
}

.card-image-container {
  position: relative;
  overflow: hidden;
}

.card-image {
  transition: transform 0.4s ease;
}

.event-card:hover .card-image {
  transform: scale(1.1);
}

.card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.6) 0%, transparent 50%);
  pointer-events: none;
}

.card-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: linear-gradient(135deg, #53f06a, #3ddc55);
  color: #000;
  font-family: 'Press Start 2P', monospace;
  font-size: 7px;
  padding: 6px 10px;
  border-radius: 20px;
  box-shadow: 0 4px 12px rgba(83, 240, 106, 0.4);
  z-index: 2;
}

.card-badge.cultural {
  background: linear-gradient(135deg, #a855f7, #c084fc);
  box-shadow: 0 4px 12px rgba(168, 85, 247, 0.4);
}

.card-badge.story {
  background: linear-gradient(135deg, #f59e0b, #fbbf24);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.card-badge.profession {
  background: linear-gradient(135deg, #3b82f6, #60a5fa);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.card-badge.milestone {
  background: linear-gradient(135deg, #ffd700, #ffed4a);
  box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
  font-size: 8px;
}

.event-card-title {
  font-family: 'Press Start 2P', monospace !important;
  color: #53f06a !important;
  font-size: 10px !important;
  text-transform: uppercase;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
  padding: 12px 16px !important;
}

.event-card-text {
  color: #a0c0a0 !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 8px !important;
  line-height: 1.5;
  padding: 0 16px 16px !important;
}

.event-card.opacity-50 {
  opacity: 0.4;
}

.pointer-events-none {
  pointer-events: none;
}

/* Milestone Card Special */
.milestone-card {
  border-color: #ffd700 !important;
}

.milestone-card::before {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(255, 235, 59, 0.1));
}

.milestone-card:hover {
  border-color: #ffd700 !important;
  box-shadow: 0 20px 50px rgba(255, 215, 0, 0.3);
}

.milestone-wrapper {
  flex: 0 0 320px;
}

/* Event Choices */
.choices-container {
  margin: 20px 0;
  padding: 16px;
  border: 2px solid #82f582;
  border-radius: 12px;
  background: rgba(83, 240, 106, 0.05);
}

.choices-title {
  color: #53f06a !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 9px !important;
  text-transform: uppercase;
}

.choice-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 9px !important;
  border-color: #82f582 !important;
  color: #82f582 !important;
}

.choice-btn:hover {
  background: rgba(83, 240, 106, 0.2) !important;
  border-color: #53f06a !important;
  color: #53f06a !important;
}

/* Action Buttons */
.action-btn-primary {
  background: linear-gradient(135deg, #53f06a, #3ddc55) !important;
  color: #000 !important;
  font-family: 'Press Start 2P', monospace !important;
  border: 3px solid #000 !important;
  text-transform: uppercase;
  font-size: 10px !important;
  box-shadow: 0 6px 20px rgba(83, 240, 106, 0.4);
  transition: all 0.3s ease;
}

.action-btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(83, 240, 106, 0.5);
}

.action-btn-primary:active {
  transform: translateY(1px);
  box-shadow: 0 3px 10px rgba(83, 240, 106, 0.3);
}

.action-btn {
  background: #82f582 !important;
  color: #000 !important;
  font-family: 'Press Start 2P', monospace !important;
  border: 2px solid #000 !important;
  text-transform: uppercase;
  font-size: 10px !important;
  min-height: 44px !important;
}

.action-btn:hover {
  background: #53f06a !important;
  box-shadow: 0 4px 12px rgba(83, 240, 106, 0.3);
}

/* Dialog Styles */
.event-dialog {
  border: 3px solid #53f06a !important;
  border-radius: 20px !important;
  overflow: hidden;
}

.dialog-image-container {
  position: relative;
}

.dialog-image {
  transition: transform 0.4s ease;
}

.dialog-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, transparent 60%);
}

.dialog-title-container {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 20px;
}

.dialog-title {
  font-family: 'Press Start 2P', monospace !important;
  color: #53f06a !important;
  font-size: 16px !important;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.dialog-text {
  color: #c0e0c0 !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 10px !important;
}

.cancel-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 9px !important;
}

.profile-dialog {
  border: 3px solid #53f06a !important;
  border-radius: 16px !important;
}

.dialog-title-main {
  font-family: 'Press Start 2P', monospace !important;
  color: #53f06a !important;
  padding: 20px !important;
}

::v-deep(.v-dialog) {
  border: 3px solid #53f06a !important;
  border-radius: 16px !important;
}

::v-deep(.v-card) {
  border-radius: 12px !important;
  background: rgba(20, 24, 40, 0.98) !important;
}

::v-deep(.v-card-title) {
  font-family: 'Press Start 2P', monospace !important;
  color: #53f06a !important;
  font-size: 16px !important;
  text-shadow: 2px 2px 0 rgba(0, 0, 0, 0.5);
}

::v-deep(.v-card-text) {
  color: #c0e0c0 !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 10px !important;
}

/* Game Over Card */
.game-over-card {
  background: linear-gradient(145deg, rgba(40, 20, 20, 0.95), rgba(20, 10, 10, 0.98)) !important;
  border: 3px solid #ef4444 !important;
}

.game-over-title {
  font-family: 'Press Start 2P', monospace !important;
  color: #ef4444 !important;
}

.game-over-text {
  font-family: 'Press Start 2P', monospace !important;
  color: #c0c0c0 !important;
  font-size: 10px;
}

.new-game-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 10px !important;
}

/* Animations */
@keyframes fadeInText {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Scrollbar styling for other elements */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(83, 240, 106, 0.1);
}

::-webkit-scrollbar-thumb {
  background: #53f06a;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #82f582;
}
</style>
