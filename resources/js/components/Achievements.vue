<template>
  <v-dialog v-model="dialogVisible" max-width="800" scrollable>
    <v-card class="achievements-dialog" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
      <v-card-title class="achievements-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
        <v-icon size="28" class="mr-2" color="white">mdi-trophy</v-icon>
        <span style="font-weight: bold; font-size: 1.3rem;">ACHIEVEMENTS</span>
        <v-spacer></v-spacer>
        <v-chip color="white" variant="flat" size="small" style="font-weight: bold;">
          {{ unlockedCount }}/{{ totalCount }}
        </v-chip>
      </v-card-title>
      
      <v-card-text class="achievements-content">
        <!-- Category Tabs -->
        <v-tabs v-model="activeCategory" class="mb-4 achievements-tabs">
          <v-tab value="all">All</v-tab>
          <v-tab value="career">Career</v-tab>
          <v-tab value="health">Health</v-tab>
          <v-tab value="relationship">Relationship</v-tab>
          <v-tab value="skills">Skills</v-tab>
          <v-tab value="lifespan">Lifespan</v-tab>
          <v-tab value="social">Social</v-tab>
          <v-tab value="luck">Luck</v-tab>
          <v-tab value="special">Special</v-tab>
          <v-tab value="milestone">Milestone</v-tab>
        </v-tabs>

        <!-- Achievement Stats -->
        <v-row class="mb-4">
          <v-col cols="6" sm="3">
            <v-card variant="tonal" color="grey" style="background: rgba(158, 158, 158, 0.15);">
              <v-card-text class="text-center">
                <div class="text-h4 font-weight-bold" style="color: #9e9e9e;">{{ stats.common }}</div>
                <div class="text-caption" style="color: #9e9e9e;">Common</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="3">
            <v-card variant="tonal" color="success" style="background: rgba(76, 175, 80, 0.15);">
              <v-card-text class="text-center">
                <div class="text-h4 font-weight-bold" style="color: #4caf50;">{{ stats.uncommon }}</div>
                <div class="text-caption" style="color: #4caf50;">Uncommon</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="3">
            <v-card variant="tonal" color="info" style="background: rgba(33, 150, 243, 0.15);">
              <v-card-text class="text-center">
                <div class="text-h4 font-weight-bold" style="color: #2196f3;">{{ stats.rare }}</div>
                <div class="text-caption" style="color: #2196f3;">Rare</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="3">
            <v-card variant="tonal" color="purple" style="background: rgba(156, 39, 176, 0.15);">
              <v-card-text class="text-center">
                <div class="text-h4 font-weight-bold" style="color: #9c27b0;">{{ stats.epic }}</div>
                <div class="text-caption" style="color: #9c27b0;">Epic</div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Achievements List -->
        <div class="achievements-grid">
          <div
            v-for="achievement in filteredAchievements"
            :key="achievement.id"
            class="achievement-card"
            :class="{ 'locked': !achievement.unlocked, [`rarity-${achievement.rarity}`]: true }"
            @click="showAchievementDetail(achievement)"
          >
            <div class="achievement-icon">
              <v-icon 
                size="40" 
                :color="achievement.unlocked ? getRarityColor(achievement.rarity) : 'grey-darken-2'"
              >
                {{ achievement.unlocked ? (achievement.icon || 'mdi-trophy') : 'mdi-lock' }}
              </v-icon>
            </div>
            <div class="achievement-info">
              <div class="achievement-name">{{ achievement.name }}</div>
              <div class="achievement-description">{{ achievement.description }}</div>
              <v-chip 
                v-if="achievement.unlocked" 
                size="x-small" 
                :color="getRarityColor(achievement.rarity)"
                variant="flat"
                class="mt-1"
              >
                {{ achievement.rarity }}
              </v-chip>
            </div>
            <v-icon 
              v-if="achievement.unlocked" 
              color="success" 
              size="20" 
              class="check-icon"
            >
              mdi-check-circle
            </v-icon>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredAchievements.length === 0" class="text-center py-8">
          <v-icon size="64" color="grey">mdi-trophy-outline</v-icon>
          <div class="text-h6 mt-2">No achievements in this category</div>
        </div>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" variant="text" @click="dialogVisible = false">
          Close
        </v-btn>
      </v-card-actions>
    </v-card>

    <!-- Achievement Detail Dialog - RETRO PIXEL EDITION -->
    <v-dialog v-model="detailDialog" max-width="500" persistent rounded="0" content-class="achievement-detail-dialog">
      <v-card v-if="selectedAchievement" class="achievement-detail-card">
        <v-card-title class="achievement-detail-header" :class="`rarity-${selectedAchievement.rarity}`">
          <v-icon size="32" class="mr-2">{{ selectedAchievement.icon || 'mdi-trophy' }}</v-icon>
          <span class="achievement-detail-title">{{ selectedAchievement.name }}</span>
        </v-card-title>
        <v-card-text class="pt-4">
          <div class="text-body-1 mb-4">{{ selectedAchievement.description }}</div>
          
          <v-divider class="mb-4"></v-divider>
          
          <v-row dense>
            <v-col cols="6">
              <v-chip :color="getRarityColor(selectedAchievement.rarity)" variant="flat">
                {{ selectedAchievement.rarity }}
              </v-chip>
            </v-col>
            <v-col cols="6" class="text-right">
              <v-chip v-if="selectedAchievement.unlocked" color="success" variant="flat">
                <v-icon start>mdi-check</v-icon>
                Unlocked
              </v-chip>
              <v-chip v-else color="grey" variant="flat">
                <v-icon start>mdi-lock</v-icon>
                Locked
              </v-chip>
            </v-col>
          </v-row>

          <!-- Progress for unlockable achievements -->
          <div v-if="!selectedAchievement.unlocked && selectedAchievement.progress" class="mt-4">
            <div class="text-caption mb-1">Progress: {{ selectedAchievement.progress.current }}/{{ selectedAchievement.progress.target }}</div>
            <v-progress-linear
              :model-value="(selectedAchievement.progress.current / selectedAchievement.progress.target) * 100"
              :color="getRarityColor(selectedAchievement.rarity)"
              height="8"
              rounded
            ></v-progress-linear>
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" variant="text" @click="detailDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  characterId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const dialogVisible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const activeCategory = ref('all')
const selectedAchievement = ref(null)
const detailDialog = ref(false)
const achievements = ref([])
const loading = ref(false)

