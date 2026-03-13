<template>
  <div
    class="game-screen galaxy-screen relative min-h-screen overflow-hidden"
    style="background: linear-gradient(135deg, #0d0d1a 0%, #1a0a2e 30%, #0a1a2e 50%, #0a1a1a 70%, #0d0d1a 100%);"
  >
    <div class="starfield pointer-events-none fixed inset-0 z-0">
      <div
        v-for="n in 90"
        :key="`star-${n}`"
        class="star absolute rounded-full"
        :class="{ fast: n % 9 === 0, slow: n % 13 === 0, shooting: n % 37 === 0 }"
        :style="getStarStyle(n)"
      ></div>
    </div>

    <div class="nebula pointer-events-none fixed inset-0 z-5"></div>

    <div class="grid-lines pointer-events-none fixed inset-0 z-10"></div>

    <div class="scanlines pointer-events-none fixed inset-0 z-25"></div>
    
    <div class="game-container relative z-20">
      <div class="game-header">
        <div class="character-panel">
          <div class="character-card-enhanced">
            <div class="avatar-wrapper">
              <v-img :src="character.image" alt="player portrait" class="character-avatar" />
              <div class="avatar-ring"></div>
            </div>
            
            <div class="character-main-info">
              <div class="character-info">
                <h2 class="character-name">{{ character.name }}</h2>
                <div class="character-meta">
                <span class="meta-badge">{{ character.ageGroup }}</span>
                <span class="meta-badge gender-badge">{{ character.gender }}</span>
                   <span class="meta-divider">|</span>
                   <span class="day-counter">Day {{ character.currentDay }}</span>
                  <span class="meta-divider">|</span>
                  <span class="profession-badge">{{ character.profession || 'No Profession' }}</span>
                </div>
              </div>
              
              <div class="header-stats">
                <div v-for="(value, stat) in effectiveStats.visible" :key="stat" class="header-stat-bar">
                  <div class="header-stat-header">
                    <span class="header-stat-icon">{{ getStatIcon(stat) }}</span>
                    <span class="header-stat-name">{{ stat }}</span>
                    <span class="header-stat-value">{{ value }}%</span>
                  </div>
                  <div class="header-stat-track">
                    <div 
                      class="header-stat-fill" 
                      :style="{ width: value + '%', background: getStatGradient(value) }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="character-actions">
              <v-btn
                variant="tonal"
                size="small"
                class="stats-toggle-btn"
                @click="toggleStats"
                prepend-icon="mdi-account"
              >
                {{ showStats ? 'Hide' : 'Show' }} Skills
              </v-btn>
              <v-btn
                variant="outlined"
                size="x-small"
                prepend-icon="mdi-pencil"
                @click="editProfile"
                class="action-btn-header"
              >
                Edit
              </v-btn>
              <v-btn
                variant="outlined"
                size="x-small"
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
                size="x-small"
                color="error"
                prepend-icon="mdi-logout"
                @click="logout"
                class="action-btn-header"
              >
                Exit
              </v-btn>
            </div>
          </div>

          <v-expand-transition>
            <div v-if="showStats" class="stats-panel-enhanced">
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
                    <span v-if="!character.skills || character.skills.length === 0" class="no-skills">No skills yet</span>
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
                    <span v-if="!character.talents || character.talents.length === 0" class="no-talents">No talents yet</span>
                  </div>
                </div>
              </div>
            </div>
          </v-expand-transition>
        </div>
      </div>

      <div class="game-content">
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

        <div class="action-section">
          <v-btn
            block
            size="large"
            class="random-event-btn"
            prepend-icon="mdi-dice-multiple"
            @click="randomEvent"
            :disabled="selectedEvent || loading"
          >
            Random Event
          </v-btn>
        </div>

        <div class="events-area">
          <div v-if="availableEvents.daily && availableEvents.daily.length > 0" class="event-section">
            <div class="section-header-wrapper">
              <h3 class="section-header daily">
                <v-icon class="header-icon">mdi-calendar-today</v-icon>
                <span class="section-header-text glitch" data-text="DAILY OCCURRENCES">DAILY OCCURRENCES</span>
              </h3>
              <v-btn
                size="x-small"
                variant="tonal"
                color="info"
                prepend-icon="mdi-refresh"
                @click="redrawEventType('daily')"
                :disabled="loading || !canRedrawDaily"
                class="redraw-section-btn"
              >
                {{ canRedrawDaily ? 'Re-Draw' : 'Used' }}
              </v-btn>
            </div>
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

          <div v-if="availableEvents.cultural && availableEvents.cultural.length > 0" class="event-section">
            <div class="section-header-wrapper">
              <h3 class="section-header cultural">
                <v-icon class="header-icon">mdi-theater</v-icon>
                <span class="section-header-text glitch" data-text="CULTURAL EVENTS">CULTURAL EVENTS</span>
              </h3>
              <v-btn
                size="x-small"
                variant="tonal"
                color="info"
                prepend-icon="mdi-refresh"
                @click="redrawEventType('cultural')"
                :disabled="loading || !canRedrawCultural"
                class="redraw-section-btn"
              >
                {{ canRedrawCultural ? 'Re-Draw' : 'Used' }}
              </v-btn>
            </div>
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

          <div v-if="availableEvents.ageSpecific && availableEvents.ageSpecific.length > 0" class="event-section">
            <div class="section-header-wrapper">
              <h3 class="section-header story">
                <v-icon class="header-icon">mdi-account-heart</v-icon>
                <span class="section-header-text glitch" data-text="YOUR STORY">YOUR STORY</span>
              </h3>
              <v-btn
                size="x-small"
                variant="tonal"
                color="info"
                prepend-icon="mdi-refresh"
                @click="redrawEventType('ageSpecific')"
                :disabled="loading || !canRedrawAgeSpecific"
                class="redraw-section-btn"
              >
                {{ canRedrawAgeSpecific ? 'Re-Draw' : 'Used' }}
              </v-btn>
            </div>
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

          <div v-if="availableEvents.profession && availableEvents.profession.length > 0" class="event-section">
            <div class="section-header-wrapper">
              <h3 class="section-header career">
                <v-icon class="header-icon">mdi-briefcase</v-icon>
                <span class="section-header-text glitch" data-text="PROFESSIONAL PATH">PROFESSIONAL PATH</span>
              </h3>
              <v-btn
                size="x-small"
                variant="tonal"
                color="info"
                prepend-icon="mdi-refresh"
                @click="redrawEventType('profession')"
                :disabled="loading || !canRedrawProfession"
                class="redraw-section-btn"
              >
                {{ canRedrawProfession ? 'Re-Draw' : 'Used' }}
              </v-btn>
            </div>
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

          <div v-if="availableEvents.milestone" class="event-section">
            <h3 class="section-header milestone">
              <v-icon class="header-icon">mdi-star-circle</v-icon>
              <span class="section-header-text glitch" data-text="LIFE MILESTONE">LIFE MILESTONE</span>
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

          <div v-if="gameOver" class="event-section">
            <h3 class="section-header game-over">
              <v-icon class="header-icon">mdi-skull</v-icon>
              <span class="section-header-text glitch" data-text="GAME OVER">GAME OVER</span>
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


    <v-dialog
      v-model="showEventDialog"
      max-width="700"
      rounded="xl"
      content-class="event-dialog-content"
      @after-leave="onDialogClosed"
    >
      <v-card v-if="selectedEvent" class="event-dialog">
        <div class="dialog-glow" aria-hidden="true"></div>
        <div class="dialog-scanlines" aria-hidden="true"></div>
        <div class="dialog-image-container">
          <v-img :src="selectedEvent.image" height="280" cover class="dialog-image" />
          <div class="dialog-overlay"></div>
          <div class="dialog-title-container">
            <v-card-title class="dialog-title">
              <span class="dialog-title-text glitch" :data-text="selectedEvent.title">{{ selectedEvent.title }}</span>
            </v-card-title>
          </div>
        </div>
        <v-card-text class="dialog-text px-6 py-4">
          <p class="text-body1 mb-6 text-center">{{ selectedEvent.description }}</p>
          
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
            @click="closeEvent"
            :disabled="applyingOutcome"
            class="cancel-btn"
          >
            Cancel
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-overlay v-model="loading" class="align-center justify-center">
      <v-progress-circular indeterminate size="64" color="#00ffcc"></v-progress-circular>
    </v-overlay>

    <v-dialog v-model="editProfileDialog" max-width="450" rounded="xl" content-class="profile-dialog-content">
      <v-theme-provider theme="dark" with-background>
        <v-card class="profile-dialog profile-dialog--kiosk" theme="dark">
          <div class="profile-glow" aria-hidden="true"></div>
          <div class="profile-scanlines" aria-hidden="true"></div>
          <div class="profile-stars" aria-hidden="true"></div>
          <div class="profile-noise" aria-hidden="true"></div>

          <div class="profile-topbar">
            <div class="profile-topbar-left">
              <v-icon class="profile-topbar-icon" size="22" color="#00ffcc">mdi-account-edit</v-icon>
              <div class="profile-topbar-titles">
                <div class="profile-topbar-title">
                  <span class="profile-title-text glitch" data-text="PROFILE EDITOR">PROFILE EDITOR</span>
                </div>
                <div class="profile-topbar-subtitle">Neural Uplink • Secure Channel</div>
              </div>
            </div>

            <v-btn
              icon
              variant="text"
              class="profile-close-btn"
              @click="closeEditProfile"
              :disabled="isSavingProfile"
              aria-label="Close"
            >
              <v-icon size="20">mdi-close</v-icon>
            </v-btn>
          </div>
          <v-card-text class="profile-dialog-text">
            <div class="profile-layout">
              <div class="profile-left">
                <div class="profile-avatar-shell">
                  <div class="profile-avatar-ring profile-avatar-ring--pulse" aria-hidden="true"></div>
                  <div class="profile-avatar-ring profile-avatar-ring--spin" aria-hidden="true"></div>
                  <div class="avatar-ring avatar-ring--profile" aria-hidden="true"></div>

                  <div class="avatar-upload-container-large">
                    <v-img
                      :src="previewImage || editedCharacter.image || '/css/images/player.jpg'"
                      class="avatar-preview-large"
                    />
                    <div class="avatar-upload-overlay-large" @click="triggerImageUpload">
                      <v-icon class="avatar-camera-icon" size="36" color="white">mdi-camera</v-icon>
                      <span class="upload-text">Change Photo</span>
                    </div>
                  </div>
                </div>

                <div class="profile-hint">Tap the avatar to upload a new photo.</div>

                <input
                  ref="imageInput"
                  type="file"
                  accept="image/*"
                  @change="handleImageChange"
                  style="display: none"
                />
              </div>

              <div class="profile-right">
                <div class="mb-4">
                  <label class="text-subtitle2 mb-2 d-block profile-label">Character Name</label>
                  <v-text-field
                    v-model="editedCharacter.name"
                    placeholder="Enter your character name"
                    variant="outlined"
                    density="comfortable"
                    class="profile-input"
                    :disabled="isSavingProfile"
                    bg-color="rgba(0,0,0,0.3)"
                    color="#00ffcc"
                  />

                  <div class="mb-2">
                    <label class="text-subtitle2 mb-2 d-block profile-label">Data Sharing</label>
                    <v-switch
                      v-model="shareConsent"
                      inset
                      color="#00ffcc"
                      :disabled="isSavingProfile || savingConsent"
                      @update:modelValue="updateShareConsent"
                    >
                      <template #label>
                        <span style="font-family: 'VT323', monospace; font-size: 16px; color: rgba(255,255,255,0.75);">
                          {{ shareConsent ? 'Sharing enabled' : 'Private mode' }}
                        </span>
                      </template>
                    </v-switch>
                    <div class="profile-help">
                      Private mode skips decision logging. Sharing records your choices + stat changes to help improve the game.
                    </div>
                    <div v-if="isGuestUser" class="profile-help">
                      Guest sessions in private mode are deleted when you exit.
                    </div>
                  </div>
                  <div class="profile-help">Keep it short — it shows on your main HUD.</div>
                </div>
              </div>
            </div>
          </v-card-text>
          <v-card-actions class="profile-dialog-actions">
            <v-btn
              variant="outlined"
              color="error"
              @click="closeEditProfile"
              :disabled="isSavingProfile"
              class="profile-cancel-btn"
            >
              Cancel
            </v-btn>
            <v-btn
              variant="elevated"
              color="#00ffcc"
              prepend-icon="mdi-content-save"
              @click="saveProfile"
              :loading="isSavingProfile"
              class="profile-save-btn"
            >
              Save Changes
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-theme-provider>
    </v-dialog>
  </div>
