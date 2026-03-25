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
            <div class="corner-bl"></div>
            <div class="corner-br"></div>
            
            <!-- LEFT COLUMN: Avatar -->
            <div class="character-card-left">
              <div class="avatar-wrapper">
                <v-img :src="character.image" alt="player portrait" class="character-avatar" />
                <div class="avatar-ring"></div>
              </div>
            </div>
            
            <!-- RIGHT COLUMN: Info -->
            <div class="character-card-right">
              <!-- NAME AND META ROW -->
              <div class="character-header-row">
                <div class="character-info">
                  <div class="character-name-row">
                    <h2 class="character-name">{{ character.name }}</h2>
                    <!-- Health Status Display - Right of name -->
                    <div v-if="character.healthStatus" class="health-status-badge-inline" :class="getHealthStatusClass(character.healthStatus)">
                      <v-icon size="14">{{ getHealthStatusIcon(character.healthStatus) }}</v-icon>
                      <span>{{ healthStatusLabel }}</span>
                    </div>
                  </div>
                  <div class="character-meta">
                    <span class="meta-badge">{{ character.ageGroup }}</span>
                    <span class="meta-badge gender-badge">{{ character.gender }}</span>
                    <span class="day-counter">Age {{ character.age || character.currentDay }}</span>
                    <span class="profession-badge">{{ character.profession || 'No Profession' }}</span>
                    <span class="relationship-badge-header" :class="getRelationshipClass(character.relationshipStatus)">
                      <v-icon size="14">{{ getRelationshipIcon(character.relationshipStatus) }}</v-icon>
                      {{ formatRelationshipStatus(character.relationshipStatus) }}
                    </span>
                  </div>
                </div>
                <div class="header-actions">
                  <v-btn
                    size="x-small"
                    class="retro-btn"
                    @click="showStatsDialog = true"
                    prepend-icon="mdi-chart-box"
                    color="purple"
                  >
                    All Stats
                  </v-btn>
                  <v-btn
                    size="x-small"
                    class="retro-btn"
                    @click="toggleStats"
                    prepend-icon="mdi-account"
                    :color="showStats ? 'warning' : 'info'"
                  >
                    {{ showStats ? 'Hide Skills' : 'Show Skills' }}
                  </v-btn>
                  <v-btn
                    size="x-small"
                    class="retro-btn"
                    color="info"
                    prepend-icon="mdi-book-open-variant"
                    @click="showStoryRecap = true"
                  >
                    Story
                  </v-btn>
                  <v-btn
                    size="x-small"
                    class="retro-btn"
                    color="success"
                    prepend-icon="mdi-help-circle"
                    @click="showHowToPlay = true"
                  >
                    Help
                  </v-btn>
                  <v-btn
                    size="x-small"
                    class="retro-btn"
                    prepend-icon="mdi-pencil"
                    @click="editProfile"
                  >
                    Edit
                  </v-btn>
                  <v-btn
                    size="x-small"
                    class="retro-btn"
                    color="warning"
                    prepend-icon="mdi-floppy"
                    @click="saveGame"
                    :loading="isSavingGame"
                  >
                    Save
                  </v-btn>
                  <v-btn
                    size="x-small"
                    class="retro-btn"
                    color="error"
                    prepend-icon="mdi-power"
                    @click="logout"
                  >
                    Exit
                  </v-btn>
                </div>
              </div>
              
              <!-- STATS ROW -->
              <div class="header-stats">
                <div v-for="(value, stat) in headerStats" :key="stat" class="header-stat-bar">
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
              
              <!-- STATUS AND ACTIONS ROW -->
              <div class="character-footer-row">
                <div class="status-section">
                </div>
              </div>
              
              <!-- State Strip - Balanced: Show narrative + path + key consequences -->
              <div v-if="currentNarrativeLabel || activePathBadges.length || activeConsequenceBadges.length" class="state-strip">
                <div v-if="currentNarrativeLabel" class="state-pill narrative-pill">
                  <v-icon size="14">mdi-source-branch</v-icon>
                  <span>{{ currentNarrativeLabel }}</span>
                </div>
                <!-- Show first active path if exists -->
                <div v-if="activePathBadges.length > 0" class="state-pill path-pill">
                  <v-icon size="14">mdi-map-marker-path</v-icon>
                  <span>{{ activePathBadges[0] }}</span>
                </div>
                <!-- Show up to 2 key consequences -->
                <div v-for="(flag, idx) in activeConsequenceBadges.slice(0, 2)" :key="`cf-${idx}`" class="state-pill consequence-pill">
                  <v-icon size="14">{{ flag.icon }}</v-icon>
                  <span>{{ flag.label }}</span>
                </div>
                <!-- More consequences in Story Recap -->
              </div>
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

        

                <!-- Social Connections -->
                <div v-if="character.socialConnections && character.socialConnections.length > 0" class="badges-group mt-3">
                  <h4 class="section-title">CONNECTIONS</h4>
                  <div class="badge-list">
                    <v-chip
                      v-for="(conn, index) in character.socialConnections"
                      :key="index"
                      class="badge-chip connection-badge"
                      size="small"
                      :color="getConnectionColor(conn.type)"
                    >
                      <v-icon start size="12">{{ getConnectionIcon(conn.type) }}</v-icon>
                      {{ conn.name }}
                    </v-chip>
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
          <div class="narration-body" ref="narrationBodyRef">
            <p v-if="narrationHistory.length === 0" class="narration-empty">
              Select an event to begin your life...
            </p>
            <div class="narration-entries">
              <p v-for="(entry, i) in narrationHistory" :key="i" class="narration-entry">
                <span class="entry-bullet">▸</span>
                {{ entry }}
              </p>
            </div>
          </div>
        </div>

        <!-- Life Pressures now shown in Story Recap Dialog - consolidated -->

        <div class="action-section">
          <div class="action-buttons">
            <v-btn
              size="large"
              class="end-day-btn action-btn"
              color="primary"
              prepend-icon="mdi-weather-night"
              @click="endDay"
              :disabled="loading || selectedEvent"
              block
            >
              <span class="text-truncate">Advance Age</span>
            </v-btn>
            <v-spacer />
            <v-btn
              size="large"
              class="achievements-btn action-btn"
              color="amber"
              prepend-icon="mdi-trophy"
              @click="showAchievementsDialog = true"
              block
            >
              <span class="text-truncate">Achievements</span>
            </v-btn>
            <v-spacer />
            <v-btn
              size="large"
              class="suicide-btn action-btn"
              color="error"
              prepend-icon="mdi-skull-crossbones"
              @click="suicide"
              block
            >
              <span class="text-truncate">Suicide</span>
            </v-btn>
          </div>
        </div>

        <div class="events-area">
          <!-- Skills to Learn Section -->
          <div v-if="availableEvents.skills_to_learn && availableEvents.skills_to_learn.length > 0" class="event-section">
            <h3 class="section-header actions">
              <v-icon class="header-icon">mdi-school</v-icon>
              <span class="section-header-text glitch" data-text="SKILLS TO LEARN">SKILLS TO LEARN</span>
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.skills_to_learn"
                :key="`skills-${index}`"
                class="event-card-item"
                @click="!selectedEvent && !event.disabled && selectEvent(event)"
                :class="{ 'disabled': selectedEvent || event.disabled }"
              >
                <div class="card-visual">
                  <v-img :src="event.image" cover class="card-img" />
                  <div class="card-type-badge skill">Skill</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                  <p v-if="event.disabled && event.disabled_reason" class="card-disabled-reason">{{ event.disabled_reason }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Daily Actions Section -->
          <div v-if="availableEvents.daily_actions && availableEvents.daily_actions.length > 0" class="event-section">
            <h3 class="section-header actions">
              <v-icon class="header-icon">mdi-lightning-bolt</v-icon>
              <span class="section-header-text glitch" data-text="DAILY ACTIONS">DAILY ACTIONS</span>
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.daily_actions"
                :key="`daily-${index}`"
                class="event-card-item"
                @click="!selectedEvent && !event.disabled && selectEvent(event)"
                :class="{ 'disabled': selectedEvent || event.disabled }"
              >
                <div class="card-visual">
                  <v-img :src="event.image" cover class="card-img" />
                  <div class="card-type-badge" :class="getCardBadgeClass(event)">{{ getCardBadgeLabel(event) }}</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                  <p v-if="event.disabled && event.disabled_reason" class="card-disabled-reason">{{ event.disabled_reason }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Fallback: use life_actions if new categories don't exist (backward compat) -->
          <div v-else-if="availableEvents.life_actions && availableEvents.life_actions.length > 0" class="event-section">
            <h3 class="section-header actions">
              <v-icon class="header-icon">mdi-lightning-bolt</v-icon>
              <span class="section-header-text glitch" data-text="DAILY ACTIONS">DAILY ACTIONS</span>
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.life_actions"
                :key="`actions-${index}`"
                class="event-card-item"
                @click="!selectedEvent && !event.disabled && selectEvent(event)"
                :class="{ 'disabled': selectedEvent || event.disabled }"
              >
                <div class="card-visual">
                  <v-img :src="event.image" cover class="card-img" />
                  <div class="card-type-badge" :class="getCardBadgeClass(event)">{{ getCardBadgeLabel(event) }}</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                  <p v-if="event.disabled && event.disabled_reason" class="card-disabled-reason">{{ event.disabled_reason }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="availableEvents.triggers && availableEvents.triggers.length > 0" class="event-section">
            <h3 class="section-header milestone">
              <v-icon class="header-icon">mdi-alert-circle</v-icon>
              <span class="section-header-text glitch" data-text="TRIGGERS">TRIGGERS</span>
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.triggers"
                :key="`trigger-${index}`"
                class="event-card-item"
                @click="!selectedEvent && selectEvent(event)"
                :class="{ 'disabled': selectedEvent }"
              >
                <div class="card-visual">
                  <v-img :src="event.image" cover class="card-img" />
                  <div class="card-type-badge" :class="getCardBadgeClass(event)">{{ getCardBadgeLabel(event) }}</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Luck Events Section -->
          <div v-if="availableEvents.luck && availableEvents.luck.length > 0" class="event-section">
            <h3 class="section-header luck">
              <v-icon class="header-icon">mdi-clover</v-icon>
              <span class="section-header-text glitch" data-text="LUCK">LUCK</span>
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.luck"
                :key="`luck-${index}`"
                class="event-card-item"
                @click="!selectedEvent && selectEvent(event)"
                :class="{ 'disabled': selectedEvent }"
              >
                <div class="card-visual">
                  <v-img :src="event.image" cover class="card-img" />
                  <div class="card-type-badge" :class="getCardBadgeClass(event)">{{ getCardBadgeLabel(event) }}</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="availableEvents.profession_choices && availableEvents.profession_choices.length > 0 && !character.profession" class="event-section">
            <h3 class="section-header career">
              <v-icon class="header-icon">mdi-briefcase</v-icon>
              <span class="section-header-text glitch" data-text="PROFESSION PATHS">PROFESSION PATHS</span>
            </h3>
            <div class="events-grid">
              <div
                v-for="(event, index) in availableEvents.profession_choices"
                :key="`prof-choice-${event.id || index}`"
                class="event-card-item"
                @click="!selectedEvent && selectEvent(event)"
                :class="{ 'disabled': selectedEvent }"
              >
                <div class="card-visual">
                  <v-img :src="event.image || '/css/images/milestone.jpg'" cover class="card-img" />
                  <div class="card-type-badge" :class="getCardBadgeClass(event)">{{ getCardBadgeLabel(event) }}</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
                </div>
                <div class="card-content">
                  <h4 class="card-title">{{ event.title }}</h4>
                  <p class="card-desc">{{ event.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="availableEvents.daily && availableEvents.daily.length > 0" class="event-section">
            <div class="section-header-wrapper">
              <h3 class="section-header daily">
                <v-icon class="header-icon">mdi-calendar-today</v-icon>
                <span class="section-header-text glitch" data-text="DAILY OCCURRENCES">DAILY OCCURRENCES</span>
              </h3>
              <v-btn v-if="false"
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
                  <div class="card-type-badge" :class="getCardBadgeClass(event)">{{ getCardBadgeLabel(event) }}</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
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
              <v-btn v-if="false"
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
                  <div class="card-type-badge" :class="getCardBadgeClass(event)">{{ getCardBadgeLabel(event) }}</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
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
              <v-btn v-if="false"
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
                  <div class="card-type-badge" :class="getCardBadgeClass(event)">{{ getCardBadgeLabel(event) }}</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
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
              <v-btn v-if="false"
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
                  <div class="card-type-badge" :class="getCardBadgeClass(event)">{{ getCardBadgeLabel(event) }}</div>
                  <div v-if="event.mini_game" class="mini-game-badge" title="This event has a mini-game">
                    <v-icon size="14" color="#00ffcc">mdi-gamepad-variant</v-icon>
                  </div>
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
                <h3 class="game-over-title">{{ endingTitle || 'Your journey has ended.' }}</h3>
                <p class="game-over-text">{{ endingDescription || `You lived until Age ${character.age || character.currentDay} as a ${character.ageGroup}.` }}</p>
                <p v-if="deathCause" class="death-cause">💀 Cause of Death: {{ deathCause }}</p>
                <v-btn color="info" size="large" class="new-game-btn" variant="tonal" @click="openLifeSummary">
                  Life Summary
                </v-btn>
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
          <p v-if="selectedEvent.narrative_description" class="text-body1 mb-3 text-center narrative-text">
            {{ selectedEvent.narrative_description }}
          </p>
          <p class="text-body1 mb-6 text-center">{{ selectedEvent.description }}</p>

          <div v-if="selectedEventMeta.length > 0" class="selected-event-meta">
            <span v-for="meta in selectedEventMeta" :key="meta" class="selected-event-meta-pill">{{ meta }}</span>
          </div>
          
          <div v-if="selectedEvent.choices && selectedEvent.choices.length > 0" class="choices-container">
            <p class="text-subtitle2 mb-4 text-center choices-title">How will you respond?</p>
            <div v-if="selectedEvent.mini_game" class="mini-game-indicator mb-3">
              <v-icon size="18" color="#00ffcc">mdi-gamepad-variant</v-icon>
              <span class="ml-2">This event includes a mini-game!</span>
            </div>
            <div class="d-flex flex-column gap-3">
              <v-btn
                v-for="(choice, idx) in selectedEvent.choices"
                :key="idx"
                variant="outlined"
                size="large"
                class="choice-btn"
                :class="{ 
                  'choice-with-effects': choice.stat_effects || choice.outcomes,
                  'choice-locked': choice.is_locked,
                  'choice-with-minigame': selectedEvent.mini_game
                }"
                @click="handleChoiceClick(idx)"
                :disabled="applyingOutcome || choice.is_locked"
              >
                <div class="choice-btn-content">
                  <span v-if="choice.is_locked" class="locked-indicator" :title="choice.lock_reason || 'Requires previous choice'">
                    <v-icon size="16">mdi-chain</v-icon>
                  </span>
                  <span class="choice-btn-label">{{ formatChoiceLabel(choice) }}</span>
                  <span v-if="choice.is_locked" class="locked-text">LOCKED</span>
                </div>
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

    <v-dialog v-model="showLifeSummaryDialog" max-width="900" scrollable rounded="xl">
      <v-card class="event-dialog">
        <v-card-title class="dialog-title">
          <span class="dialog-title-text glitch" data-text="LIFE SUMMARY">LIFE SUMMARY</span>
        </v-card-title>
        <v-card-text class="dialog-text px-6 py-4">
          <div v-if="lifeSummaryLoading" class="text-center py-6">
            <v-progress-circular indeterminate size="48" color="#00ffcc"></v-progress-circular>
          </div>
          <div v-else-if="lifeSummaryError" class="text-center py-6">
            {{ lifeSummaryError }}
          </div>
          <div v-else-if="lifeSummary">
            <p class="text-body1 mb-4 text-center">
              {{ lifeSummary.ending_title }} — {{ lifeSummary.ending_description }}
            </p>
            <p class="text-body2 mb-6 text-center">
              Lived {{ lifeSummary.lifespan_years }} years • Story: {{ lifeSummary.story_decisions || 0 }} • Daily Actions: {{ lifeSummary.daily_actions || 0 }}
            </p>

            <div v-if="lifeSummary.milestones && lifeSummary.milestones.length > 0" class="mb-6">
              <h4 class="section-title mb-2">MILESTONES</h4>
              <div class="d-flex flex-column gap-2">
                <div v-for="(m, idx) in lifeSummary.milestones" :key="`ms-${idx}`" class="memory-entry">
                  <div class="memory-date">Age {{ m.age }}</div>
                  <div class="memory-event">{{ m.title }}</div>
                  <div class="memory-effects">{{ m.description }}</div>
                </div>
              </div>
            </div>

            <div v-if="lifeSummary.timeline && lifeSummary.timeline.length > 0">
              <h4 class="section-title mb-2">TIMELINE</h4>
              <div class="memory-entries">
                <div v-for="(t, idx) in lifeSummary.timeline" :key="`tl-${idx}`" class="memory-entry">
                  <div class="memory-date">Age {{ t.age }}</div>
                  <div class="memory-event">{{ t.event_title }}</div>
                  <div class="memory-choice">{{ t.choice_text }}</div>
                  <div class="memory-effects">{{ t.outcome }}</div>
                </div>
              </div>
            </div>
          </div>
        </v-card-text>
        <v-card-actions class="justify-center gap-3 pb-6">
          <v-btn variant="elevated" color="primary" size="large" @click="showLifeSummaryDialog = false">
            Close
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
                          Share your data?
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

    <!-- How to Play Dialog - RETRO PIXEL EDITION -->
    <v-dialog v-model="showHowToPlay" max-width="800" max-height="92vh" persistent rounded="0" content-class="howtoplay-dialog-content">
      <v-card class="retro-pixel-howtoplay" style="min-height: 550px; max-height: 90vh; overflow-y: auto; image-rendering: pixelated;">
        <!-- CRT Glow Overlay -->
        <div class="htp-glow" aria-hidden="true"></div>
        <div class="htp-scanlines" aria-hidden="true"></div>
        <div class="htp-vignette" aria-hidden="true"></div>
        
        <!-- HEADER -->
        <v-card-title class="htp-title" data-text="HOW TO PLAY">
          <div style="font-size: clamp(1.5rem, 4vw, 2.5rem); line-height: 1.1; padding: 16px 0; text-align: center; width: 100%;">
            HOW TO PLAY
            <div style="font-size: 2.5rem; margin: 0.2em 0; animation: htp-bounce 2s infinite;">🎮</div>
          </div>
        </v-card-title>
        
        <v-card-subtitle class="htp-subtitle mb-4 text-center">
          <span>MASTER THE GAME OF LIFE</span>
          <div style="font-size: 2rem; margin-top: 8px;">⭐</div>
        </v-card-subtitle>
        
        <!-- STEPS GRID -->
        <v-card-text class="htp-steps-container">
          <div class="steps-grid">
            <div class="step-card" style="--step-delay: 0s">
              <div class="step-number">1</div>
              <div class="step-icon">👤</div>
              <div class="step-title">CREATE CHARACTER</div>
              <div class="step-desc">Choose name, gender & age group. Your choices affect events!</div>
            </div>
            <div class="step-card" style="--step-delay: 0.1s">
              <div class="step-number">2</div>
              <div class="step-icon">🎯</div>
              <div class="step-title">MAKE DECISIONS</div>
              <div class="step-desc">Each choice impacts Health, Happiness, Intelligence & Wealth!</div>
            </div>
            <div class="step-card" style="--step-delay: 0.2s">
              <div class="step-number">3</div>
              <div class="step-icon">📊</div>
              <div class="step-title">MANAGE STATS</div>
              <div class="step-desc">Balance your 4 stats. Low stats = serious consequences!</div>
            </div>
            <div class="step-card" style="--step-delay: 0.3s">
              <div class="step-number">4</div>
              <div class="step-icon">🎂</div>
              <div class="step-title">AGE & EVENTS</div>
              <div class="step-desc">Experience life events from childhood to retirement!</div>
            </div>
            <div class="step-card" style="--step-delay: 0.4s">
              <div class="step-number">5</div>
              <div class="step-icon">💼</div>
              <div class="step-title">BUILD CAREER</div>
              <div class="step-desc">Choose your profession path wisely for success!</div>
            </div>
            <div class="step-card" style="--step-delay: 0.5s">
              <div class="step-number">6</div>
              <div class="step-icon">✨</div>
              <div class="step-title">TALENTS & SKILLS</div>
              <div class="step-desc">Discover unique talents and develop new skills!</div>
            </div>
            <div class="step-card" style="--step-delay: 0.6s">
              <div class="step-number">7</div>
              <div class="step-icon">📈</div>
              <div class="step-title">TRACK PROGRESS</div>
              <div class="step-desc">View analytics to see how your choices shaped your life!</div>
            </div>
            <div class="step-card" style="--step-delay: 0.7s">
              <div class="step-number">8</div>
              <div class="step-icon">🔄</div>
              <div class="step-title">PLAY AGAIN</div>
              <div class="step-desc">Try different choices and discover all possibilities!</div>
            </div>
          </div>
          
          <div class="htp-tip mt-6 p-4">
            <span class="tip-icon">💡</span>
            <span class="tip-text">TIP: There's no winning strategy - every choice creates a unique story!</span>
          </div>
        </v-card-text>
        
        <!-- CLOSE BUTTON -->
        <v-card-actions class="htp-actions justify-center pb-6">
          <v-btn 
            size="x-large"
            variant="elevated"
            color="amber-darken-2"
            class="htp-start-btn"
            @click="showHowToPlay = false"
            style="font-size: 1.2rem; padding: 16px 50px; min-width: 220px;"
          >
            <v-icon left>mdi-play</v-icon>
            LET'S PLAY!
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Story Recap Dialog -->
    <v-dialog v-model="showStoryRecap" max-width="700" max-height="85vh" persistent rounded="0" content-class="story-dialog-content">
      <v-card class="retro-pixel-card" style="min-height: 400px; max-height: 85vh; overflow-y: auto; image-rendering: pixelated;">
        <v-card-title class="story-recap-title" style="font-size: 1.8rem; padding: 20px; text-align: center;">
          📖 STORY RECAP
        </v-card-title>
        <v-card-subtitle class="text-center mb-4" style="font-size: 1rem; color: #9e9e9e;">
          Your narrative journey so far
        </v-card-subtitle>
        <v-card-text>
          <div v-if="pathProgressBadges.length === 0" class="text-center pa-6">
            <v-icon size="64" color="grey">mdi-book-off</v-icon>
            <p class="mt-4" style="font-size: 1.1rem; color: #9e9e9e;">No story paths started yet</p>
            <p style="color: #757575;">Make choices to begin your journey</p>
          </div>
          <div v-else class="path-progress-grid">
            <div 
              v-for="path in pathProgressBadges" 
              :key="path.path"
              class="path-progress-card"
              :class="{ 'active-path': path.isActive, 'current-path': path.isCurrent }"
            >
              <div class="path-header">
                <span class="path-icon">{{ getPathIcon(path.path) }}</span>
                <span class="path-label">{{ path.label }}</span>
                <v-chip 
                  v-if="path.isCurrent" 
                  size="x-small" 
                  color="warning" 
                  class="ml-2"
                >
                  Current
                </v-chip>
                <v-chip 
                  v-else-if="path.isActive" 
                  size="x-small" 
                  color="info" 
                  class="ml-2"
                >
                  Active
                </v-chip>
              </div>
              <div class="path-progress-bar-container">
                <div class="path-progress-bar" :style="{ width: path.progressPercent + '%' }"></div>
              </div>
              <div class="path-stage-info">
                Stage {{ path.stageIndex + 1 }} of {{ path.totalStages }}: <strong>{{ path.stage }}</strong>
                <span class="path-percentage">({{ path.progressPercent }}%)</span>
              </div>
            </div>
          </div>
          
          <!-- Active Consequences Summary -->
          <div v-if="activeConsequenceBadges.length > 0" class="mt-6">
            <v-divider class="mb-4"></v-divider>
            <h4 style="text-align: center; margin-bottom: 12px; color: #ff7043;">🔥 Active Story Effects</h4>
            <div class="consequence-badges">
              <v-chip 
                v-for="badge in activeConsequenceBadges" 
                :key="badge.key"
                :color="badge.color || 'error'"
                size="small"
                class="ma-1"
              >
                {{ badge.label || badge.key }}
              </v-chip>
            </div>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn 
            class="retro-btn" 
            color="primary" 
            @click="showStoryRecap = false"
            style="font-size: 1rem; padding: 12px 40px;"
          >
            <v-icon left>mdi-check</v-icon>
            Got it!
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Suicide Dialog - RETRO PIXEL EDITION -->
    <v-dialog v-model="suicideDialog" max-width="850" max-height="92vh" persistent rounded="0" content-class="suicide-dialog-content">
      <v-card class="suicide-dialog retro-pixel-death" style="min-height: 500px; max-height: 85vh; overflow-y: auto; image-rendering: pixelated;">
        <!-- CRT Glow Overlay -->
        <div class="death-glow" aria-hidden="true"></div>
        <div class="death-scanlines" aria-hidden="true"></div>
        <div class="death-vignette" aria-hidden="true"></div>
        
        <!-- SKULL HEADER -->
        <v-card-title class="death-title glitch-death" data-text="FINAL FATE 💀">
          <div style="font-size: clamp(1.4rem, 4vw, 2.2rem); line-height: 1.1; padding: 12px 0; text-align: center;">
            YOUR FINAL FATE
            <div style="font-size: 3.5rem; margin: 0.2em 0; animation: skull-bounce 2s infinite;">💀</div>
          </div>
        </v-card-title>
        
        <v-card-subtitle class="death-subtitle mb-6 text-center">
          <span>CHOOSE YOUR END - NO TURNING BACK</span>
          <div style="font-size: 3rem; margin-top: 8px;">⚰️</div>
        </v-card-subtitle>
        
        <!-- ALL METHODS GRID - RETRO PIXEL CARDS -->
        <v-card-text class="death-methods-container">
          <div class="methods-grid">
            <div 
              v-for="(method, idx) in suicideMethods" 
              :key="idx"
              class="death-method-card"
              :style="{ '--method-delay': `${idx * 0.15}s` }"
              :class="{ 'selected-method': method === selectedSuicideMethod }"
              @click="selectSuicideMethod(method)"
            >
              <div class="method-icon">💀</div>
              <div class="method-name">{{ method.name }}</div>
            </div>
          </div>
          <div class="death-warning mt-8 p-4">
            <span class="warning-text">ALL PATHS LEAD TO DARKNESS</span>
          </div>
        </v-card-text>
        
        <!-- SINGLE MASSIVE CONFIRM BUTTON -->
        <v-card-actions class="death-actions justify-center pb-8">
          <v-btn 
            size="x-large"
            variant="elevated"
            color="error"
            class="death-confirm-btn pulse-danger"
            @click="confirmSuicide"
            :disabled="!selectedSuicideMethod"
            style="font-size: 1.4rem; padding: 20px 60px; min-width: 280px;"
          >
            💀 END BY {{ selectedSuicideMethod ? selectedSuicideMethod.name.toUpperCase() : 'CHOOSE METHOD' }} 💀
          </v-btn>
<v-btn 
            variant="tonal" 
            color="grey"
            size="large"
            @click="cancelSuicide"
            class="live-on-btn"
          >
            LIVE ON...
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Step 4: MIRACULOUS SURVIVAL POPUP (Retro Pixel) -->
    <v-dialog v-model="showSurvivalPopup" max-width="650" rounded="0" content-class="survival-popup-content">
      <v-card class="survival-popup retro-pixel-survival" style="image-rendering: pixelated;">

        <!-- Glow + Scanlines -->
        <div class="survival-glow" aria-hidden="true"></div>
        <div class="survival-scanlines" aria-hidden="true"></div>
        
        <!-- Header -->
        <v-card-title class="survival-title glitch-survival">
          <div style="font-size: clamp(1.4rem, 5vw, 2rem); line-height: 1.1;">
            🌟 MIRACULOUS SURVIVAL 🌟
          </div>
        </v-card-title>
        
        <v-card-subtitle class="survival-subtitle mb-6 text-center">
          You miraculously survived...<br>
          <span style="font-size: 1.4rem; color: #00ff88;">Maybe it's not your time yet</span>
        </v-card-subtitle>
        
        <!-- Message -->
        <v-card-text class="survival-message text-center">
          <div style="font-size: 1.6rem; margin-bottom: 12px;">Your attempt did not succeed.</div>
          <div style="font-size: 1.1rem; opacity: 0.9;">Sometimes life has other plans...</div>
        </v-card-text>
        
        <!-- CONTINUE Button -->
        <v-card-actions class="survival-actions justify-center pb-8">
        <v-btn 
            size="x-large"
            color="warning"
            class="survival-btn pulse-warning"
            @click="closeSurvivalPopup"
            style="z-index: 10; pointer-events: auto; font-size: 1.3rem; padding: 18px 48px; min-width: 260px;"
          >
            CONTINUE LIVING... 👻
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Step 5: BLOODY HELLISH GAME OVER OVERLAY ☠️🩸 -->
    <v-overlay v-model="showGameOverOverlay" contained z-index="9999" class="gameover-hell-overlay align-center justify-center text-center">
      <div class="gameover-hell-container">
        <!-- Circling Death Emojis (4 orbiting) -->
        <div class="death-orbit" style="--orbit-delay: 0s; --orbit-radius: 120px;">☠️</div>
        <div class="death-orbit" style="--orbit-delay: -3s; --orbit-radius: 160px;">💀</div>
        <div class="death-orbit" style="--orbit-delay: -6s; --orbit-radius: 200px;">🩸</div>
        <div class="death-orbit" style="--orbit-delay: -9s; --orbit-radius: 240px;">🔥</div>
        
        <!-- MASSIVE DISTORTED STAMP -->
        <div class="gameover-stamp glitch-hell" data-text="GAME OVER">
          G A M E&nbsp;&nbsp;&nbsp;O V E R
        </div>
        
        <!-- Bloody Subtitle -->
        <div class="hell-subtitle">
          {{ selectedSuicideMethod?.name?.toUpperCase() || 'FATE UNKNOWN' }}<br>
          <span>FINAL AGE: {{ character.age || character.currentDay }}</span>
        </div>
        
        <!-- Blood Splatter Effects -->
        <div class="blood-splatter" aria-hidden="true"></div>
        <div class="blood-drips-1" aria-hidden="true"></div>
        <div class="blood-drips-2" aria-hidden="true"></div>
        
        <!-- Hellfire Background Glow -->
        <div class="hellfire-glow" aria-hidden="true"></div>
        
        <!-- REINCARNATE Button -->
        <v-btn 
          size="x-large" 
          color="error" 
          class="restart-hell-btn pulse-hellfire"
          style="font-size: 1.4rem; padding: 20px 60px; margin-top: 40px;"
          @click="startNewGame"
        >
          👹 REINCARNATE 👹
        </v-btn>
      </div>
    </v-overlay>

    <!-- 5-Card Random Event Animation Overlay -->
    <v-overlay v-model="showCardAnimation" contained class="card-animation-overlay align-center justify-center text-center" z-index="999">
      <div class="animation-container">
        <h3 class="animation-title glitch" data-text="RANDOM EVENT CARDS">RANDOM EVENT CARDS</h3>
        <div class="cards-arc">
          <div
            v-for="(card, index) in animationCards"
            :key="`anim-${index}`"
            class="animation-card"
              :class="[
                `phase-${animationPhase}`,
                `pos-${shufflePositions[index] || index}`,
                index === winnerIndex || index === selectedAnimationCardIndex ? 'winner' : ''
              ]"
              :style="getCardStyle(index)"
              @click="animationPhase === 'choose' ? selectAnimationCard(index) : null"
            >
            <div class="card-back" v-if="animationPhase !== 'show'">
              ❓
            </div>
            <div v-else class="card-preview">
              <div class="card-visual-mini">
                <div class="card-img-mini" :style="{ backgroundImage: `url(${card.image})` }"></div>
              </div>
              <div class="card-title-mini">{{ card.title }}</div>
            </div>
          </div>
        </div>
        <div class="phase-indicator">{{ getPhaseLabel(animationPhase) }}</div>
      </div>
    </v-overlay>

    <!-- Profession Choice Dialog -->
    <v-dialog
      v-model="showProfessionChoiceDialog"
      max-width="800"
      rounded="xl"
      content-class="profession-dialog-content"
    >
      <v-card v-if="professionChoices.length > 0" class="profession-dialog">
        <div class="profession-glow" aria-hidden="true"></div>
        <div class="profession-scanlines" aria-hidden="true"></div>
        
        <v-card-title class="profession-title">
          <v-icon class="profession-title-icon" size="32">mdi-briefcase</v-icon>
          <span class="profession-title-text">CHOOSE YOUR CAREER</span>
        </v-card-title>
        
        <v-card-subtitle class="profession-subtitle text-center">
          Select your professional path wisely - it will affect future career events!
        </v-card-subtitle>
        
        <v-card-text class="profession-cards-container">
          <div class="profession-cards-grid">
            <div
              v-for="(choice, idx) in professionChoices"
              :key="idx"
              class="profession-choice-card"
              :class="{ 'selected': selectingProfession }"
              @click="selectingProfession ? null : selectProfession(choice)"
              :disabled="selectingProfession"
            >
              <div class="profession-card-icon">
                <v-icon size="48">mdi-account-tie</v-icon>
              </div>
              <div class="profession-card-name">{{ choice.profession }}</div>
              <div class="profession-card-desc">{{ choice.description }}</div>
              <v-btn
                v-if="!selectingProfession"
                color="primary"
                variant="tonal"
                size="small"
                class="mt-2"
              >
                Choose
              </v-btn>
              <v-progress-circular
                v-else
                indeterminate
                size="24"
                color="primary"
                class="mt-2"
              />
            </div>
          </div>
        </v-card-text>
        
        <v-card-actions class="profession-actions justify-center pb-6">
          <v-btn
            variant="outlined"
            color="error"
            size="large"
            @click="closeProfessionChoiceDialog"
            :disabled="selectingProfession"
          >
            Cancel
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- All Stats Dialog -->
    <v-dialog v-model="showStatsDialog" max-width="500" rounded="xl" content-class="stats-dialog-content">
      <v-card class="stats-dialog-card">
        <v-card-title class="stats-dialog-title">
          <v-icon class="mr-2">mdi-chart-box</v-icon>
          CHARACTER STATS
        </v-card-title>
        <v-card-text class="stats-dialog-content-inner">
          <!-- Core Attributes -->
          <div class="stats-section">
            <h5 class="stats-section-title">⚔️ CORE ATTRIBUTES</h5>
            <div class="stats-grid">
              <div v-for="(value, stat) in coreStats" :key="stat" class="stat-item">
                <div class="stat-header">
                  <span class="stat-icon">{{ getStatIcon(stat) }}</span>
                  <span class="stat-name">{{ stat }}</span>
                  <span class="stat-value">{{ typeof value === 'number' ? value + '%' : value }}</span>
                </div>
                <div class="stat-bar">
                  <div class="stat-fill" :style="{ width: (typeof value === 'number' ? value : 50) + '%', background: getStatGradient(typeof value === 'number' ? value : 50) }"></div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Fate Stats -->
          <div class="stats-section mt-4">
            <h5 class="stats-section-title fate">🍀 FATE</h5>
            <div class="stats-grid">
              <div v-for="(value, stat) in fateStats" :key="stat" class="stat-item">
                <div class="stat-header">
                  <span class="stat-icon">{{ getStatIcon(stat) }}</span>
                  <span class="stat-name">{{ stat }}</span>
                  <span class="stat-value">{{ typeof value === 'number' ? value + '%' : value }}</span>
                </div>
                <div class="stat-bar">
                  <div class="stat-fill" :style="{ width: (typeof value === 'number' ? value : 50) + '%', background: getStatGradient(typeof value === 'number' ? value : 50) }"></div>
                </div>
              </div>
            </div>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" variant="tonal" @click="showStatsDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Achievements Dialog -->
    <Achievements
      v-model="showAchievementsDialog"
      :character-id="character?.id"
    />

    <!-- Mini Game Dialog -->
    <MiniGameWrapper
      v-model="showMiniGameDialog"
      :game-type="miniGameType"
      :game-data="miniGameData"
      :character-id="character?.id"
      @complete="handleMiniGameComplete"
    />
  </div>
</template>




<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import MiniGameWrapper from '../components/MiniGames/MiniGameWrapper.vue'
import Achievements from '../components/Achievements.vue'

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
  if (value > 70) return 'linear-gradient(90deg, #22c55e, #16a34a)'
  if (value < 30) return 'linear-gradient(90deg, #ef4444, #dc2626)'
  return 'linear-gradient(90deg, #f59e0b, #d97706)'
}

// Get health status CSS class
const getHealthStatusClass = (status) => {
  const classes = {
    'healthy': 'status-healthy',
    'fever': 'status-fever',
    'sick': 'status-sick',
    'unhealthy': 'status-unhealthy',
    'critical': 'status-critical',
    'dead': 'status-dead'
  }
  return classes[status] || 'status-healthy'
}

// Get health status icon
const getHealthStatusIcon = (status) => {
  const icons = {
    'healthy': 'mdi-heart-check',
    'fever': 'mdi-thermometer',
    'sick': 'mdi-hospital',
    'unhealthy': 'mdi-heart-pulse',
    'critical': 'mdi-alert',
    'dead': 'mdi-skull-crossbones'
  }
  return icons[status] || 'mdi-heart'
}

const formatChoiceLabel = (choice) => {
  const baseText = choice?.text || 'Accept'
  return baseText
}

// Helper to get outcomes from choice
const getChoiceOutcomes = (choice) => {
  if (!choice) return []
  return choice.outcomes || []
}

// Helper to format stat effects for display
const formatStatEffects = (effects) => {
  if (!effects) return ''
  // Parse effects like "+5 Intelligence, -2 Happiness"
  const parts = effects.split(',').map(part => part.trim())
  return parts.slice(0, 3).join(', ') // Show max 3 effects
}

const getCardBadgeLabel = (event) => {
  if (event?.deck_label) return event.deck_label
  const type = String(event?.type || '')
  const labels = {
    system: 'Action',
    daily: 'Daily',
    cultural: 'Culture',
    ageSpecific: 'Story',
    profession: 'Career',
    profession_choice: 'Career',
    trigger: 'Trigger',
    milestone: 'Milestone',
  }
  return labels[type] || 'Event'
}

const getCardBadgeClass = (event) => {
  const type = String(event?.type || '')
  const classes = {
    system: 'actions',
    daily: 'daily',
    cultural: 'cultural',
    ageSpecific: 'story',
    profession: 'career',
    profession_choice: 'career',
    trigger: 'milestone',
    milestone: 'milestone',
    luck: 'luck',
  }
  return classes[type] || 'actions'
}

const formatBadgeLabel = (value) => {
  const text = String(value || '')
  if (!text) return ''

  return text
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase())
}

const getPathIcon = (path) => {
  const icons = {
    'education': '🎓',
    'career': '💼',
    'family': '👨‍👩‍👧',
    'health': '❤️',
    'wealth': '💰',
    'social': '🤝',
    'adventure': '🗺️',
    'creativity': '🎨',
    'spirituality': '🧘',
    'legacy': '🏛️'
  }
  return icons[path] || '⭐'
}

const formatNarrativeLabel = (value) => {
  if (!value) return ''
  // Just show the clean label without "Arc:" prefix
  return formatBadgeLabel(value)
}

const normalizeEventsPayload = (data = {}) => ({
  skills_to_learn: Array.isArray(data.skills_to_learn) ? data.skills_to_learn : [],
  daily_actions: Array.isArray(data.daily_actions) ? data.daily_actions : [],
  life_actions: Array.isArray(data.life_actions) ? data.life_actions : [],
  triggers: Array.isArray(data.triggers) ? data.triggers : [],
  profession_choices: Array.isArray(data.profession_choices) ? data.profession_choices : [],
  daily: Array.isArray(data.daily) ? data.daily : [],
  cultural: Array.isArray(data.cultural) ? data.cultural : [],
  ageSpecific: Array.isArray(data.ageSpecific) ? data.ageSpecific : [],
  profession: Array.isArray(data.profession) ? data.profession : [],
  luck: Array.isArray(data.luck) ? data.luck : [],
  milestone: data.milestone || null,
  // Branching system data
  path_progress: Array.isArray(data.path_progress) ? data.path_progress : [],
  completed_chains: data.completed_chains || {}
})

const syncCharacterRuntime = (source = {}, { announceState = false } = {}) => {
  const previousFlags = { ...(character.value?.characterState?.decision_profile?.flags || {}) }
  const nextCharacterState = source.character_state || source.characterState || character.value.characterState || {}
  const nextFlags = { ...(nextCharacterState?.decision_profile?.flags || {}) }

  character.value = {
    ...character.value,
    stats: source.stats || character.value.stats || {},
    hiddenStats: source.hidden_stats || source.hiddenStats || character.value.hiddenStats || {},
    effectiveStats: source.effective_stats || source.effectiveStats || character.value.effectiveStats || {},
    luck: source.luck ?? character.value.luck ?? 50,
    karma: source.karma ?? character.value.karma ?? 50,
    profession: source.profession ?? character.value.profession,
    ageGroup: source.age_group || source.ageGroup || character.value.ageGroup,
    currentDay: source.current_day || source.currentDay || character.value.currentDay,
    age: source.age || character.value.age || source.current_day || character.value.currentDay,
    healthStatus: source.health_status || source.healthStatus || character.value.healthStatus || 'healthy',
    healthPercentage: source.health_percentage || source.healthPercentage || character.value.healthPercentage || 100,
    currentNarrative: source.narrative_path || source.current_narrative || source.currentNarrative || character.value.currentNarrative || null,
    activePaths: source.active_paths || source.active_event_paths || source.activePaths || character.value.activePaths || [],
    characterState: nextCharacterState,
  }

  if (announceState) {
    Object.entries(consequenceCatalog).forEach(([key, meta]) => {
      if (!previousFlags[key] && nextFlags[key]) {
        narrationHistory.value.push(`✦ ${meta.label}: ${meta.description}`)
      }
    })
  }
}

// End Day function - advance to next day
const endDay = async () => {
  if (!character.value.id) return
  
  loading.value = true
  try {
    const response = await fetch(`/api/characters/${character.value.id}/end-day`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      credentials: 'include'
    })
    
    if (!response.ok) throw new Error('Failed to end day')
    
    const data = await response.json()
    
    syncCharacterRuntime(data, { announceState: true })
    
    // Check for game over
    if (data.game_over) {
      gameOver.value = true
      endingTitle.value = data.ending_title
      endingDescription.value = data.ending_description
      endingType.value = data.ending_type
      deathCause.value = data.death_cause || null
      return
    }
    
    // Reload events for new age
    await fetchEvents()
    
    narrationHistory.value.push(`Age ${character.value.age} begins...`)
  } catch (error) {
    console.error('Error ending day:', error)
  } finally {
    loading.value = false
  }
}