// Achievement data with all achievements
const allAchievementsData = [
  // Career achievements
  { id: 'first_job', name: 'Career Beginnings', description: 'Start your professional journey', icon: 'mdi-briefcase', category: 'career', rarity: 'common' },
  { id: 'career_master', name: 'Career Master', description: 'Reach career level 5', icon: 'mdi-star', category: 'career', rarity: 'rare' },
  { id: 'career_promotion', name: 'Promotion!', description: 'Get promoted to next career level', icon: 'mdi-arrow-up-bold', category: 'career', rarity: 'common' },
  { id: 'jack_of_all_trades', name: 'Jack of All Trades', description: 'Try 3 different professions', icon: 'mdi-shape', category: 'career', rarity: 'uncommon' },
  { id: 'entrepreneur', name: 'Entrepreneur', description: 'Start your own business', icon: 'mdi-store', category: 'career', rarity: 'epic' },
  { id: 'workaholic', name: 'Workaholic', description: 'Work for 20+ days in a row', icon: 'mdi-clock', category: 'career', rarity: 'uncommon' },
  
  
  
  
  
  
  
  
  
  // Health achievements - Made easier
  { id: 'fitness_fanatic', name: 'Fitness Fanatic', description: 'Maintain 70+ health for 10 days', icon: 'mdi-heart', category: 'health', rarity: 'common' },
  { id: 'marathon_runner', name: 'Marathon Runner', description: 'Complete a fitness event', icon: 'mdi-run', category: 'health', rarity: 'rare' },
  { id: 'survivor', name: 'Survivor', description: 'Recover from low health', icon: 'mdi-hospital', category: 'health', rarity: 'uncommon' },
  
  // Relationship achievements
  { id: 'first_love', name: 'First Love', description: 'Enter your first relationship', icon: 'mdi-heart', category: 'relationship', rarity: 'common' },
  { id: 'tie_the_knot', name: 'Tie the Knot', description: 'Get married', icon: 'mdi-ring', category: 'relationship', rarity: 'uncommon' },
  { id: 'family_planner', name: 'Family Planner', description: 'Have 2 children', icon: 'mdi-account-group', category: 'relationship', rarity: 'rare' },
  { id: 'social_butterfly', name: 'Social Butterfly', description: 'Have 5+ social connections', icon: 'mdi-account-multiple', category: 'relationship', rarity: 'uncommon' },
  { id: 'popular', name: 'Popular', description: 'Have 10+ social connections', icon: 'mdi-star', category: 'relationship', rarity: 'epic' },
  
  // Skills achievements - Made easier
  { id: 'eager_learner', name: 'Eager Learner', description: 'Complete 5 education events', icon: 'mdi-school', category: 'skills', rarity: 'common' },
  { id: 'polymath', name: 'Polymath', description: 'Master 3 different skills', icon: 'mdi-brain', category: 'skills', rarity: 'rare' },
  { id: 'master_of_skills', name: 'Master of Skills', description: 'Reach level 3 in any skill', icon: 'mdi-star-circle', category: 'skills', rarity: 'epic' },
  { id: 'talent_spotted', name: 'Talent Spotted', description: 'Discover a hidden talent', icon: 'mdi-lightbulb', category: 'skills', rarity: 'uncommon' },
  { id: 'gifted', name: 'Gifted', description: 'Have all stats above 60', icon: 'mdi-diamond', category: 'skills', rarity: 'rare' },
  
  // Lifespan achievements - Made easier
  { id: 'baby_steps', name: 'Baby Steps', description: 'Reach age 5', icon: 'mdi-baby-carriage', category: 'lifespan', rarity: 'common' },
  { id: 'childhood', name: 'Childhood Complete', description: 'Reach age 12', icon: 'mdi-human-child', category: 'lifespan', rarity: 'common' },
  { id: 'teenager', name: 'Teenager', description: 'Reach age 13', icon: 'mdi-account', category: 'lifespan', rarity: 'common' },
  { id: 'adulthood', name: 'Adulthood', description: 'Reach age 18', icon: 'mdi-human', category: 'lifespan', rarity: 'common' },
  
  // Special achievements - Made easier
  { id: 'lucky_star', name: 'Lucky Star', description: 'Have 60+ luck', icon: 'mdi-star', category: 'special', rarity: 'uncommon' },
  { id: 'story_master', name: 'Story Master', description: 'Complete 5 story events', icon: 'mdi-book', category: 'special', rarity: 'uncommon' },
  { id: 'perfect_day', name: 'Perfect Day', description: 'Have all stats above 80 in one day', icon: 'mdi-weather-sunny', category: 'special', rarity: 'rare' },
  { id: 'comeback_kid', name: 'Comeback Kid', description: 'Recover from low health', icon: 'mdi-hospital-box', category: 'special', rarity: 'uncommon' },
  { id: 'game_master', name: 'Game Master', description: 'Unlock 20 achievements', icon: 'mdi-crown', category: 'special', rarity: 'legendary' },
]