</template>




<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// Generate galaxy star styles for background (seeded so it doesn't jump on re-render)
const seededRandom = (seed) => {
  const x = Math.sin(seed) * 10000
  return x - Math.floor(x)
}

const getStarStyle = (n) => {
  const colors = [
    '#00ffcc', // cyan
    '#ff00ff', // magenta
    '#00ccff', // light blue
    '#8b5cf6', // violet
    '#ffffff', // white
    '#00d4aa', // teal
  ]

  const color = colors[n % colors.length]
  const left = seededRandom(n * 17.13) * 100
  const top = seededRandom(n * 31.77) * 100
  const size = seededRandom(n * 7.91) * 2.6 + 1
  const duration = seededRandom(n * 11.03) * 5 + 3
  const delay = seededRandom(n * 23.41) * 4
  const opacity = seededRandom(n * 5.37) * 0.55 + 0.25

  return {
    left: `${left}%`,
    top: `${top}%`,
    width: `${size}px`,
    height: `${size}px`,
    background: color,
    boxShadow: `0 0 ${size * 2}px ${color}`,
    opacity,
    animationDuration: `${duration}s`,
    animationDelay: `${delay}s`,
  }
}

// Get gradient color based on stat value
const getStatGradient = (value) => {
  if (value > 70) return 'linear-gradient(90deg, #00ffcc, #00d4aa)'
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
const isGuestUser = ref(false)
const shareConsent = ref(false)
const savingConsent = ref(false)

// If the dialog is closed via scrim click / ESC, ensure cards are clickable again.
watch(showEventDialog, (isOpen) => {
  if (!isOpen) selectedEvent.value = null
})

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
const canRedrawDaily = ref(true)
const canRedrawCultural = ref(true)
const canRedrawAgeSpecific = ref(true)
const canRedrawProfession = ref(true)

// Fetch character and events on mount
onMounted(async () => {
  await refreshSessionUser()

  // First fetch the character data
  await fetchCharacter()
  
  // If we have a character loaded (not showing selection screen), fetch events
  if (character.value?.id) {
    await fetchEvents()
  }
})

const refreshSessionUser = async () => {
  try {
    const response = await fetch('/api/me', {
      headers: {
        'Accept': 'application/json'
      }
    })
    if (!response.ok) return

    const data = await response.json()
    isGuestUser.value = !!data?.user?.is_guest
    shareConsent.value = data?.user?.share_consent === true
  } catch (error) {
    console.error('Error fetching session user:', error)
  }
}

const updateShareConsent = async (value) => {
  const previousValue = shareConsent.value
  shareConsent.value = value

  try {
    savingConsent.value = true
    const response = await fetch('/api/consent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        share_consent: !!value
      })
    })
    if (!response.ok) throw new Error('Failed to update consent')
  } catch (error) {
    console.error('Error updating share consent:', error)
    shareConsent.value = previousValue
  } finally {
    savingConsent.value = false
  }
}

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
      character.value.profession = data.character.profession || character.value.profession
      character.value.ageGroup = data.character.age_group || character.value.ageGroup
      character.value.currentDay = data.character.current_day || data.current_day || character.value.currentDay
      console.log('Updated character effectiveStats:', character.value.effectiveStats)
      
      // Update effective stats from backend response
      updateEffectiveStats()
    }

    gameOver.value = data.game_over === true
    
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
 * Re-draw all event cards - can only be used once per day
 */
