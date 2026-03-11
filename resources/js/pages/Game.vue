<template>
  <div class="game-screen">
    <!-- Animated Background -->
    <div class="bg-particles">
      <div v-for="n in 30" :key="n" class="particle" :style="getParticleStyle(n)"></div>
    </div>
    <div class="bg-grid"></div>
    
    <!-- Main Game Container - Fixed View -->
    <div class="game-container">
      <!-- Header Section -->
      <div class="game-header">
        <div class="character-panel">
          <div class="character-card-enhanced">
            <div class="avatar-wrapper">
              <v-img :src="character.image" alt="player portrait" class="character-avatar" />
              <div class="avatar-ring"></div>
            </div>
            <div class="character-info">
              <h2 class="character-name">{{ character.name }}</h2>
              <div class="character-meta">
                <span class="meta-badge">{{ character.ageGroup }}</span>
                <span class="meta-divider">|</span>
                <span class="day-counter">Day {{ character.currentDay }}</span>
              </div>
            </div>
            <v-btn
              variant="tonal"
              size="small"
              class="stats-toggle-btn"
              @click="toggleStats"
              prepend-icon="mdi-account"
            >
              {{ showStats ? 'Hide' : 'Show' }} Info
            </v-btn>
          </div>

          <!-- Stats Panel - Collapsible -->
          <v-expand-transition>
            <div v-if="showStats" class="stats-panel-enhanced">
              <div class="profile-section">
                <div class="stat-row">
                  <span class="stat-label">Gender</span>
                  <span class="stat-value">{{ character.gender }}</span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Age Group</span>
                  <span class="stat-value">{{ character.ageGroup }}</span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Profession</span>
                  <span class="stat-value">{{ character.profession || 'None' }}</span>
                </div>
              </div>
              
              <v-divider class="my-3" />
              
              <!-- Stats Bars -->
              <div class="stats-section">
                <h4 class="section-title">STATISTICS</h4>
                <div v-for="(value, stat) in effectiveStats.visible" :key="stat" class="stat-bar-container">
                  <div class="stat-bar-header">
                    <span class="stat-icon">{{ getStatIcon(stat) }}</span>
                    <span class="stat-name">{{ stat }}</span>
                    <span class="stat-percent">{{ value }}%</span>
                  </div>
                  <div class="stat-bar-track">
                    <div 
                      class="stat-bar-fill" 
                      :style="{ width: value + '%', background: getStatGradient(value) }"
                    ></div>
                  </div>
                </div>
              </div>

              <v-divider class="my-3" />

              <!-- Skills & Talents -->
              <div class="badges-section">
                <div class="badges-group">
                  <h4 class="section-title">SKILLS</h4>
                  <div class="badge-list">
                    <v-chip
                      v-for="skill in character.skills"
                      :key="skill.name"
                      class="badge-chip skill-badge"
                      size="small"
                    >
                      <v-icon start size="12">mdi-star</v-icon>
                      {{ skill.name }}
                    </v-chip>
                  </div>
                </div>
                <div class="badges-group mt-3">
                  <h4 class="section-title">TALENTS</h4>
                  <div class="badge-list">
                    <v-chip
                      v-for="talent in character.talents"
                      :key="talent.name"
                      class="badge-chip talent-badge"
                      size="small"
                    >
                      <v-icon start size="12">mdi-sparkles</v-icon>
                      {{ talent.name }}
                    </v-chip>
                  </div>
                </div>
              </div>
            </div>
          </v-expand-transition>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
          <v-btn
            variant="outlined"
            size="small"
            prepend-icon="mdi-pencil"
            @click="editProfile"
            class="action-btn-header"
          >
            Edit
          </v-btn>
          <v-btn
            variant="outlined"
            size="small"
            color="warning"
            prepend-icon="mdi-content-save"
            @click="saveGame"
            :loading="isSavingGame"
            class="action-btn-header"
          >
            Save
          </v-btn>
          <v-btn
            variant="outlined"
            size="small"
            color="error"
            prepend-icon="mdi-logout"
            @click="logout"
            class="action-btn-header"
          >
            Exit
          </v-btn>
        </div>
      </div>

      <!-- Main Content Area - Scrollable -->
      <div class="game-content">
        <!-- Narration Box -->
        <div class="narration-panel">
          <div class="narration-header">
            <v-icon class="narration-icon">mdi-script-text</v-icon>
            <h4 class="narration-title">LIFE LOG</h4>
          </div>
          <div class="narration-body">
            <p v-if="narrationHistory.length === 0" class="narration-empty">
              Select an event to begin your journey...
            </p>
            <div class="narration-entries">
              <p v-for="(entry, i) in narrationHistory" :key="i" class="narration-entry">
                <span class="entry-bullet">▸</span>
                {{ entry }}
              </p>
            </div>
          </div>
        </div>

        <!-- Random Event Button -->
        <div class="action-section">
          <v-btn
            block
            size="large"
            class="random-event-btn"
            prepend-icon="mdi-dice-multiple"
            @click="randomEvent"
            :disabled="selectedEvent || loading"
          >
            🎲 Random Event
          </v-btn>
        </div>

        <!-- Events Grid - Fixed Card Sizes -->
        <div class="events-area">
          
          <!-- Daily Occurrences -->
          <div v-if="availableEvents.daily && availableEvents.daily.length > 0" class="event-section">
            <h3 class="section-header daily">
              <v-icon class="header-icon">mdi-calendar-today</v-icon>
              DAILY OCCURRENCES
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.daily"
                :key="`daily-${index}`"
                class="event-card-item"
                @click="!selectedEvent && selectEvent(event)"
                :class="{ 'disabled': selectedEvent }"
              >
                <div class="card-visual">
                  <v-img :src="event.image" cover class="card-img" />
                  <div class="card-type-badge daily">Daily</div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Cultural Events -->
          <div v-if="availableEvents.cultural && availableEvents.cultural.length > 0" class="event-section">
            <h3 class="section-header cultural">
              <v-icon class="header-icon">mdi-theater</v-icon>
              CULTURAL EVENTS
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.cultural"
                :key="`cultural-${index}`"
                class="event-card-item"
                @click="!selectedEvent && selectEvent(event)"
                :class="{ 'disabled': selectedEvent }"
              >
                <div class="card-visual">
                  <v-img :src="event.image" cover class="card-img" />
                  <div class="card-type-badge cultural">Cultural</div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Age-Specific Events -->
          <div v-if="availableEvents.ageSpecific && availableEvents.ageSpecific.length > 0" class="event-section">
            <h3 class="section-header story">
              <v-icon class="header-icon">mdi-account-heart</v-icon>
              YOUR STORY
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.ageSpecific"
                :key="`ageSpecific-${index}`"
                class="event-card-item"
                @click="!selectedEvent && selectEvent(event)"
                :class="{ 'disabled': selectedEvent }"
              >
                <div class="card-visual">
                  <v-img :src="event.image" cover class="card-img" />
                  <div class="card-type-badge story">Story</div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Profession Events -->
          <div v-if="availableEvents.profession && availableEvents.profession.length > 0" class="event-section">
            <h3 class="section-header career">
              <v-icon class="header-icon">mdi-briefcase</v-icon>
              PROFESSIONAL PATH
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.profession"
                :key="`profession-${index}`"
                class="event-card-item"
                @click="!selectedEvent && selectEvent(event)"
                :class="{ 'disabled': selectedEvent }"
              >
                <div class="card-visual">
                  <v-img :src="event.image" cover class="card-img" />
                  <div class="card-type-badge career">Career</div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Milestone -->
          <div v-if="availableEvents.milestone" class="event-section">
            <h3 class="section-header milestone">
              <v-icon class="header-icon">mdi-star-circle</v-icon>
              LIFE MILESTONE
            </h3>
            <div class="events-grid milestone-grid">
              <div
                class="event-card-item milestone-card"
                @click="showMilestone(availableEvents.milestone)"
              >
                <div class="card-visual">
                  <v-img :src="availableEvents.milestone.image || '/css/images/milestone.jpg'" cover class="card-img" />
                  <div class="card-type-badge milestone">Milestone</div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ availableEvents.milestone.title }}</h4>
                  <p class="card-desc">{{ availableEvents.milestone.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Game Over -->
          <div v-if="gameOver" class="event-section">
            <h3 class="section-header game-over">
              <v-icon class="header-icon">mdi-skull</v-icon>
              GAME OVER
            </h3>
            <div class="game-over-panel">
              <div class="game-over-content">
                <h3 class="game-over-title">Your journey has ended.</h3>
                <p class="game-over-text">You lived until Day {{ character.currentDay }} as a {{ character.ageGroup }}.</p>
                <v-btn color="primary" size="large" class="new-game-btn" @click="startNewGame">
                  Start New Life
                </v-btn>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


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
          <!-- Profile Image Upload -->
          <div class="mb-6 text-center">
            <div class="avatar-upload-container">
              <v-img
                :src="previewImage || editedCharacter.image || '/css/images/player.jpg'"
                class="avatar-preview"
              />
              <div class="avatar-upload-overlay" @click="triggerImageUpload">
                <v-icon size="32" color="white">mdi-camera</v-icon>
                <span class="upload-text">Change Photo</span>
              </div>
            </div>
            <input
              ref="imageInput"
              type="file"
              accept="image/*"
              @change="handleImageChange"
              style="display: none"
            />
          </div>

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
  </div>
</template>




<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// Generate random particle styles for background
const getParticleStyle = (n) => {
  const random = (min, max) => Math.random() * (max - min) + min
  return {
    left: random(0, 100) + '%',
    top: random(0, 100) + '%',
    animationDelay: random(0, 5) + 's',
    animationDuration: random(3, 8) + 's',
    width: random(2, 6) + 'px',
    height: random(2, 6) + 'px',
    opacity: random(0.3, 0.8)
  }
}

// Get gradient color based on stat value
const getStatGradient = (value) => {
  if (value > 70) return 'linear-gradient(90deg, #22c55e, #4ade80)'
  if (value < 30) return 'linear-gradient(90deg, #ef4444, #f87171)'
  return 'linear-gradient(90deg, #f59e0b, #fbbf24)'
}

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

// Image upload refs
const imageInput = ref(null)
const previewImage = ref(null)

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
  image: ""
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
    image: character.value.image
  }
  previewImage.value = null
  editProfileDialog.value = true
}