// Get rarity color
const getRarityColor = (rarity) => {
  const colors = {
    common: 'grey',
    uncommon: 'success',
    rare: 'info',
    epic: 'purple',
    legendary: 'amber'
  }
  return colors[rarity] || 'grey'
}

// Compute filtered achievements
const filteredAchievements = computed(() => {
  // Use API data if available (it has unlocked status), otherwise fallback to hardcoded data
  let filtered
  if (achievements.value.length > 0 && achievements.value[0]?.hasOwnProperty('unlocked')) {
    // API returns all achievements with unlocked status
    filtered = achievements.value
  } else {
    // Fallback to hardcoded data with ID mapping
    filtered = allAchievementsData.map(achievement => {
      const unlocked = achievements.value.includes(achievement.id)
      return { ...achievement, unlocked }
    })
  }
  
  if (activeCategory.value !== 'all') {
    filtered = filtered.filter(a => a.category === activeCategory.value)
  }
  
  // Sort: unlocked first, then by rarity
  return filtered.sort((a, b) => {
    if (a.unlocked !== b.unlocked) return b.unlocked - a.unlocked
    const rarityOrder = { legendary: 0, epic: 1, rare: 2, uncommon: 3, common: 4 }
    return rarityOrder[a.rarity] - rarityOrder[b.rarity]
  })
})

// Count statistics
const unlockedCount = computed(() => {
  return filteredAchievements.value.filter(a => a.unlocked).length
})

const totalCount = computed(() => filteredAchievements.value.length)

const stats = computed(() => {
  const unlocked = filteredAchievements.value.filter(a => a.unlocked)
  return {
    common: unlocked.filter(a => a.rarity === 'common').length,
    uncommon: unlocked.filter(a => a.rarity === 'uncommon').length,
    rare: unlocked.filter(a => a.rarity === 'rare').length,
    epic: unlocked.filter(a => a.rarity === 'epic').length,
  }
})