const redrawEvents = async () => {
  try {
    loading.value = true
    
    const response = await fetch(`/api/characters/${character.value.id}/redraw-events`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })
    
    const data = await response.json()
    
    if (!response.ok) {
      if (data.can_redraw === false) {
        canRedraw.value = false
        lastRedrawDate.value = data.last_redraw_date
        narrationHistory.value.push('⚠️ Re-draw already used today! Come back tomorrow.')
        return
      }
      throw new Error(data.message || 'Failed to re-draw events')
    }
    
    // Update available events with new ones
    if (data.events) {
      availableEvents.value = {
        daily: data.events.daily || [],
        cultural: data.events.cultural || [],
        ageSpecific: data.events.ageSpecific || [],
        profession: data.events.profession || [],
        milestone: data.events.milestone || null
      }
    }
    
    // Update re-draw state
    canRedraw.value = false
    lastRedrawDate.value = data.last_redraw_date
    
    narrationHistory.value.push('🔄 You shuffled the deck! New events have appeared.')
  } catch (error) {
    console.error('Error re-drawing events:', error)
    narrationHistory.value.push('Error re-drawing events: ' + error.message)
  } finally {
    loading.value = false
  }
}

/**
 * Re-draw a specific type of events - can only be used once per day
 */
const redrawEventType = async (eventType) => {
  try {
    loading.value = true
    
    const response = await fetch(`/api/characters/${character.value.id}/redraw-event-type`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        event_type: eventType
      })
    })
    
    const data = await response.json()
    
    if (!response.ok) {
      if (data.can_redraw === false) {
        // Set the specific flag to false based on event type
        if (eventType === 'daily') canRedrawDaily.value = false
        if (eventType === 'cultural') canRedrawCultural.value = false
        if (eventType === 'ageSpecific') canRedrawAgeSpecific.value = false
        if (eventType === 'profession') canRedrawProfession.value = false
        narrationHistory.value.push('⚠️ Re-draw already used today for this category!')
        return
      }
      throw new Error(data.message || 'Failed to re-draw events')
    }
    
    // Update available events with new ones for the specific type
    if (data.events) {
      const typeKey = eventType === 'ageSpecific' ? 'ageSpecific' : eventType
      availableEvents.value[typeKey] = data.events
    }
    
    // Set the specific flag to false based on event type
    if (eventType === 'daily') canRedrawDaily.value = false
    if (eventType === 'cultural') canRedrawCultural.value = false
    if (eventType === 'ageSpecific') canRedrawAgeSpecific.value = false
    if (eventType === 'profession') canRedrawProfession.value = false
    
    // Get friendly name for the event type
    const typeNames = {
      daily: 'Daily Occurrences',
      cultural: 'Cultural Events',
      ageSpecific: 'Your Story',
      profession: 'Professional Path'
    }
    narrationHistory.value.push(`🔄 You shuffled ${typeNames[eventType] || eventType}! New events have appeared.`)
  } catch (error) {
    console.error('Error re-drawing events:', error)
    narrationHistory.value.push('Error re-drawing events: ' + error.message)
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
  refreshSessionUser()
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
const logout = async () => {
  try {
    await saveGame()
    await fetch('/api/guest/exit', {
      method: 'POST',
      headers: {
        'Accept': 'application/json'
      }
    })
  } catch (error) {
    console.error('Error logging out:', error)
  } finally {
    router.push('/home')
  }
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
   MAIN SCREEN - Galaxy Theme (match Home.vue)
   ======================================== */
.game-screen {
  position: relative;
  --accent-rgb: 0, 255, 204; /* #00ffcc */
  --accent2-rgb: 139, 92, 246; /* #8b5cf6 */
  --indigo-rgb: 99, 102, 241; /* #6366f1 */
  --teal-rgb: 0, 212, 170; /* #00d4aa */

  color: #e0e0e0;
  background: linear-gradient(135deg, #0d0d1a 0%, #1a0a2e 30%, #0a1a2e 50%, #0a1a1a 70%, #0d0d1a 100%);
  min-height: 100vh;
  overflow: hidden;
}

/* ========================================
   GALAXY BACKGROUND - MOVING STARS
   ======================================== */
.starfield {
  background: transparent;
}

.star {
  animation: twinkle 4s ease-in-out infinite, drift 20s linear infinite;
}

.star.shooting {
  animation: shooting-star 3s ease-in-out infinite;
}

.star.fast {
  animation: twinkle 3s ease-in-out infinite, drift-fast 15s linear infinite;
}

.star.slow {
  animation: twinkle 5s ease-in-out infinite, drift-slow 30s linear infinite;
}

@keyframes twinkle {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 1; }
}

@keyframes drift {
  0% { transform: translateY(0) translateX(0); }
  100% { transform: translateY(30px) translateX(20px); }
}

@keyframes drift-fast {
  0% { transform: translateY(0) translateX(0); }
  100% { transform: translateY(50px) translateX(30px); }
}

@keyframes drift-slow {
  0% { transform: translateY(0) translateX(0); }
  100% { transform: translateY(20px) translateX(10px); }
}

@keyframes shooting-star {
  0% {
    transform: translateX(0) translateY(0);
    opacity: 1;
  }
  70% {
    opacity: 1;
  }
  100% {
    transform: translateX(300px) translateY(300px);
    opacity: 0;
  }
}

.nebula {
  background: 
    radial-gradient(ellipse at 20% 20%, rgba(var(--accent2-rgb), 0.18) 0%, transparent 40%),
    radial-gradient(ellipse at 80% 80%, rgba(var(--teal-rgb), 0.10) 0%, transparent 40%),
    radial-gradient(ellipse at 60% 40%, rgba(0, 206, 209, 0.10) 0%, transparent 35%),
    radial-gradient(ellipse at 40% 70%, rgba(255, 0, 255, 0.08) 0%, transparent 35%);
  animation: nebula-drift 30s ease-in-out infinite;
}

@keyframes nebula-drift {
  0%, 100% { 
    transform: translateX(0) translateY(0);
    opacity: 0.8;
  }
  25% { 
    transform: translateX(20px) translateY(-10px);
    opacity: 1;
  }
  50% { 
    transform: translateX(-10px) translateY(20px);
    opacity: 0.9;
  }
  75% { 
    transform: translateX(-20px) translateY(-15px);
    opacity: 1;
  }
}

.grid-lines {
  background: 
    linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
    linear-gradient(0deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
  background-size: 60px 60px;
  animation: grid-scroll 20s linear infinite;
}

@keyframes grid-scroll {
  0% { background-position: 0 0; }
  100% { background-position: 60px 60px; }
}

.scanlines {
  background: repeating-linear-gradient(
    0deg,
    rgba(0, 0, 0, 0.04),
    rgba(0, 0, 0, 0.04) 1px,
    transparent 1px,
    transparent 3px
  );
}

/* ========================================
   GAME CONTAINER - Fixed View Layout
   ======================================== */
.game-container {
  position: relative;
  z-index: 20;
  max-width: 1400px;
  margin: 0 auto;
  padding:
    calc(18px + env(safe-area-inset-top))
    calc(18px + env(safe-area-inset-right))
    calc(18px + env(safe-area-inset-bottom))
    calc(18px + env(safe-area-inset-left));
  height: 100vh;
  height: 100svh;
  height: 100dvh;
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
  background: rgba(20, 15, 35, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px;
  backdrop-filter: blur(20px);
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.45),
    0 0 60px rgba(var(--accent2-rgb), 0.12),
    0 0 60px rgba(var(--accent-rgb), 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.08);
  position: relative;
}

/* Player card corner decorations */
.character-panel::before {
  content: '♠';
  position: absolute;
  top: 12px;
  left: 16px;
  font-size: 1.5rem;
  color: rgba(var(--accent-rgb), 0.35);
}

.character-panel::after {
  content: '♠';
  position: absolute;
  bottom: 12px;
  right: 16px;
  font-size: 1.5rem;
  color: rgba(var(--accent-rgb), 0.35);
  transform: rotate(180deg);
}

.character-card-enhanced {
  display: flex;
  align-items: flex-start;
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
  border: 3px solid rgb(var(--accent-rgb));
  object-fit: cover;
  box-shadow: 0 0 20px rgba(var(--accent-rgb), 0.25);
}

.avatar-ring {
  position: absolute;
  inset: -6px;
  border-radius: 50%;
  border: 2px solid rgba(var(--accent2-rgb), 0.45);
  animation: ring-pulse 2s ease-in-out infinite;
}

.avatar-ring--profile {
  inset: auto;
  left: 50%;
  top: 50%;
  width: calc(var(--avatar-size, 140px) + 12px);
  height: calc(var(--avatar-size, 140px) + 12px);
  transform: translate(-50%, -50%);
  border-color: rgba(var(--accent2-rgb), 0.55);
  box-shadow:
    0 0 16px rgba(var(--accent-rgb), 0.16),
    0 0 24px rgba(var(--accent2-rgb), 0.14);
  animation: ring-pulse-centered 2s ease-in-out infinite !important;
}

@keyframes ring-pulse {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.1); opacity: 0.2; }
}

@keyframes ring-pulse-centered {
  0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.55; }
  50% { transform: translate(-50%, -50%) scale(1.08); opacity: 0.22; }
}