/**
 * Trigger file input for image upload
 */
const triggerImageUpload = () => {
  imageInput.value?.click()
}

/**
 * Handle image file selection
 */
const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Create preview URL
    previewImage.value = URL.createObjectURL(file)
    editedCharacter.value.image = file
  }
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

    // Check if there's a new image to upload
    if (editedCharacter.value.image && editedCharacter.value.image instanceof File) {
      // Upload image using the dedicated endpoint
      const formData = new FormData()
      formData.append('image', editedCharacter.value.image)
      formData.append('name', editedCharacter.value.name)

      const response = await fetch(`/api/characters/${character.value.id}/upload-image`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json'
        },
        body: formData
      })

      if (!response.ok) throw new Error('Failed to save profile')

      const data = await response.json()
      character.value.name = data.character.name
      character.value.image = data.character.image || character.value.image
    } else {
      // Just update name without image
      const response = await fetch(`/api/characters/${character.value.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          name: editedCharacter.value.name
        })
      })

      if (!response.ok) throw new Error('Failed to save profile')

      const data = await response.json()
      character.value.name = data.character.name
    }

    editProfileDialog.value = false
    previewImage.value = null
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
/* ========================================
   MAIN SCREEN - Enhanced Game Background
   ======================================== */
.game-screen {
  position: relative;
  background: linear-gradient(165deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
  min-height: 100vh;
  overflow: hidden;
}

/* Animated Background Particles */
.bg-particles {
  position: fixed;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 0;
}

.particle {
  position: absolute;
  background: radial-gradient(circle, rgba(134, 238, 135, 0.8), transparent);
  border-radius: 50%;
  animation: float-particle linear infinite;
}

@keyframes float-particle {
  0% {
    transform: translateY(0) scale(1);
    opacity: 0;
  }
  10% {
    opacity: 0.8;
  }
  90% {
    opacity: 0.8;
  }
  100% {
    transform: translateY(-100vh) scale(0.5);
    opacity: 0;
  }
}

/* Grid Pattern Overlay */
.bg-grid {
  position: fixed;
  inset: 0;
  background-image: 
    linear-gradient(rgba(134, 238, 135, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(134, 238, 135, 0.03) 1px, transparent 1px);
  background-size: 50px 50px;
  pointer-events: none;
  z-index: 1;
}

/* ========================================
   GAME CONTAINER - Fixed View Layout
   ======================================== */
.game-container {
  position: relative;
  z-index: 2;
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px;
  height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ========================================
   GAME HEADER - Character Panel
   ======================================== */
.game-header {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  flex-shrink: 0;
}

.character-panel {
  flex: 1;
  background: linear-gradient(155deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
  border: 2px solid rgba(134, 238, 135, 0.4);
  border-radius: 20px;
  padding: 24px;
  backdrop-filter: blur(20px);
  box-shadow: 
    0 10px 40px rgba(0, 0, 0, 0.5),
    0 0 20px rgba(34, 197, 94, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
  position: relative;
}

/* Player card corner decorations */
.character-panel::before {
  content: '♠';
  position: absolute;
  top: 12px;
  left: 16px;
  font-size: 1.5rem;
  color: rgba(34, 197, 94, 0.4);
}

.character-panel::after {
  content: '♠';
  position: absolute;
  bottom: 12px;
  right: 16px;
  font-size: 1.5rem;
  color: rgba(34, 197, 94, 0.4);
  transform: rotate(180deg);
}

.character-card-enhanced {
  display: flex;
  align-items: center;
  gap: 20px;
}

.avatar-wrapper {
  position: relative;
  flex-shrink: 0;
}

.character-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 3px solid #22c55e;
  object-fit: cover;
  box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
}

.avatar-ring {
  position: absolute;
  inset: -6px;
  border-radius: 50%;
  border: 2px solid rgba(34, 197, 94, 0.5);
  animation: ring-pulse 2s ease-in-out infinite;
}

@keyframes ring-pulse {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.1); opacity: 0.2; }
}

.character-info {
  flex: 1;
  min-width: 0;
}

.character-name {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #f0fdf4;
  text-shadow: 0 2px 10px rgba(34, 197, 94, 0.3);
  margin-bottom: 4px;
}

.character-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.meta-badge {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #000;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
  text-transform: capitalize;
}

.meta-divider {
  color: rgba(255, 255, 255, 0.3);
}

.day-counter {
  color: #86efac;
  font-size: 0.9rem;
  font-weight: 500;
}

.stats-toggle-btn {
  background: rgba(34, 197, 94, 0.1) !important;
  border: 1px solid rgba(34, 197, 94, 0.3) !important;
  color: #22c55e !important;
  font-size: 0.75rem !important;
  text-transform: none !important;
}

.stats-toggle-btn:hover {
  background: rgba(34, 197, 94, 0.2) !important;
}

/* ========================================
   STATS PANEL - Enhanced
   ======================================== */
.stats-panel-enhanced {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(134, 238, 135, 0.2);
}

.profile-section {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  border-left: 3px solid #22c55e;
}

.stat-row .stat-label {
  color: #94a3b8;
  font-size: 0.75rem;
  font-weight: 500;
}

.stat-row .stat-value {
  color: #f0fdf4;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}

.section-title {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 0.7rem;
  font-weight: 700;
  color: #22c55e;
  letter-spacing: 0.1em;
  margin-bottom: 12px;
  text-transform: uppercase;
}

/* Stats Bars */
.stats-section {
  margin-top: 16px;
}

.stat-bar-container {
  margin-bottom: 12px;
}

.stat-bar-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.stat-icon {
  font-size: 0.9rem;
}

.stat-name {
  flex: 1;
  color: #cbd5e1;
  font-size: 0.8rem;
  font-weight: 500;
}

.stat-percent {
  color: #22c55e;
  font-size: 0.75rem;
  font-weight: 600;
}

.stat-bar-track {
  height: 8px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
  overflow: hidden;
}

.stat-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
  box-shadow: 0 0 10px currentColor;
}

/* Badges Section */
.badges-section {
  margin-top: 8px;
}

.badges-group {
  margin-bottom: 8px;
}

.badge-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.badge-chip {
  font-size: 0.7rem !important;
}

.skill-badge {
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color: #000 !important;
  border: none !important;
}

.talent-badge {
  background: linear-gradient(135deg, #f59e0b, #d97706) !important;
  color: #000 !important;
  border: none !important;
}

/* ========================================
   HEADER ACTIONS
   ======================================== */
.header-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.action-btn-header {
  font-size: 0.75rem !important;
  text-transform: none !important;
}

/* ========================================
   GAME CONTENT - Scrollable Area
   ======================================== */
.game-content {
  flex: 1;
  overflow-y: auto;
  padding-right: 8px;
}

.game-content::-webkit-scrollbar {
  width: 6px;
}

.game-content::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.game-content::-webkit-scrollbar-thumb {
  background: #22c55e;
  border-radius: 3px;
}

/* ========================================
   NARRATION PANEL - Game Log
   ======================================== */
.narration-panel {
  background: linear-gradient(180deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98));
  border: 2px solid rgba(134, 238, 135, 0.3);
  border-radius: 16px;
  margin-bottom: 20px;
  overflow: hidden;
  box-shadow: 
    0 4px 20px rgba(0, 0, 0, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.narration-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  background: linear-gradient(90deg, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0.05));
  border-bottom: 2px solid rgba(34, 197, 94, 0.3);
}

.narration-icon {
  color: #22c55e !important;
}

.narration-title {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  color: #22c55e;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.narration-body {
  padding: 16px;
  max-height: 200px;
  overflow-y: auto;
}

.narration-empty {
  color: #64748b;
  text-align: center;
  font-style: italic;
  padding: 20px;
}

.narration-entries {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.narration-entry {
  color: #cbd5e1;
  font-size: 0.85rem;
  line-height: 1.5;
  padding: 10px 12px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 6px;
  border-left: 3px solid #22c55e;
  animation: fadeInEntry 0.3s ease;
}

@keyframes fadeInEntry {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.entry-bullet {
  color: #22c55e;
  margin-right: 8px;
}

/* ========================================
   ACTION SECTION - Draw Card Area
   ======================================== */
.action-section {
  margin-bottom: 24px;
  padding: 20px;
  background: linear-gradient(180deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
  border: 2px solid rgba(134, 238, 135, 0.3);
  border-radius: 16px;
  text-align: center;
}

.random-event-btn {
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color: #000 !important;
  font-weight: 700 !important;
  font-size: 1.1rem !important;
  border-radius: 12px !important;
  box-shadow: 
    0 6px 20px rgba(34, 197, 94, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
  transition: all 0.3s ease !important;
  padding: 12px 32px !important;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.random-event-btn:hover:not(:disabled) {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 
    0 12px 40px rgba(34, 197, 94, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
}

.random-event-btn:active:not(:disabled) {
  transform: translateY(0) scale(0.98);
}

/* ========================================
   EVENTS AREA
   ======================================== */
.events-area {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.event-section {
  background: linear-gradient(180deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
  border: 2px solid rgba(134, 238, 135, 0.2);
  border-radius: 20px;
  padding: 24px;
  position: relative;
  /* Card table felt effect */
  box-shadow: 
    inset 0 2px 10px rgba(0, 0, 0, 0.3),
    0 4px 20px rgba(0, 0, 0, 0.4);
}

/* Card zone decorations */
.event-section::before {
  content: '';
  position: absolute;
  top: -1px;
  left: 20px;
  right: 20px;
  height: 3px;
  background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.5), transparent);
  border-radius: 2px;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1.1rem;
  font-weight: 700;
  color: #f0fdf4;
  margin-bottom: 20px;
  padding: 12px 16px;
  background: linear-gradient(90deg, rgba(34, 197, 94, 0.15), transparent);
  border-radius: 8px;
  border-left: 4px solid #22c55e;
}

/* Card suit in header */
.section-header::before {
  content: '♠';
  font-size: 1.2rem;
  margin-right: 4px;
}

.section-header.daily::before {
  content: '♦';
  color: #22c55e;
}

.section-header.cultural::before {
  content: '♣';
  color: #a855f7;
}

.section-header.story::before {
  content: '♥';
  color: #f59e0b;
}

.section-header.career::before {
  content: '♠';
  color: #3b82f6;
}

.section-header.milestone {
  color: #fbbf24;
}

.section-header.game-over {
  color: #ef4444;
}

.header-icon {
  color: #22c55e !important;
}

.section-header.milestone .header-icon {
  color: #fbbf24 !important;
}

.section-header.game-over .header-icon {
  color: #ef4444 !important;
}

/* Events Grid - Card Hand Layout */
.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
  padding: 10px;
  /* Card hand effect - slight perspective */
  perspective: 1000px;
}

.milestone-grid {
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
}

/* Card stack effect - subtle offset shadows */
.event-section .events-grid .event-card-item:nth-child(1) {
  --card-offset: 0px;
}

.event-section .events-grid .event-card-item:nth-child(2) {
  --card-offset: 2px;
}

.event-section .events-grid .event-card-item:nth-child(3) {
  --card-offset: 4px;
}

/* Event Card Item - Playing Card Style */
.event-card-item {
  background: linear-gradient(155deg, #1e293b 0%, #0f172a 100%);
  border: 2px solid rgba(134, 238, 135, 0.3);
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  display: flex;
  flex-direction: column;
  position: relative;
  /* Card shadow for depth */
  box-shadow: 
    0 4px 6px rgba(0, 0, 0, 0.3),
    0 1px 3px rgba(0, 0, 0, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* Card corner decorations - like real playing cards */
.event-card-item::before {
  content: '';
  position: absolute;
  top: 8px;
  left: 8px;
  width: 24px;
  height: 24px;
  border: 2px solid rgba(134, 238, 135, 0.3);
  border-radius: 4px;
  z-index: 2;
}

.event-card-item::after {
  content: '';
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  border: 2px solid rgba(134, 238, 135, 0.3);
  border-radius: 4px;
  transform: rotate(180deg);
  z-index: 2;
}

/* Card hover effect - lift and glow like picking up a card */
.event-card-item:hover:not(.disabled) {
  border-color: #22c55e;
  transform: translateY(-12px) scale(1.02) rotateX(5deg);
  box-shadow: 
    0 20px 40px rgba(34, 197, 94, 0.3),
    0 8px 16px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  z-index: 10;
}

/* Disabled state - face down card */
.event-card-item.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  filter: grayscale(30%);
}

/* Card flip hint on hover */
.event-card-item:hover:not(.disabled) .card-img {
  transform: scale(1.05);
  filter: brightness(1.1);
}

.milestone-card {
  border-color: rgba(251, 191, 36, 0.5);
  background: linear-gradient(155deg, #292222 0%, #1a1515 100%);
}

.milestone-card::before,
.milestone-card::after {
  border-color: rgba(251, 191, 36, 0.5);
}

.milestone-card:hover:not(.disabled) {
  border-color: #fbbf24;
  box-shadow: 
    0 20px 40px rgba(251, 191, 36, 0.3),
    0 8px 16px rgba(0, 0, 0, 0.4);
}

/* Card Visual */
.card-visual {
  position: relative;
  height: 140px;
  overflow: hidden;
  /* Card image area */
  border-bottom: 1px solid rgba(134, 238, 135, 0.1);
}

.card-img {
  width: 100%;
  height: 100%;
  transition: transform 0.4s ease;
}

.event-card-item:hover:not(.disabled) .card-img {
  transform: scale(1.1);
}

.card-type-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.card-type-badge.daily {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #000;
}

.card-type-badge.cultural {
  background: linear-gradient(135deg, #a855f7, #7c3aed);
  color: #fff;
}

.card-type-badge.story {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #000;
}

.card-type-badge.career {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: #fff;
}

.card-type-badge.milestone {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  color: #000;
  font-size: 0.7rem;
}

/* Card Content */
.card-content {
  padding: 14px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.card-title {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  color: #f0fdf4;
  margin-bottom: 8px;
  line-height: 1.3;
}

.card-desc {
  font-size: 0.8rem;
  color: #94a3b8;
  line-height: 1.5;
  flex: 1;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* ========================================
   GAME OVER PANEL
   ======================================== */
.game-over-panel {
  background: linear-gradient(145deg, rgba(40, 20, 20, 0.9), rgba(20, 10, 10, 0.95));
  border: 2px solid #ef4444;
  border-radius: 16px;
  padding: 40px;
  text-align: center;
}

.game-over-content {
  max-width: 400px;
  margin: 0 auto;
}

.game-over-title {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #ef4444;
  margin-bottom: 12px;
}

.game-over-text {
  color: #cbd5e1;
  font-size: 1rem;
  margin-bottom: 24px;
}

.new-game-btn {
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color: #000 !important;
  font-weight: 600 !important;
}

/* ========================================
   DIALOG STYLES
   ======================================== */
.event-dialog {
  border: 2px solid #22c55e !important;
  border-radius: 16px !important;
  overflow: hidden;
}

.profile-dialog {
  border: 2px solid #22c55e !important;
  border-radius: 16px !important;
}

.cancel-btn {
  text-transform: none !important;
}

::v-deep(.v-dialog) {
  border: 2px solid #22c55e !important;
  border-radius: 16px !important;
}

::v-deep(.v-card) {
  border-radius: 12px !important;
  background: rgba(30, 41, 59, 0.98) !important;
}

::v-deep(.v-card-title) {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif !important;
  color: #22c55e !important;
}

::v-deep(.v-card-text) {
  color: #cbd5e1 !important;
}

/* ========================================
   EVENT DIALOG STYLES - Card Reveal Effect
   ======================================== */
.event-dialog {
  border: 3px solid #22c55e !important;
  border-radius: 24px !important;
  overflow: hidden;
  background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.6),
    0 0 40px rgba(34, 197, 94, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
  animation: dialogReveal 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes dialogReveal {
  0% {
    opacity: 0;
    transform: scale(0.8) translateY(20px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.dialog-image-container {
  position: relative;
  overflow: hidden;
}

.dialog-image {
  transition: transform 0.6s ease;
  filter: saturate(1.1);
}

/* Card glow effect on image */
.dialog-image-container::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    180deg,
    transparent 50%,
    rgba(15, 23, 42, 0.9) 100%
  );
  z-index: 1;
  pointer-events: none;
}

.dialog-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, transparent 50%);
  z-index: 2;
}

/* Event card decorations */
.dialog-image-container::after {
  content: '';
  position: absolute;
  top: 16px;
  left: 16px;
  right: 16px;
  bottom: 16px;
  border: 2px solid rgba(34, 197, 94, 0.3);
  border-radius: 12px;
  pointer-events: none;
  z-index: 2;
}

.dialog-title-container {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 24px;
  z-index: 3;
}

.dialog-title {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif !important;
  color: #22c55e !important;
  font-size: 1.5rem !important;
  font-weight: 700 !important;
  text-shadow: 
    2px 2px 4px rgba(0, 0, 0, 0.8),
    0 0 20px rgba(34, 197, 94, 0.5) !important;
  letter-spacing: 0.02em;
}

.dialog-text {
  color: #e2e8f0 !important;
  font-size: 1rem !important;
  line-height: 1.7;
  padding: 20px 28px !important;
  background: rgba(0, 0, 0, 0.3);
  margin: 0 !important;
}

/* Choice Cards */
.choices-container {
  margin: 24px 0;
  padding: 20px;
  border: 2px solid rgba(34, 197, 94, 0.4);
  border-radius: 16px;
  background: linear-gradient(180deg, rgba(34, 197, 94, 0.08), rgba(34, 197, 94, 0.02));
}

.choices-title {
  color: #22c55e !important;
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif !important;
  font-size: 0.9rem !important;
  font-weight: 700 !important;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  margin-bottom: 16px !important;
  text-align: center;
  text-shadow: 0 0 10px rgba(34, 197, 94, 0.5);
}

.choice-btn {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif !important;
  font-size: 0.95rem !important;
  font-weight: 600 !important;
  border: 2px solid rgba(34, 197, 94, 0.5) !important;
  color: #e2e8f0 !important;
  margin-bottom: 12px;
  padding: 14px 20px !important;
  border-radius: 12px !important;
  background: linear-gradient(180deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95)) !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.choice-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0.1)) !important;
  border-color: #22c55e !important;
  color: #22c55e !important;
  transform: translateX(8px);
  box-shadow: 
    0 6px 20px rgba(34, 197, 94, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.choice-btn:active:not(:disabled) {
  transform: translateX(4px) scale(0.98);
}

/* Cancel Button */
.cancel-btn {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif !important;
  font-weight: 600 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.1em !important;
  padding: 12px 32px !important;
  border-radius: 12px !important;
}

/* Dialog Card corners */
.event-dialog::before,
.event-dialog::after {
  content: '♠';
  position: absolute;
  font-size: 2rem;
  color: rgba(34, 197, 94, 0.3);
  z-index: 10;
}

.event-dialog::before {
  top: 20px;
  left: 20px;
}

.event-dialog::after {
  bottom: 20px;
  right: 20px;
  transform: rotate(180deg);
}

/* ========================================
   AVATAR UPLOAD STYLES
   ======================================== */
.avatar-upload-container {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto;
  border-radius: 50%;
  overflow: hidden;
  cursor: pointer;
  border: 3px solid #22c55e;
  box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
}

.avatar-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-upload-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.avatar-upload-container:hover .avatar-upload-overlay {
  opacity: 1;
}

.upload-text {
  color: white;
  font-size: 0.7rem;
  margin-top: 4px;
  font-weight: 500;
}
</style>