const getRouteCharacterId = () => {
  const id = route.params.characterId
  if (Array.isArray(id)) return id[0] || null
  return id ?? null
}

const loading = ref(false)
const showStats = ref(false)
const showHowToPlay = ref(false)
const showStoryRecap = ref(false)
const selectedEvent = ref(null)
const showEventDialog = ref(false)
const applyingOutcome = ref(false)
const narrationHistory = ref([])
const narrationBodyRef = ref(null)
const statsPanelKey = ref(0)
const isGuestUser = ref(false)
const shareConsent = ref(false)
const savingConsent = ref(false)

const suicideDialog = ref(false)
const selectedSuicideMethod = ref(null)
const showSurvivalPopup = ref(false)
const showGameOverOverlay = ref(false)

// Ending details
const endingTitle = ref(null)
const endingDescription = ref(null)
const endingType = ref(null)
const deathCause = ref(null)

const suicideMethods = ref([
  { name: 'JUMP OFF BRIDGE' },
  { name: 'CAR COLLISION' },
  { name: 'HANGING' },
  { name: 'OVERDOSE' },
  { name: 'GUNSHOT' }
])

// If the dialog is closed via scrim click / ESC, ensure cards are clickable again.
watch(showEventDialog, (isOpen) => {
  if (!isOpen) selectedEvent.value = null
})

// Auto-scroll narration to bottom when new entries are added
watch(narrationHistory, () => {
  nextTick(() => {
    if (narrationBodyRef.value) {
      narrationBodyRef.value.scrollTop = narrationBodyRef.value.scrollHeight
    }
  })
}, { deep: true })

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
  age: 1,
  healthStatus: "healthy",
  healthPercentage: 100,
  profession: null,
  currentNarrative: null,
  activePaths: [],
  characterState: {},
  skills: [],
  talents: [],
  stats: {},
  hiddenStats: {},
  luck: 50,
  karma: 50
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

const headerStats = computed(() => {
  const stats = effectiveStats.value?.visible || {}
  // Requirement: do not show Health % in the UI
  const { Health: _health, ...rest } = stats
  return rest
})

// Split stats into categories for the All Stats dialog
const visibleStats = computed(() => {
  const stats = effectiveStats.value?.visible || {}
  // Show only the main visible stats (not Health)
  const { Health: _h, ...rest } = stats
  return rest
})

const coreStats = computed(() => {
  const stats = character.value.stats || {}
  return stats
})

const hiddenStats = computed(() => {
  const stats = character.value.hiddenStats || {}
  return stats
})

const fateStats = computed(() => {
  // Luck and Karma from character
  return {
    Luck: character.value.luck ?? 50,
    Karma: character.value.karma ?? 50
  }
})

const healthStatusLabel = computed(() => {
  const status = String(character.value?.healthStatus || 'healthy')
  return status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Healthy'
})

const availableEvents = ref({
  skills_to_learn: [],
  daily_actions: [],
  life_actions: [],
  triggers: [],
  profession_choices: [],
  daily: [],
  cultural: [],
  ageSpecific: [],
  profession: [],
  milestone: null,
  // Branching system data
  path_progress: [],
  completed_chains: {}
})

const consequenceCatalog = {
  study_habit: {
    label: 'Study Habit',
    icon: 'mdi-book-open-page-variant',
    description: 'Your learning routine is opening more study-focused paths and disciplined options.'
  },
  burnout_cycle: {
    label: 'Burnout Cycle',
    icon: 'mdi-fire-alert',
    description: 'Overwork is starting to reshape your choices. Recovery options appear, but collapse risks do too.'
  },
  relationship_strain: {
    label: 'Relationship Strain',
    icon: 'mdi-heart-broken',
    description: 'Distance is building up, so future social and family choices now revolve around repair or withdrawal.'
  },
  scandal_marked: {
    label: 'Scandal Pressure',
    icon: 'mdi-bullhorn',
    description: 'Your reputation is unstable, so more image-control and reputation-recovery choices will show up.'
  },
  dependency_flag: {
    label: 'Dependency Risk',
    icon: 'mdi-pill',
    description: 'A coping habit is taking hold. Help, relapse, and self-control choices now affect your future heavily.'
  },
  financial_trap: {
    label: 'Financial Trap',
    icon: 'mdi-cash-alert',
    description: 'Money pressure is steering the story, unlocking harsher survival and recovery decisions.'
  },
  recovery_arc: {
    label: 'Recovery Arc',
    icon: 'mdi-heart-plus',
    description: 'Healthy habits are beginning to pay off, making restorative paths more available.'
  },
}

const decisionProfile = computed(() => character.value?.characterState?.decision_profile || {})

const currentNarrativeLabel = computed(() => formatNarrativeLabel(character.value?.currentNarrative))

const activePathBadges = computed(() => {
  const paths = Array.isArray(character.value?.activePaths) ? character.value.activePaths : []
  return paths.map(formatBadgeLabel)
})

// Path progress display for branching visualization
const pathProgressBadges = computed(() => {
  const progress = Array.isArray(availableEvents.value?.path_progress) 
    ? availableEvents.value.path_progress 
    : []
  return progress.map(p => ({
    path: p.path,
    label: formatBadgeLabel(p.path),
    stage: p.current_stage,
    stageIndex: p.stage_index,
    totalStages: p.total_stages,
    isActive: p.is_active,
    isCurrent: p.is_current,
    // Use backend-calculated progress_percent if available, otherwise calculate locally
    progressPercent: p.progress_percent ?? (p.total_stages > 0 ? Math.round((p.stage_index / p.total_stages) * 100) : 0)
  }))
})

const completedChains = computed(() => {
  return availableEvents.value?.completed_chains || {}
})

const activeConsequenceBadges = computed(() => {
  const flags = decisionProfile.value?.flags || {}
  return Object.entries(consequenceCatalog)
    .filter(([key]) => flags[key] === true)
    .map(([key, meta]) => ({ key, ...meta }))
})