.character-main-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
}

.character-info {
  flex-shrink: 0;
}

.character-name {
  font-family: 'VT323', monospace;
  font-size: 1.9rem;
  font-weight: 700;
  color: #ffffff;
  text-shadow: 0 0 18px rgba(var(--accent-rgb), 0.15), 0 0 26px rgba(var(--accent2-rgb), 0.12);
  margin-bottom: 4px;
}

.character-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.meta-badge {
  background: linear-gradient(135deg, rgb(var(--accent2-rgb)) 0%, rgb(var(--indigo-rgb)) 50%, rgb(var(--teal-rgb)) 100%);
  color: #ffffff;
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
  color: rgb(var(--accent-rgb));
  font-size: 0.9rem;
  font-weight: 500;
}

.profession-badge {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
  text-transform: capitalize;
}

/* Header Stats - Always Visible */
.header-stats {
  display: flex;
  flex-direction: row;
  gap: 10px;
  flex-wrap: wrap;
}

.header-stat-bar {
  flex: 1;
  min-width: 90px;
  max-width: 120px;
  display: flex;
  flex-direction: column;
}

.header-stat-header {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-bottom: 3px;
}

.header-stat-icon {
  font-size: 0.7rem;
}

.header-stat-name {
  color: #94a3b8;
  font-size: 0.65rem;
  font-weight: 500;
  flex: 1;
}

.header-stat-value {
  color: rgb(var(--accent-rgb));
  font-size: 0.65rem;
  font-weight: 600;
}

.header-stat-track {
  height: 8px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
  overflow: hidden;
}

.header-stat-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.no-skills, .no-talents {
  color: #64748b;
  font-size: 0.75rem;
  font-style: italic;
}

.stats-toggle-btn {
  background: rgba(var(--accent-rgb), 0.08) !important;
  border: 1px solid rgba(var(--accent-rgb), 0.25) !important;
  color: rgb(var(--accent-rgb)) !important;
  font-size: 0.75rem !important;
  text-transform: none !important;
  font-family: 'VT323', monospace !important;
}

.stats-toggle-btn:hover {
  background: rgba(var(--accent-rgb), 0.14) !important;
}

/* Character Actions - Upper Right */
.character-actions {
  display: flex;
  flex-direction: row;
  gap: 8px;
  flex-shrink: 0;
}

.character-actions .v-btn {
  font-size: 0.65rem !important;
  padding: 4px 8px !important;
  min-width: auto;
}

/* ========================================
   STATS PANEL - Enhanced
   ======================================== */
.stats-panel-enhanced {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
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
  border-left: 3px solid rgb(var(--accent-rgb));
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
  font-family: 'Press Start 2P', monospace;
  font-size: 0.62rem;
  font-weight: 700;
  color: rgb(var(--accent-rgb));
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
  color: rgb(var(--accent-rgb));
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
  background: linear-gradient(135deg, rgb(var(--accent2-rgb)) 0%, rgb(var(--indigo-rgb)) 55%, rgb(var(--teal-rgb)) 100%) !important;
  color: #ffffff !important;
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
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}

.game-content::-webkit-scrollbar {
  width: 6px;
}

.game-content::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.game-content::-webkit-scrollbar-thumb {
  background: rgb(var(--accent-rgb));
  border-radius: 3px;
}

/* ========================================
   NARRATION PANEL - Game Log
   ======================================== */
.narration-panel {
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.92), rgba(15, 15, 30, 0.94));
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  margin-bottom: 20px;
  overflow: hidden;
  box-shadow: 
    0 4px 20px rgba(0, 0, 0, 0.3),
    0 0 40px rgba(var(--accent2-rgb), 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

.narration-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  background: linear-gradient(90deg, rgba(var(--accent2-rgb), 0.18), rgba(var(--accent-rgb), 0.06));
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.narration-icon {
  color: rgb(var(--accent-rgb)) !important;
}

.narration-title {
  font-family: 'Press Start 2P', monospace;
  font-size: 0.78rem;
  font-weight: 700;
  color: rgb(var(--accent-rgb));
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
  border-left: 3px solid rgb(var(--accent-rgb));
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
  color: rgb(var(--accent-rgb));
  margin-right: 8px;
}

/* ========================================
   ACTION SECTION - Draw Card Area
   ======================================== */
.action-section {
  margin-bottom: 24px;
  padding: 20px;
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.82), rgba(15, 15, 30, 0.9));
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  text-align: center;
  box-shadow:
    0 20px 60px rgba(0, 0, 0, 0.35),
    0 0 50px rgba(var(--accent2-rgb), 0.08);
}

.random-event-btn {
  background: linear-gradient(135deg, rgb(var(--accent2-rgb)) 0%, rgb(var(--indigo-rgb)) 50%, rgb(var(--teal-rgb)) 100%) !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  font-size: 1.1rem !important;
  border-radius: 12px !important;
  box-shadow: 
    0 6px 20px rgba(var(--accent2-rgb), 0.3),
    0 0 30px rgba(var(--accent-rgb), 0.18),
    inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
  transition: all 0.3s ease !important;
  padding: 12px 32px !important;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-family: 'VT323', monospace !important;
}

.random-event-btn:hover:not(:disabled) {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 
    0 12px 40px rgba(var(--accent2-rgb), 0.35),
    0 0 40px rgba(var(--accent-rgb), 0.25),
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
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.8) 0%, rgba(15, 15, 30, 0.9) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px;
  position: relative;
  /* Card table felt effect */
  box-shadow: 
    inset 0 2px 10px rgba(0, 0, 0, 0.3),
    0 4px 20px rgba(0, 0, 0, 0.4),
    0 0 50px rgba(var(--accent2-rgb), 0.08);
}

/* Card zone decorations */
.event-section::before {
  content: '';
  position: absolute;
  top: -1px;
  left: 20px;
  right: 20px;
  height: 3px;
  background: linear-gradient(90deg, transparent, rgba(var(--accent-rgb), 0.45), transparent);
  border-radius: 2px;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  font-family: 'Press Start 2P', monospace;
  font-size: 0.92rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 18px;
  padding: 10px 0;
  width: 100%;
  justify-content: center;
  text-align: center;
  letter-spacing: 0.14em;
  text-shadow:
    0 0 12px rgba(var(--accent-rgb), 0.18),
    0 0 20px rgba(var(--accent2-rgb), 0.12);
}