// Load achievements from API
const loadAchievements = async () => {
  if (!props.characterId) return
  
  loading.value = true
  try {
    const response = await fetch(`/api/characters/${props.characterId}/achievements`, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })
    const data = await response.json()
    if (data.achievements) {
      // API returns all achievements with unlocked status
      achievements.value = data.achievements
    }
    // Also load stats if available
    if (data.stats) {
      stats.value = data.stats
    }
  } catch (error) {
    console.error('Error loading achievements:', error)
    // Fallback: try to get from character.achievement_flags if API fails
    // This is handled by the parent component passing data
  } finally {
    loading.value = false
  }
}

// Show achievement detail
const showAchievementDetail = (achievement) => {
  selectedAchievement.value = achievement
  detailDialog.value = true
}

// Watch for dialog open
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    loadAchievements()
  }
})
</script>

<style scoped>
/* Import pixel font */
@import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&display=swap');

/* Achievement Tabs - White Text */
.achievements-tabs {
  background: rgba(0, 0, 0, 0.3) !important;
  border-bottom: 2px solid #3d3d5c;
}

.achievements-tabs .v-tab {
  color: #fff !important;
  font-family: 'VT323', monospace !important;
  font-size: 16px !important;
  text-transform: uppercase !important;
  letter-spacing: 1px !important;
  opacity: 0.7;
  transition: all 0.2s ease;
}

.achievements-tabs .v-tab:hover {
  opacity: 1;
  background: rgba(255, 255, 255, 0.1) !important;
}

.achievements-tabs .v-tab--selected {
  opacity: 1 !important;
  color: #ffd43b !important;
  background: rgba(255, 212, 59, 0.15) !important;
  text-shadow: 0 0 10px rgba(255, 212, 59, 0.5);
}

/* CRT Screen Effect */
.achievements-dialog {
  background: linear-gradient(180deg, #0a0a12 0%, #1a1a2e 50%, #0d0d1a 100%) !important;
  position: relative;
  overflow: hidden;
}

/* Scanline overlay */
.achievements-dialog::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: repeating-linear-gradient(
    0deg,
    rgba(0, 0, 0, 0.15),
    rgba(0, 0, 0, 0.15) 1px,
    transparent 1px,
    transparent 2px
  );
  pointer-events: none;
  z-index: 1;
}

/* CRT glow effect */
.achievements-dialog::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(ellipse at center, transparent 0%, rgba(0, 0, 0, 0.3) 100%);
  pointer-events: none;
  z-index: 2;
}