const selectedEventMeta = computed(() => {
  if (!selectedEvent.value) return []

  const meta = []
  if (selectedEvent.value.deck_label) meta.push(selectedEvent.value.deck_label)
  if (selectedEvent.value.archetype) meta.push(formatBadgeLabel(selectedEvent.value.archetype))
  if (currentNarrativeLabel.value) meta.push(currentNarrativeLabel.value)

  return meta
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

// Life summary (shown on death)
const showLifeSummaryDialog = ref(false)
const lifeSummary = ref(null)
const lifeSummaryLoading = ref(false)
const lifeSummaryError = ref(null)

const canRedrawDaily = ref(true)
const canRedrawCultural = ref(true)
const canRedrawAgeSpecific = ref(true)
const canRedrawProfession = ref(true)

// Animation state for 5-card random event
const showCardAnimation = ref(false)
const animationCards = ref([])
const animationPhase = ref('')
const animationTimer = ref(null)
const winnerIndex = ref(-1)
const selectedAnimationCardIndex = ref(null)
const shufflePositions = ref([0,1,2,3,4])

// Profession choice dialog state
const showProfessionChoiceDialog = ref(false)
const professionChoices = ref([])
const selectingProfession = ref(false)

// Mini-game state
const showMiniGameDialog = ref(false)
const showAchievementsDialog = ref(false)
const showStatsDialog = ref(false)
const miniGameType = ref(null)
const miniGameData = ref(null)
const miniGameEventId = ref(null)
const miniGameEventType = ref(null)
const pendingChoiceApply = ref(null)

// Enhanced riffle shuffle simulation
const shuffleArray = (arr) => {
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
  return arr
}

const shuffleCards = () => {
  // TRIPLE FULL ARRAY RESHUFFLE - card 3 CHANGES!
  for(let pass = 0; pass < 3; pass++) {
    shuffleArray(animationCards.value)
    setTimeout(() => {}, pass * 800)
  }
  
  // Visual chaos during
  shufflePositions.value = Array(5).fill().map((_,i) => i)
  for(let i = 0; i < 20; i++) {
    setTimeout(() => shuffleArray(shufflePositions.value), i * 100)
  }
  
  // Settle neat for pick
  setTimeout(() => shufflePositions.value = [0,1,2,3,4], 3000)
}

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
      },
      credentials: 'include'
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    const response = await fetch('/api/consent', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken || '',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        share_consent: !!value
      }),
      credentials: 'include'
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
        },
        credentials: 'include'
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
      },
      credentials: 'include'
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
      image: data.image || '/css/images/player.jpg',
      gender: data.gender,
      ageGroup: data.age_group,
      currentDay: data.current_day || 1,
      age: data.age || data.current_day || 1,
      healthStatus: data.health_status || 'healthy',
      healthPercentage: data.health_percentage || 100,
      profession: data.profession || null,
      relationshipStatus: data.relationship_status || 'single',
      socialConnections: data.social_connections || [],
      currentNarrative: data.current_narrative || null,
      activePaths: data.active_event_paths || [],
      characterState: data.character_state || {},
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
    narrationHistory.value.push(`Welcome, ${character.value.name}! Your life adventure begins...`)
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
      },
      credentials: 'include'
    })

    if (response.status === 401 || response.status === 403 || response.status === 419) {
      throw new Error('Unauthenticated')
    }

    if (!response.ok) {
      let details = ''
      try {
        const errorData = await response.json()
        details = errorData?.message || errorData?.error || JSON.stringify(errorData)
      } catch (e) {
        try {
          details = await response.text()
        } catch (e2) {
          details = ''
        }
      }
      throw new Error(`Failed to fetch events (${response.status})${details ? `: ${details}` : ''}`)
    }
    
    const data = await response.json()
    availableEvents.value = normalizeEventsPayload(data)
    syncCharacterRuntime(data, { announceState: false })
  } catch (error) {
    console.error('Error fetching events:', error)
    narrationHistory.value.push('Error loading events.')

    if (String(error?.message || '').includes('Unauthenticated')) {
      router.push('/home')
      return
    }
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

// Mini-game functions
const triggerMiniGame = async (event, choice, choiceIndex) => {
  const gameType = choice.mini_game || event.mini_game
  if (!gameType) return null
  
  const difficulty = choice.mini_game_difficulty || event.mini_game_difficulty || 1
  const category = choice.category || 'career'
  
  try {
    const response = await fetch(`/api/characters/${character.value.id}/mini-game?game_type=${gameType}&difficulty=${difficulty}&category=${category}&event_id=${event.id}&event_type=${event.type}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })
    
    if (!response.ok) throw new Error('Failed to get mini-game data')
    
    const data = await response.json()
    
    // Store pending choice to apply after game
    pendingChoiceApply.value = { choiceIndex, choice, event }
    
    // Open mini-game dialog
    miniGameType.value = gameType
    miniGameData.value = data.game_data
    miniGameEventId.value = event.id
    miniGameEventType.value = event.type
    showMiniGameDialog.value = true
    
    return true
  } catch (error) {
    console.error('Error triggering mini-game:', error)
    return null
  }
}

const handleMiniGameComplete = (result) => {
  console.log('Mini-game result:', result)
  
  // Apply the pending choice with modified effects from mini-game
  if (pendingChoiceApply.value) {
    const { choiceIndex, choice, event } = pendingChoiceApply.value
    
    // Store the mini-game result effects to merge with choice effects
    choice._miniGameEffects = result.effects
    choice._miniGameMessage = result.message
    choice._miniGameScore = result.score
    
    // Apply the choice with modified effects
    applyChoiceWithEffects(choiceIndex, result.effects, result.message)
    
    pendingChoiceApply.value = null
  }
  
  showMiniGameDialog.value = false
  miniGameType.value = null
  miniGameData.value = null
}

const applyChoiceWithEffects = async (choiceIndex, additionalEffects = {}, message = '') => {
  // Similar to applyChoice but merges the additional effects from mini-game
  if (!selectedEvent.value) return
  
  try {
    applyingOutcome.value = true
    
    const choice = selectedEvent.value.choices[choiceIndex]
    const choiceText = choice?.text || 'Accept'
    
    // Get the mini-game score from the pending choice
    const miniGameScore = choice._miniGameScore ?? null
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/api/characters/${character.value.id}/apply-event`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken || ''
      },
      body: JSON.stringify({
        event_type: selectedEvent.value.type,
        event_id: selectedEvent.value.id,
        choice_index: choiceIndex,
        mini_game_score: miniGameScore
      }),
      credentials: 'include'
    })
    
    // Check if response is ok, if not throw detailed error
    if (!response.ok) {
      let errorMessage = 'Failed to apply event outcome'
      try {
        const errorData = await response.json()
        errorMessage = errorData.message || errorData.error || errorMessage
        console.error('Server error details:', errorData)
      } catch (e) {
        console.error('Error response:', response.status, response.statusText)
      }
      throw new Error(errorMessage)
    }
    
    const data = await response.json()
    console.log('Apply choice with effects response:', data)
    
    // Show mini-game message if any
    if (message) {
      narrationHistory.value.push(`🎮 ${message}`)
    }
    
    // Update character stats - create new object to trigger Vue reactivity
    if (data.character || data.character_state || data.active_paths || data.narrative_path) {
      // Update skills and talents if they changed
      if (data.skills) {
        character.value.skills = data.skills
      }
      if (data.talents) {
        character.value.talents = data.talents
      }
      // Update relationship status and social connections if changed
      if (data.character?.relationship_status) {
        character.value.relationshipStatus = data.character.relationship_status
      }
      if (data.character?.social_connections) {
        character.value.socialConnections = data.character.social_connections
      }
      syncCharacterRuntime({
        ...data,
        narrative: data.narrative || data.outcome || `${choiceText} - ${message || 'Completed'}`
      })
    }
    
    // Close the event dialog and refresh events
    selectedEvent.value = null
    showEventDialog.value = false
    await fetchEvents()
    
  } catch (error) {
    console.error('Error applying choice with effects:', error)
    narrationHistory.value.push(`❌ Error: ${error.message}`)
  } finally {
    applyingOutcome.value = false
  }
}

/**
 * Check if event/choice has mini-game
 */
const hasMiniGame = (event, choice) => {
  const hasGame = !!(choice?.mini_game || event?.mini_game)
  console.log('hasMiniGame check:', { eventMiniGame: event?.mini_game, choiceMiniGame: choice?.mini_game, result: hasGame })
  return hasGame
}

/**
 * Handle choice click - check for mini-game first
 */
const handleChoiceClick = async (choiceIndex) => {
  console.log('DEBUG: handleChoiceClick called', { choiceIndex, event: selectedEvent.value, choices: selectedEvent.value?.choices })
  if (!selectedEvent.value) return
  
  const choice = selectedEvent.value.choices[choiceIndex]
  console.log('DEBUG: Choice clicked:', choice)
  console.log('DEBUG: Event mini_game:', selectedEvent.value.mini_game)
  console.log('DEBUG: Choice mini_game:', choice?.mini_game)
  
  // Check if this choice has a mini-game
  const hasMiniGameNow = await checkAndTriggerMiniGame(selectedEvent.value, choice, choiceIndex)
  
  // If no mini-game triggered, apply choice directly
  if (!hasMiniGameNow) {
    applyChoice(choiceIndex)
  }
}

/**
 * Check if choice or event requires mini-game before applying
 */
const checkAndTriggerMiniGame = async (event, choice, choiceIndex) => {
  if (hasMiniGame(event, choice)) {
    return await triggerMiniGame(event, choice, choiceIndex)
  }
  return false
}

const applyChoice = async (choiceIndex) => {
  if (!selectedEvent.value) return
  
  try {
    applyingOutcome.value = true
    
    const choice = selectedEvent.value.choices[choiceIndex]
    const choiceText = choice?.text || 'Accept'
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/api/characters/${character.value.id}/apply-event`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken || ''
      },
      body: JSON.stringify({
        event_type: selectedEvent.value.type,
        event_id: selectedEvent.value.id,
        choice_index: choiceIndex
      }),
      credentials: 'include'
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
    if (data.character || data.character_state || data.active_paths || data.narrative_path) {
      // Update skills and talents if they changed
      if (data.skills) {
        character.value.skills = data.skills
      }
      if (data.talents) {
        character.value.talents = data.talents
      }
      // Update relationship status and social connections if changed
      if (data.character?.relationship_status) {
        character.value.relationshipStatus = data.character.relationship_status
      }
      if (data.character?.social_connections) {
        character.value.socialConnections = data.character.social_connections
      }
      syncCharacterRuntime({
        ...(data.character || {}),
        age: data.age || data.character?.age,
        health_status: data.health_status || data.character?.health_status,
        health_percentage: data.health_percentage || data.character?.health_percentage,
        character_state: data.character_state || data.character?.character_state,
        active_paths: data.active_paths,
        narrative_path: data.narrative_path,
      }, { announceState: true })
      console.log('Updated character effectiveStats:', character.value.effectiveStats)
      updateEffectiveStats()
    }
    
    // Feature 8: Handle severe consequences
    if (data.consequences && data.consequences.length > 0) {
      for (const consequence of data.consequences) {
        narrationHistory.value.push(`⚠️ ${consequence.message}`)
      }
    }
    
    gameOver.value = data.game_over === true
    
    // Update ending details if game over
    if (data.game_over) {
      endingTitle.value = data.ending_title || null
      endingDescription.value = data.ending_description || null
      endingType.value = data.ending_type || null
      deathCause.value = data.death_cause || null
    }
    
    // Handle milestone from age transition
    if (data.milestone) {
      availableEvents.value.milestone = data.milestone
      narrationHistory.value.push(`🌟 ${data.milestone.title}: ${data.milestone.description}`)
    }
    
    // Log the choice and effects
    narrationHistory.value.push(`You chose: "${choiceText}"`)
    if (data.choice_outcome) {
      narrationHistory.value.push(String(data.choice_outcome))
    }
    if (data.effects) {
      const effectsText = Object.entries(data.effects)
        .map(([stat, value]) => `${value > 0 ? '+' : ''}${value} ${stat}`)
        .join(', ')
      narrationHistory.value.push(`Effects: ${effectsText}`)
    }
    
    // Handle random outcome - show to player!
    if (data.random_outcome && data.random_outcome.occurred) {
      const ro = data.random_outcome
      const typeIcon = ro.type === 'positive' ? '✨' : ro.type === 'negative' ? '💔' : '🎲'
      narrationHistory.value.push(`${typeIcon} Random Event: ${ro.name}`)
      if (ro.description) {
        narrationHistory.value.push(`  "${ro.description}"`)
      }
      if (ro.effects && Object.keys(ro.effects).length > 0) {
        const roEffectsText = Object.entries(ro.effects)
          .map(([stat, value]) => `${value > 0 ? '+' : ''}${value} ${stat}`)
          .join(', ')
        narrationHistory.value.push(`  Bonus Effects: ${roEffectsText}`)
      }
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
  // Clear selectedEvent to re-enable card clicking
  selectedEvent.value = null

  await fetchEvents()
  narrationHistory.value.push(`Age ${character.value.age || character.value.currentDay} continues...`)
}

const loadLifeSummary = async () => {
  if (!character.value?.id) {
    console.warn('No character ID available for life summary')
    lifeSummaryError.value = 'No character ID available'
    return
  }

  try {
    lifeSummaryLoading.value = true
    lifeSummaryError.value = null

    const response = await fetch(`/api/characters/${character.value.id}/life-summary`, {
      headers: { 'Accept': 'application/json' },
      credentials: 'include'
    })
    
    const data = await response.json()
    if (!response.ok) {
      console.error('Life summary API error:', response.status, data)
      throw new Error(data.message || data.error || `HTTP ${response.status}: Failed to load life summary`)
    }
    lifeSummary.value = data
  } catch (error) {
    console.error('Error loading life summary:', error)
    lifeSummaryError.value = error?.message || error?.toString() || 'Error loading life summary.'
  } finally {
    lifeSummaryLoading.value = false
  }
}

const openLifeSummary = async () => {
  showLifeSummaryDialog.value = true
  if (!lifeSummary.value && !lifeSummaryLoading.value) {
    await loadLifeSummary()
  }
}

watch(gameOver, (isOver) => {
  if (isOver) loadLifeSummary()
})

/**
 * Get 5 random events and start card animation
 */
const randomEvent = async () => {
  try {
    loading.value = true
    
    // Fetch 5 random events
    const promises = Array(5).fill().map(() => 
      fetch(`/api/characters/${character.value.id}/random-event`, {
        headers: { 'Accept': 'application/json' },
        credentials: 'include'
      }).then(r => r.json())
    )
    
    const eventsData = await Promise.all(promises)
    let rawCards = eventsData.map(data => data.event).filter(Boolean)
    
    if (rawCards.length === 0) {
      throw new Error('No events loaded')
    }
    
    // INITIAL FULL SHUFFLE #1
    shuffleArray(rawCards)
    animationCards.value = rawCards
    
    // Reset states
    shufflePositions.value = [0,1,2,3,4]
    selectedAnimationCardIndex.value = null
    winnerIndex.value = -1
    
    // Start animation
    await nextTick()
    showCardAnimation.value = true
    animationPhase.value = 'popup'
    startAnimationSequence()
    
    narrationHistory.value.push('🎲 Cards are shuffling... Watch closely! Pick wisely after shuffle.')
    
  } catch (error) {
    console.error('Error fetching random events:', error)
    narrationHistory.value.push('Error loading random event.')
  } finally {
    loading.value = false
  }
}

const startAnimationSequence = () => {
  // Clear existing timer
  if (animationTimer.value) clearTimeout(animationTimer.value)
  
  const phases = [
    { phase: 'popup', duration: 800 },
    { phase: 'show', duration: 1800 },
    { phase: 'flipback', duration: 800 },
    { phase: 'shuffle', duration: 3000, onStart: shuffleCards }
  ]
  
  let i = 0
  const runPhase = () => {
    if (i < phases.length) {
      const phaseData = phases[i]
      const { phase, duration, onStart } = phaseData
      animationPhase.value = phase
      if (onStart) onStart()
      i++
      animationTimer.value = setTimeout(runPhase, duration)
    } else {
      // Enter choose phase
animationPhase.value = 'choose'
    
    // Reset to neat positions for choosing
    shufflePositions.value = [0,1,2,3,4]
    }
  }
  runPhase()
}

const getCardStyle = (index) => ({
  '--card-delay': `${index * 0.1}s`,
  '--card-scale': animationPhase === 'reveal' && index === winnerIndex.value ? '1.1' : '1'
})

const getPhaseLabel = (phase) => {
  const labels = {
    popup: 'Cards Rising...',
    show: 'Memorize Positions',
    flipback: 'Flipping Back...',
    shuffle: 'SHUFFLING...',
    choose: 'PICK A CARD!'
  }
  return labels[phase] || ''
}

const selectAnimationCard = async (index) => {
  if (animationPhase.value !== 'choose' || selectedAnimationCardIndex.value !== null) return
  
  selectedAnimationCardIndex.value = index
  winnerIndex.value = index
  
  const selectedCard = animationCards.value[index]
  selectedEvent.value = selectedCard
  showEventDialog.value = true
  
  narrationHistory.value.push(`You picked card #${index + 1}. Let's see what fate has in store...`)
  
  // Keep overlay briefly for reveal effect, then close
  await new Promise(resolve => setTimeout(resolve, 1200))
  showCardAnimation.value = false
  animationPhase.value = ''
  selectedAnimationCardIndex.value = null
}


/**
 * Re-draw all event cards - can only be used once per day
 */
const redrawEvents = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/api/characters/${character.value.id}/redraw-events`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken || '',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'include'
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
        ...availableEvents.value,
        ...normalizeEventsPayload(data.events)
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/api/characters/${character.value.id}/redraw-event-type`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken || '',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        event_type: eventType
      }),
      credentials: 'include'
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
      availableEvents.value[typeKey] = Array.isArray(data.events) ? data.events : []
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
    // Visible stats
    Health: "❤️",
    Charisma: "✨",
    Burnout: "🔥",
    Wealth: "💰",
    Happiness: "😊",
    // Core stats
    Intelligence: "🧠",
    Strength: "💪",
    Creativity: "🎨",
    Empathy: "💕",
    Social: "👥",
    // Hidden stats
    Reputation: "⭐",
    Isolation: "🌑",
    Addiction: "💉",
    Debt: "📉",
    Morality: "⚖️",
    Discipline: "🎯",
    Ego: "👑",
    // Fate stats
    Luck: "🍀",
    Karma: "🧘"
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
 * Format relationship status for display
 */
const formatRelationshipStatus = (status) => {
  const labels = {
    'single': 'Single',
    'dating': 'Dating',
    'engaged': 'Engaged',
    'married': 'Married',
    'divorced': 'Divorced',
    'widowed': 'Widowed'
  }
  return labels[status] || status
}

/**
 * Get color for relationship status
 */
const getRelationshipColor = (status) => {
  const colors = {
    'single': 'grey',
    'dating': 'pink',
    'engaged': 'purple',
    'married': 'red',
    'divorced': 'orange',
    'widowed': 'blue-grey'
  }
  return colors[status] || 'grey'
}

/**
 * Get icon for connection type
 */
const getConnectionIcon = (type) => {
  const icons = {
    'friend': 'mdi-account-group',
    'family': 'mdi-home-heart',
    'romantic': 'mdi-heart',
    'colleague': 'mdi-briefcase',
    'mentor': 'mdi-school'
  }
  return icons[type] || 'mdi-account'
}

/**
 * Get color for connection type
 */
const getConnectionColor = (type) => {
  const colors = {
    'friend': 'blue',
    'family': 'green',
    'romantic': 'pink',
    'colleague': 'orange',
    'mentor': 'purple'
  }
  return colors[type] || 'grey'
}

/**
 * Get icon for relationship status (header display)
 */
const getRelationshipIcon = (status) => {
  const icons = {
    'single': 'mdi-heart-off',
    'dating': 'mdi-heart-half-full',
    'engaged': 'mdi-ring',
    'married': 'mdi-heart',
    'divorced': 'mdi-heart-broken',
    'widowed': 'mdi-candle'
  }
  return icons[status] || 'mdi-heart'
}

/**
 * Get CSS class for relationship status (header display)
 */
const getRelationshipClass = (status) => {
  const classes = {
    'single': 'relationship-single',
    'dating': 'relationship-dating',
    'engaged': 'relationship-engaged',
    'married': 'relationship-married',
    'divorced': 'relationship-divorced',
    'widowed': 'relationship-widowed'
  }
  return classes[status] || 'relationship-single'
}

/**
 * Save game progress
 */