.section-header-wrapper {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  margin-bottom: 18px;
}

.section-header-wrapper .section-header {
  grid-column: 2;
  margin-bottom: 0;
}

.section-header-wrapper .redraw-section-btn {
  grid-column: 3;
  justify-self: end;
}

.redraw-section-btn {
  text-transform: none !important;
  font-size: 0.7rem !important;
}

/* Card suit in header */
.section-header::before {
  content: '♠';
  font-size: 1.2rem;
  margin-right: 4px;
}

.section-header.daily::before {
  content: '♦';
  color: rgb(var(--accent-rgb));
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
  color: rgb(var(--accent-rgb)) !important;
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
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.92) 0%, rgba(15, 15, 30, 0.94) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
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
    0 0 40px rgba(var(--accent2-rgb), 0.06),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

/* Card hover effect - lift and glow like picking up a card */
.event-card-item:hover:not(.disabled) {
  border-color: rgb(var(--accent-rgb));
  transform: translateY(-12px) scale(1.02) rotateX(5deg);
  box-shadow: 
    0 20px 40px rgba(var(--accent-rgb), 0.22),
    0 0 40px rgba(var(--accent2-rgb), 0.18),
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
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
  background: linear-gradient(135deg, rgb(var(--accent2-rgb)) 0%, rgb(var(--indigo-rgb)) 55%, rgb(var(--teal-rgb)) 100%);
  color: #fff;
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
  font-family: 'VT323', monospace;
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffffff;
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
  font-family: 'VT323', monospace;
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
  font-family: 'Press Start 2P', monospace;
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
  background: linear-gradient(135deg, rgb(var(--accent2-rgb)) 0%, rgb(var(--indigo-rgb)) 50%, rgb(var(--teal-rgb)) 100%) !important;
  color: #ffffff !important;
  font-weight: 600 !important;
}

/* ========================================
   DIALOG STYLES
   ======================================== */
.event-dialog {
  border: 2px solid rgb(var(--accent-rgb)) !important;
  border-radius: 16px !important;
  overflow: hidden;
}

.profile-dialog {
  border: 2px solid rgb(var(--accent-rgb)) !important;
  border-radius: 16px !important;
}

.cancel-btn {
  text-transform: none !important;
}

::v-deep(.v-dialog) {
  border: 2px solid rgb(var(--accent-rgb)) !important;
  border-radius: 16px !important;
}

::v-deep(.v-card) {
  border-radius: 12px !important;
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.96) 0%, rgba(15, 15, 30, 0.96) 100%) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

::v-deep(.v-card-title) {
  font-family: 'Press Start 2P', monospace !important;
  color: rgb(var(--accent-rgb)) !important;
}

::v-deep(.v-card-text) {
  color: #cbd5e1 !important;
}

/* ========================================
   EVENT DIALOG STYLES - Card Reveal Effect
   ======================================== */
.event-dialog {
  position: relative;
  border: 2px solid transparent !important;
  border-radius: 24px !important;
  overflow: hidden !important;
  overflow-y: hidden !important;
  max-height: calc(100dvh - 28px - env(safe-area-inset-top) - env(safe-area-inset-bottom));
  background:
    linear-gradient(180deg, rgba(25, 20, 45, 0.98) 0%, rgba(15, 15, 30, 0.98) 100%) padding-box,
    linear-gradient(135deg, rgba(var(--accent2-rgb), 0.55) 0%, rgba(var(--indigo-rgb), 0.35) 45%, rgba(var(--accent-rgb), 0.55) 100%) border-box !important;
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.6),
    0 0 40px rgba(var(--accent-rgb), 0.22),
    0 0 60px rgba(var(--accent2-rgb), 0.14),
    inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
  animation: dialogReveal 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  backdrop-filter: blur(18px);
  display: flex;
  flex-direction: column;
}

.event-dialog > :not(.dialog-glow):not(.dialog-scanlines) {
  position: relative;
  z-index: 2;
}

/* Hide scrollbars for the dialog overlay content (still allows scrolling if needed) */
:deep(.event-dialog-content) {
  overflow: hidden !important;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

:deep(.event-dialog-content)::-webkit-scrollbar {
  width: 0;
  height: 0;
}

.dialog-glow {
  position: absolute;
  inset: -40px;
  background:
    radial-gradient(ellipse at 20% 20%, rgba(var(--accent2-rgb), 0.20) 0%, transparent 55%),
    radial-gradient(ellipse at 80% 80%, rgba(var(--accent-rgb), 0.14) 0%, transparent 60%);
  filter: blur(24px);
  z-index: 0;
  pointer-events: none;
}

.dialog-scanlines {
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    0deg,
    rgba(0, 0, 0, 0.05),
    rgba(0, 0, 0, 0.05) 1px,
    transparent 1px,
    transparent 3px
  );
  opacity: 0.65;
  z-index: 1;
  pointer-events: none;
  mix-blend-mode: overlay;
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
  flex: 0 0 auto;
}

.dialog-image {
  transition: transform 0.6s ease;
  filter: saturate(1.1);
  height: clamp(170px, 28vh, 280px) !important;
}

/* Card glow effect on image */
.dialog-image-container::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    180deg,
    transparent 50%,
    rgba(15, 15, 30, 0.92) 100%
  );
  z-index: 1;
  pointer-events: none;
}

.dialog-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to top,
    rgba(0, 0, 0, 0.85) 0%,
    rgba(var(--accent2-rgb), 0.06) 30%,
    transparent 60%
  );
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
  border: 2px solid rgba(var(--accent-rgb), 0.25);
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
  padding: 0 !important;
  text-align: center;
}

.dialog-title-text {
  position: relative;
  display: inline-block;
  font-family: 'Press Start 2P', monospace;
  font-size: 1.05rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: 0.08em;
  text-shadow:
    2px 2px 0 rgba(0, 0, 0, 0.75),
    0 0 18px rgba(var(--accent-rgb), 0.25),
    0 0 26px rgba(var(--accent2-rgb), 0.18);
}

.dialog-title-text.glitch {
  animation: dialog-glitch-skew 3.1s infinite;
}

.dialog-title-text.glitch::before,
.dialog-title-text.glitch::after {
  content: attr(data-text);
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.8;
}

.dialog-title-text.glitch::before {
  color: rgb(var(--accent-rgb));
  transform: translate(-2px, 0);
  clip-path: polygon(0 0, 100% 0, 100% 38%, 0 38%);
  animation: dialog-glitch-1 3.1s infinite;
}

.dialog-title-text.glitch::after {
  color: rgb(var(--accent2-rgb));
  transform: translate(2px, 0);
  clip-path: polygon(0 64%, 100% 64%, 100% 100%, 0 100%);
  animation: dialog-glitch-2 3.1s infinite;
}

@keyframes dialog-glitch-skew {
  0%, 92%, 100% { transform: none; }
  94% { transform: skewX(9deg); }
  96% { transform: skewX(-7deg); }
}

@keyframes dialog-glitch-1 {
  0%, 88%, 100% { opacity: 0; transform: translate(-2px, 0); }
  92% { opacity: 0.9; transform: translate(-4px, 1px); }
  94% { opacity: 0.45; transform: translate(-1px, -1px); }
}

@keyframes dialog-glitch-2 {
  0%, 88%, 100% { opacity: 0; transform: translate(2px, 0); }
  93% { opacity: 0.9; transform: translate(4px, -1px); }
  96% { opacity: 0.5; transform: translate(1px, 1px); }
}

.dialog-text {
  color: #e2e8f0 !important;
  font-size: 1rem !important;
  line-height: 1.7;
  padding: 20px 28px !important;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.28), rgba(0, 0, 0, 0.18));
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  margin: 0 !important;
  font-family: 'VT323', monospace;
  letter-spacing: 0.02em;
  flex: 1 1 auto;
}