.achievements-header {
  background: linear-gradient(180deg, #ff6b6b 0%, #c92a2a 50%, #862e2e 100%) !important;
  color: #fff !important;
  font-family: 'Press Start 2P', 'VT323', monospace !important;
  font-size: 14px !important;
  text-transform: uppercase !important;
  letter-spacing: 2px !important;
  padding: 20px !important;
  border-bottom: 4px solid #ffd43b !important;
  text-shadow: 3px 3px 0 #000, -1px -1px 0 #000 !important;
  position: relative;
}

/* Pixel art header icon */
.achievements-header::before {
  content: '🏆';
  font-size: 24px;
  margin-right: 12px;
  filter: drop-shadow(2px 2px 0 #000);
}

.achievements-content {
  background: rgba(0, 0, 0, 0.4) !important;
  position: relative;
  z-index: 3;
}

/* Pixelated tabs */
.achievements-content :deep(.v-tab) {
  font-family: 'VT323', monospace !important;
  font-size: 18px !important;
  text-transform: uppercase !important;
  letter-spacing: 1px !important;
  border: 2px solid transparent !important;
  transition: all 0.2s !important;
}

.achievements-content :deep(.v-tab--selected) {
  background: rgba(255, 212, 59, 0.2) !important;
  border-color: #ffd43b !important;
  color: #ffd43b !important;
  text-shadow: 0 0 10px #ffd43b !important;
}

.achievements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  padding: 16px;
}

/* Retro Pixel Achievement Cards */
.achievement-card {
  display: flex;
  align-items: center;
  padding: 16px;
  margin-bottom: 0;
  border: 4px solid #2d2d44 !important;
  border-radius: 0 !important;
  background: linear-gradient(135deg, #1e1e32 0%, #14142a 100%) !important;
  cursor: pointer;
  transition: all 0.15s steps(3) !important;
  position: relative;
  image-rendering: pixelated;
}

/* Pixel hover effect */
.achievement-card:hover {
  transform: translate(-4px, -4px);
  box-shadow: 8px 8px 0 #000 !important;
  border-color: #ffd43b !important;
}

.achievement-card:active {
  transform: translate(0, 0);
  box-shadow: 4px 4px 0 #000 !important;
}

/* Locked state */
.achievement-card.locked {
  opacity: 0.5;
  filter: grayscale(80%);
}

.achievement-card.locked .achievement-icon {
  filter: grayscale(100%) brightness(0.5);
}

/* Rarity pixel borders */
.achievement-card.rarity-common { 
  border-color: #868e96 !important; 
}
.achievement-card.rarity-common:hover {
  border-color: #ffd43b !important;
}

.achievement-card.rarity-uncommon { 
  border-color: #51cf66 !important;
  box-shadow: inset 0 0 0 2px rgba(81, 207, 102, 0.3);
}
.achievement-card.rarity-uncommon:hover {
  border-color: #ffd43b !important;
  box-shadow: 0 0 15px rgba(81, 207, 102, 0.5), 8px 8px 0 #000 !important;
}

.achievement-card.rarity-rare { 
  border-color: #339af0 !important;
  box-shadow: inset 0 0 0 2px rgba(51, 154, 240, 0.3);
}
.achievement-card.rarity-rare:hover {
  border-color: #ffd43b !important;
  box-shadow: 0 0 20px rgba(51, 154, 240, 0.6), 8px 8px 0 #000 !important;
}

.achievement-card.rarity-epic { 
  border-color: #cc5de8 !important;
  box-shadow: inset 0 0 0 2px rgba(204, 93, 232, 0.3);
}
.achievement-card.rarity-epic:hover {
  border-color: #ffd43b !important;
  box-shadow: 0 0 25px rgba(204, 93, 232, 0.7), 8px 8px 0 #000 !important;
}

.achievement-card.rarity-legendary { 
  border-color: #ffd43b !important;
  box-shadow: inset 0 0 0 2px rgba(255, 212, 59, 0.4), 0 0 20px rgba(255, 212, 59, 0.3);
}
.achievement-card.rarity-legendary:hover {
  border-color: #ffd43b !important;
  box-shadow: 0 0 30px rgba(255, 212, 59, 0.8), 8px 8px 0 #000 !important;
}

/* Pixel Icon Box */
.achievement-icon {
  flex-shrink: 0;
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #2d2d44 0%, #1a1a2e 100%) !important;
  border: 3px solid #3d3d5c !important;
  border-radius: 0 !important;
  margin-right: 14px;
  box-shadow: inset 2px 2px 0 rgba(255,255,255,0.1), inset -2px -2px 0 rgba(0,0,0,0.3);
}

.achievement-icon :deep(.v-icon) {
  font-size: 28px !important;
  filter: drop-shadow(2px 2px 0 rgba(0,0,0,0.5));
}

/* Achievement Info */
.achievement-info {
  flex: 1;
  min-width: 0;
}

.achievement-name {
  font-family: 'VT323', monospace !important;
  font-size: 20px !important;
  font-weight: 400 !important;
  color: #fff !important;
  text-transform: uppercase !important;
  letter-spacing: 1px !important;
  text-shadow: 2px 2px 0 #000 !important;
  margin-bottom: 4px;
}

.achievement-description {
  font-family: 'VT323', monospace !important;
  font-size: 16px !important;
  color: #adb5bd !important;
  line-height: 1.4;
}

/* Rarity chip */
.achievement-card .v-chip {
  font-family: 'VT323', monospace !important;
  font-size: 14px !important;
  text-transform: uppercase !important;
  border: 2px solid !important;
  border-radius: 0 !important;
}

/* Check icon */
.check-icon {
  position: absolute;
  top: 6px;
  right: 6px;
  font-size: 16px !important;
}

/* Retro Pixel Achievement Detail Dialog */
.achievement-detail-dialog {
  animation: achievement-popup 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
}

@keyframes achievement-popup {
  0% { transform: scale(0.5); opacity: 0; }
  70% { transform: scale(1.05); }
  100% { transform: scale(1); opacity: 1; }
}

.achievement-detail-card {
  background: linear-gradient(180deg, #1a1a2e 0%, #0a0a12 100%) !important;
  border: 4px solid #ffd43b !important;
  border-radius: 0 !important;
  box-shadow: 
    0 0 0 4px #000,
    0 0 40px rgba(255, 212, 59, 0.4),
    inset 0 0 30px rgba(0, 0, 0, 0.5) !important;
  position: relative;
}

/* Pixel corner decorations */
.achievement-detail-card::before,
.achievement-detail-card::after {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  border: 3px solid #ffd43b;
}

.achievement-detail-card::before {
  top: -8px;
  left: -8px;
  border-right: none;
  border-bottom: none;
}

.achievement-detail-card::after {
  bottom: -8px;
  right: -8px;
  border-left: none;
  border-top: none;
}

.achievement-detail-header {
  padding: 20px 24px !important;
  font-family: 'Press Start 2P', 'VT323', monospace !important;
  font-size: 12px !important;
  text-transform: uppercase !important;
  letter-spacing: 2px !important;
  display: flex !important;
  align-items: center !important;
  border-bottom: 4px solid #000 !important;
  text-shadow: 2px 2px 0 rgba(0,0,0,0.5) !important;
}

/* Rarity-based header colors */
.achievement-detail-header.rarity-common {
  background: linear-gradient(180deg, #bdbdbd 0%, #9e9e9e 50%, #757575 100%) !important;
  color: #000 !important;
}

.achievement-detail-header.rarity-uncommon {
  background: linear-gradient(180deg, #69db7c 0%, #51cf66 50%, #2f9e44 100%) !important;
  color: #000 !important;
}

.achievement-detail-header.rarity-rare {
  background: linear-gradient(180deg, #74c0fc 0%, #339af0 50%, #1971c2 100%) !important;
  color: #000 !important;
}

.achievement-detail-header.rarity-epic {
  background: linear-gradient(180deg, #da77f2 0%, #cc5de8 50%, #9c36c5 100%) !important;
  color: #fff !important;
}

.achievement-detail-header.rarity-legendary {
  background: linear-gradient(180deg, #ffe066 0%, #ffd43b 50%, #f59f00 100%) !important;
  color: #000 !important;
  animation: legendary-glow 1.5s ease-in-out infinite alternate;
}

@keyframes legendary-glow {
  0% { box-shadow: 0 0 20px rgba(255, 212, 59, 0.5); }
  100% { box-shadow: 0 0 40px rgba(255, 212, 59, 0.8), 0 0 60px rgba(255, 212, 59, 0.4); }
}

.achievement-detail-title {
  text-shadow: 2px 2px 0 rgba(0, 0, 0, 0.3);
}

.achievement-detail-card .v-card-text {
  background: rgba(0, 0, 0, 0.5) !important;
  padding: 24px !important;
  font-family: 'VT323', monospace !important;
}

.achievement-detail-card .v-chip {
  font-family: 'VT323', monospace !important;
  font-size: 18px !important;
  border: 2px solid !important;
  border-radius: 0 !important;
}

.achievement-detail-card .v-btn {
  font-family: 'VT323', monospace !important;
  font-size: 18px !important;
  text-transform: uppercase !important;
  border: 3px solid !important;
  border-radius: 0 !important;
}

/* Empty state */
.achievements-empty {
  text-align: center;
  padding: 60px 20px;
}

.achievements-empty .v-icon {
  font-size: 80px !important;
  opacity: 0.3;
  filter: grayscale(100%);
}

.achievements-empty .text-h6 {
  font-family: 'VT323', monospace !important;
  font-size: 24px !important;
  color: #868e96 !important;
}

/* Rarity backgrounds */
.achievement-card.rarity-legendary {
  background: linear-gradient(135deg, rgba(255, 212, 59, 0.15) 0%, rgba(255, 152, 0, 0.15) 100%) !important;
}

.achievement-card.rarity-epic {
  background: linear-gradient(135deg, rgba(204, 93, 232, 0.15) 0%, rgba(103, 58, 183, 0.15) 100%) !important;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .achievements-grid {
    grid-template-columns: 1fr;
    gap: 12px;
    padding: 12px;
  }
  
  .achievement-card {
    padding: 12px;
  }
  
  .achievement-icon {
    width: 44px;
    height: 44px;
  }
  
  .achievement-name {
    font-size: 16px !important;
  }
  
  .achievement-description {
    font-size: 14px !important;
  }
}
</style>