const saveGame = async () => {
  try {
    isSavingGame.value = true

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    const response = await fetch(`/api/characters/${character.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken || ''
      },
      credentials: 'include',
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    const response = await fetch(`/api/characters/${character.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken || ''
      },
      credentials: 'include',
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
    // Check if this is a profession milestone with choices
    if (milestone.is_profession_milestone && milestone.profession_choices && milestone.profession_choices.length > 0) {
      professionChoices.value = milestone.profession_choices
      showProfessionChoiceDialog.value = true
      narrationHistory.value.push(`🌟 ${milestone.title}: ${milestone.description}`)
    } else {
      narrationHistory.value.push(`🌟 ${milestone.title}: ${milestone.description}`)
    }
  }
}

/**
 * Select a profession from the milestone choice
 */
const selectProfession = async (choice) => {
  if (!character.value?.id || selectingProfession.value) return
  
  try {
    selectingProfession.value = true
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/api/characters/${character.value.id}/set-profession`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken || ''
      },
      body: JSON.stringify({
        profession: choice.profession
      }),
      credentials: 'include'
    })
    
    if (!response.ok) {
      throw new Error('Failed to set profession')
    }
    
    const data = await response.json()
    
    // Update character with the new profession
    character.value.profession = data.profession
    
    // Close dialog
    showProfessionChoiceDialog.value = false
    professionChoices.value = []
    
    // Add narration
    narrationHistory.value.push(`💼 You chose: ${data.profession}!`)
    narrationHistory.value.push(data.message)
    
    // Refresh events to show profession-specific events
    await fetchEvents()
    
  } catch (error) {
    console.error('Error selecting profession:', error)
    narrationHistory.value.push('Error choosing profession. Please try again.')
  } finally {
    selectingProfession.value = false
  }
}

/**
 * Close profession choice dialog
 */
const closeProfessionChoiceDialog = () => {
  showProfessionChoiceDialog.value = false
  professionChoices.value = []
}

/**
 * Suicide methods
 */
const suicide = () => {
  suicideDialog.value = true
}

const selectSuicideMethod = (method) => {
  selectedSuicideMethod.value = method
}

const cancelSuicide = () => {
  suicideDialog.value = false
  selectedSuicideMethod.value = null
}

const closeSurvivalPopup = async () => {
  showSurvivalPopup.value = false
  selectedSuicideMethod.value = null  // Reset for next try
  await nextTick()
  // Force Vuetify dialog state sync
  setTimeout(() => {
    window.dispatchEvent(new Event('resize'))
  }, 50)
}

const confirmSuicide = () => {
  // TRULY RANDOM SUCCESS - REGENERATED EVERY ATTEMPT (0-50%)
  const successRate = 0.90  // 90% success rate - high chance of death, minimal survival
  const success = Math.random() < successRate
  
  console.log(`[DEBUG] ${selectedSuicideMethod.value.name}: rate=${successRate.toFixed(3)} → ${success ? 'HELL' : 'SURVIVE'}`)

  suicideDialog.value = false

  if (success) {
    // RARE SUCCESS → FULL HELL OVERLAY (Step 5 prepares this)
    narrationHistory.value.push(`💀 ${selectedSuicideMethod.value.name.toUpperCase()}: FATE SEALED`)
    narrationHistory.value.push('GAME OVER → ETERNAL DARKNESS')
    showGameOverOverlay.value = true
    gameOver.value = true
  } else {
    // SURVIVAL (most common)
    narrationHistory.value.push(`☠️ ${selectedSuicideMethod.value.name} → FAILED`)
    narrationHistory.value.push('😈 JOKES ON YOU - MIRACULOUS SURVIVAL!')
    effectiveStats.value.visible.Health = Math.max(0, effectiveStats.value.visible.Health - 30)
    showSurvivalPopup.value = true
  }
}

/**
 * Start new game
 */
const startNewGame = () => {
  gameOver.value = false
  endingTitle.value = null
  endingDescription.value = null
  endingType.value = null
  deathCause.value = null
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
  gap: 24px;
  background: 
    linear-gradient(135deg, rgba(15,10,25,0.92) 0%, rgba(25,15,40,0.95) 50%, rgba(10,20,30,0.92) 100%);
  border: 2px solid rgba(0,255,204,0.5);
  border-radius: 6px;
  padding: 24px;
  position: relative;
  box-shadow: 
    0 0 20px rgba(0,255,204,0.2),
    0 4px 20px rgba(0,0,0,0.5);
  font-family: 'Press Start 2P', 'VT323', monospace;
}

/* Pixel corner decorations */
.character-card-enhanced::before {
  content: '';
  position: absolute;
  inset: 0;
  background: 
    repeating-linear-gradient(90deg, transparent 0, transparent 2px, rgba(0,255,204,0.08) 2px, rgba(0,255,204,0.08) 4px),
    repeating-linear-gradient(0deg, transparent 0, transparent 3px, rgba(139,92,246,0.06) 3px, rgba(139,92,246,0.06) 6px);
  pointer-events: none;
}

/* Retro corner decorations with pixel effect */
.character-card-enhanced::after {
  content: '';
  position: absolute;
  top: 8px;
  right: 8px;
  width: 16px;
  height: 16px;
  border-top: 3px solid #00ffcc;
  border-right: 3px solid #00ffcc;
  opacity: 0.8;
}

.character-card-enhanced .corner-bl {
  content: '';
  position: absolute;
  bottom: 8px;
  left: 8px;
  width: 16px;
  height: 16px;
  border-bottom: 3px solid #00ffcc;
  border-left: 3px solid #00ffcc;
  opacity: 0.8;
}

.character-card-enhanced .corner-br {
  content: '';
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 16px;
  height: 16px;
  border-bottom: 3px solid #00ffcc;
  border-right: 3px solid #00ffcc;
  opacity: 0.8;
}

.avatar-wrapper {
  position: relative;
  flex-shrink: 0;
}

/* Avatar pixel frame with glow */
.avatar-wrapper::after {
  content: '';
  position: absolute;
  bottom: -6px;
  right: -6px;
  width: 12px;
  height: 12px;
  border-bottom: 3px solid #00ffcc;
  border-right: 3px solid #00ffcc;
}

.avatar-wrapper {
  position: relative;
  flex-shrink: 0;
}

.character-avatar {
  width: 110px;
  height: 110px;
  border-radius: 4px;
  border: 3px solid #00ffcc;
  object-fit: cover;
  box-shadow: 
    0 0 20px rgba(0,255,204,0.4),
    0 0 40px rgba(139,92,246,0.2);
}

.avatar-ring {
  position: absolute;
  inset: -6px;
  border-radius: 2px;
  border: 3px solid rgba(139,92,246,0.7);
  animation: ring-pulse 2s ease-in-out infinite;
  box-shadow: 
    0 0 15px rgba(139,92,246,0.5),
    inset 0 0 10px rgba(0,255,204,0.2);
}

@keyframes ring-pulse {
  0%, 100% { transform: scale(1); opacity: 0.6; box-shadow: 0 0 15px rgba(139,92,246,0.5); }
  50% { transform: scale(1.12); opacity: 0.3; box-shadow: 0 0 25px rgba(139,92,246,0.8); }
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

/* Two-column layout for character card */
.character-card-left {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
}

.character-card-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-width: 0;
}

.character-header-row {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.header-actions {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  flex-shrink: 0;
  gap: 8px;
  align-items: center;
  white-space: nowrap;
}

.character-footer-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
}

.status-section {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

.avatar-actions {
  display: flex;
  flex-direction: column;
  gap: 4px;
  width: 100%;
}

.character-info {
  flex-shrink: 1;
  min-width: 0;
}

.character-name {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 1.5rem !important;
  font-weight: 700 !important;
  color: #00ffcc !important;
  text-shadow: 
    0 0 25px #00ffcc,
    0 0 50px #00ffcc,
    3px 0 0 #000, -3px 0 0 #00ffcc,
    0 3px 0 #000, 0 -3px 0 #00ffcc !important;
  letter-spacing: 0.15em !important;
  text-transform: uppercase !important;
  animation: name-glitch 4s infinite;
  margin-bottom: 10px !important;
  line-height: 1 !important;
  position: relative;
}

.character-name::before {
  content: '█';
  position: absolute;
  left: -20px;
  opacity: 0.6;
  animation: blink 1s infinite;
}

@keyframes blink {
  0%, 50% { opacity: 0.6; }
  51%, 100% { opacity: 0; }
}

.character-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  max-width: 100%;
}

.meta-badge {
  background: 
    linear-gradient(135deg, rgba(139,92,246,0.95), rgba(99,102,241,0.9), rgba(0,212,170,0.95)) !important;
  color: #ffffff !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.55rem !important;
  font-weight: 700 !important;
  padding: 5px 10px !important;
  border-radius: 0px !important;
  text-transform: uppercase !important;
  letter-spacing: 0.1em !important;
  border: 2px solid rgba(255,255,255,0.4) !important;
  box-shadow: 
    0 3px 0 rgba(0,0,0,0.4),
    0 4px 10px rgba(0,0,0,0.5),
    0 0 15px rgba(139,92,246,0.5) !important;
  image-rendering: pixelated !important;
}

.meta-badge.gender-badge {
  background: linear-gradient(135deg, rgba(236,72,153,0.95), rgba(168,85,247,0.9)) !important;
  box-shadow: 
    0 3px 0 rgba(0,0,0,0.4),
    0 4px 10px rgba(0,0,0,0.5),
    0 0 15px rgba(236,72,153,0.5) !important;
}

.day-counter {
  color: #00ffcc;
  font-size: 1rem;
  font-weight: 700;
  font-family: 'Press Start 2P', monospace;
  text-shadow: 0 0 10px #00ffcc;
  letter-spacing: 0.1em;
}

.profession-badge {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: #fff;
  font-family: 'Press Start 2P', monospace;
  font-size: 0.6rem;
  font-weight: 700;
  padding: 5px 12px;
  border-radius: 0px;
  text-transform: uppercase;
  border: 2px solid rgba(255,255,255,0.3);
  box-shadow: 
    0 3px 0 rgba(0,0,0,0.4),
    0 0 12px rgba(59,130,246,0.5);
}

/* Header Stats - Always Visible - Simplified Style */
.header-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 16px;
  background: 
    linear-gradient(135deg, rgba(10,5,20,0.85), rgba(20,10,35,0.9));
  border: 2px solid rgba(0,255,204,0.4);
  border-radius: 4px;
  box-shadow: 
    0 0 15px rgba(0,255,204,0.2),
    0 4px 15px rgba(0,0,0,0.4);
  font-family: 'Press Start 2P', monospace;
}

/* Health Status Badge */
.health-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 8px;
}

/* Inline Health Status Badge - Next to name */
.health-status-badge-inline {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-left: 12px;
  vertical-align: middle;
}

.character-name-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
}

.status-healthy {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(22, 163, 74, 0.3));
  border: 1px solid #22c55e;
  color: #22c55e;
  box-shadow: 0 0 10px rgba(34, 197, 94, 0.3);
}

.status-fever {
  background: linear-gradient(135deg, rgba(255, 152, 0, 0.2), rgba(255, 87, 34, 0.3));
  border: 1px solid #ff9800;
  color: #ff9800;
}

.status-sick {
  background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(255, 152, 0, 0.3));
  border: 1px solid #ffc107;
  color: #ffc107;
}

.status-unhealthy {
  background: linear-gradient(135deg, rgba(255, 87, 34, 0.2), rgba(244, 67, 54, 0.3));
  border: 1px solid #ff5722;
  color: #ff5722;
}

.status-critical {
  background: linear-gradient(135deg, rgba(244, 67, 54, 0.2), rgba(183, 28, 28, 0.3));
  border: 1px solid #f44336;
  color: #f44336;
  animation: pulse-critical 1s infinite;
}

.status-dead {
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6));
  border: 1px solid #666;
  color: #999;
}

@keyframes pulse-critical {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

.header-stat-bar {
  flex: 1 1 calc(48% - 10px);
  min-width: 100px;
  background: 
    linear-gradient(135deg, rgba(20,15,35,0.95), rgba(10,5,25,0.98));
  border: 2px solid rgba(255,255,255,0.2);
  border-radius: 4px;
  padding: 12px 14px;
  box-shadow: 
    0 3px 10px rgba(0,0,0,0.4),
    0 0 8px rgba(139,92,246,0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.header-stat-bar:hover {
  border-color: rgba(0,255,204,0.6);
  box-shadow: 
    0 4px 15px rgba(0,0,0,0.5),
    inset 0 1px 0 rgba(255,255,255,0.2),
    0 0 20px rgba(0,255,204,0.3);
}

.header-stat-header {
  display: flex;
  align-items: center;
  gap: 3px;
  margin-bottom: 4px;
  font-size: 0.45rem !important;
}

.header-stat-icon {
  font-size: 0.75rem !important;
  text-shadow: 0 0 8px currentColor;
  filter: drop-shadow(0 0 4px rgba(255,255,255,0.5));
}

.header-stat-name {
  color: #22c55e !important;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.55rem;
  font-family: 'Press Start 2P', monospace !important;
  text-transform: uppercase !important;
  flex: 1;
  text-shadow: 1px 1px 0 #000;
}

.header-stat-value {
  color: rgb(var(--accent-rgb));
  font-size: 0.65rem;
  font-weight: 600;
}

.header-stat-track {
  height: 10px;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 0px;
  overflow: hidden;
  box-shadow: 
    inset 0 2px 4px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(0,255,204,0.2);
  position: relative;
}

.header-stat-track::before {
  content: '';
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    0deg,
    transparent 0px,
    transparent 2px,
    rgba(255,255,255,0.03) 2px,
    rgba(255,255,255,0.03) 4px
  );
}

.header-stat-fill {
  height: 100%;
  border-radius: 0px;
  transition: width 0.5s ease;
  box-shadow: 
    0 0 12px currentColor,
    0 0 4px currentColor;
  position: relative;
}

.header-stat-fill::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: rgba(255,255,255,0.3);
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
  flex-wrap: wrap;
  gap: 6px;
  flex-shrink: 0;
  padding: 8px;
  background: 
    linear-gradient(135deg, rgba(0,0,0,0.4), rgba(20,10,35,0.5));
  border-radius: 2px;
  border: 2px solid rgba(0,255,204,0.3);
  align-items: center;
  justify-content: flex-end;
  box-shadow: 
    inset 0 1px 0 rgba(255,255,255,0.1),
    0 0 15px rgba(139,92,246,0.2);
}

/* Retro Pixel Header Buttons - Enhanced Galaxy Theme */
.retro-btn {
  font-family: 'Press Start 2P', 'VT323', monospace !important;
  text-transform: uppercase !important;
  letter-spacing: 0.08em !important;
  image-rendering: pixelated !important;
  border-radius: 0px !important;
  border: 2px solid !important;
  box-shadow: 
    0 0 0 1px rgba(0,0,0,0.8),
    0 4px 0 rgba(0,0,0,0.5),
    0 6px 12px rgba(0,0,0,0.4),
    0 0 15px rgba(139, 92, 246, 0.3),
    inset 0 1px 0 rgba(255,255,255,0.25) !important;
  transition: all 0.15s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
  font-weight: 700 !important;
  font-size: 0.5rem !important;
  padding: 6px 12px !important;
  min-height: 32px !important;
  min-width: auto !important;
  position: relative;
  overflow: hidden;
  background: linear-gradient(180deg, #6366f1 0%, #4f46e5 50%, #3730a3 100%) !important;
  border-color: #818cf8 !important;
  color: #e0e7ff !important;
}

/* Pixel scanline overlay */
.retro-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    0deg,
    transparent 0px,
    transparent 2px,
    rgba(255,255,255,0.03) 2px,
    rgba(255,255,255,0.03) 4px
  );
  pointer-events: none;
}

/* Star sparkle effect */
.retro-btn::after {
  content: '';
  position: absolute;
  top: 2px;
  right: 2px;
  width: 4px;
  height: 4px;
  background: white;
  clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
  animation: btn-sparkle 2s ease-in-out infinite;
  opacity: 0.6;
}

@keyframes btn-sparkle {
  0%, 100% { opacity: 0.3; transform: scale(0.8); }
  50% { opacity: 0.8; transform: scale(1.1); }
}

.retro-btn:hover:not(:disabled) {
  transform: translateY(-2px) !important;
  box-shadow: 
    0 0 0 1px rgba(0,0,0,0.8),
    0 6px 0 rgba(0,0,0,0.5),
    0 10px 20px rgba(0,0,0,0.5),
    0 0 30px rgba(139, 92, 246, 0.5),
    0 0 60px rgba(0,255,204,0.3),
    inset 0 1px 0 rgba(255,255,255,0.4) !important;
  background: linear-gradient(180deg, #818cf8 0%, #6366f1 50%, #4f46e5 100%) !important;
}

.retro-btn:active:not(:disabled) {
  transform: translateY(2px) !important;
  box-shadow: 
    0 0 0 1px rgba(0,0,0,0.8),
    0 2px 0 rgba(0,0,0,0.5),
    0 3px 6px rgba(0,0,0,0.4),
    inset 0 2px 4px rgba(0,0,0,0.3) !important;
}

/* Retro Stats Button - Blue cyan glow */
.retro-stats-btn {
  background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 50%, #1e3a8a 100%) !important;
  border-color: #60a5fa !important;
  color: #00ffcc !important;
  text-shadow: 0 0 10px rgba(0,255,204,0.5) !important;
}

.retro-stats-btn:hover { 
  background: linear-gradient(180deg, #60a5fa 0%, #3b82f6 50%, #1d4ed8 100%) !important;
  box-shadow: 
    0 0 20px rgba(0,255,204,0.4),
    0 6px 12px rgba(59, 130, 246, 0.5),
    0 0 40px rgba(0,255,204,0.2) !important;
}

.retro-edit-btn {
  background: linear-gradient(135deg, #10b981, #059669) !important;
  border-color: #34d399 !important;
  color: white !important;
}

.retro-edit-btn:hover { 
  background: linear-gradient(135deg, #34d399, #10b981) !important;
  box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4), 0 0 20px rgba(16, 185, 129, 0.3) !important;
}

.retro-save-btn {
  background: linear-gradient(135deg, #f59e0b, #d97706) !important;
  border-color: #fbbf24 !important;
  color: white !important;
}

.retro-save-btn:hover { 
  background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
  box-shadow: 0 6px 12px rgba(245, 158, 11, 0.4), 0 0 20px rgba(245, 158, 11, 0.3) !important;
}

.retro-exit-btn {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  border-color: #f87171 !important;
  color: white !important;
}

.retro-exit-btn:hover { 
  background: linear-gradient(135deg, #f87171, #ef4444) !important;
  box-shadow: 0 6px 12px rgba(239, 68, 68, 0.5), 0 0 20px rgba(239, 68, 68, 0.4) !important;
}

.retro-memory-btn {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
  border-color: #60a5fa !important;
  color: white !important;
}

.retro-memory-btn:hover { 
  background: linear-gradient(135deg, #60a5fa, #3b82f6) !important;
  box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4), 0 0 20px rgba(59, 130, 246, 0.3) !important;
}

/* ========================================
   STATS PANEL - Enhanced
   ======================================== */
.stats-panel-enhanced {
  margin-top: 20px;
  padding: 20px;
  background: linear-gradient(135deg, rgba(15,10,25,0.95), rgba(25,20,40,0.98));
  border: 2px solid rgba(0,255,204,0.4);
  border-radius: 16px;
  box-shadow: 
    inset 0 1px 0 rgba(255,255,255,0.1),
    0 0 30px rgba(0,255,204,0.25),
    0 12px 40px rgba(0,0,0,0.6);
  font-family: 'Press Start 2P', 'VT323', monospace;
  image-rendering: pixelated;
  position: relative;
  overflow: hidden;
}

.stats-panel-enhanced::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: 
    repeating-linear-gradient(90deg, transparent 0, transparent 3px, rgba(0,255,204,0.06) 3px, rgba(0,255,204,0.06) 6px),
    repeating-linear-gradient(0deg, transparent 0, transparent 4px, rgba(139,92,246,0.05) 4px, rgba(139,92,246,0.05) 8px);
  pointer-events: none;
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
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.65rem !important;
  font-weight: 800 !important;
  color: #00ff88 !important;
  letter-spacing: 0.15em !important;
  margin-bottom: 16px !important;
  text-transform: uppercase !important;
  text-shadow: 
    0 0 12px #00ff88,
    2px 0 0 #000,
    -2px 0 0 #00ff88 !important;
  position: relative;
}

.section-title::after {
  content: '▔▔▔▔▔▔▔▔▔▔▔▔';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  color: rgba(0,255,136,0.3);
  font-size: 0.5rem;
  letter-spacing: 0;
  text-shadow: none;
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
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.5rem !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.08em !important;
  image-rendering: pixelated !important;
  border-radius: 6px !important;
  border: 2px solid !important;
  height: 28px !important;
  margin: 2px !important;
  box-shadow: 
    0 3px 8px rgba(0,0,0,0.5),
    inset 0 1px 0 rgba(255,255,255,0.3) !important;
}

.skill-badge {
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  border-color: #4ade80 !important;
  color: #ffffff !important;
  box-shadow: 0 0 12px rgba(34, 197, 94, 0.6) !important;
}

.talent-badge {
  background: linear-gradient(135deg, #f59e0b, #d97706) !important;
  border-color: #fbbf24 !important;
  color: #000 !important;
  box-shadow: 0 0 16px rgba(245,158,11,0.7) !important;
  font-weight: 900 !important;
}

.relationship-badge {
  background: linear-gradient(135deg, #ec4899, #be185d) !important;
  border-color: #f472b6 !important;
  color: #ffffff !important;
  box-shadow: 0 0 12px rgba(236,72,153,0.6) !important;
}

.connection-badge {
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  border-color: #4ade80 !important;
  color: #ffffff !important;
  box-shadow: 0 0 12px rgba(34, 197, 94, 0.6) !important;
}

.relationship-badge-header {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.relationship-single {
  background: rgba(107, 114, 128, 0.3);
  color: #9ca3af;
  border: 1px solid rgba(107, 114, 128, 0.5);
}

.relationship-dating {
  background: rgba(236, 72, 153, 0.3);
  color: #f472b6;
  border: 1px solid rgba(236, 72, 153, 0.5);
}

.relationship-engaged {
  background: rgba(168, 85, 247, 0.3);
  color: #d8b4fe;
  border: 1px solid rgba(168, 85, 247, 0.5);
}

.relationship-married {
  background: rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  border: 1px solid rgba(239, 68, 68, 0.5);
}

.relationship-divorced {
  background: rgba(249, 115, 22, 0.3);
  color: #fdba74;
  border: 1px solid rgba(249, 115, 22, 0.5);
}

.relationship-widowed {
  background: rgba(71, 85, 105, 0.3);
  color: #cbd5e1;
  border: 1px solid rgba(71, 85, 105, 0.5);
}

/* ========================================
   HEADER ACTIONS
   ======================================== */
.header-actions {
  display: flex;
  flex-direction: row;
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
  width: 8px;
}

.game-content::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
}

.game-content::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #22c55e 0%, #16a34a 100%);
  border-radius: 4px;
  border: 2px solid rgba(0, 0, 0, 0.3);
}

.game-content::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #4ade80 0%, #22c55e 100%);
}

/* ========================================
   GLOBAL SCROLLBAR THEMING
   ======================================== */
html::-webkit-scrollbar,
body::-webkit-scrollbar,
*::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}

html::-webkit-scrollbar-track,
body::-webkit-scrollbar-track,
*::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.25);
  border-radius: 5px;
}

html::-webkit-scrollbar-thumb,
body::-webkit-scrollbar-thumb,
*::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #22c55e 0%, #15803d 100%);
  border-radius: 5px;
  border: 2px solid rgba(0, 0, 0, 0.35);
}

html::-webkit-scrollbar-thumb:hover,
body::-webkit-scrollbar-thumb:hover,
*::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #4ade80 0%, #22c55e 100%);
}

html::-webkit-scrollbar-corner,
body::-webkit-scrollbar-corner,
*::-webkit-scrollbar-corner {
  background: rgba(0, 0, 0, 0.3);
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
  background: linear-gradient(145deg, rgba(15,12,25,0.95), rgba(25,20,40,0.98));
  border: 3px solid rgba(255,255,255,0.2);
  border-radius: 14px;
  padding: 24px;
  position: relative;
  box-shadow: 
    inset 0 1px 0 rgba(255,255,255,0.12),
    0 0 0 1px rgba(0,255,204,0.4),
    0 12px 40px rgba(0,0,0,0.7),
    0 0 40px rgba(0,255,204,0.25);
  font-family: 'Press Start 2P', monospace;
  image-rendering: pixelated;
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
  color: #e0e7ff;
  margin-bottom: 18px;
  padding: 10px 0;
  width: 100%;
  justify-content: center;
  text-align: center;
  letter-spacing: 0.14em;
  text-shadow:
    0 0 15px rgba(139, 92, 246, 0.6),
    0 0 30px rgba(0, 255, 204, 0.4),
    2px 2px 0 #000;
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
.section-header.actions::before {
  content: '⚡';
  color: #22c55e;
}

.section-header::before {
  content: '♠';
  font-size: 1.2rem;
  margin-right: 4px;
}

.section-header.daily::before {
  content: '♦';
  color: #00ffcc;
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
  color: #a855f7;
  text-shadow:
    0 0 15px rgba(168, 85, 247, 0.6),
    0 0 30px rgba(0, 255, 204, 0.4),
    2px 2px 0 #000;
}

.section-header.game-over {
  color: #ef4444;
}

.header-icon {
  color: #a855f7 !important;
  filter: drop-shadow(0 0 8px rgba(168, 85, 247, 0.6));
}

.section-header.milestone .header-icon {
  color: #a855f7 !important;
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

/* Event Card Item - Galaxy Playing Card Style */
.event-card-item {
  background: linear-gradient(170deg, rgba(35, 25, 55, 0.98) 0%, rgba(20, 15, 40, 0.98) 50%, rgba(15, 10, 30, 0.98) 100%);
  border: 2px solid rgba(139, 92, 246, 0.4);
  border-radius: 4px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: 
    0 8px 25px rgba(0,0,0,0.6),
    0 0 15px rgba(139, 92, 246, 0.2),
    inset 0 1px 0 rgba(255,255,255,0.15),
    inset 0 0 30px rgba(139, 92, 246, 0.05);
  font-family: 'VT323', monospace;
  image-rendering: pixelated;
  transform-style: preserve-3d;
}

/* Pixel corner decorations */
.event-card-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    linear-gradient(90deg, rgba(139, 92, 246, 0.3) 0%, transparent 3%) no-repeat,
    linear-gradient(180deg, rgba(139, 92, 246, 0.3) 0%, transparent 3%) no-repeat,
    linear-gradient(270deg, rgba(139, 92, 246, 0.3) 0%, transparent 3%) no-repeat,
    linear-gradient(0deg, rgba(139, 92, 246, 0.3) 0%, transparent 3%) no-repeat;
  background-size: 100% 2px, 2px 100%, 100% 2px, 2px 100%;
  background-position: top left, top left, bottom right, bottom right;
  pointer-events: none;
  z-index: 1;
}

/* Card hover effect - lift and glow like picking up a card */
.event-card-item:hover:not(.disabled) {
  border-color: rgba(168, 85, 247, 0.8) !important;
  transform: translateY(-10px) scale(1.03) rotateY(5deg) !important;
  box-shadow: 
    0 25px 60px rgba(0,0,0,0.5),
    0 0 40px rgba(139, 92, 246, 0.5),
    0 0 80px rgba(0, 255, 204, 0.3),
    inset 0 1px 0 rgba(255,255,255,0.3),
    0 0 0 2px rgba(168, 85, 247, 0.6) !important;
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
  transform: scale(1.08);
  filter: brightness(1.15);
}

.milestone-card {
  border-color: rgba(168, 85, 247, 0.5);
  background: linear-gradient(170deg, rgba(40, 25, 50, 0.98) 0%, rgba(25, 15, 35, 0.98) 100%);
}

.milestone-card::before,
.milestone-card::after {
  border-color: rgba(168, 85, 247, 0.6);
}

.milestone-card:hover:not(.disabled) {
  border-color: #a855f7;
  box-shadow: 
    0 25px 50px rgba(0,0,0,0.5),
    0 0 40px rgba(168, 85, 247, 0.5),
    0 0 80px rgba(0, 255, 204, 0.3),
    inset 0 1px 0 rgba(255,255,255,0.3) !important;
}

/* Card Visual */
.card-visual {
  position: relative;
  height: 140px;
  overflow: hidden;
  /* Card image area */
  border-bottom: 2px solid rgba(139, 92, 246, 0.3);
}

.card-img {
  width: 100%;
  height: 100%;
  transition: transform 0.4s ease;
}

.event-card-item:hover:not(.disabled) .card-img {
  transform: scale(1.12);
}

.card-type-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.45rem !important;
  font-weight: 800 !important;
  padding: 6px 10px !important;
  border-radius: 0px !important;
  text-transform: uppercase !important;
  letter-spacing: 0.12em !important;
  border: 2px solid !important;
  image-rendering: pixelated !important;
  box-shadow: 
    0 3px 8px rgba(0,0,0,0.6),
    0 0 10px rgba(139, 92, 246, 0.3);
}

.card-type-badge.daily {
  background: linear-gradient(180deg, rgba(0,255,204,0.9) 0%, rgba(0,200,180,0.9) 50%, rgba(0,150,140,0.9) 100%);
  color: #001a15;
  border-color: #00ffcc !important;
  box-shadow: 0 0 15px rgba(0,255,204,0.5), 0 3px 8px rgba(0,0,0,0.6);
}

.card-type-badge.actions {
  background: linear-gradient(180deg, #22c55e 0%, #16a34a 50%, #0f5728 100%);
  color: #bbf7d0;
  border-color: #4ade80 !important;
}

.card-type-badge.cultural {
  background: linear-gradient(180deg, #a855f7 0%, #7c3aed 50%, #5b21b6 100%);
  color: #f3e8ff;
  border-color: #c084fc !important;
  box-shadow: 0 0 15px rgba(168, 85, 247, 0.5), 0 3px 8px rgba(0,0,0,0.6);
}

.card-type-badge.story {
  background: linear-gradient(180deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
  color: #000;
  border-color: #fbbf24 !important;
}

.card-type-badge.career {
  background: linear-gradient(180deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
  color: #e0f2fe;
  border-color: #60a5fa !important;
  box-shadow: 0 0 15px rgba(59, 130, 246, 0.5), 0 3px 8px rgba(0,0,0,0.6);
}

.card-type-badge.skill {
  background: linear-gradient(180deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
  color: #f3e8ff;
  border-color: #a78bfa !important;
  box-shadow: 0 0 15px rgba(139, 92, 246, 0.5), 0 3px 8px rgba(0,0,0,0.6);
}

.card-type-badge.milestone {
  background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
  color: #000;
  font-size: 0.7rem;
  border-color: #fcd34d !important;
  box-shadow: 0 0 15px rgba(251, 191, 36, 0.5), 0 3px 8px rgba(0,0,0,0.6);
}

.mini-game-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  background: rgba(0, 0, 0, 0.7);
  border: 2px solid #00ffcc;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 0 5px rgba(0, 255, 204, 0.5);
  }
  50% {
    box-shadow: 0 0 15px rgba(0, 255, 204, 0.8);
  }
}

/* Card Content */
.card-content {
  padding: 14px;
  flex: 1;
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, transparent 0%, rgba(139, 92, 246, 0.05) 100%);
}

.card-title {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.85rem !important;
  font-weight: 700 !important;
  color: #e0e7ff !important;
  margin-bottom: 10px !important;
  line-height: 1.1 !important;
  letter-spacing: 0.08em !important;
  text-shadow: 2px 2px 0 #000, 0 0 15px rgba(139, 92, 246, 0.6), 0 0 30px rgba(0,255,204,0.3);
  text-transform: uppercase;
}

.card-disabled-reason {
  margin-top: 8px;
  color: #ef4444;
  font-family: 'VT323', monospace;
  font-size: 1rem;
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

/* ========================================
   GLOBAL VUETIFY COMPONENT THEMING
   ======================================== */
/* Buttons */
::v-deep(.v-btn) {
  text-transform: none !important;
  letter-spacing: 0.5px !important;
  font-weight: 600 !important;
}

::v-deep(.v-btn--variant-elevated),
::v-deep(.v-btn--variant-flat) {
  border-radius: 8px !important;
}

/* Inputs */
::v-deep(.v-field) {
  border-radius: 8px !important;
}

::v-deep(.v-field__outline) {
  border-color: rgba(255, 255, 255, 0.2) !important;
}

::v-deep(.v-input__details) {
  display: none !important;
}

/* Selects */
::v-deep(.v-select__selection-text) {
  color: #e2e8f0 !important;
}

/* Chips */
::v-deep(.v-chip) {
  font-weight: 500 !important;
}

/* Text fields */
::v-deep(.v-text-field input),
::v-deep(.v-text-field textarea) {
  color: #e2e8f0 !important;
}

/* Cards */
::v-deep(.v-card) {
  border-radius: 12px !important;
}

/* Dialog overlay */
::v-deep(.v-overlay__scrim) {
  background: rgba(0, 0, 0, 0.75) !important;
}

/* Menu */
::v-deep(.v-menu__content) {
  border-radius: 12px !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

/* Tooltip */
::v-deep(.v-tooltip > .v-overlay__content) {
  background: rgba(30, 30, 50, 0.95) !important;
  border: 1px solid rgba(34, 197, 94, 0.5) !important;
  border-radius: 8px !important;
}

/* Slider */
::v-deep(.v-slider-track__background) {
  background: rgba(255, 255, 255, 0.15) !important;
}

::v-deep(.v-slider-track__fill) {
  background: linear-gradient(90deg, #22c55e, #16a34a) !important;
}

/* Switch */
::v-deep(.v-switch__track) {
  background: rgba(255, 255, 255, 0.15) !important;
}

::v-deep(.v-switch__track--is-active) {
  background: #22c55e !important;
}

/* Tabs */
::v-deep(.v-tab) {
  text-transform: none !important;
}

::v-deep(.v-tab--selected) {
  color: #22c55e !important;
}

/* Progress */
::v-deep(.v-progress-linear) {
  border-radius: 4px !important;
}

::v-deep(.v-progress-linear__bar) {
  background: linear-gradient(90deg, #22c55e, #16a34a) !important;
}

/* Badge */
::v-deep(.v-badge__badge) {
  font-weight: 700 !important;
}

/* List */
::v-deep(.v-list) {
  background: rgba(25, 20, 45, 0.95) !important;
  border-radius: 12px !important;
}

::v-deep(.v-list-item) {
  border-radius: 8px !important;
}

::v-deep(.v-list-item:hover) {
  background: rgba(34, 197, 94, 0.1) !important;
}

/* Divider */
::v-deep(.v-divider) {
  border-color: rgba(255, 255, 255, 0.1) !important;
}

/* Expansion Panels */
::v-deep(.v-expansion-panel) {
  background: rgba(0, 0, 0, 0.2) !important;
  border-radius: 8px !important;
  margin-bottom: 4px !important;
}

::v-deep(.v-expansion-panel-title) {
  padding: 12px 16px !important;
}

/* Navigation Drawer */
::v-deep(.v-navigation-drawer) {
  border-color: rgba(255, 255, 255, 0.1) !important;
}

/* App Bar */
::v-deep(.v-app-bar) {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

/* Bottom Navigation */
::v-deep(.v-bottom-navigation) {
  border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
}

/* ========================================
   ALL STATS DIALOG STYLES (Reference: Purple Galaxy Theme)
   ======================================== */
.stats-dialog-card {
  background: linear-gradient(180deg, rgba(25, 20, 45, 0.98) 0%, rgba(15, 15, 30, 0.98) 100%) !important;
  border: 2px solid rgba(168, 85, 247, 0.5) !important;
  box-shadow: 0 0 30px rgba(168, 85, 247, 0.3) !important;
}

.stats-dialog-title {
  font-family: 'Press Start 2P', monospace !important;
  color: #a855f7 !important;
  text-shadow: 0 0 10px rgba(168, 85, 247, 0.5) !important;
  padding: 20px !important;
  border-bottom: 1px solid rgba(168, 85, 247, 0.3) !important;
}

.stats-dialog-content-inner {
  padding: 20px !important;
  max-height: 55vh;
  overflow-y: auto;
}

/* Custom Scrollbar */
.stats-dialog-content-inner::-webkit-scrollbar {
  width: 8px;
}

.stats-dialog-content-inner::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
}

.stats-dialog-content-inner::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #a855f7 0%, #7c3aed 100%);
  border-radius: 4px;
  border: 2px solid rgba(0, 0, 0, 0.3);
}

.stats-dialog-content-inner::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #c084fc 0%, #8b5cf6 100%);
}

.stats-section {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 12px;
  padding: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.stats-section-title {
  font-family: 'Press Start 2P', monospace;
  font-size: 10px;
  color: #22c55e;
  margin-bottom: 12px;
  letter-spacing: 1px;
}

.stats-section-title.hidden {
  color: #f59e0b;
}

.stats-section-title.fate {
  color: #8b5cf6;
}

.stats-grid {
  display: grid;
  gap: 12px;
}

.stat-item {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 6px 10px;
}

.stat-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.stat-icon {
  font-size: 14px;
}

.stat-name {
  flex: 1;
  font-size: 12px;
  color: #e2e8f0;
  font-weight: 500;
}

.stat-value {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 600;
}

.stat-bar {
  height: 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
  overflow: hidden;
}

.stat-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.3s ease;
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
   EVENT DIALOG STYLES - Card Reveal Effect - Galaxy Theme
   ======================================== */
.event-dialog {
  position: relative;
  border: 2px solid transparent !important;
  border-radius: 12px !important;
  overflow: hidden !important;
  overflow-y: hidden !important;
  max-height: calc(100dvh - 28px - env(safe-area-inset-top) - env(safe-area-inset-bottom));
  background:
    linear-gradient(180deg, rgba(35, 25, 55, 0.98) 0%, rgba(20, 15, 40, 0.98) 50%, rgba(15, 10, 30, 0.98) 100%) padding-box,
    linear-gradient(135deg, rgba(0, 255, 204, 0.5) 0%, rgba(139, 92, 246, 0.4) 50%, rgba(0, 255, 204, 0.5) 100%) border-box !important;
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.6),
    0 0 40px rgba(139, 92, 246, 0.3),
    0 0 80px rgba(0, 255, 204, 0.2),
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
    radial-gradient(ellipse at 20% 20%, rgba(139, 92, 246, 0.3) 0%, transparent 55%),
    radial-gradient(ellipse at 80% 80%, rgba(0, 255, 204, 0.2) 0%, transparent 60%);
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
  color: #e0e7ff;
  letter-spacing: 0.08em;
  text-shadow:
    2px 2px 0 rgba(0, 0, 0, 0.75),
    0 0 20px rgba(139, 92, 246, 0.6),
    0 0 40px rgba(0, 255, 204, 0.4);
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
  color: #a855f7;
  transform: translate(-2px, 0);
  clip-path: polygon(0 0, 100% 0, 100% 38%, 0 38%);
  animation: dialog-glitch-1 3.1s infinite;
}

.dialog-title-text.glitch::after {
  color: #00ffcc;
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
  background: linear-gradient(180deg, rgba(35, 25, 55, 0.4), rgba(15, 10, 30, 0.3));
  border-top: 1px solid rgba(139, 92, 246, 0.3);
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
  border: 2px solid rgba(139, 92, 246, 0.4);
  border-radius: 4px;
  background: linear-gradient(180deg, rgba(35, 25, 55, 0.85), rgba(20, 15, 40, 0.9));
  box-shadow: 0 0 20px rgba(139, 92, 246, 0.2);
}

.choices-title {
  color: #a855f7 !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.9rem !important;
  font-weight: 700 !important;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  margin-bottom: 16px !important;
  text-align: center;
  text-shadow: 0 0 15px rgba(168, 85, 247, 0.6);
  text-shadow: 0 0 10px rgba(var(--accent-rgb), 0.35);
}

.choice-btn {
  font-family: 'Press Start 2P', 'VT323', monospace !important;
  font-size: 0.85rem !important;
  font-weight: 600 !important;
  border: 2px solid rgba(139, 92, 246, 0.4) !important;
  color: #e0e7ff !important;
  margin-bottom: 12px;
  padding: 16px 24px !important;
  border-radius: 0px !important;
  background: linear-gradient(180deg, rgba(35, 25, 55, 0.95) 0%, rgba(20, 15, 40, 0.98) 100%) !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  box-shadow: 
    0 4px 12px rgba(0, 0, 0, 0.3),
    0 0 10px rgba(139, 92, 246, 0.2);
  width: 100%;
  text-align: center;
  justify-content: center;
}

.choice-btn:hover:not(:disabled) {
  background: linear-gradient(180deg, rgba(139, 92, 246, 0.25) 0%, rgba(90, 60, 175, 0.2) 100%) !important;
  border-color: rgba(168, 85, 247, 0.7) !important;
  color: #00ffcc !important;
  transform: translateY(-4px);
  box-shadow: 
    0 8px 25px rgba(0, 0, 0, 0.4),
    0 0 30px rgba(139, 92, 246, 0.4),
    0 0 60px rgba(0, 255, 204, 0.25),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.choice-btn:active:not(:disabled) {
  transform: translateY(-2px);
}

.choice-btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  gap: 8px;
}

/* Locked choice styling */
.choice-btn.choice-locked {
  border-color: rgba(239, 68, 68, 0.5) !important;
  background: linear-gradient(180deg, rgba(55, 20, 20, 0.95) 0%, rgba(40, 15, 15, 0.98) 100%) !important;
  opacity: 0.7;
}

.choice-btn.choice-locked .choice-btn-label {
  color: #fca5a5 !important;
}

/* Mini-game indicator styling */
.mini-game-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  background: linear-gradient(90deg, rgba(0, 255, 204, 0.1) 0%, rgba(0, 255, 204, 0.05) 100%);
  border: 1px solid rgba(0, 255, 204, 0.3);
  border-radius: 8px;
  color: #00ffcc;
  font-family: 'Press Start 2P', 'VT323', monospace;
  font-size: 0.7rem;
  text-transform: uppercase;
  animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 0 5px rgba(0, 255, 204, 0.3);
  }
  50% {
    box-shadow: 0 0 15px rgba(0, 255, 204, 0.5);
  }
}

.mini-game-icon {
  display: inline-flex;
  align-items: center;
  animation: icon-pulse 1.5s ease-in-out infinite;
}

@keyframes icon-pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.1);
  }
}

.choice-btn.choice-with-minigame:not(.choice-locked):hover:not(:disabled) {
  border-color: rgba(0, 255, 204, 0.6) !important;
  box-shadow: 
    0 8px 25px rgba(0, 0, 0, 0.4),
    0 0 30px rgba(0, 255, 204, 0.3),
    0 0 60px rgba(0, 255, 204, 0.15) !important;
}

.locked-indicator {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fca5a5;
  margin-right: 8px;
  animation: pulse-lock 2s infinite;
}

.locked-text {
  font-size: 0.65rem;
  font-weight: bold;
  color: #fca5a5;
  margin-left: 8px;
  padding: 2px 6px;
  border: 1px solid rgba(239, 68, 68, 0.5);
  border-radius: 4px;
  background: rgba(55, 20, 20, 0.5);
}

@keyframes pulse-lock {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

.outcome-label {
  font-size: 0.65rem;
  color: #94a3b8;
  text-transform: uppercase;
}

.outcome-badge {
  font-size: 0.6rem;
  padding: 2px 6px;
  border-radius: 4px;
  text-transform: uppercase;
  font-weight: 600;
}

.outcome-badge.positive {
  background: rgba(34, 197, 94, 0.25);
  color: #4ade80;
  border: 1px solid rgba(34, 197, 94, 0.4);
}

.outcome-badge.negative {
  background: rgba(239, 68, 68, 0.25);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.4);
}

.outcome-badge.mixed {
  background: rgba(251, 191, 36, 0.25);
  color: #fbbf24;
  border: 1px solid rgba(251, 191, 36, 0.4);
}

/* Cancel Button */
.cancel-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-weight: 800 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.12em !important;
  font-size: 0.75rem !important;
  padding: 14px 34px !important;
  border-radius: 0px !important;
  background: linear-gradient(180deg, rgba(239, 68, 68, 0.95) 0%, rgba(185, 28, 28, 0.95) 50%, rgba(127, 29, 29, 0.98) 100%) !important;
  color: #ffffff !important;
  box-shadow:
    0 4px 0 rgba(127, 29, 29, 0.8),
    0 10px 30px rgba(239, 68, 68, 0.3),
    0 0 20px rgba(239, 68, 68, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.18) !important;
  border: 2px solid rgba(248, 113, 113, 0.6) !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.cancel-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  filter: brightness(1.1);
  box-shadow:
    0 6px 0 rgba(127, 29, 29, 0.8),
    0 14px 38px rgba(239, 68, 68, 0.4),
    0 0 40px rgba(239, 68, 68, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.25) !important;
}

.cancel-btn:active:not(:disabled) {
  transform: translateY(2px);
  box-shadow: 0 2px 0 rgba(127, 29, 29, 0.8) !important;
}

/* Dialog Card corners */
.event-dialog::before,
.event-dialog::after {
  content: '✦';
  position: absolute;
  font-size: 1.5rem;
  color: rgba(139, 92, 246, 0.5);
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
    linear-gradient(180deg, rgba(30, 20, 50, 0.98) 0%, rgba(15, 10, 30, 0.98) 100%) padding-box,
    linear-gradient(135deg, rgba(0,255,204,0.45) 0%, rgba(168,85,247,0.35) 45%, rgba(0,255,204,0.55) 100%) border-box !important;
  box-shadow: 
    0 20px 60px rgba(0, 0, 0, 0.6),
    0 0 30px rgba(0, 255, 204, 0.2) !important;
  backdrop-filter: blur(16px);
  display: flex;
  flex-direction: column;
}

.profile-dialog--kiosk {
  border-radius: 12px !important;
  background: 
    linear-gradient(180deg, rgba(30, 20, 50, 0.98), rgba(20, 15, 40, 0.99)) padding-box,
    linear-gradient(135deg, rgba(0,255,204,0.6), rgba(168,85,247,0.5), rgba(0,255,204,0.6)) border-box !important;
  box-shadow: 
    0 0 0 2px rgba(0,255,204,0.6),
    0 30px 90px rgba(0,0,0,0.8),
    0 0 60px rgba(168,85,247,0.3),
    inset 0 0 20px rgba(0,0,0,0.5) !important;
  font-family: 'Press Start 2P', monospace !important;
  image-rendering: pixelated !important;
  border: 3px solid rgba(168,85,247,0.8) !important;
  position: relative;
  overflow: hidden;
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
  padding: 16px;
  border-bottom: 2px solid rgba(0,255,204,0.5);
  background: linear-gradient(90deg, rgba(0,255,204,0.2), transparent);
  position: relative;
}

.profile-topbar::after {
  content: '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  color: rgba(0,255,204,0.3);
  font-size: 0.8rem;
  font-family: 'VT323', monospace;
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
  background: 
    linear-gradient(180deg, rgba(10,8,20,0.9), rgba(20,15,35,0.95)),
    repeating-linear-gradient(
      90deg, transparent 0, transparent 2px, rgba(0,255,204,0.08) 2px, rgba(0,255,204,0.08) 4px
    );
  border-top: 2px solid rgba(0,255,204,0.4);
  border-bottom: 2px solid rgba(139,92,246,0.4);
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
  color: #00ff88 !important;
  font-family: 'Press Start 2P', monospace !important;
  font-weight: 800 !important;
  font-size: 0.6rem !important;
  letter-spacing: 0.15em !important;
  text-transform: uppercase !important;
  text-shadow: 0 0 10px #00ff88, 1px 1px 0 #000 !important;
  margin-bottom: 8px !important;
}

:deep(.profile-input .v-field) {
  background: linear-gradient(135deg, rgba(15,10,25,0.9), rgba(25,20,40,0.95)) !important;
  border: 2px solid rgba(0,255,204,0.5) !important;
  border-radius: 8px !important;
  box-shadow: 
    inset 0 1px 0 rgba(255,255,255,0.1),
    0 0 15px rgba(0,255,204,0.3) !important;
  font-family: 'VT323', monospace !important;
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
  padding: 20px !important;
  display: flex;
  justify-content: center;
  gap: 20px !important;
  border-top: 2px solid rgba(0,255,204,0.5);
  background: linear-gradient(135deg, rgba(15,10,25,0.9), rgba(25,20,40,0.95));
  font-family: 'Press Start 2P', monospace !important;
}

.profile-cancel-btn, .profile-save-btn {
  flex: 1;
  max-width: 160px;
  font-family: 'Press Start 2P', monospace !important;
  font-weight: 900 !important;
  font-size: 0.6rem !important;
  text-transform: uppercase !important;
  letter-spacing: 0.15em !important;
  border-radius: 6px !important;
  image-rendering: pixelated !important;
  border: 2px solid !important;
  height: 36px !important;
  box-shadow: 
    0 4px 12px rgba(0,0,0,0.5),
    inset 0 1px 0 rgba(255,255,255,0.2) !important;
}

.profile-save-btn {
  background: linear-gradient(135deg, #00ffcc, #00d4aa) !important;
  border-color: #00ffcc !important;
  color: #000 !important;
  box-shadow: 
    0 4px 12px rgba(0,255,204,0.4),
    0 0 20px rgba(0,255,204,0.6),
    inset 0 1px 0 rgba(255,255,255,0.4) !important;
}

.profile-cancel-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  box-shadow: 
    0 6px 16px rgba(239,68,68,0.5),
    0 0 20px rgba(239,68,68,0.4) !important;
  transform: translateY(-2px) !important;
}

.profile-save-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #00d4aa, #00ffcc) !important;
  box-shadow: 
    0 6px 16px rgba(0,212,170,0.5),
    0 0 25px rgba(0,255,204,0.7) !important;
  transform: translateY(-2px) !important;
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
    gap: 12px;
    padding: 14px;
  }

  .header-stat-bar {
    flex: 1 1 calc(48% - 8px);
    min-width: 80px;
    padding: 10px 12px;
  }

  /* Ensure buttons stay horizontal on tablet */
  .header-actions {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
}

/* Desktop: Force horizontal buttons */
@media (min-width: 961px) {
  .header-actions {
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 8px;
  }

  .header-actions .v-btn {
    min-width: 80px;
    padding-left: 14px;
    padding-right: 14px;
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
    margin-bottom: 16px;
  }

  .character-panel {
    padding: 16px;
    border-radius: 12px;
  }

  .character-card-enhanced {
    flex-direction: column;
    align-items: center;
    gap: 20px;
    text-align: center;
    padding: 20px;
  }

  .character-card-left,
  .character-card-right {
    width: 100%;
  }

  .character-card-left {
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
  }

  /* Stack buttons vertically on mobile */
  .header-actions {
    flex-direction: column;
    gap: 6px;
    width: 100%;
  }

  .header-actions .v-btn {
    width: 100%;
  }

  .avatar-actions {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
  }

  .avatar-actions .retro-btn {
    flex: 0 1 auto;
  }

  .character-header-row {
    align-items: center;
  }

  .character-meta {
    justify-content: center;
  }

  .character-avatar {
    width: 80px;
    height: 80px;
  }

  .character-meta {
    justify-content: center;
    gap: 10px;
  }

  .character-meta .meta-badge,
  .character-meta .profession-badge,
  .character-meta .relationship-badge-header,
  .character-meta .day-counter {
    font-size: 0.5rem !important;
    padding: 4px 8px !important;
  }

  .header-stats {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px;
  }

  .header-stat-bar {
    flex: 1 1 calc(50% - 6px);
    min-width: 0;
    max-width: none;
    padding: 10px 12px;
  }

  .header-stat-header {
    gap: 4px;
    margin-bottom: 4px;
  }

  .header-stat-name {
    font-size: 0.45rem !important;
  }

  .header-stat-value {
    font-size: 0.6rem;
  }

  .header-stat-track {
    height: 8px;
  }

  .character-name {
    font-size: 1rem !important;
    text-align: center;
  }

  .character-actions {
    width: 100%;
    flex-wrap: wrap;
    justify-content: center;
    gap: 4px;
  }

  .character-actions .v-btn {
    flex: 1 1 45%;
    font-size: 0.4rem !important;
    padding: 4px 6px !important;
    min-height: 26px !important;
  }

  .state-strip,
  .path-progress-strip {
    flex-wrap: wrap;
    justify-content: center;
  }

  .state-pill {
    font-size: 0.5rem;
    padding: 2px 6px;
  }

  .health-status-badge {
    font-size: 0.5rem;
    padding: 4px 8px;
  }
}

/* Additional game content responsive */
@media (max-width: 640px) {

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

/* ENHANCED RETRO PIXEL SUICIDE BUTTON */
.suicide-btn {
  background: 
    linear-gradient(135deg, #ff0000, #cc0000, #990000),
    repeating-linear-gradient(
      45deg,
      transparent,
      transparent 4px,
      rgba(255,255,255,0.1) 4px,
      rgba(255,255,255,0.1) 8px
    ) !important;
  color: #ffffff !important;
  font-weight: 900 !important;
  font-size: 1.1rem !important;
  text-transform: uppercase !important;
  letter-spacing: 0.12em !important;
  font-family: 'Press Start 2P', monospace !important;
  border-radius: 8px !important;
  border: 3px solid #ff3333 !important;
  box-shadow: 
    0 12px 35px rgba(255,0,0,0.5),
    0 0 50px rgba(255,0,0,0.4),
    inset 0 1px 0 rgba(255,255,255,0.3),
    0 0 0 1px rgba(255,255,255,0.2) !important;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
  image-rendering: pixelated !important;
  text-shadow: 
    2px 2px 0 #000,
    0 0 15px #ff0000 !important;
}

/* 5-CARD ANIMATION STYLES */
.card-animation-overlay {
  background: radial-gradient(circle at center, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.95) 100%) !important;
  backdrop-filter: blur(12px);
}

.animation-container {
  max-width: 800px;
  padding: 40px 20px;
}

.animation-title {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 1.4rem !important;
  color: #00ffcc !important;
  margin-bottom: 40px !important;
  text-shadow: 0 0 20px #00ffcc !important;
  animation: titleGlow 2s ease-in-out infinite alternate;
}

@keyframes titleGlow {
  from { text-shadow: 0 0 20px #00ffcc, 0 0 30px #00ffcc; }
  to { text-shadow: 0 0 30px #00ffcc, 0 0 40px #00ffcc, 0 0 50px #8b5cf6; }
}

.cards-arc {
  display: flex;
  justify-content: center;
  align-items: flex-end;
  gap: 30px;
  margin-bottom: 40px;
  perspective: 1200px;
  height: 320px;
}

.animation-card {
  width: 160px;
  height: 220px;
  border-radius: 16px;
  position: relative;
  transform-style: preserve-3d;
  cursor: pointer;
  transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 0 15px 40px rgba(0,0,0,0.6);
  image-rendering: pixelated;
  font-family: 'VT323', monospace;
}

.animation-card.pos-0 { transform: rotateY(-35deg) translateX(-180px) translateZ(-150px); }
.animation-card.pos-1 { transform: rotateY(-18deg) translateX(-90px) translateZ(-80px); }
.animation-card.pos-2 { transform: rotateY(0deg) translateZ(0); }
.animation-card.pos-3 { transform: rotateY(18deg) translateX(90px) translateZ(-80px); }
.animation-card.pos-4 { transform: rotateY(35deg) translateX(180px) translateZ(-150px); }

.phase-popup .animation-card {
  animation: cardPopup 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
  animation-delay: var(--card-delay);
  opacity: 0;
  transform: translateY(100px) scale(0.8) rotateX(90deg);
}

@keyframes cardPopup {
  to {
    opacity: 1;
    transform: translateY(0) scale(1) rotateX(0deg);
  }
}

.phase-show .animation-card {
  animation: cardGlow 0.5s ease-out 0.2s forwards;
}

@keyframes cardGlow {
  0% { box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
  100% { box-shadow: 0 20px 50px rgba(0,255,204,0.6), 0 0 40px rgba(0,255,204,0.4); }
}

.phase-flipback .animation-card {
  animation: cardFlip 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
  animation-delay: calc(var(--card-delay, 0s) * 0.5);
}

@keyframes cardFlip {
  0% { transform: rotateY(0deg); }
  50% { transform: rotateY(90deg) scale(0.95); }
  100% { transform: rotateY(180deg); }
}

.phase-shuffle .animation-card {
  animation: riffleShuffle 3s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

@keyframes riffleShuffle {
  0% { transform: rotateY(180deg); }
  /* Pass 1: Quarter cut left */
  8% { transform: rotateY(180deg) translateX(-35px) translateY(-5px) rotateZ(3deg); }
  16% { transform: rotateY(180deg) translateX(0) translateY(8px) rotateZ(-2deg) scale(0.98); }
  /* Perfect riffle interleave */
  24% { transform: rotateY(180deg) translateX(20px) skewY(1deg) rotateZ(-5deg); }
  32% { transform: rotateY(180deg) translateX(-10px) translateY(-12px) rotateZ(8deg) skewY(-0.5deg); }
  /* Pass 2: Quarter cut right */
  40% { transform: rotateY(180deg) translateX(40px) rotateZ(-4deg) scale(1.02); }
  48% { transform: rotateY(180deg) translateX(-20px) translateY(10px) rotateZ(6deg); }
  /* Interleave 2 */
  56% { transform: rotateY(180deg) skewY(0.8deg) translateY(-6px) rotateZ(-7deg); }
  64% { transform: rotateY(180deg) translateX(15px) rotateZ(9deg) scale(0.97); }
  /* Pass 3: Final riffle */
  72% { transform: rotateY(180deg) translateX(-28px) translateY(15px) rotateZ(2deg); }
  80% { transform: rotateY(180deg) translateX(25px) rotateZ(-9deg) skewY(-0.3deg); }
  /* Settle */
  88% { transform: rotateY(180deg) translateY(-3px) rotateZ(4deg) scale(1.005); }
  95%, 100% { transform: rotateY(180deg); }
}

/* Choose phase - Interactive */
.phase-choose .animation-card {
  cursor: pointer !important;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Fixed neat row for choose */
.phase-choose .pos-0 { transform: translateX(-120px) rotateY(180deg) translateZ(-20px); }
.phase-choose .pos-1 { transform: translateX(-60px) rotateY(180deg) translateZ(-10px); }
.phase-choose .pos-2 { transform: rotateY(180deg) translateZ(0); }
.phase-choose .pos-3 { transform: translateX(60px) rotateY(180deg) translateZ(-10px); }
.phase-choose .pos-4 { transform: translateX(120px) rotateY(180deg) translateZ(-20px); }

.phase-choose .animation-card:hover {
  transform: translateY(-12px) scale(1.08) rotateY(180deg) !important;
  box-shadow: 
    0 25px 60px rgba(0,255,204,0.5),
    0 0 50px rgba(0,255,204,0.7),
    inset 0 1px 0 rgba(255,255,255,0.3);
}

.phase-choose .animation-card:hover .card-back {
  background: linear-gradient(145deg, #3a2a3a, #2a1a2a);
  box-shadow: inset 0 0 20px rgba(0,255,204,0.3);
  animation: cardPulse 1.5s infinite;
}

@keyframes cardPulse {
  0%, 100% { box-shadow: inset 0 0 20px rgba(0,255,204,0.3); }
  50% { box-shadow: inset 0 0 40px rgba(0,255,204,0.6), 0 0 30px rgba(0,255,204,0.4); }
}

.winner {
  animation: winnerGlow 2s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite alternate !important;
}

@keyframes winnerGlow {
  0% { 
    box-shadow: 0 20px 60px rgba(0,255,204,0.6), 0 0 40px rgba(0,255,204,0.4); 
    transform: scale(1);
  }
  100% { 
    box-shadow: 0 35px 90px rgba(0,255,204,1), 0 0 70px rgba(0,255,204,0.8); 
    transform: scale(1.12);
  }
}

.phase-reveal .animation-card {
  transition: none;
}

.phase-reveal .animation-card:not(.winner) {
  animation: cardsFadeOut 1s ease-out forwards;
}

@keyframes cardsFadeOut {
  to { opacity: 0; transform: scale(0.8) translateY(-50px); }
}

.phase-reveal .winner {
  animation: winnerReveal 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

@keyframes winnerReveal {
  0% { transform: rotateY(180deg) scale(1); }
  50% { transform: rotateY(90deg) scale(1.2); box-shadow: 0 30px 80px rgba(0,255,204,0.8); }
  100% { transform: rotateY(0deg) scale(var(--card-scale)) translateZ(50px); box-shadow: 0 40px 100px rgba(0,255,204,1); }
}

.card-back {
  background: linear-gradient(145deg, #2a1a2a, #1a0f1a);
  border: 2px solid #444;
  border-radius: 10px;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Press Start 2P', monospace;
  font-size: 3rem;
  color: #888;
  text-shadow: 2px 2px 0 #000;
  backdrop-filter: blur(4px);
}

.card-preview {
  height: 100%;
  border-radius: 10px;
  overflow: hidden;
  background: linear-gradient(160deg, rgba(20,15,30,0.95), rgba(30,25,45,0.98));
  border: 2px solid rgba(255,255,255,0.25);
  display: flex;
  flex-direction: column;
  box-shadow: 0 8px 25px rgba(0,0,0,0.6);
}

.card-visual-mini {
  height: 70%;
  position: relative;
  overflow: hidden;
}

.card-img-mini {
  height: 100%;
  width: 100%;
  background-size: cover;
  background-position: center;
  filter: brightness(0.9);
}

.card-title-mini {
  padding: 6px 8px;
  font-family: 'Press Start 2P', monospace;
  font-size: 0.4rem;
  color: #fff;
  text-align: center;
  height: 30%;
  display: flex;
  align-items: center;
  justify-content: center;
  text-shadow: 1px 1px 0 #000;
  letter-spacing: 0.05em;
}

.phase-indicator {
  font-family: 'VT323', monospace;
  font-size: 1.4rem;
  color: #00ffcc;
  text-shadow: 0 0 15px #00ffcc;
  margin-top: 20px;
  opacity: 0.9;
}

@media (max-width: 768px) {
  .cards-arc {
    gap: 8px;
    height: 220px;
    perspective: 800px;
  }
  
  .animation-card {
    width: 85px;
    height: 120px;
    font-size: 0.9rem;
  }
  
  .phase-choose .animation-card.pos-0 { transform: translateX(-100px) rotateY(180deg) translateZ(-10px); }
  .phase-choose .animation-card.pos-1 { transform: translateX(-50px) rotateY(180deg) translateZ(-5px); }
  .phase-choose .animation-card.pos-2 { transform: rotateY(180deg) translateZ(0); }
  .phase-choose .animation-card.pos-3 { transform: translateX(50px) rotateY(180deg) translateZ(-5px); }
  .phase-choose .animation-card.pos-4 { transform: translateX(100px) rotateY(180deg) translateZ(-10px); }
  
  .animation-card.pos-0 { transform: rotateY(-25deg) translateX(-70px) translateZ(-50px); }
  .animation-card.pos-1 { transform: rotateY(-12deg) translateX(-35px) translateZ(-25px); }
  .animation-card.pos-2 { transform: rotateY(0deg) translateZ(0); }
  .animation-card.pos-3 { transform: rotateY(12deg) translateX(35px) translateZ(-25px); }
  .animation-card.pos-4 { transform: rotateY(25deg) translateX(70px) translateZ(-50px); }
  
  .card-back { font-size: 2.2rem !important; }
  .card-title-mini { font-size: 0.35rem; }
}

/* RETRO DEATH DIALOG - SUPER PIXEL GAMING */
.suicide-dialog-content::deep(.v-overlay__content) {
  animation: death-entrance 0.8s cubic-bezier(0.36, 0, 0.66, -0.56);
}

@keyframes death-entrance {
  0% { 
    opacity: 0; 
    transform: scale(0.7) rotate(-5deg); 
  }
  50% { transform: scale(1.05) rotate(2deg); }
  100% { opacity: 1; transform: scale(1) rotate(0); }
}

.death-glow {
  position: absolute;
  inset: -50px;
  background: 
    radial-gradient(ellipse at 30% 20%, rgba(255,50,50,0.3) 0%, transparent 50%),
    radial-gradient(ellipse at 70% 70%, rgba(200,0,0,0.2) 0%, transparent 60%),
    radial-gradient(circle at center, rgba(255,0,0,0.15) 0%, transparent 70%);
  filter: blur(40px);
  z-index: 0;
  pointer-events: none;
  animation: death-glow-pulse 3s ease-in-out infinite;
}

@keyframes death-glow-pulse {
  0%, 100% { opacity: 0.6; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.1); }
}

.death-scanlines {
  position: absolute;
  inset: 0;
  background: 
    repeating-linear-gradient(0deg, rgba(255,0,0,0.08), rgba(255,0,0,0.08) 1px, transparent 1px, transparent 2px),
    repeating-linear-gradient(90deg, rgba(139,0,0,0.06), rgba(139,0,0,0.06) 2px, transparent 2px, transparent 4px);
  z-index: 1;
  pointer-events: none;
  mix-blend-mode: overlay;
}

.death-vignette {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at center, transparent 40%, rgba(0,0,0,0.85) 90%);
  z-index: 1;
  pointer-events: none;
}

.retro-pixel-death {
  background: 
    linear-gradient(170deg, #2a0a0a 0%, #1a0505 50%, #100303 100%),
    #111;
  border: 4px solid #ff0000 !important;
  box-shadow: 
    inset 0 0 0 2px rgba(255,255,255,0.1),
    0 0 0 2px #ff0000,
    0 40px 120px rgba(255,0,0,0.4),
    0 0 80px rgba(255,0,0,0.3),
    inset 0 0 40px rgba(0,0,0,0.8);
  font-family: 'Press Start 2P', monospace !important;
  text-rendering: optimizeSpeed;
  image-rendering: pixelated;
  position: relative;
  overflow: hidden;
}

.retro-pixel-death::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: 
    repeating-linear-gradient(90deg, transparent 0, transparent 3px, rgba(255,50,50,0.1) 3px, rgba(255,50,50,0.1) 6px),
    repeating-linear-gradient(0deg, transparent 0, transparent 4px, rgba(139,0,0,0.08) 4px, rgba(139,0,0,0.08) 8px);
  pointer-events: none;
  z-index: 0;
}

.glitch-death {
  animation: death-glitch 4s infinite;
  color: #ff4444 !important;
  text-shadow: 
    0 0 30px #ff0000,
    4px 0 0 #000, -4px 0 0 #ff0000,
    0 4px 0 #000, 0 -4px 0 #ff0000,
    2px 2px 0 rgba(255,0,0,0.5) !important;
}

@keyframes death-glitch {
  0%, 90%, 100% { transform: none; color: #ff4444; }
  20% { transform: skewX(-12deg); color: #ff6666; }
  40% { transform: skewX(12deg); color: #cc0000; }
  60% { transform: skewX(-8deg); color: #ff0000; }
  80% { transform: skewX(8deg); color: #990000; }
}

.death-subtitle {
  color: #ff8888 !important;
  font-size: clamp(0.95rem, 2.5vw, 1.3rem) !important;
  font-weight: 700 !important;
  letter-spacing: 0.2em !important;
  text-shadow: 0 0 20px rgba(255,100,100,0.8);
  animation: subtitle-shake 0.5s infinite;
}

@keyframes subtitle-shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px); }
  75% { transform: translateX(2px); }
}

.death-methods-container {
  padding: 0 !important;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.methods-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
  padding: 40px 35px;
  max-height: 320px;
  overflow-y: auto;
}

.death-method-card {
  background: linear-gradient(145deg, #3a1a1a, #2a0f0f);
  border: 3px solid #ff3333;
  border-radius: 12px;
  padding: 25px 20px;
  text-align: center;
  position: relative;
  animation: method-pop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
  animation-delay: var(--method-delay);
  opacity: 0;
  transform: scale(0.8) translateY(30px);
  box-shadow: 
    0 12px 35px rgba(255,0,0,0.3),
    inset 0 1px 0 rgba(255,255,255,0.15),
    0 0 25px rgba(255,50,50,0.2);
  transition: all 0.3s ease;
  font-family: 'Press Start 2P', monospace !important;
  image-rendering: pixelated;
}

.death-method-card:hover {
  transform: scale(1.05) !important;
  border-color: #ff6666 !important;
  box-shadow: 
    0 20px 50px rgba(255,0,0,0.5),
    0 0 40px rgba(255,0,0,0.4),
    inset 0 1px 0 rgba(255,255,255,0.3);
}

@keyframes method-pop {
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.method-icon {
  font-size: 4rem;
  margin-bottom: 12px;
  filter: drop-shadow(0 0 20px rgba(255,0,0,0.8));
  animation: skull-glow 2s ease-in-out infinite alternate;
}

@keyframes skull-glow {
  from { filter: drop-shadow(0 0 15px rgba(255,0,0,0.6)); }
  to { filter: drop-shadow(0 0 30px rgba(255,0,0,1)); }
}

.method-name {
  font-size: clamp(1rem, 3vw, 1.4rem) !important;
  font-weight: 900 !important;
  color: #ffddcc !important;
  margin-bottom: 12px;
  letter-spacing: 0.15em !important;
  text-shadow: 
    3px 3px 0 #000,
    0 0 20px rgba(255,100,100,0.8) !important;
  line-height: 1.1;
}

.method-prob {
  background: linear-gradient(135deg, #ff3333, #cc0000);
  color: #000 !important;
  font-weight: 900 !important;
  font-size: 1.1rem !important;
  padding: 10px 20px;
  border-radius: 8px;
  text-shadow: none !important;
  box-shadow: 
    0 6px 20px rgba(255,0,0,0.4),
    inset 0 1px 0 rgba(255,255,255,0.3);
  image-rendering: pixelated;
}

.death-warning {
  background: rgba(139,0,0,0.4);
  border: 2px solid #ff5555;
  border-radius: 12px;
  text-align: center;
  margin: 0 25px;
}

.warning-text {
  color: #ffaaaa !important;
  font-size: 1.4rem !important;
  font-weight: 700 !important;
  letter-spacing: 0.2em !important;
  text-shadow: 0 0 15px rgba(255,100,100,0.9);
  animation: warning-flicker 1.5s infinite;
}

@keyframes warning-flicker {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.death-actions {
  background: linear-gradient(180deg, rgba(20,5,5,0.9), rgba(10,0,0,0.95));
  border-top: 3px solid #ff0000;
  padding-top: 25px !important;
  gap: 20px !important;
}

.death-confirm-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-weight: 900 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.2em !important;
  border-radius: 8px !important;
  image-rendering: pixelated !important;
  border: 4px solid #ff0000 !important;
  box-shadow: 
    0 15px 45px rgba(255,0,0,0.6),
    0 0 60px rgba(255,0,0,0.5),
    inset 0 1px 0 rgba(255,255,255,0.3) !important;
  background: 
    linear-gradient(145deg, #ff3333, #cc0000, #990000),
    repeating-linear-gradient(45deg, rgba(255,255,255,0.15) 0px, rgba(255,255,255,0.15) 2px, transparent 2px, transparent 4px) !important;
  color: #ffffff !important;
  text-shadow: 
    4px 4px 0 #000,
    0 0 25px #ff0000 !important;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
}

.death-confirm-btn:hover:not(:disabled) {
  transform: translateY(-8px) scale(1.08) !important;
  box-shadow: 
    0 30px 80px rgba(255,0,0,0.8),
    0 0 80px rgba(255,0,0,0.7),
    inset 0 1px 0 rgba(255,255,255,0.4) !important;
  filter: brightness(1.2) drop-shadow(0 0 30px #ff0000);
}

.death-confirm-btn:active:not(:disabled) {
  transform: translateY(-3px) scale(1.03) !important;
}

.pulse-danger {
  animation: danger-pulse 1.2s infinite;
}

@keyframes danger-pulse {
  0%, 100% { 
    box-shadow: 
      0 15px 45px rgba(255,0,0,0.6),
      0 0 60px rgba(255,0,0,0.5); 
  }
  50% { 
    box-shadow: 
      0 25px 65px rgba(255,0,0,0.9),
      0 0 90px rgba(255,0,0,0.8); 
  }
}

.live-on-btn {
  font-family: 'VT323', monospace !important;
  font-size: 1.1rem !important;
  background: linear-gradient(135deg, rgba(100,100,100,0.8), rgba(60,60,60,0.9)) !important;
  border: 2px solid #666 !important;
  color: #ddd !important;
  text-shadow: 2px 2px 0 #000 !important;
}

.live-on-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, rgba(120,120,120,0.9), rgba(80,80,80,0.95)) !important;
  transform: translateY(-4px) !important;
}

/* ENHANCED RETRO PIXEL DEATH SCROLLBAR */
.death-methods-container {
  scrollbar-width: thin;
  scrollbar-color: linear-gradient(#ff0000, #cc0000) #1a0505;
}

.death-methods-container::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.death-methods-container::-webkit-scrollbar-track {
  background: linear-gradient(180deg, #1a0505, #100303);
  border-radius: 4px;
  border: 1px solid #330000;
  box-shadow: inset 0 0 6px rgba(0,0,0,0.8);
}

.death-methods-container::-webkit-scrollbar-thumb {
  background: linear-gradient(145deg, #ff3333, #cc0000, #990000);
  border-radius: 4px;
  border: 1px solid #ff5555;
  box-shadow: 
    0 0 8px rgba(255,0,0,0.6),
    inset 0 1px 0 rgba(255,255,255,0.2),
    inset 0 -1px 0 rgba(0,0,0,0.5);
  image-rendering: pixelated;
  min-height: 20px;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.death-methods-container::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(145deg, #ff6666, #ff0000, #cc0000);
  transform: scale(1.2);
  box-shadow: 
    0 0 16px rgba(255,0,0,0.9),
    0 4px 12px rgba(255,0,0,0.4),
    inset 0 1px 0 rgba(255,255,255,0.3);
  animation: scrollbar-shake 0.6s cubic-bezier(0.36, 0, 0.66, -0.56);
}

.death-methods-container::-webkit-scrollbar-thumb:active {
  background: linear-gradient(145deg, #ff0000, #990000);
  transform: scale(1.1);
  box-shadow: 
    0 2px 8px rgba(255,0,0,0.7),
    inset 0 2px 4px rgba(0,0,0,0.4);
}

@keyframes scrollbar-shake {
  0%, 100% { transform: translateX(0) scale(1.2); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-1px) scale(1.2); }
  20%, 40%, 60%, 80% { transform: translateX(1px) scale(1.2); }
}

/* Mobile/touch refinement */
@media (hover: none), (pointer: coarse) {
  .death-methods-container::-webkit-scrollbar-thumb:hover {
    transform: none;
    animation: none;
  }
}

@media (max-width: 600px) {
  .methods-grid {
    grid-template-columns: 1fr;
    gap: 18px;
    padding: 25px 20px;
    max-height: 300px;
  }
  
  .death-method-card {
    padding: 30px 25px;
    min-height: 160px;
  }
  
  .death-actions {
    flex-direction: column-reverse !important;
    gap: 20px !important;
    padding: 30px 20px 35px !important;
  }
  
  .death-confirm-btn {
    order: 2;
    min-width: 100% !important;
    padding: 25px 40px !important;
    font-size: 1.6rem !important;
  }
  
  .live-on-btn {
    order: 1;
    min-height: 56px;
  }
  
  .retro-pixel-death {
    margin: 12px !important;
    min-height: 520px;
  }
}

/* ============================================
   RETRO PIXEL HOW TO PLAY DIALOG STYLES
   ============================================ */

.htp-glow {
  position: absolute;
  inset: -50px;
  background: 
    radial-gradient(ellipse at 30% 20%, rgba(255,200,50,0.25) 0%, transparent 50%),
    radial-gradient(ellipse at 70% 70%, rgba(255,150,0,0.2) 0%, transparent 60%),
    radial-gradient(circle at center, rgba(255,180,0,0.15) 0%, transparent 70%);
  filter: blur(40px);
  z-index: 0;
  pointer-events: none;
  animation: htp-glow-pulse 3s ease-in-out infinite;
}

@keyframes htp-glow-pulse {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.05); }
}

.htp-scanlines {
  position: absolute;
  inset: 0;
  background: 
    repeating-linear-gradient(0deg, rgba(255,180,0,0.06), rgba(255,180,0,0.06) 1px, transparent 1px, transparent 2px),
    repeating-linear-gradient(90deg, rgba(200,150,0,0.04), rgba(200,150,0,0.04) 2px, transparent 2px, transparent 4px);
  z-index: 1;
  pointer-events: none;
  mix-blend-mode: overlay;
}

.htp-vignette {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at center, transparent 40%, rgba(0,0,0,0.6) 85%);
  z-index: 1;
  pointer-events: none;
}

.retro-pixel-howtoplay {
  background: 
    linear-gradient(170deg, rgba(30, 20, 50, 0.98) 0%, rgba(15, 10, 30, 0.98) 50%, rgba(10, 8, 20, 0.98) 100%),
    #111;
  border: 4px solid rgba(168, 85, 247, 0.6) !important;
  box-shadow: 
    inset 0 0 0 2px rgba(255,255,255,0.1),
    0 0 0 2px rgba(168, 85, 247, 0.6),
    0 40px 120px rgba(168, 85, 247, 0.35),
    0 0 80px rgba(0, 255, 204, 0.25),
    inset 0 0 40px rgba(0,0,0,0.8);
  font-family: 'Press Start 2P', monospace !important;
  text-rendering: optimizeSpeed;
  image-rendering: pixelated;
  position: relative;
  overflow: hidden;
}

.htp-title {
  position: relative;
  z-index: 2;
  text-align: center;
  justify-content: center;
  padding: 20px 16px 10px !important;
  background: linear-gradient(180deg, rgba(168, 85, 247, 0.15), transparent);
  color: #a855f7 !important;
  text-shadow: 
    0 0 20px rgba(168, 85, 247, 0.8),
    3px 0 0 #000, -3px 0 0 rgba(168, 85, 247, 0.5),
    0 3px 0 #000, 0 -3px 0 rgba(168, 85, 247, 0.5),
    2px 2px 0 rgba(168, 85, 247, 0.5) !important;
  animation: htp-title-glow 2s ease-in-out infinite alternate;
}

@keyframes htp-title-glow {
  from { text-shadow: 0 0 20px rgba(168, 85, 247, 0.8), 3px 0 0 #000, -3px 0 0 rgba(168, 85, 247, 0.5), 0 3px 0 #000, 0 -3px 0 rgba(168, 85, 247, 0.5); }
  to { text-shadow: 0 0 40px rgba(192, 132, 252, 1), 3px 0 0 #000, -3px 0 0 rgba(168, 85, 247, 0.8), 0 3px 0 #000, 0 -3px 0 rgba(168, 85, 247, 0.8), 0 0 60px rgba(168, 85, 247, 0.5); }
}

@keyframes htp-bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.htp-subtitle {
  position: relative;
  z-index: 2;
  font-size: 0.75rem !important;
  color: #ffcc66 !important;
  text-shadow: 2px 2px 0 #000;
  letter-spacing: 2px;
  background: transparent !important;
}

.htp-steps-container {
  position: relative;
  z-index: 2;
  padding: 10px 20px 15px !important;
}

.steps-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: 15px;
}

.step-card {
  background: linear-gradient(145deg, rgba(30,25,10,0.9), rgba(20,15,5,0.95));
  border: 3px solid #ffaa00;
  border-radius: 0;
  padding: 15px 12px;
  text-align: center;
  position: relative;
  overflow: hidden;
  animation: step-card-appear 0.6s ease-out backwards;
  animation-delay: var(--step-delay, 0s);
  box-shadow: 
    inset 0 0 20px rgba(255,170,0,0.1),
    0 4px 15px rgba(0,0,0,0.5),
    0 0 0 1px rgba(255,170,0,0.3);
  transition: all 0.3s ease;
}

.step-card:hover {
  transform: translateY(-5px);
  border-color: #ffcc00;
  box-shadow: 
    inset 0 0 30px rgba(255,200,0,0.15),
    0 8px 25px rgba(0,0,0,0.6),
    0 0 20px rgba(255,170,0,0.3),
    0 0 0 2px rgba(255,200,0,0.5);
}

@keyframes step-card-appear {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.step-number {
  position: absolute;
  top: 5px;
  left: 8px;
  font-size: 0.65rem;
  color: #ffaa00;
  text-shadow: 1px 1px 0 #000;
}

.step-icon {
  font-size: 2rem;
  margin: 8px 0;
  animation: icon-bounce 2s infinite;
  animation-delay: var(--step-delay, 0s);
}

@keyframes icon-bounce {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.step-title {
  font-size: 0.6rem;
  color: #ffcc00;
  margin: 8px 0 5px;
  text-shadow: 2px 2px 0 #000;
  line-height: 1.4;
}

.step-desc {
  font-size: 0.5rem;
  color: #ccbb99;
  line-height: 1.5;
  text-shadow: 1px 1px 0 #000;
}

.htp-tip {
  background: linear-gradient(90deg, rgba(255,180,0,0.1), rgba(255,150,0,0.15), rgba(255,180,0,0.1));
  border: 2px solid #ffaa00;
  border-radius: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 12px 20px;
  box-shadow: 
    inset 0 0 15px rgba(255,170,0,0.1),
    0 0 15px rgba(255,170,0,0.2);
}

.tip-icon {
  font-size: 1.5rem;
  animation: tip-shine 2s infinite;
}

@keyframes tip-shine {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.1); }
}

.tip-text {
  font-size: 0.6rem;
  color: #ffdd66;
  text-shadow: 1px 1px 0 #000;
  line-height: 1.4;
}

.htp-actions {
  position: relative;
  z-index: 2;
  background: linear-gradient(180deg, rgba(20,15,5,0.9), rgba(10,5,0,0.95));
  padding: 20px 30px 25px;
  display: flex;
  justify-content: center;
}

.htp-start-btn {
  font-family: 'Press Start 2P', monospace !important;
  text-rendering: optimizeSpeed;
  background: linear-gradient(180deg, #ffcc00 0%, #ff9900 50%, #ff7700 100%) !important;
  color: #1a0a00 !important;
  border: 3px solid #ffdd00 !important;
  box-shadow: 
    0 6px 0 #aa5500,
    0 8px 20px rgba(255,170,0,0.4),
    inset 0 2px 0 rgba(255,255,255,0.4),
    inset 0 -2px 0 rgba(0,0,0,0.2) !important;
  transition: all 0.15s ease !important;
  animation: btn-pulse 2s infinite;
}

@keyframes btn-pulse {
  0%, 100% { box-shadow: 0 6px 0 #aa5500, 0 8px 20px rgba(255,170,0,0.4), inset 0 2px 0 rgba(255,255,255,0.4), inset 0 -2px 0 rgba(0,0,0,0.2); }
  50% { box-shadow: 0 6px 0 #aa5500, 0 12px 30px rgba(255,170,0,0.6), inset 0 2px 0 rgba(255,255,255,0.4), inset 0 -2px 0 rgba(0,0,0,0.2); }
}

.htp-start-btn:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 
    0 9px 0 #aa5500,
    0 15px 35px rgba(255,170,0,0.5),
    inset 0 2px 0 rgba(255,255,255,0.5),
    inset 0 -2px 0 rgba(0,0,0,0.2) !important;
}

.htp-start-btn:active:not(:disabled) {
  transform: translateY(3px);
  box-shadow: 
    0 3px 0 #aa5500,
    0 5px 15px rgba(255,170,0,0.3),
    inset 0 2px 0 rgba(255,255,255,0.3),
    inset 0 2px 3px rgba(0,0,0,0.3) !important;
}

/* How to Play Dialog Content */
.howtoplay-dialog-content {
  background: transparent !important;
  box-shadow: none !important;
}

/* Story Recap Dialog */
.story-dialog-content {
  background: transparent !important;
  box-shadow: none !important;
}

.story-recap-title {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: bold;
  letter-spacing: 2px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.path-progress-grid {
  display: grid;
  gap: 16px;
}

.path-progress-card {
  background: #1e1e2e;
  border-radius: 12px;
  padding: 16px;
  border: 2px solid #333;
  transition: all 0.3s ease;
}

.path-progress-card.active-path {
  border-color: #42a5f5;
  background: linear-gradient(135deg, #1e1e2e 0%, #263238 100%);
}

.path-progress-card.current-path {
  border-color: #ffa726;
  background: linear-gradient(135deg, #1e1e2e 0%, #3e2723 100%);
  box-shadow: 0 0 15px rgba(255, 167, 38, 0.3);
}

.path-header {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}

.path-icon {
  font-size: 1.8rem;
  margin-right: 10px;
}

.path-label {
  font-size: 1.1rem;
  font-weight: 600;
  color: #e0e0e0;
}

.path-progress-bar-container {
  background: #333;
  border-radius: 8px;
  height: 12px;
  overflow: hidden;
  margin-bottom: 8px;
}

.path-progress-bar {
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  height: 100%;
  border-radius: 8px;
  transition: width 0.5s ease;
}

.path-progress-card.active-path .path-progress-bar {
  background: linear-gradient(90deg, #42a5f5 0%, #1976d2 100%);
}

.path-progress-card.current-path .path-progress-bar {
  background: linear-gradient(90deg, #ffa726 0%, #f57c00 100%);
}

.path-stage-info {
  font-size: 0.9rem;
  color: #9e9e9e;
}

.path-percentage {
  color: #bdbdbd;
  font-size: 0.85rem;
}

.consequence-badges {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}

/* Mobile Responsive */
@media (max-width: 600px) {
  .steps-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  
  .step-card {
    padding: 12px 8px;
  }
  
  .step-icon {
    font-size: 1.5rem;
  }
  
  .step-title {
    font-size: 0.5rem;
  }
  
  .step-desc {
    font-size: 0.4rem;
  }
  
  .htp-tip {
    flex-direction: column;
    gap: 8px;
    padding: 10px;
  }
  
  .htp-actions {
    padding: 15px 20px 20px;
  }
  
  .htp-start-btn {
    font-size: 0.8rem !important;
    padding: 14px 30px !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .step-card, .step-icon, .tip-icon, .htp-title, .htp-start-btn,
  .death-method-card, .glitch-death, .pulse-danger {
    animation: none !important;
  }
}

.suicide-btn:hover:not(:disabled) {
  transform: translateY(-6px) scale(1.05) !important;
  box-shadow: 
    0 25px 60px rgba(255,0,0,0.6),
    0 0 60px rgba(255,0,0,0.5),
    inset 0 1px 0 rgba(255,255,255,0.4),
    0 0 0 1px rgba(255,100,100,0.8) !important;
  filter: brightness(1.1) drop-shadow(0 0 20px #ff0000);
}

.suicide-btn:active:not(:disabled) {
  transform: translateY(-2px) scale(1.02) !important;
}

.suicide-dialog-content::deep(.v-card) {
  background: linear-gradient(180deg, rgba(45, 20, 20, 0.98), rgba(20, 10, 10, 0.99)) !important;
  border: 2px solid #ef4444 !important;
  border-radius: 20px !important;
  box-shadow: 
    0 30px 90px rgba(239, 68, 68, 0.25),
    0 0 60px rgba(239, 68, 68, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
  backdrop-filter: blur(20px);
}

.suicide-title {
  color: #ef4444 !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: 1.35rem !important;
  font-weight: 800 !important;
  letter-spacing: 0.12em !important;
  text-shadow: 
    0 0 20px rgba(239, 68, 68, 0.6),
    2px 2px 0 rgba(0, 0, 0, 0.8) !important;
}

.suicide-subtitle {
  color: #fca5a5 !important;
  font-size: 1rem !important;
  opacity: 0.9;
}

.method-select :deep(.v-field__outline) {
  border-color: rgba(239, 68, 68, 0.6) !important;
}

.method-select :deep(.v-field--focused .v-field__outline) {
  border-color: #ef4444 !important;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25) !important;
}

.cancel-suicide-btn {
  font-family: 'VT323', monospace !important;
  color: #fca5a5 !important;
  border-color: rgba(252, 165, 165, 0.5) !important;
}

.confirm-suicide-btn {
  background: linear-gradient(135deg, #ef4444, #dc2626, #b91c1c) !important;
  color: #ffffff !important;
  font-family: 'Press Start 2P', monospace !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.1em !important;
  box-shadow: 
    0 8px 25px rgba(239, 68, 68, 0.45),
    0 0 30px rgba(239, 68, 68, 0.3) !important;
}

.confirm-suicide-btn:hover:not(:disabled) {
  transform: translateY(-2px) !important;
  box-shadow: 
    0 14px 40px rgba(239, 68, 68, 0.55),
    0 0 40px rgba(239, 68, 68, 0.4) !important;
}

/* Action Buttons Layout */
.action-section {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
  padding: 20px;
}

.action-buttons {
  display: flex;
  align-items: center;
  gap: 24px;
  max-width: 900px;
  width: 100%;
  justify-content: center;
}

.action-btn {
  flex: 1;
  min-width: 180px !important;
  max-width: 250px !important;
  font-weight: 600 !important;
  font-size: 0.7rem !important;
  letter-spacing: 0.3px !important;
  text-transform: uppercase !important;
  border-radius: 0px !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  padding: 0 4px !important;
  height: 52px !important;
  font-family: 'Press Start 2P', 'VT323', monospace !important;
  image-rendering: pixelated !important;
  position: relative;
  overflow: hidden;
}

/* Pixel border effect */
.action-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    0deg,
    transparent 0px,
    transparent 2px,
    rgba(255,255,255,0.02) 2px,
    rgba(255,255,255,0.02) 4px
  );
  pointer-events: none;
}

.action-btn:hover:not(:disabled) {
  transform: translateY(-3px) scale(1.02) !important;
}

.action-btn:active:not(:disabled) {
  transform: translateY(-1px) scale(1.01) !important;
}

.end-day-btn {
  background: linear-gradient(180deg, #6366f1 0%, #4f46e5 50%, #3730a3 100%) !important;
  border: 2px solid #818cf8 !important;
  font-family: 'Press Start 2P', 'VT323', monospace !important;
  text-transform: uppercase !important;
  letter-spacing: 0.1em !important;
  box-shadow: 
    0 0 15px rgba(99, 102, 241, 0.3),
    0 4px 0 #3730a3,
    0 6px 15px rgba(0,0,0,0.4) !important;
}

.end-day-btn:hover:not(:disabled) {
  background: linear-gradient(180deg, #818cf8 0%, #6366f1 50%, #4f46e5 100%) !important;
  box-shadow: 
    0 0 30px rgba(0,255,204,0.5),
    0 0 60px rgba(99, 102, 241, 0.4),
    0 6px 0 #3730a3,
    0 10px 25px rgba(0,0,0,0.5) !important;
}

.achievements-btn {
  background: linear-gradient(180deg, #f59e0b 0%, #d97706 50%, #b45309 100%) !important;
  border: 2px solid #fbbf24 !important;
  font-family: 'Press Start 2P', 'VT323', monospace !important;
  text-transform: uppercase !important;
  letter-spacing: 0.1em !important;
  box-shadow: 
    0 0 15px rgba(245, 158, 11, 0.3),
    0 4px 0 #b45309,
    0 6px 15px rgba(0,0,0,0.4) !important;
}

.achievements-btn:hover:not(:disabled) {
  background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%) !important;
  box-shadow: 
    0 0 30px rgba(251, 191, 36, 0.5),
    0 0 60px rgba(245, 158, 11, 0.4),
    0 6px 0 #b45309,
    0 10px 25px rgba(0,0,0,0.5) !important;
}

.suicide-btn {
  background: linear-gradient(180deg, #dc2626 0%, #b91c1c 50%, #7f1d1d 100%) !important;
  border: 2px solid #ef4444 !important;
  font-family: 'Press Start 2P', 'VT323', monospace !important;
  text-transform: uppercase !important;
  letter-spacing: 0.1em !important;
  box-shadow: 
    0 0 15px rgba(239, 68, 68, 0.3),
    0 4px 0 #7f1d1d,
    0 6px 15px rgba(0,0,0,0.4) !important;
}

.suicide-btn:hover:not(:disabled) {
  background: linear-gradient(180deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%) !important;
  box-shadow: 
    0 0 30px rgba(239, 68, 68, 0.6),
    0 0 60px rgba(220, 38, 38, 0.4),
    0 6px 0 #7f1d1d,
    0 10px 25px rgba(0,0,0,0.5) !important;
}

/* Enhanced Retro Pixel Suicide Dialog */
.suicide-dialog-content::deep(.v-overlay__content) {
  animation: death-shake 0.6s cubic-bezier(0.36, 0, 0.66, -0.56);
}

@keyframes death-shake {
  0%, 100% { transform: translate3d(0,0,0); }
  10%, 30%, 50%, 70%, 90% { transform: translate3d(-4px, 2px, 0); }
  20%, 40%, 60%, 80% { transform: translate3d(4px, -2px, 0); }
}

.suicide-dialog-content {
  filter: drop-shadow(0 0 40px rgba(239, 68, 68, 0.4));
}

.suicide-dialog-content::deep(.v-card) {
  background: 
    linear-gradient(180deg, rgba(45, 15, 15, 0.98), rgba(15, 5, 5, 1)),
    repeating-linear-gradient(
      0deg,
      transparent,
      transparent 2px,
      rgba(239, 68, 68, 0.03) 2px,
      rgba(239, 68, 68, 0.03) 4px
    );
  border: 3px solid #ef4444 !important;
  border-image: linear-gradient(45deg, #ef4444, transparent, #ef4444) 1 !important;
  box-shadow: 
    0 0 0 2px rgba(239, 68, 68, 0.5),
    0 30px 90px rgba(239, 68, 68, 0.3),
    0 0 80px rgba(239, 68, 68, 0.2),
    inset 0 0 20px rgba(0, 0, 0, 0.7) !important;
  font-family: 'Press Start 2P', monospace !important;
  image-rendering: pixelated;
  backdrop-filter: blur(8px) contrast(1.2);
  position: relative;
  overflow: hidden;
}

.suicide-dialog-content::before {
  content: '';
  position: absolute;
  inset: 0;
  background: 
    repeating-linear-gradient(
      90deg,
      transparent 0,
      transparent 2px,
      rgba(239, 68, 68, 0.05) 2px,
      rgba(239, 68, 68, 0.05) 4px
    ),
    repeating-linear-gradient(
      0deg,
      transparent 0,
      transparent 3px,
      rgba(0,0,0,0.1) 3px,
      rgba(0,0,0,0.1) 6px
    );
  pointer-events: none;
  z-index: 0;
}

.suicide-dialog-content::deep(.v-card > *) {
  position: relative;
  z-index: 1;
}

.suicide-title {
  color: #ff0000 !important;
  font-size: 1.1rem !important;
  line-height: 1.1 !important;
  max-width: 100% !important;
  word-break: break-word !important;
  text-align: center;
  letter-spacing: 0.15em !important;
  animation: title-glitch 2.5s infinite, skull-pulse 1.5s infinite;
  text-shadow: 
    0 0 15px #ff0000,
    2px 0 0 #000,
    -2px 0 0 #ff0000,
    0 2px 0 #000,
    0 -2px 0 #ff0000 !important;
  position: relative;
  padding: 12px 16px !important;
}

.suicide-title::before,
.suicide-title::after {
  content: '💀';
  position: absolute;
  font-size: 1.8rem;
  animation: skull-float 3s ease-in-out infinite;
}

.suicide-title::before { left: 10px; top: -10px; animation-delay: 0s; }
.suicide-title::after { right: 10px; top: -10px; animation-delay: 1.5s; }

@keyframes title-glitch {
  0%, 90%, 100% { transform: none; }
  20% { transform: skewX(-5deg); }
  40% { transform: skewX(5deg); color: #ff6666; }
  60% { transform: skewX(-3deg); color: #cc0000; }
  80% { transform: skewX(3deg); color: #ff0000; }
}

@keyframes skull-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.1); }
}

@keyframes skull-float {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-4px) rotate(5deg); }
}

.suicide-subtitle {
  color: #ff6666 !important;
  font-size: 0.85rem !important;
  text-align: center;
  font-weight: 600 !important;
  letter-spacing: 0.1em !important;
  text-shadow: 0 0 10px rgba(255, 102, 102, 0.5);
  animation: subtitle-glow 2s ease-in-out infinite alternate;
}

@keyframes subtitle-glow {
  from { text-shadow: 0 0 10px rgba(255, 102, 102, 0.5); }
  to { text-shadow: 0 0 20px rgba(255, 102, 102, 0.8), 0 0 30px rgba(255, 0, 0, 0.3); }
}

.method-select {
  max-width: 100% !important;
}

.method-select :deep(.v-field) {
  background: rgba(20, 5, 5, 0.8) !important;
  border: 2px solid rgba(239, 68, 68, 0.6) !important;
  border-radius: 8px !important;
  font-family: 'VT323', monospace !important;
  font-size: 1.1rem !important;
}

.method-select :deep(.v-field__text) {
  color: #ffcccc !important;
}

.method-select :deep(.v-icon) {
  color: #ef4444 !important;
}

.method-select :deep(.v-field--focused) {
  border-color: #ff0000 !important;
  box-shadow: 
    0 0 0 4px rgba(255, 0, 0, 0.3),
    0 0 20px rgba(255, 0, 0, 0.4) !important;
}

.method-select :deep(.v-list-item) {
  font-family: 'VT323', monospace !important;
  border-left: 4px solid transparent;
  transition: all 0.2s ease;
}

.method-select :deep(.v-list-item--active) {
  background: rgba(239, 68, 68, 0.2) !important;
  border-left-color: #ef4444 !important;
}

.confirm-suicide-btn,
.cancel-suicide-btn {
  font-family: 'Press Start 2P', 'VT323', monospace !important;
  text-transform: uppercase !important;
  letter-spacing: 0.15em !important;
  border-radius: 6px !important;
  image-rendering: pixelated;
}

.confirm-suicide-btn {
  font-size: 0.85rem !important;
  padding: 12px 28px !important;
  min-width: 140px !important;
}

/* ===== STEP 7: NEW SUICIDE CSS ENHANCEMENTS ===== */

/* Selected Method Pulsing Blood Glow */
.death-method-card.selected-method {
  border-color: #ff6666 !important;
  background: linear-gradient(145deg, #4a2020, #3a1515) !important;
  box-shadow: 
    0 0 40px rgba(255,50,50,0.8),
    0 20px 60px rgba(255,0,0,0.6),
    inset 0 1px 0 rgba(255,255,255,0.2),
    0 0 0 2px rgba(255,100,100,0.8) !important;
  animation: selected-blood-pulse 1.5s infinite, method-pop 0.6s ease-out;
  transform: scale(1.05);
}

@keyframes selected-blood-pulse {
  0%, 100% { box-shadow: 0 0 40px rgba(255,50,50,0.8), 0 20px 60px rgba(255,0,0,0.6); }
  50% { box-shadow: 0 0 60px rgba(255,0,0,1), 0 30px 80px rgba(255,0,0,0.8), 0 0 0 4px rgba(255,100,100,1); }
}

/* Disabled Confirm = Cracked Skull Glass */
.death-confirm-btn:disabled {
  background: linear-gradient(145deg, #2a0f0f, #1a0505) !important;
  border: 3px solid #660000 !important;
  opacity: 0.6;
  position: relative;
  cursor: not-allowed !important;
}

.death-confirm-btn:disabled::before {
  content: '⚠️ CHOOSE METHOD';
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 900;
  color: #ff4444;
  text-shadow: 0 0 10px #ff0000;
  z-index: 1;
  background: repeating-linear-gradient(
    45deg,
    rgba(100,0,0,0.8),
    rgba(100,0,0,0.8) 4px,
    rgba(200,0,0,0.6) 4px,
    rgba(200,0,0,0.6) 8px
  );
  animation: crack-shake 2s infinite;
}

@keyframes crack-shake {
  0%, 100% { background-position: 0 0; }
  50% { background-position: 8px 8px; }
}

/* ===== SURVIVAL POPUP RETRO STYLE ===== */
.survival-popup-content::deep(.v-overlay__content) {
  animation: survival-bounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes survival-bounce {
  0% { transform: scale(0.7) rotate(5deg); opacity: 0; }
  60% { transform: scale(1.05) rotate(-2deg); }
  100% { transform: scale(1) rotate(0); opacity: 1; }
}

.retro-pixel-survival {
  background: linear-gradient(145deg, #2a1a1a, #1a0f0f) !important;
  border: 4px solid #ffaa00 !important;
  box-shadow: 
    0 0 0 2px #ffaa00,
    0 40px 120px rgba(255,170,0,0.5),
    0 0 80px rgba(255,170,0,0.4),
    inset 0 0 40px rgba(0,0,0,0.8);
  font-family: 'Press Start 2P', monospace !important;
  image-rendering: pixelated;
  position: relative;
  overflow: hidden;
  max-height: 85vh;
}

.survival-glow,
.survival-scanlines {
  pointer-events: none !important;
}

.survival-glow {
  position: absolute;
  inset: -30px;
  background: 
    radial-gradient(ellipse, rgba(255,170,0,0.4) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(255,200,100,0.3) 0%, transparent 60%);
  filter: blur(30px);
  animation: survival-glow-pulse 2s ease-in-out infinite;
  z-index: 0;
}

.survival-scanlines {
  pointer-events: none !important;
}

@keyframes survival-glow-pulse {
  0%, 100% { opacity: 0.7; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.05); }
}

.survival-scanlines {
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    0deg,
    rgba(255,170,0,0.1),
    rgba(255,170,0,0.1) 1px,
    transparent 1px,
    transparent 3px
  );
  z-index: 1;
  mix-blend-mode: overlay;
}

.survival-title {
  color: #ffaa00 !important;
  text-align: center !important;
  font-size: clamp(1.4rem, 6vw, 2.2rem) !important;
  font-weight: 900 !important;
  letter-spacing: 0.2em !important;
  text-shadow: 
    0 0 30px #ffaa00,
    4px 0 0 #000, -4px 0 0 #ffaa00,
    0 4px 0 #000, 0 -4px 0 #ffaa00 !important;
  animation: survival-glitch 3s infinite;
}

@keyframes survival-glitch {
  0%, 90% { transform: none; }
  20% { transform: skewX(-8deg); }
  40%, 60% { transform: skewX(8deg); }
  80% { transform: skewX(-4deg); }
}

.survival-subtitle {
  color: #ffdd99 !important;
  font-size: clamp(1rem, 3vw, 1.4rem) !important;
  font-weight: 700 !important;
  text-shadow: 0 0 20px rgba(255,170,0,0.8);
}

.survival-message {
  color: #ffcc99 !important;
  font-size: 1.3rem !important;
  font-weight: 600 !important;
  line-height: 1.4 !important;
  text-shadow: 0 0 15px rgba(255,170,0,0.6);
  animation: message-fade 0.8s ease-out;
}

@keyframes message-fade {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.survival-btn {
  background: linear-gradient(135deg, #ffaa00, #ff8800, #ff6600) !important;
  border: 3px solid #ffdd99 !important;
  color: #000 !important;
  font-weight: 900 !important;
  text-shadow: none !important;
  box-shadow: 
    0 15px 45px rgba(255,170,0,0.6),
    0 0 40px rgba(255,170,0,0.5),
    inset 0 1px 0 rgba(255,255,255,0.4) !important;
  font-family: 'Press Start 2P', monospace !important;
  image-rendering: pixelated;
}

.pulse-warning {
  animation: warning-pulse 1.2s infinite;
}

@keyframes warning-pulse {
  0%, 100% { box-shadow: 0 15px 45px rgba(255,170,0,0.6); }
  50% { box-shadow: 0 25px 65px rgba(255,170,0,0.9), 0 0 60px rgba(255,170,0,0.8); }
}

.survival-actions {
  background: linear-gradient(180deg, rgba(25,15,15,0.9), rgba(15,5,5,0.95));
  border-top: 3px solid #ffaa00;
}

/* ===== HELLISH GAME OVER CSS ===== */
.gameover-hell-overlay {
  background: 
    radial-gradient(circle at 50% 20%, rgba(139,0,0,0.95) 0%, rgba(80,0,0,0.98) 40%, #000 70%),
    linear-gradient(45deg, rgba(200,0,0,0.6) 0%, transparent 50%, rgba(139,0,0,0.8) 100%);
  animation: hell-shake 0.15s infinite, hellfire-flicker 4s ease-in-out infinite;
}

@keyframes hell-shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px) translateY(1px); }
  75% { transform: translateX(2px) translateY(-1px); }
}

@keyframes hellfire-flicker {
  0%, 100% { filter: brightness(1) hue-rotate(0deg); }
  25% { filter: brightness(1.1) hue-rotate(5deg); }
  50% { filter: brightness(0.9) hue-rotate(-3deg); }
  75% { filter: brightness(1.15) hue-rotate(8deg); }
}

.gameover-hell-container {
  text-align: center;
  position: relative;
  max-width: 90vw;
  animation: container-hell-glow 3s ease-in-out infinite alternate;
}

@keyframes container-hell-glow {
  from { filter: drop-shadow(0 0 40px rgba(255,0,0,0.8)); }
  to { filter: drop-shadow(0 0 80px rgba(255,50,50,1)) hue-rotate(10deg); }
}

.death-orbit {
  position: absolute;
  inset: 0;
  font-size: clamp(3rem, 12vw, 6rem);
  filter: drop-shadow(0 0 30px rgba(255,0,0,0.9));
  animation: orbit-hell linear infinite, skull-rot 3s infinite;
  will-change: transform;
}

@keyframes orbit-hell {
  from { 
    transform: rotate(0deg) translateX(var(--orbit-radius, 180px)) rotate(0deg) scale(1); 
  }
  to { 
    transform: rotate(360deg) translateX(var(--orbit-radius, 180px)) rotate(-360deg) scale(1.1); 
  }
}

@keyframes skull-rot {
  0%, 100% { transform: rotateY(0deg) rotateZ(0deg); }
  25% { transform: rotateY(180deg) rotateZ(-90deg); }
  50% { transform: rotateY(360deg) rotateZ(-180deg); }
  75% { transform: rotateY(180deg) rotateZ(-270deg); }
}

.gameover-stamp {
  font-family: 'Press Start 2P', monospace !important;
  font-size: clamp(3rem, 18vw, 8rem) !important;
  font-weight: 900 !important;
  color: #ff0000 !important;
  letter-spacing: 0.3em !important;
  line-height: 0.8 !important;
  margin: 20px 0 !important;
  transform: rotate(-8deg) translateY(-20px);
  text-shadow: 
    0 0 60px #ff0000,
    10px 0 0 #000, -10px 0 0 #ff0000,
    0 10px 0 #000, 0 -10px 0 #ff0000,
    5px 5px 0 rgba(139,0,0,0.8),
    -5px -5px 0 rgba(200,0,0,0.7) !important;
  position: relative;
  z-index: 10;
  animation: stamp-crush 4s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite,
             hell-glitch 2.5s infinite;
}

@keyframes stamp-crush {
  0%, 90% { transform: rotate(-8deg) scale(1) translateY(-20px); }
  95% { transform: rotate(-12deg) scale(1.02) translateY(-15px); }
  100% { transform: rotate(-6deg) scale(0.98) translateY(-25px); }
}

@keyframes hell-glitch {
  0%, 85%, 100% { transform: rotate(-8deg); }
  88% { transform: rotate(-15deg) skewX(12deg); }
  92% { transform: rotate(-3deg) skewX(-8deg); }
  96% { transform: rotate(-10deg) skewX(6deg); }
}

.glitch-hell::before,
.glitch-hell::after {
  content: attr(data-text);
  position: absolute;
  inset: 0;
  color: #ff4444;
  opacity: 0.8;
  z-index: -1;
}

.glitch-hell::before {
  animation: hell-glitch-before 2.5s infinite;
  transform: translate(4px, 2px);
  clip-path: polygon(0 0, 100% 0, 100% 40%, 0 40%);
}

.glitch-hell::after {
  animation: hell-glitch-after 2.5s infinite;
  transform: translate(-4px, -2px);
  clip-path: polygon(0 60%, 100% 60%, 100% 100%, 0 100%);
}

@keyframes hell-glitch-before {
  0%, 90% { clip-path: polygon(0 0, 100% 0, 100% 40%, 0 40%); }
  92% { clip-path: polygon(0 0, 100% 0, 100% 45%, 0 45%); }
  94% { clip-path: polygon(0 0, 100% 0, 100% 35%, 0 35%); }
}

@keyframes hell-glitch-after {
  0%, 90% { clip-path: polygon(0 60%, 100% 60%, 100% 100%, 0 100%); }
  93% { clip-path: polygon(0 55%, 100% 55%, 100% 100%, 0 100%); }
  97% { clip-path: polygon(0 65%, 100% 65%, 100% 100%, 0 100%); }
}

.hell-subtitle {
  color: #ff6666 !important;
  font-family: 'Press Start 2P', monospace !important;
  font-size: clamp(1.1rem, 4vw, 1.8rem) !important;
  font-weight: 700 !important;
  letter-spacing: 0.15em !important;
  text-shadow: 
    0 0 25px rgba(255,100,100,0.9),
    3px 3px 0 #000 !important;
  margin: 30px 0 40px;
  animation: subtitle-bleed 3s ease-in-out infinite;
}

@keyframes subtitle-bleed {
  0%, 100% { filter: hue-rotate(0deg) brightness(1); }
  50% { filter: hue-rotate(15deg) brightness(1.1); text-shadow: 0 0 35px rgba(255,50,50,1); }
}

.blood-splatter,
.blood-drips-1,
.blood-drips-2 {
  position: absolute;
  pointer-events: none;
  z-index: 1;
}

.blood-splatter {
  top: 15%;
  left: 10%;
  width: 120px;
  height: 120px;
  background: radial-gradient(ellipse, rgba(139,0,0,0.9) 0%, rgba(200,0,0,0.7) 30%, transparent 70%);
  border-radius: 50%;
  filter: blur(2px);
  animation: splatter-float 6s ease-in-out infinite;
}

@keyframes splatter-float {
  0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.6; }
  33% { transform: translate(20px, -10px) scale(1.1); opacity: 0.8; }
  66% { transform: translate(-15px, 15px) scale(0.95); opacity: 0.4; }
}

.blood-drips-1 {
  top: 40%;
  right: 20%;
  width: 8px;
  height: 60px;
  background: linear-gradient(to bottom, transparent, #8b0000, #660000);
  border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
  animation: drip-fall-1 4s linear infinite;
}

.blood-drips-2 {
  bottom: 30%;
  left: 25%;
  width: 6px;
  height: 45px;
  background: linear-gradient(to bottom, transparent, #a00000, #800000);
  border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
  animation: drip-fall-2 5s linear infinite 1s;
}

@keyframes drip-fall-1 {
  0% { transform: translateY(-100px) scaleY(0.3); opacity: 0; }
  20% { opacity: 1; }
  80% { opacity: 1; }
  100% { transform: translateY(100px) scaleY(1.2); opacity: 0; }
}

@keyframes drip-fall-2 {
  0% { transform: translateY(-80px) scaleY(0.4); opacity: 0; }
  25% { opacity: 1; }
  75% { opacity: 1; }
  100% { transform: translateY(80px) scaleY(1.1); opacity: 0; }
}

.hellfire-glow {
  position: absolute;
  inset: -100px;
  background: 
    radial-gradient(ellipse at 20% 80%, rgba(255,100,0,0.6) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 40%, rgba(200,50,0,0.5) 0%, transparent 60%),
    radial-gradient(circle at center, rgba(255,0,0,0.3) 0%, transparent 70%);
  filter: blur(50px);
  animation: hellfire-pulse 3s ease-in-out infinite;
  mix-blend-mode: screen;
  z-index: 0;
}

@keyframes hellfire-pulse {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.15); }
}

.restart-hell-btn {
  font-family: 'Press Start 2P', monospace !important;
  font-weight: 900 !important;
  border: 4px solid #ff4400 !important;
  background: linear-gradient(145deg, #ff6600, #cc4400, #aa3300) !important;
  color: #fff !important;
  text-shadow: 
    3px 3px 0 #000,
    0 0 20px #ff4400 !important;
  box-shadow: 
    0 20px 60px rgba(255,100,0,0.7),
    0 0 50px rgba(255,68,0,0.6),
    inset 0 1px 0 rgba(255,255,255,0.3) !important;
  image-rendering: pixelated;
  position: relative;
  z-index: 20;
}

.pulse-hellfire {
  animation: hellfire-btn-pulse 1.8s infinite;
}

@keyframes hellfire-btn-pulse {
  0%, 100% { 
    box-shadow: 0 20px 60px rgba(255,100,0,0.7), 0 0 40px rgba(255,68,0,0.5); 
    transform: scale(1);
  }
  50% { 
    box-shadow: 0 30px 90px rgba(255,100,0,1), 0 0 70px rgba(255,68,0,0.9); 
    transform: scale(1.05);
  }
}

.restart-hell-btn:hover:not(:disabled) {
  transform: translateY(-12px) scale(1.08) !important;
  box-shadow: 
    0 40px 120px rgba(255,100,0,0.9),
    0 0 80px rgba(255,68,0,0.8) !important;
}

.selected-method .method-icon {
  animation: selected-skull-glow 1s infinite alternate !important;
  filter: drop-shadow(0 0 25px #ff6666) !important;
}

@keyframes selected-skull-glow {
  from { filter: drop-shadow(0 0 20px rgba(255,100,100,0.8)); }
  to { filter: drop-shadow(0 0 40px rgba(255,50,50,1)); }
}

/* Remove unused stat % class */
.stat-percent { display: none; }

.cancel-suicide-btn {
  font-size: 0.8rem !important;
  border-width: 2px !important;
}

/* Memory Panel Styles */
.memory-panel-enhanced {
  margin-top: 20px;
  padding: 20px;
  background: linear-gradient(135deg, rgba(15,10,25,0.95), rgba(25,20,40,0.98));
  border: 2px solid rgba(59,130,246,0.4);
  border-radius: 16px;
  box-shadow: 
    inset 0 1px 0 rgba(255,255,255,0.1),
    0 0 30px rgba(59,130,246,0.25),
    0 12px 40px rgba(0,0,0,0.6);
  font-family: 'Press Start 2P', 'VT323', monospace;
  image-rendering: pixelated;
  position: relative;
  overflow: hidden;
  max-height: 300px;
  overflow-y: auto;
}

.life-pressure-panel {
  margin-bottom: 20px;
}

/* Removed .pressure-arc */

.pressure-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.pressure-card {
  padding: 12px;
  background: rgba(7, 12, 28, 0.72);
  border: 1px solid rgba(59,130,246,0.25);
  border-radius: 10px;
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
}

.pressure-card-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  color: #67e8f9;
  font-family: 'Press Start 2P', 'VT323', monospace;
  font-size: 0.68rem;
  text-transform: uppercase;
}

.pressure-card-copy {
  color: #cbd5e1;
  font-family: 'VT323', monospace;
  font-size: 1rem;
  line-height: 1.35;
  margin: 0;
}

.state-strip {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 8px;
  padding: 8px;
  background: 
    linear-gradient(135deg, rgba(0,0,0,0.3), rgba(20,10,35,0.4));
  border-radius: 0px;
  border: 2px solid rgba(0,255,204,0.2);
  width: 100%;
  box-shadow: 
    inset 0 1px 0 rgba(255,255,255,0.1),
    0 0 10px rgba(0,255,204,0.1);
}

.state-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 0px;
  font-family: 'Press Start 2P', 'VT323', monospace;
  font-size: 0.45rem;
  line-height: 1;
  border: 2px solid;
  box-shadow: 
    0 2px 0 rgba(0,0,0,0.4),
    inset 0 1px 0 rgba(255,255,255,0.15);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.state-pill .v-icon {
  font-size: 0.7rem !important;
}

.narrative-pill {
  color: #fcd34d;
  background: linear-gradient(135deg, rgba(120, 53, 15, 0.5), rgba(180, 100, 30, 0.5));
  border-color: rgba(252, 211, 77, 0.6);
  box-shadow: 
    0 2px 0 rgba(0,0,0,0.4),
    0 0 10px rgba(252, 211, 77, 0.3),
    inset 0 1px 0 rgba(255,255,255,0.2);
}

.path-pill {
  color: #93c5fd;
  background: linear-gradient(135deg, rgba(30, 64, 175, 0.5), rgba(59, 130, 246, 0.5));
  border-color: rgba(147, 197, 253, 0.6);
  box-shadow: 
    0 2px 0 rgba(0,0,0,0.4),
    0 0 10px rgba(147, 197, 253, 0.3),
    inset 0 1px 0 rgba(255,255,255,0.2);
}

.consequence-pill {
  color: #fca5a5;
  background: linear-gradient(135deg, rgba(127, 29, 29, 0.5), rgba(185, 50, 50, 0.5));
  border-color: rgba(252, 165, 165, 0.6);
  box-shadow: 
    0 2px 0 rgba(0,0,0,0.4),
    0 0 10px rgba(252, 165, 165, 0.3),
    inset 0 1px 0 rgba(255,255,255,0.2);
}

/* Clickable indicator for consequences - opens Story Recap */
.consequence-indicator {
  color: #fbbf24;
  background: linear-gradient(135deg, rgba(180, 83, 9, 0.5), rgba(245, 158, 11, 0.4));
  border-color: rgba(251, 191, 36, 0.6);
  cursor: pointer;
  box-shadow: 
    0 2px 0 rgba(0,0,0,0.4),
    0 0 10px rgba(251, 191, 36, 0.3),
    inset 0 1px 0 rgba(255,255,255,0.2);
}

.consequence-indicator:hover {
  background: linear-gradient(135deg, rgba(217, 119, 6, 0.6), rgba(249, 115, 22, 0.5));
  transform: translateY(-1px);
}

.character-card-enhanced + .path-progress-strip {
  margin-top: 8px;
  padding: 6px;
}

.character-card-enhanced + .path-progress-strip .path-progress-title {
  font-size: 0.6rem;
  margin-bottom: 2px;
}

.character-card-enhanced + .path-progress-strip .path-progress-list {
  gap: 4px;
}

.character-card-enhanced + .path-progress-strip .path-progress-item {
  padding: 3px 6px;
  min-width: 50px;
  max-width: 80px;
}

.character-card-enhanced + .path-progress-strip .path-name {
  font-size: 0.5rem;
}

.character-card-enhanced + .path-progress-strip .path-stage {
  font-size: 0.45rem;
}

.character-card-enhanced + .path-progress-strip .path-bar-container {
  height: 2px;
  margin-top: 2px;
}

.character-card-enhanced + .path-progress-strip .path-stage-info {
  font-size: 0.4rem;
}

.path-progress-title {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.65rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  width: 100%;
  margin-bottom: 4px;
}

.path-progress-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  width: 100%;
}

.path-progress-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 4px 8px;
  background: rgba(30, 41, 59, 0.6);
  border: 1px solid rgba(100, 116, 139, 0.3);
  border-radius: 4px;
  min-width: 60px;
  flex: 1 1 auto;
  max-width: 100px;
  transition: all 0.3s ease;
}

.path-progress-item.active-path {
  background: rgba(59, 130, 246, 0.15);
  border-color: rgba(59, 130, 246, 0.4);
}

.path-progress-item.current-path {
  background: rgba(168, 85, 247, 0.2);
  border-color: rgba(168, 85, 247, 0.5);
  box-shadow: 0 0 10px rgba(168, 85, 247, 0.3);
}

.path-progress-item.inactive-path {
  opacity: 0.5;
  background: rgba(30, 41, 59, 0.3);
}

.path-progress-item .path-name {
  font-size: 0.6rem;
  color: #e2e8f0;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  font-weight: 600;
  text-align: center;
}

.path-progress-item .path-stage {
  font-size: 0.55rem;
  color: #94a3b8;
  margin-top: 2px;
}

.path-progress-item .path-bar-container {
  width: 100%;
  height: 3px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 2px;
  margin-top: 4px;
  overflow: hidden;
}

.path-progress-item .path-bar {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  border-radius: 2px;
  transition: width 0.5s ease;
}

.path-progress-item .path-stage-info {
  font-size: 0.5rem;
  color: #64748b;
  margin-top: 2px;
}

.selected-event-meta {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 8px;
  margin: -6px 0 20px;
}

.selected-event-meta-pill {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid rgba(0,255,204,0.18);
  background: rgba(7, 20, 34, 0.72);
  color: #9ae6ff;
  font-family: 'VT323', monospace;
  font-size: 0.9rem;
  letter-spacing: 0.04em;
}

.choice-btn-content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  width: 100%;
  gap: 4px;
  text-align: left;
  white-space: normal;
}

.choice-btn-label {
  font-family: 'Press Start 2P', 'VT323', monospace;
  font-size: 0.8rem;
  line-height: 1.35;
}

.choice-btn-tags {
  font-family: 'VT323', monospace;
  font-size: 0.95rem;
  opacity: 0.88;
  line-height: 1.2;
}


.memory-panel-enhanced::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: 
    repeating-linear-gradient(90deg, transparent 0, transparent 3px, rgba(59,130,246,0.06) 3px, rgba(59,130,246,0.06) 6px),
    repeating-linear-gradient(0deg, transparent 0, transparent 4px, rgba(139,92,246,0.05) 4px, rgba(139,92,246,0.05) 8px);
  pointer-events: none;
}

.memory-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(59,130,246,0.3);
}

.memory-title {
  color: #3b82f6 !important;
}

.refresh-memory-btn {
  font-size: 0.65rem !important;
}

.no-memories {
  color: #64748b;
  font-style: italic;
  text-align: center;
  padding: 40px 20px;
  font-size: 0.9rem;
}

.no-memories small {
  display: block;
  margin-top: 8px;
  opacity: 0.7;
  font-size: 0.8rem;
}

.memory-entries {
  max-height: 220px;
  overflow-y: auto;
}

.memory-entry {
  padding: 12px;
  margin-bottom: 12px;
  background: rgba(10,10,25,0.6);
  border-radius: 8px;
  border-left: 3px solid #3b82f6;
}

.memory-date {
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 4px;
  font-family: 'VT323', monospace;
}

.memory-event {
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 4px;
  font-family: 'VT323', monospace;
  font-size: 0.95rem;
}

.memory-choice {
  color: #00ffcc;
  font-family: 'VT323', monospace;
  font-size: 0.9rem;
  margin-bottom: 6px;
}

.memory-effects {
  font-size: 0.8rem;
  color: #94a3b8;
  font-family: 'VT323', monospace;
}

/* ========================================
   PROFESSION CHOICE DIALOG
   ======================================== */
.profession-dialog-content {
  background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%) !important;
  border: 2px solid #00ffcc !important;
  box-shadow: 0 0 30px rgba(0, 255, 204, 0.3), inset 0 0 60px rgba(0, 0, 0, 0.5) !important;
}

.profession-dialog {
  background: transparent !important;
  overflow: hidden;
}

.profession-glow {
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(0, 255, 204, 0.15) 0%, transparent 50%);
  animation: profession-glow-pulse 3s ease-in-out infinite;
  pointer-events: none;
}

@keyframes profession-glow-pulse {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.1); }
}

.profession-scanlines {
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    0deg,
    transparent,
    transparent 2px,
    rgba(0, 0, 0, 0.1) 2px,
    rgba(0, 0, 0, 0.1) 4px
  );
  pointer-events: none;
  z-index: 1;
}

.profession-title {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 24px 16px 12px !important;
  background: linear-gradient(180deg, rgba(0, 255, 204, 0.1) 0%, transparent 100%);
}

.profession-title-icon {
  color: #00ffcc !important;
  text-shadow: 0 0 10px #00ffcc;
}

.profession-title-text {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 1rem !important;
  color: #00ffcc !important;
  text-shadow: 0 0 10px #00ffcc, 2px 0 0 #000;
  letter-spacing: 0.1em;
}

.profession-subtitle {
  color: #94a3b8 !important;
  font-family: 'VT323', monospace !important;
  font-size: 1.1rem !important;
  padding: 8px 16px 16px !important;
}

.profession-cards-container {
  padding: 16px !important;
}

.profession-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  justify-items: center;
}

.profession-choice-card {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border: 2px solid #334155;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 180px;
  max-width: 220px;
}

.profession-choice-card:hover:not([disabled]) {
  transform: translateY(-4px);
  border-color: #00ffcc;
  box-shadow: 0 8px 20px rgba(0, 255, 204, 0.3), 0 0 30px rgba(0, 255, 204, 0.2);
}

.profession-choice-card.selected {
  opacity: 0.7;
  cursor: not-allowed;
}

.profession-card-icon {
  display: flex;
  justify-content: center;
  margin-bottom: 12px;
  color: #00ffcc;
}

.profession-card-name {
  font-family: 'Press Start 2P', monospace !important;
  font-size: 0.75rem !important;
  color: #e2e8f0 !important;
  margin-bottom: 8px;
  text-shadow: 1px 1px 0 #000;
}

.profession-card-desc {
  font-family: 'VT323', monospace !important;
  font-size: 0.95rem !important;
  color: #94a3b8 !important;
  line-height: 1.4;
}

.profession-actions {
  padding: 16px !important;
}
</style>