.dialog-text :deep(.text-body1) {
  font-family: 'VT323', monospace;
  font-size: 1.15rem;
  line-height: 1.6;
}

/* Choice Cards */
.choices-container {
  margin: 24px 0;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 16px;
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.72), rgba(15, 15, 30, 0.82));
}

.choices-title {
  color: rgb(var(--accent-rgb)) !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.9rem !important;
  font-weight: 700 !important;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  margin-bottom: 16px !important;
  text-align: center;
  text-shadow: 0 0 10px rgba(var(--accent-rgb), 0.35);
}

.choice-btn {
  font-family: 'VT323', monospace !important;
  font-size: 0.95rem !important;
  font-weight: 600 !important;
  border: 1px solid rgba(255, 255, 255, 0.14) !important;
  color: #e2e8f0 !important;
  margin-bottom: 12px;
  padding: 14px 20px !important;
  border-radius: 12px !important;
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.9), rgba(15, 15, 30, 0.95)) !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.choice-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, rgba(var(--accent2-rgb), 0.18), rgba(var(--accent-rgb), 0.08)) !important;
  border-color: rgba(var(--accent-rgb), 0.45) !important;
  color: rgb(var(--accent-rgb)) !important;
  transform: translateX(8px);
  box-shadow: 
    0 6px 20px rgba(var(--accent-rgb), 0.22),
    0 0 30px rgba(var(--accent2-rgb), 0.12),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.choice-btn:active:not(:disabled) {
  transform: translateX(4px) scale(0.98);
}

/* Cancel Button */
.cancel-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-weight: 800 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.12em !important;
  font-size: 0.75rem !important;
  padding: 14px 34px !important;
  border-radius: 14px !important;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.92), rgba(168, 85, 247, 0.70)) !important;
  color: #ffffff !important;
  box-shadow:
    0 10px 30px rgba(239, 68, 68, 0.25),
    0 0 28px rgba(var(--accent2-rgb), 0.14),
    inset 0 1px 0 rgba(255, 255, 255, 0.18) !important;
  border: 1px solid rgba(255, 255, 255, 0.14) !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.cancel-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  filter: brightness(1.05);
  box-shadow:
    0 14px 38px rgba(239, 68, 68, 0.32),
    0 0 36px rgba(var(--accent2-rgb), 0.18),
    inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

.cancel-btn:active:not(:disabled) {
  transform: translateY(0) scale(0.99);
}

/* Dialog Card corners */
.event-dialog::before,
.event-dialog::after {
  content: '♠';
  position: absolute;
  font-size: 2rem;
  color: rgba(var(--accent-rgb), 0.25);
  z-index: 10;
  pointer-events: none;
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
  border: 3px solid rgb(var(--accent-rgb));
  box-shadow: 0 0 20px rgba(var(--accent-rgb), 0.22), 0 0 30px rgba(var(--accent2-rgb), 0.12);
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

/* ========================================
   EDIT PROFILE DIALOG STYLES
   ======================================== */
.profile-dialog {
  position: relative;
  border: 2px solid transparent !important;
  border-radius: 20px !important;
  overflow: hidden !important;
  overflow-y: hidden !important;
  max-height: calc(100dvh - 28px - env(safe-area-inset-top) - env(safe-area-inset-bottom));
  background:
    linear-gradient(180deg, rgba(25, 20, 45, 0.86) 0%, rgba(15, 15, 30, 0.90) 100%) padding-box,
    linear-gradient(135deg, rgba(var(--accent2-rgb), 0.45) 0%, rgba(var(--indigo-rgb), 0.28) 45%, rgba(var(--accent-rgb), 0.55) 100%) border-box !important;
  box-shadow: 
    0 20px 60px rgba(0, 0, 0, 0.6),
    0 0 30px rgba(var(--accent2-rgb), 0.16) !important;
  backdrop-filter: blur(16px);
  display: flex;
  flex-direction: column;
}

.profile-dialog--kiosk {
  border-radius: 22px !important;
  background:
    radial-gradient(ellipse at 20% 10%, rgba(var(--accent2-rgb), 0.18) 0%, transparent 55%),
    radial-gradient(ellipse at 80% 90%, rgba(var(--accent-rgb), 0.12) 0%, transparent 60%),
    linear-gradient(180deg, rgba(10, 10, 25, 0.78) 0%, rgba(15, 15, 30, 0.86) 100%) padding-box,
    linear-gradient(135deg, rgba(var(--accent2-rgb), 0.58) 0%, rgba(var(--indigo-rgb), 0.32) 45%, rgba(var(--accent-rgb), 0.62) 100%) border-box !important;
  box-shadow:
    0 26px 90px rgba(0, 0, 0, 0.65),
    0 0 70px rgba(var(--accent2-rgb), 0.12),
    0 0 55px rgba(var(--accent-rgb), 0.10),
    inset 0 1px 0 rgba(255, 255, 255, 0.10) !important;
}

.profile-dialog > :not(.profile-glow):not(.profile-scanlines):not(.profile-stars) {
  position: relative;
  z-index: 2;
}

.profile-glow {
  position: absolute;
  inset: -40px;
  background:
    radial-gradient(ellipse at 30% 20%, rgba(var(--accent2-rgb), 0.18) 0%, transparent 55%),
    radial-gradient(ellipse at 70% 85%, rgba(var(--accent-rgb), 0.12) 0%, transparent 60%);
  filter: blur(22px);
  z-index: 0;
  pointer-events: none;
}

.profile-scanlines {
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    0deg,
    rgba(0, 0, 0, 0.05),
    rgba(0, 0, 0, 0.05) 1px,
    transparent 1px,
    transparent 3px
  );
  opacity: 0.55;
  z-index: 1;
  pointer-events: none;
  mix-blend-mode: overlay;
}

.profile-stars {
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  opacity: 0.8;
  background-image:
    radial-gradient(circle, rgba(255, 255, 255, 0.22) 1px, transparent 1.5px),
    radial-gradient(circle, rgba(var(--accent-rgb), 0.18) 1px, transparent 1.5px),
    radial-gradient(circle, rgba(var(--accent2-rgb), 0.14) 1px, transparent 1.5px),
    radial-gradient(circle, rgba(255, 255, 255, 0.12) 1px, transparent 2px);
  background-size: 110px 110px, 160px 160px, 220px 220px, 320px 320px;
  background-position: 12px 24px, 64px 18px, 40px 120px, 140px 80px;
  filter: drop-shadow(0 0 6px rgba(var(--accent-rgb), 0.10));
  mix-blend-mode: screen;
}

.profile-noise {
  position: absolute;
  inset: 0;
  z-index: 2;
  pointer-events: none;
  opacity: 0.08;
  background:
    repeating-linear-gradient(
      90deg,
      rgba(255, 255, 255, 0.04),
      rgba(255, 255, 255, 0.04) 1px,
      transparent 1px,
      transparent 3px
    );
  mix-blend-mode: overlay;
}

.profile-dialog--kiosk > :not(.profile-glow):not(.profile-scanlines):not(.profile-stars):not(.profile-noise) {
  position: relative;
  z-index: 3;
}

/* Hide scrollbars for the profile dialog overlay content */
:deep(.profile-dialog-content) {
  overflow: hidden !important;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

:deep(.profile-dialog-content)::-webkit-scrollbar {
  width: 0;
  height: 0;
}

.profile-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px 16px 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.10);
  background:
    linear-gradient(90deg, rgba(var(--accent2-rgb), 0.16), rgba(var(--accent-rgb), 0.05), transparent),
    linear-gradient(180deg, rgba(0, 0, 0, 0.10), rgba(0, 0, 0, 0));
}

