<template>
  <v-dialog v-model="dialogVisible" max-width="800" scrollable>
    <v-card class="achievements-dialog">
      <v-card-title class="achievements-header">
        <v-icon size="28" class="mr-2">mdi-trophy</v-icon>
        Achievements
        <v-spacer></v-spacer>
        <v-chip color="amber" variant="flat" size="small">
          {{ unlockedCount }}/{{ totalCount }}
        </v-chip>
      </v-card-title>
      
      <v-card-text class="achievements-content">
        <!-- Category Tabs -->
        <v-tabs v-model="activeCategory" color="primary" class="mb-4">
          <v-tab value="all">All</v-tab>
          <v-tab value="career">Career</v-tab>
          <v-tab value="wealth">Wealth</v-tab>
          <v-tab value="health">Health</v-tab>
          <v-tab value="relationship">Relationship</v-tab>
          <v-tab value="skills">Skills</v-tab>
          <v-tab value="lifespan">Lifespan</v-tab>
          <v-tab value="special">Special</v-tab>
        </v-tabs>

        <!-- Achievement Stats -->
        <v-row class="mb-4">
          <v-col cols="6" sm="3">
            <v-card variant="tonal" color="grey">
              <v-card-text class="text-center">
                <div class="text-h4 font-weight-bold">{{ stats.common }}</div>
                <div class="text-caption">Common</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="3">
            <v-card variant="tonal" color="success">
              <v-card-text class="text-center">
                <div class="text-h4 font-weight-bold">{{ stats.uncommon }}</div>
                <div class="text-caption">Uncommon</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="3">
            <v-card variant="tonal" color="info">
              <v-card-text class="text-center">
                <div class="text-h4 font-weight-bold">{{ stats.rare }}</div>
                <div class="text-caption">Rare</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" sm="3">
            <v-card variant="tonal" color="purple">
              <v-card-text class="text-center">
                <div class="text-h4 font-weight-bold">{{ stats.epic }}</div>
                <div class="text-caption">Epic</div>
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

    <!-- Achievement Detail Dialog -->
    <v-dialog v-model="detailDialog" max-width="500">
      <v-card v-if="selectedAchievement">
        <v-card-title class="d-flex align-center" :class="`bg-${getRarityColor(selectedAchievement.rarity)}`">
          <v-icon size="32" class="mr-2">{{ selectedAchievement.icon || 'mdi-trophy' }}</v-icon>
          {{ selectedAchievement.name }}
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
  { id: 'first_job', name: 'First Steps', description: 'Start your first job', icon: 'mdi-briefcase', category: 'career', rarity: 'common' },
  { id: 'career_master', name: 'Career Master', description: 'Reach career level 10', icon: 'mdi-star', category: 'career', rarity: 'rare' },
  { id: 'jack_of_all_trades', name: 'Jack of All Trades', description: 'Try 5 different professions', icon: 'mdi-shape', category: 'career', rarity: 'uncommon' },
  { id: 'entrepreneur', name: 'Entrepreneur', description: 'Start your own business', icon: 'mdi-store', category: 'career', rarity: 'epic' },
  { id: 'workaholic', name: 'Workaholic', description: 'Work for 50+ days without a break', icon: 'mdi-clock', category: 'career', rarity: 'rare' },
  
  // Wealth achievements
  { id: 'savings_start', name: 'Savings Start', description: 'Save your first $10,000', icon: 'mdi-piggy-bank', category: 'wealth', rarity: 'common' },
  { id: 'millionaire', name: 'Millionaire', description: 'Accumulate $1,000,000', icon: 'mdi-cash', category: 'wealth', rarity: 'epic' },
  { id: 'multi_millionaire', name: 'Multi-Millionaire', description: 'Accumulate $5,000,000', icon: 'mdi-cash-multiple', category: 'wealth', rarity: 'legendary' },
  { id: 'comfortable_retirement', name: 'Comfortable Retirement', description: 'Retire with $500,000+', icon: 'mdi-beach', category: 'wealth', rarity: 'rare' },
  { id: 'big_spender', name: 'Big Spender', description: 'Spend $100,000 in one go', icon: 'mdi-cart', category: 'wealth', rarity: 'uncommon' },
  
  // Health achievements
  { id: 'fitness_fanatic', name: 'Fitness Fanatic', description: 'Maintain 80+ health for 20 days', icon: 'mdi-heart', category: 'health', rarity: 'uncommon' },
  { id: 'marathon_runner', name: 'Marathon Runner', description: 'Complete a fitness event', icon: 'mdi-run', category: 'health', rarity: 'rare' },
  { id: 'healthy_living', name: 'Healthy Living', description: 'Live to age 70 with 50+ health', icon: 'mdi-apple', category: 'health', rarity: 'epic' },
  { id: 'survivor', name: 'Survivor', description: 'Recover from critical health', icon: 'mdi-hospital', category: 'health', rarity: 'rare' },
  { id: 'longevity', name: 'Longevity', description: 'Live to age 80+', icon: 'mdi-clock-check', category: 'health', rarity: 'epic' },
  
  // Relationship achievements
  { id: 'first_love', name: 'First Love', description: 'Enter your first relationship', icon: 'mdi-heart', category: 'relationship', rarity: 'common' },
  { id: 'tie_the_knot', name: 'Tie the Knot', description: 'Get married', icon: 'mdi-ring', category: 'relationship', rarity: 'uncommon' },
  { id: 'family_planner', name: 'Family Planner', description: 'Have 3 children', icon: 'mdi-account-group', category: 'relationship', rarity: 'rare' },
  { id: 'granny', name: 'Granny/Grandpa', description: 'Have a grandchild', icon: 'mdi-human', category: 'relationship', rarity: 'epic' },
  { id: 'social_butterfly', name: 'Social Butterfly', description: 'Have 10+ social connections', icon: 'mdi-account-multiple', category: 'relationship', rarity: 'uncommon' },
  
  // Skills achievements
  { id: 'eager_learner', name: 'Eager Learner', description: 'Complete 10 education events', icon: 'mdi-school', category: 'skills', rarity: 'common' },
  { id: 'polymath', name: 'Polymath', description: 'Master 5 different skills', icon: 'mdi-brain', category: 'skills', rarity: 'rare' },
  { id: 'master_of_skills', name: 'Master of Skills', description: 'Reach level 5 in any skill', icon: 'mdi-star-circle', category: 'skills', rarity: 'epic' },
  { id: 'talent_spotted', name: 'Talent Spotted', description: 'Discover a hidden talent', icon: 'mdi-lightbulb', category: 'skills', rarity: 'uncommon' },
  { id: 'gifted', name: 'Gifted', description: 'Have all stats above 70', icon: 'mdi-diamond', category: 'skills', rarity: 'legendary' },
  
  // Lifespan achievements
  { id: 'teenager', name: 'Teenager', description: 'Reach age 13', icon: 'mdi-human-child', category: 'lifespan', rarity: 'common' },
  { id: 'adulthood', name: 'Adulthood', description: 'Reach age 18', icon: 'mdi-human', category: 'lifespan', rarity: 'common' },
  { id: 'full_life', name: 'Full Life', description: 'Reach age 70', icon: 'mdi-clock-check', category: 'lifespan', rarity: 'rare' },
  
  // Special achievements
  { id: 'lucky_star', name: 'Lucky Star', description: 'Have 80+ luck', icon: 'mdi-star', category: 'special', rarity: 'rare' },
  { id: 'karma_wheel', name: 'Karma Wheel', description: 'Reach 1000 karma', icon: 'mdi-dharmachakra', category: 'special', rarity: 'epic' },
  { id: 'story_master', name: 'Story Master', description: 'Complete 10 story events', icon: 'mdi-book', category: 'special', rarity: 'uncommon' },
  { id: 'perfect_day', name: 'Perfect Day', description: 'Have all stats above 90 in one day', icon: 'mdi-weather-sunny', category: 'special', rarity: 'epic' },
  { id: 'comeback_kid', name: 'Comeback Kid', description: 'Recover from near-death', icon: 'mdi-hospital-box', category: 'special', rarity: 'rare' },
  { id: 'game_master', name: 'Game Master', description: 'Unlock all other achievements', icon: 'mdi-crown', category: 'special', rarity: 'legendary' },
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
  let filtered = allAchievementsData.map(achievement => {
    const unlocked = achievements.value.includes(achievement.id)
    return { ...achievement, unlocked }
  })
  
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
      // API returns achievement objects, extract IDs
      achievements.value = data.achievements.map(a => a.id || a)
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
.achievements-dialog {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.achievements-header {
  background: linear-gradient(90deg, #1e3a5f 0%, #0d47a1 100%);
  color: white;
  display: flex;
  align-items: center;
}

.achievements-content {
  background: rgba(255, 255, 255, 0.02);
}

.achievements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 12px;
}

.achievement-card {
  display: flex;
  align-items: center;
  padding: 12px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  border: 2px solid transparent;
}

.achievement-card:hover {
  transform: translateY(-2px);
  background: rgba(255, 255, 255, 0.1);
}

.achievement-card.locked {
  opacity: 0.6;
}

.achievement-card.locked .achievement-icon {
  filter: grayscale(100%);
}

/* Rarity borders */
.achievement-card.rarity-common { border-color: #9e9e9e; }
.achievement-card.rarity-uncommon { border-color: #4caf50; }
.achievement-card.rarity-rare { border-color: #2196f3; }
.achievement-card.rarity-epic { border-color: #9c27b0; }
.achievement-card.rarity-legendary { border-color: #ffc107; }

.achievement-icon {
  flex-shrink: 0;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  margin-right: 12px;
}

.achievement-info {
  flex: 1;
  min-width: 0;
}

.achievement-name {
  font-weight: 600;
  font-size: 14px;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.achievement-description {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.check-icon {
  position: absolute;
  top: 8px;
  right: 8px;
}

/* Rarity backgrounds */
.achievement-card.rarity-legendary {
  background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.1) 100%);
}

.achievement-card.rarity-epic {
  background: linear-gradient(135deg, rgba(156, 39, 176, 0.1) 0%, rgba(103, 58, 183, 0.1) 100%);
}
</style>