.profile-topbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.profile-topbar-icon {
  filter: drop-shadow(0 0 12px rgba(var(--accent-rgb), 0.25));
}

.profile-topbar-titles {
  min-width: 0;
}

.profile-topbar-title {
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.profile-topbar-subtitle {
  margin-top: 6px;
  font-family: 'VT323', monospace;
  font-size: 1rem;
  letter-spacing: 0.08em;
  color: rgba(255, 255, 255, 0.58);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dialog-title-main {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 1.3rem !important;
  font-weight: 700 !important;
  color: rgb(var(--accent-rgb)) !important;
  text-shadow: 0 0 15px rgba(var(--accent-rgb), 0.25), 0 0 18px rgba(var(--accent2-rgb), 0.16) !important;
}

.profile-close-btn {
  border-radius: 12px !important;
  color: rgba(255, 255, 255, 0.85) !important;
  border: 1px solid rgba(255, 255, 255, 0.10);
  background: rgba(0, 0, 0, 0.18);
}

.profile-close-btn:hover:not(:disabled) {
  color: rgb(var(--accent-rgb)) !important;
  border-color: rgba(var(--accent-rgb), 0.28);
  box-shadow: 0 0 16px rgba(var(--accent-rgb), 0.18);
}

.profile-title-text {
  position: relative;
  display: inline-block;
  color: #ffffff;
  letter-spacing: 0.12em;
  text-shadow:
    2px 2px 0 rgba(0, 0, 0, 0.75),
    0 0 18px rgba(var(--accent-rgb), 0.22),
    0 0 26px rgba(var(--accent2-rgb), 0.16);
}

.profile-title-text.glitch {
  animation: dialog-glitch-skew 3.1s infinite;
}

.profile-title-text.glitch::before,
.profile-title-text.glitch::after {
  content: attr(data-text);
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.8;
}

.profile-title-text.glitch::before {
  color: rgb(var(--accent-rgb));
  transform: translate(-2px, 0);
  clip-path: polygon(0 0, 100% 0, 100% 38%, 0 38%);
  animation: dialog-glitch-1 3.1s infinite;
}

.profile-title-text.glitch::after {
  color: rgb(var(--accent2-rgb));
  transform: translate(2px, 0);
  clip-path: polygon(0 64%, 100% 64%, 100% 100%, 0 100%);
  animation: dialog-glitch-2 3.1s infinite;
}

.profile-dialog-text {
  padding: 24px !important;
  color: #e2e8f0 !important;
  font-family: 'VT323', monospace;
  overflow: hidden;
  flex: 1 1 auto;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.08), rgba(0, 0, 0, 0.22));
}

.profile-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  align-items: start;
}

.profile-left {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.profile-right {
  padding-top: 6px;
}

.profile-hint {
  font-family: 'VT323', monospace;
  font-size: 1rem;
  letter-spacing: 0.06em;
  color: rgba(255, 255, 255, 0.62);
  text-align: center;
  max-width: 260px;
}

.profile-help {
  margin-top: 10px;
  font-family: 'VT323', monospace;
  font-size: 0.95rem;
  letter-spacing: 0.06em;
  color: rgba(255, 255, 255, 0.55);
}

.profile-label {
  color: rgba(255, 255, 255, 0.65) !important;
  font-family: 'Press Start 2P', monospace;
  font-weight: 800 !important;
  font-size: 0.7rem !important;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

:deep(.profile-input .v-field) {
  background: rgba(0, 0, 0, 0.3) !important;
  border-radius: 10px !important;
}

:deep(.profile-input input) {
  font-family: 'VT323', monospace;
  font-size: 1.15rem;
  letter-spacing: 0.03em;
  color: rgba(255, 255, 255, 0.92) !important;
}

:deep(.profile-input .v-label) {
  font-family: 'VT323', monospace;
  color: rgba(255, 255, 255, 0.55) !important;
}

:deep(.profile-input .v-field__outline) {
  border-color: rgba(255, 255, 255, 0.18) !important;
}

:deep(.profile-input .v-field--focused .v-field__outline) {
  border-color: rgb(var(--accent-rgb)) !important;
}

.profile-avatar-shell {
  --avatar-size: 140px;
  position: relative;
  width: calc(var(--avatar-size) + 26px);
  height: calc(var(--avatar-size) + 26px);
  margin: 0 auto;
  display: grid;
  place-items: center;
}

.profile-avatar-ring {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
}

.profile-avatar-ring--pulse {
  inset: 2px;
  border: 2px solid rgba(var(--accent2-rgb), 0.55);
  box-shadow:
    0 0 0 1px rgba(255, 255, 255, 0.08) inset,
    0 0 22px rgba(var(--accent-rgb), 0.22),
    0 0 40px rgba(var(--accent2-rgb), 0.16);
  animation: ring-pulse 2.1s ease-in-out infinite;
}

.profile-avatar-ring--spin {
  inset: 0;
  background: conic-gradient(
    from 90deg,
    rgba(var(--accent-rgb), 0.85),
    rgba(var(--accent2-rgb), 0.55),
    rgba(var(--indigo-rgb), 0.50),
    rgba(var(--teal-rgb), 0.65),
    rgba(var(--accent-rgb), 0.85)
  );
  filter: blur(0.1px);
  opacity: 0.65;
  mask: radial-gradient(transparent 62%, #000 64%);
  -webkit-mask: radial-gradient(transparent 62%, #000 64%);
  animation: profile-ring-rotate 8s linear infinite;
}

@keyframes profile-ring-rotate {
  to { transform: rotate(360deg); }
}

.avatar-upload-container-large {
  position: relative;
  width: var(--avatar-size, 140px);
  height: var(--avatar-size, 140px);
  margin: 0 auto;
  border-radius: 50%;
  overflow: hidden;
  cursor: pointer;
  background: radial-gradient(circle at 30% 20%, rgba(var(--accent2-rgb), 0.16) 0%, rgba(0, 0, 0, 0.55) 65%);
  border: 4px solid rgb(var(--accent-rgb));
  box-shadow: 
    0 0 0 1px rgba(255, 255, 255, 0.10) inset,
    0 0 26px rgba(var(--accent-rgb), 0.25),
    0 0 45px rgba(var(--accent2-rgb), 0.18),
    0 18px 60px rgba(0, 0, 0, 0.55);
}

.avatar-upload-container-large::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 30% 25%, rgba(255, 255, 255, 0.10) 0%, transparent 55%),
    radial-gradient(circle at 70% 70%, rgba(var(--accent-rgb), 0.07) 0%, transparent 55%),
    repeating-linear-gradient(
      90deg,
      rgba(255, 255, 255, 0.035),
      rgba(255, 255, 255, 0.035) 1px,
      transparent 1px,
      transparent 4px
    );
  opacity: 0.6;
  z-index: 1;
  pointer-events: none;
  mix-blend-mode: screen;
}

.avatar-upload-container-large::after {
  content: '';
  position: absolute;
  inset: 12px;
  border-radius: 9999px;
  border: 1px solid rgba(255, 255, 255, 0.10);
  box-shadow: 0 0 18px rgba(var(--accent2-rgb), 0.10);
  z-index: 2;
  pointer-events: none;
}

.avatar-preview-large {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: saturate(1.05) contrast(1.05) brightness(1.05);
  position: relative;
  z-index: 0;
}

.avatar-upload-overlay-large {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
  backdrop-filter: blur(4px);
  border: 1px solid rgba(var(--accent-rgb), 0.28);
  border-radius: 9999px;
  box-shadow:
    0 0 0 1px rgba(255, 255, 255, 0.08) inset,
    0 0 18px rgba(var(--accent-rgb), 0.12);
  z-index: 3;
}

.avatar-upload-container-large:hover .avatar-upload-overlay-large {
  opacity: 1;
}

.avatar-upload-overlay-large .upload-text {
  font-size: 0.8rem;
  margin-top: 8px;
  color: rgba(255, 255, 255, 0.92);
  text-shadow: 0 0 14px rgba(var(--accent-rgb), 0.25);
}

.avatar-camera-icon {
  filter: drop-shadow(0 0 12px rgba(var(--accent-rgb), 0.35));
}

@media (hover: none), (pointer: coarse) {
  .avatar-upload-overlay-large {
    opacity: 1;
    justify-content: flex-end;
    padding: 12px;
    background: linear-gradient(
      to top,
      rgba(0, 0, 0, 0.78) 0%,
      rgba(0, 0, 0, 0.18) 70%,
      rgba(0, 0, 0, 0.10) 100%
    );
  }

  .avatar-camera-icon {
    font-size: 30px !important;
  }

  .avatar-upload-overlay-large .upload-text {
    margin-top: 6px;
    font-size: 0.85rem;
  }
}

.profile-dialog-actions {
  padding: 16px 24px 24px !important;
  display: flex;
  justify-content: center;
  gap: 16px !important;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.10), rgba(0, 0, 0, 0.22));
}

.profile-cancel-btn {
  flex: 1;
  max-width: 140px;
  font-family: 'Press Start 2P', monospace !important;
  font-weight: 800 !important;
  font-size: 0.7rem !important;
  text-transform: uppercase !important;
  letter-spacing: 0.12em !important;
  border-radius: 14px !important;
  background: linear-gradient(180deg, rgba(239, 68, 68, 0.12), rgba(0, 0, 0, 0.25)) !important;
  border: 1px solid rgba(239, 68, 68, 0.45) !important;
  color: #ffffff !important;
}

.profile-save-btn {
  flex: 1;
  max-width: 160px;
  font-family: 'Press Start 2P', monospace !important;
  font-weight: 900 !important;
  font-size: 0.7rem !important;
  text-transform: uppercase !important;
  letter-spacing: 0.12em !important;
  border-radius: 14px !important;
  background: linear-gradient(135deg, rgba(var(--accent2-rgb), 0.85), rgba(var(--indigo-rgb), 0.65), rgba(var(--teal-rgb), 0.85)) !important;
  color: #ffffff !important;
  box-shadow:
    0 10px 28px rgba(var(--accent-rgb), 0.16),
    0 0 26px rgba(var(--accent2-rgb), 0.16),
    inset 0 1px 0 rgba(255, 255, 255, 0.18) !important;
}

.profile-cancel-btn:hover:not(:disabled) {
  filter: brightness(1.05);
  box-shadow:
    0 12px 32px rgba(239, 68, 68, 0.18),
    inset 0 1px 0 rgba(255, 255, 255, 0.14);
}

.profile-save-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  filter: brightness(1.05);
  box-shadow:
    0 14px 38px rgba(var(--accent-rgb), 0.2),
    0 0 32px rgba(var(--accent2-rgb), 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

/* ========================================
   SECTION HEADER - Glitch Text
   ======================================== */
.section-header-text {
  position: relative;
  display: inline-block;
  line-height: 1.15;
  transform: translateZ(0);
}

.section-header-text.glitch {
  animation: header-glitch-skew 3.2s infinite;
}

.section-header-text.glitch::before,
.section-header-text.glitch::after {
  content: attr(data-text);
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.8;
}

.section-header-text.glitch::before {
  color: rgb(var(--accent-rgb));
  transform: translate(-2px, 0);
  clip-path: polygon(0 0, 100% 0, 100% 35%, 0 35%);
  animation: header-glitch-1 3.2s infinite;
}

.section-header-text.glitch::after {
  color: rgb(var(--accent2-rgb));
  transform: translate(2px, 0);
  clip-path: polygon(0 65%, 100% 65%, 100% 100%, 0 100%);
  animation: header-glitch-2 3.2s infinite;
}

@keyframes header-glitch-skew {
  0%, 92%, 100% { transform: none; }
  94% { transform: skewX(10deg); }
  96% { transform: skewX(-8deg); }
}

@keyframes header-glitch-1 {
  0%, 88%, 100% { opacity: 0; transform: translate(-2px, 0); }
  92% { opacity: 0.85; transform: translate(-4px, 1px); }
  94% { opacity: 0.4; transform: translate(-1px, -1px); }
}

@keyframes header-glitch-2 {
  0%, 88%, 100% { opacity: 0; transform: translate(2px, 0); }
  93% { opacity: 0.85; transform: translate(4px, -1px); }
  96% { opacity: 0.45; transform: translate(1px, 1px); }
}

/* ========================================
   RESPONSIVE - Mobile Friendly
   ======================================== */
@media (max-width: 960px) {
  .game-container {
    max-width: 980px;
  }

  .header-stats {
    gap: 8px;
  }

  .header-stat-bar {
    min-width: 84px;
    max-width: 110px;
  }
}

@media (max-width: 640px) {
  .game-container {
    padding:
      calc(12px + env(safe-area-inset-top))
      calc(12px + env(safe-area-inset-right))
      calc(12px + env(safe-area-inset-bottom))
      calc(12px + env(safe-area-inset-left));
  }

  .game-header {
    margin-bottom: 12px;
  }

  .character-panel {
    padding: 16px;
    border-radius: 18px;
  }

  .character-card-enhanced {
    flex-direction: column;
    align-items: center;
    gap: 14px;
    text-align: center;
  }

  .character-avatar {
    width: 72px;
    height: 72px;
  }

  .character-meta {
    justify-content: center;
  }

  .header-stats {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .header-stat-bar {
    min-width: 0;
    max-width: none;
  }

  .character-actions {
    width: 100%;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
  }

  .character-actions .v-btn {
    flex: 1 1 130px;
  }

  .game-content {
    padding-right: 0;
  }

  .narration-body {
    max-height: 160px;
  }

  .action-section {
    padding: 14px;
    border-radius: 14px;
  }

  .events-grid {
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 14px;
    padding: 6px;
  }

  .event-section {
    padding: 16px;
    border-radius: 18px;
  }

  .section-header-wrapper {
    grid-template-columns: 1fr;
    row-gap: 10px;
  }

  .section-header-wrapper .section-header {
    grid-column: auto;
  }

  .section-header-wrapper .redraw-section-btn {
    grid-column: auto;
    justify-self: center;
  }

  .card-visual {
    height: 120px;
  }

  .section-header {
    font-size: 0.82rem;
    padding: 10px 12px;
    letter-spacing: 0.1em;
  }

  .dialog-title-container {
    padding: 16px;
  }

  .dialog-title-text {
    font-size: 0.9rem;
  }

  .dialog-text {
    padding: 16px 16px !important;
  }

  .cancel-btn {
    padding: 12px 22px !important;
    font-size: 0.7rem !important;
  }

  .profile-dialog-text {
    padding: 18px !important;
  }

  .dialog-title-main {
    font-size: 1rem !important;
  }

  .profile-avatar-shell {
    --avatar-size: 128px;
  }

  .profile-topbar {
    padding: 14px 14px 10px;
  }

  .profile-topbar-subtitle {
    font-size: 0.95rem;
  }

  .profile-layout {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .profile-right {
    padding-top: 0;
  }

  .profile-dialog-actions {
    padding: 14px 16px 18px !important;
    gap: 12px !important;
  }

  .profile-cancel-btn,
  .profile-save-btn {
    max-width: none;
    flex: 1;
  }
}

@media (hover: none), (pointer: coarse) {
  .event-card-item:hover:not(.disabled) {
    transform: none;
    box-shadow:
      0 4px 14px rgba(0, 0, 0, 0.35),
      0 0 30px rgba(var(--accent2-rgb), 0.06),
      inset 0 1px 0 rgba(255, 255, 255, 0.06);
  }

  .event-card-item:hover:not(.disabled) .card-img {
    transform: none;
    filter: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .star,
  .star.fast,
  .star.slow,
  .star.shooting,
  .nebula,
  .grid-lines {
    animation: none !important;
  }
}
</style>
