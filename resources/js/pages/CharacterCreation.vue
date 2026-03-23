<template>
  <!-- Galaxy Background Screen -->
  <div class="galaxy-screen relative min-h-screen overflow-hidden"
    style="background: linear-gradient(135deg, #0d0d1a 0%, #1a0a2e 30%, #0a1a2e 50%, #0a1a1a 70%, #0d0d1a 100%);">
    
    <!-- Galaxy Starfield -->
    <div class="starfield pointer-events-none fixed inset-0 z-0">
      <div v-for="n in 80" :key="n" class="star absolute rounded-full"
        :style="getStarStyle(n)">
      </div>
    </div>
    
    <!-- Nebula Clouds -->
    <div class="nebula pointer-events-none fixed inset-0 z-5"></div>
    
    <!-- Energy Particles -->
    <div class="energy-particles pointer-events-none fixed inset-0 z-8">
      <div v-for="n in 15" :key="'energy-'+n" class="energy-orb" :style="getEnergyOrbStyle(n)"></div>
    </div>
    
    <!-- Shooting Stars -->
    <div class="shooting-stars pointer-events-none fixed inset-0 z-6">
      <div v-for="n in 5" :key="'shooting-'+n" class="shooting-star" :style="getShootingStarStyle(n)"></div>
    </div>
    
    <!-- Grid Lines -->
    <div class="grid-lines pointer-events-none fixed inset-0 z-10"></div>
    
    <!-- Scanlines -->
    <div class="scanlines pointer-events-none fixed inset-0 z-25"></div>
    
    <!-- Vignette Overlay -->
    <div class="vignette pointer-events-none fixed inset-0 z-15"></div>
    
    <!-- Main Container -->
    <div class="creation-container relative z-20">
      <!-- Game Title -->
      <div class="title-wrapper mb-6 text-center">
        <h1 class="game-title relative uppercase" 
          style="font-family: 'Press Start 2P', monospace; font-size: clamp(1.5rem, 6vw, 3rem); text-align: center; display: block;">
          <span class="title-u">U</span><span class="title-colon">:</span><span class="glitch-title" data-text="LIFE">LIFE</span><span class="title-exclaim">!</span>
        </h1>
        <p class="tagline mt-3 text-cyan-300 text-center text-[10px] md:text-[12px] uppercase tracking-[0.35em]"
          style="font-family: 'VT323', monospace; font-size: 16px;">
          ◇ Create Your Character ◇
        </p>
      </div>

      <div class="creation-layout">
        <!-- Left Panel -->
        <div class="left-panel">
          <!-- Character Card Frame -->
          <div class="character-card">
            <!-- Decorative Corners -->
            <div class="card-corner card-corner--tl"></div>
            <div class="card-corner card-corner--tr"></div>
            <div class="card-corner card-corner--bl"></div>
            <div class="card-corner card-corner--br"></div>

            <!-- Character Info -->
            <div class="form-group character-form-group">
              <label class="form-label">
                <span class="label-icon">🎭</span> Character Name
              </label>
              <div class="input-wrapper">
                <span class="input-prefix">✦</span>
                <input type="text" v-model="character.name" placeholder="Enter your character's name" class="form-input" />
              </div>
            </div>

            <div class="form-group character-form-group">
              <label class="form-label">
                <span class="label-icon">📅</span> Choose Age Group
              </label>
              <div class="select-wrapper">
                <span class="input-prefix">✦</span>
                <select v-model="character.ageGroup" @change="applyAgeBonus" class="form-select">
                  <option value="None">None</option>
                  <option value="child">Child</option>
                  <option value="teenager">Teenager</option>
                  <option value="adult">Adult</option>
                  <option value="old">Old</option>
                </select>
              </div>
            </div>

            <div class="form-group character-form-group">
              <label class="form-label">
                <span class="label-icon">⚥</span> Choose Gender
              </label>
              <div class="select-wrapper">
                <span class="input-prefix">✦</span>
                <select v-model="character.gender" @change="applyGenderBonus" class="form-select">
                  <option value="None">None</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="non-binary">Non-Binary</option>
                  <option value="transgender">Transgender</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Stat Allocation -->
          <div class="stats-container">
            <div class="stats-header">
              <h3 class="stats-title">Allocate Stats <span class="points-remaining">(Remaining: {{ remainingPoints }})</span></h3>
              <button class="diagram-toggle" @click="toggleDiagram">
                {{ showDiagram ? 'Hide' : 'Show' }} Diagram
              </button>
            </div>

            <div class="stats-grid">
              <div v-for="(value, stat) in character.stats" :key="stat" class="stat-row">
                <label class="stat-label">
                  <span class="stat-icon">{{ getStatIcon(stat) }}</span>{{ stat }}
                </label>
                <div class="stat-controls">
                  <button class="stat-btn stat-btn--minus" @click="decreaseStat(stat)">
                    <span class="btn-glow"></span>−
                  </button>
                  <span class="stat-value">{{ effectiveStats.visible[stat] }}</span>
                  <button class="stat-btn stat-btn--plus" @click="increaseStat(stat)">
                    <span class="btn-glow"></span>+
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
          <!-- Tip Message -->
          <div class="tip-banner">
            <span class="tip-icon">💡</span>
            <span class="tip-text">You can choose more than one skill and talent!</span>
          </div>

          <!-- Skills Carousel -->
          <div class="skills-container">
            <h3 class="carousel-title glitch-title" data-text="✧ CHOOSE SKILLS ✧">
              ✧ CHOOSE SKILLS ✧
            </h3>
            <div class="card-carousel" @mouseenter="pauseCarousels" @mouseleave="resumeCarousels">
              <button class="arrow-button" @click="prevSkillCard">
                <v-icon size="22">mdi-chevron-left</v-icon>
              </button>
              <div class="card-track">
                <div v-for="(skill, index) in visibleSkills" :key="skill.name" class="skill-card"
                  :class="{ selected: character.skills.includes(skill), 'center-card': index === 2 }"
                  @click="toggleSkill(skill)">
                  <div class="card-visual">
                    <img :src="skill.image" alt="skill image" class="card-image" />
                  </div>
                  <div class="info-icon">
                    ℹ
                    <div class="info-popup">
                      <span>{{ skill.description }}</span>
                    </div>
                  </div>
                  <div class="card-content">
                    <h4 class="card-name">{{ skill.name }}</h4>
                    <p class="card-effect">{{ skill.effect }}</p>
                  </div>
                </div>
              </div>
              <button class="arrow-button" @click="nextSkillCard">
                <v-icon size="22">mdi-chevron-right</v-icon>
              </button>
            </div>
          </div>

          <!-- Talents Carousel -->
          <div class="talents-container">
            <h3 class="carousel-title glitch-title" data-text="✧ CHOOSE TALENTS ✧">
              ✧ CHOOSE TALENTS ✧
            </h3>
            <div class="card-carousel" @mouseenter="pauseCarousels" @mouseleave="resumeCarousels">
              <button class="arrow-button" @click="prevTalentCard">
                <v-icon size="22">mdi-chevron-left</v-icon>
              </button>
              <div class="card-track">
                <div v-for="(talent, index) in visibleTalents" :key="talent.name" class="talent-card"
                  :class="{ selected: character.talents.includes(talent), 'center-card': index === 2 }"
                  @click="toggleTalent(talent)">
                  <div class="card-visual">
                    <img :src="talent.image" alt="talent image" class="card-image" />
                  </div>
                  <div class="info-icon">
                    ℹ
                    <div class="info-popup">
                      <span>{{ talent.description }}</span>
                    </div>
                  </div>
                  <div class="card-content">
                    <h4 class="card-name">{{ talent.name }}</h4>
                    <p class="card-effect">{{ talent.effect }}</p>
                  </div>
                </div>
              </div>
              <button class="arrow-button" @click="nextTalentCard">
                <v-icon size="22">mdi-chevron-right</v-icon>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Skills & Talents Display (Below Panels) -->
      <div class="active-display-container">
        <!-- Active Skills Section -->
        <div class="active-section">
          <h3 class="active-title">
            Active Skills ({{ character.skills.length }})
          </h3>
          <div class="active-list">
            <div v-if="character.skills.length === 0" class="no-items">
              No skills selected yet
            </div>
            <div 
              v-for="skill in character.skills" 
              :key="skill.name" 
              class="active-box skill-box"
              @click="toggleSkill(skill)"
            >
              {{ skill.name }}
            </div>
          </div>
        </div>

        <!-- Active Talents Section -->
        <div class="active-section">
          <h3 class="active-title">
            Active Talents ({{ character.talents.length }})
          </h3>
          <div class="active-list">
            <div v-if="character.talents.length === 0" class="no-items">
              No talents selected yet
            </div>
            <div 
              v-for="talent in character.talents" 
              :key="talent.name" 
              class="active-box talent-box"
              @click="toggleTalent(talent)"
            >
              {{ talent.name }}
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Popup -->
      <div v-if="showDiagram" class="modal-overlay" @click.self="toggleDiagram">
        <div class="modal-content">
          <!-- Animated rotating rings -->
          <div class="ring-effect"></div>
          <div class="ring-effect"></div>
          
          <!-- Chart Container -->
          <div class="chart-container">
            <canvas ref="statChart"></canvas>
          </div>
          
          <!-- Constellation Legend -->
          <div class="constellation-legend" v-if="Object.keys(genderBonus).length > 0 || Object.keys(ageBonus).length > 0">
            <div class="constellation-legend-item" v-if="Object.keys(genderBonus).length > 0">
              <span class="marker gender"></span>
              <span>Gender Bonus</span>
            </div>
            <div class="constellation-legend-item" v-if="Object.keys(ageBonus).length > 0">
              <span class="marker age"></span>
              <span>Age Bonus</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Button Bar -->
      <div class="button-bar">
        <v-btn
          size="large"
          class="start-btn"
          @click="finalizeCharacter"
          :loading="isSaving"
          prepend-icon="mdi-play"
        >
          {{ isSaving ? 'Creating...' : 'Start Game' }}
        </v-btn>
        <v-btn
          size="large"
          variant="outlined"
          color="error"
          class="logout-btn"
          @click="quitGame"
          :disabled="isSaving"
          prepend-icon="mdi-logout"
        >
          Logout
        </v-btn>
      </div>
    </div>

    <!-- Popup -->
    <div v-if="showPopup" class="popup-overlay" @click.self="showPopup = false">
      <div class="popup-box" :class="`popup-box--${popupVariant}`">
        <v-img src="/css/images/ulife1.png" alt="U:LIFE Logo" max-width="120" contain class="popup-icon" />
        <div class="popup-badge" aria-hidden="true">
          <v-icon size="24">{{ popupIcon }}</v-icon>
        </div>
        <h3>{{ popupTitle }}</h3>
        <p>{{ popupMessage }}</p>
        <v-btn class="popup-btn" @click="showPopup = false">OK</v-btn>
      </div>
    </div>
  </div>
</template>


<script>
import Chart from 'chart.js/auto';
import axios from 'axios';
export default {
  data() {
    return {
      totalPoints: 50,
      isSaving: false,
      character: {
        name: "",
        ageGroup: "None",
        gender: "None",
        stats: {
          Intelligence: 0,
          Strength: 0,
          Charisma: 0,
          Creativity: 0,
          Wealth: 0,
          Luck: 0,
          Social: 0,
          Empathy: 0
        },
        hiddenStats: {
          Debt: 0,
          Health: 78,
          Addiction: 0,
          Burnout: 5,
          Morality: 45,
          Happiness: 72,
          Reputation: 35,
          Discipline: 40,
          Isolation: 6,
          Ego: 10,
        },
        skills: [],
        talents: []
      },
      genderBonus: {},
      ageBonus: {},
      showDiagram: false,
      chart: null,
      skills: [
        { name: "Reading", effect: "+3 Intelligence, +1 Creativity", hidden: "+1 Isolation", description: "Expand your mind through books. Builds knowledge but can make you retreat inward.", image: "/css/images/skill/reading.png" },
        { name: "Studying", effect: "+4 Intelligence, +1 Discipline", hidden: "+1 Stress", description: "Sharpen your intellect with focused learning. Boosts academics but can be mentally taxing.", image: "/css/images/skill/studying.png" },
        { name: "Problem-Solving", effect: "+3 Intelligence, +2 Creativity", hidden: "+1 Burnout", description: "Tackle challenges with logic and innovation. Enhances adaptability but constant puzzles can wear you down.", image: "/css/images/skill/problem-solving.png" },
        { name: "Memory Training", effect: "+3 Intelligence, +1 Luck", hidden: "+1 Stress", description: "Improve recall and retention. Strengthens intellect but can overload your mind.", image: "/css/images/skill/memory-training.png" },
        { name: "Fitness", effect: "+4 Strength, +1 Health", hidden: "+1 Discipline", description: "Train your body for endurance and resilience. Improves vitality but demands consistency.", image: "/css/images/skill/fitness.png" },
        { name: "Endurance", effect: "+3 Strength, +2 Discipline", hidden: "+1 Burnout", description: "Push your body past limits. Builds stamina but risks exhaustion.", image: "/css/images/skill/endurance.png" },
        { name: "Manual Labor", effect: "+3 Strength", hidden: "+, +1 Wealth1 Debt", description: "Work with your hands to earn and build. Strengthens body and finances but can trap you in exhausting cycles.", image: "/css/images/skill/manual-labor.png" },
        { name: "Cooking", effect: "+3 Creativity, +2 Health", hidden: "+1 Stress", description: "Prepare meals and nourish yourself. Builds independence but can be tiring.", image: "/css/images/skill/cooking.png" },
        { name: "Conversation", effect: "+4 Charisma, +1 Reputation", hidden: "+1 Stress", description: "Engage others with words and presence. Builds influence but constant interaction can drain energy.", image: "/css/images/skill/conversation.png" },
        { name: "Confidence", effect: "+3 Charisma, +2 Ego", hidden: "+1 Isolation", description: "Believe in yourself and project strength. Inspires others but can push you toward arrogance.", image: "/css/images/skill/confidence.png" },
        { name: "Teamwork", effect: "+3 Charisma, +2 Discipline", hidden: "+1 Burnout", description: "Collaborate effectively with groups. Builds trust but can lead to overreliance on others.", image: "/css/images/skill/teamwork.png" },
        { name: "Persuasion", effect: "+3 Charisma, +2 Intelligence", hidden: "+1 Ego", description: "Convince others to see your way. Builds influence but risks manipulation.", image: "/css/images/skill/persuasion.png" },
        { name: "Public Speaking", effect: "+4 Charisma, +1 Morality", hidden: "+1 Stress", description: "Address crowds with confidence. Builds leadership but can be nerve-wracking.", image: "/css/images/skill/public-speaking.png" },
        { name: "Drawing", effect: "+4 Creativity, +1 Happiness", hidden: "+1 Isolation", description: "Express ideas visually. Sparks joy but can isolate you in your own world.", image: "/css/images/skill/drawing.png" },
        { name: "Writing", effect: "+3 Creativity, +2 Intelligence", hidden: "+1 Stress", description: "Craft stories and ideas with words. Builds intellect but can be mentally draining.", image: "/css/images/skill/writing.png" },
        { name: "Improvisation", effect: "+3 Creativity, +2 Charisma", hidden: "+1 Stress", description: "Think on your feet and adapt. Builds flexibility but can be chaotic.", image: "/css/images/skill/improvisation.png" },
        { name: "Music", effect: "+4 Creativity, +1 Charisma", hidden: "+1 Isolation", description: "Play or compose music. Builds joy and influence but can isolate you in practice.", image: "/css/images/skill/music.png" },
        { name: "Budgeting", effect: "+4 Wealth, +1 Discipline", hidden: "+1 Stress", description: "Manage money wisely. Builds financial stability but constant tracking can be tiring.", image: "/css/images/skill/budgeting.png" },
        { name: "Negotiation", effect: "+3 Wealth, +2 Charisma", hidden: "+1 Ego", description: "Strike deals and gain advantage. Improves finances but can inflate self-importance.", image: "/css/images/skill/negotiation.png" },
        { name: "Planning", effect: "+3 Wealth, +2 Intelligence", hidden: "+1 Burnout", description: "Organize steps toward success. Builds foresight but can lead to overthinking.", image: "/css/images/skill/planning.png" },
        { name: "Risk Awareness", effect: "+3 Luck, +1 Intelligence", hidden: "+1 Stress", description: "Sense opportunities and dangers. Builds adaptability but can make you overly cautious.", image: "/css/images/skill/risk-awareness.png" },
        { name: "Adaptability", effect: "+4 Luck, +1 Creativity", hidden: "+1 Burnout", description: "Adjust quickly to change. Enhances resilience but constant shifting can wear you down.", image: "/css/images/skill/adaptability.png" },
        { name: "Opportunism", effect: "+3 Luck, +2 Wealth", hidden: "+1 Morality", description: "Seize chances when they appear. Builds success but can compromise ethics.", image: "/css/images/skill/opportunism.png" },
        { name: "Intuition", effect: "+3 Luck, +2 Creativity", hidden: "+1 Stress", description: "Trust your gut instincts. Builds quick decision-making but can be unreliable.", image: "/css/images/skill/intuition.png" },
        { name: "Gardening", effect: "+3 Creativity, +2 Health", hidden: "+1 Isolation", description: "Cultivate plants and nature. Builds patience and wellness but can be solitary.", image: "/css/images/skill/gardening.png" },
        { name: "Cooking Basics", effect: "+3 Creativity, +2 Health", hidden: "+1 Stress", description: "Prepare simple meals. Builds independence but can be tiring.", image: "/css/images/skill/cooking-basics.png" },
        { name: "Cleaning", effect: "+3 Discipline, +2 Health", hidden: "+1 Stress", description: "Maintain order and hygiene. Builds discipline but can feel repetitive.", image: "/css/images/skill/cleaning.png" },
        { name: "Driving", effect: "+3 Luck, +2 Intelligence", hidden: "+1 Stress", description: "Operate vehicles safely. Builds independence but can be risky.", image: "/css/images/skill/driving.png" },
        { name: "Swimming", effect: "+4 Strength, +1 Health", hidden: "+1 Stress", description: "Move confidently in water. Builds fitness but requires effort.", image: "/css/images/skill/swimming.png" },
        { name: "Meditation", effect: "+3 Discipline, +2 Morality", hidden: "-1 Stress", description: "Calm the mind and body. Builds focus but reduces spontaneity.", image: "/css/images/skill/meditation.png" },
        { name: "Basic First Aid", effect: "+3 Intelligence, +2 Health", hidden: "+1 Stress", description: "Treat minor injuries. Builds resilience but can be emotionally taxing.", image: "/css/images/skill/basic-first-aid.png" },
        { name: "Crafting", effect: "+4 Creativity, +1 Discipline", hidden: "+1 Stress", description: "Create useful items by hand. Builds innovation but requires patience.", image: "/css/images/skill/crafting.png" },
        { name: "Storytelling", effect: "+3 Charisma, +2 Creativity", hidden: "+1 Stress", description: "Captivate others with tales. Builds influence but can drain energy.", image: "/css/images/skill/storytelling.png" },
        { name: "Observation", effect: "+3 Intelligence, +2 Luck", hidden: "+1 Stress", description: "Notice details others miss. Builds awareness but can cause overthinking.", image: "/css/images/skill/observation.png" },
        { name: "Basic Math", effect: "+4 Intelligence, +1 Wealth", hidden: "+1 Stress", description: "Handle numbers and calculations. Builds problem-solving but can be tedious.", image: "/css/images/skill/basic-math.png" },
        { name: "Organization", effect: "+3 Discipline, +2 Intelligence", hidden: "+1 Stress", description: "Keep things structured and efficient. Builds stability but reduces flexibility.", image: "/css/images/skill/organization.png" },
        { name: "Negotiation Basics", effect: "+3 Wealth, +2 Charisma", hidden: "+1 Ego", description: "Find compromises and deals. Builds financial gain but risks manipulation.", image: "/css/images/skill/negotiation-basics.png" },
        { name: "Survival Skills", effect: "+4 Strength, +1 Luck", hidden: "+1 Stress", description: "Endure harsh conditions. Builds resilience but can be dangerous.", image: "/css/images/skill/survival.png" },
        { name: "Listening", effect: "+3 Charisma, +2 Morality", hidden: "+1 Stress", description: "Pay attention to others deeply. Builds trust but can be emotionally draining.", image: "/css/images/skill/listening.png" },
        { name: "Time Management", effect: "+3 Discipline, +2 Wealth", hidden: "+1 Stress", description: "Balance priorities effectively. Builds productivity but can feel rigid.", image: "/css/images/skill/time-management.png" },
        { name: "Basic Technology", effect: "+3 Intelligence, +2 Creativity", hidden: "+1 Stress", description: "Use everyday devices. Builds adaptability but can be frustrating.", image: "/css/images/skill/basic-technology.png" },
        { name: "Negotiation Advanced", effect: "+4 Wealth, +1 Charisma", hidden: "+1 Ego", description: "Master complex deals. Builds influence but risks arrogance.", image: "/css/images/skill/negotiation-advanced.png" },
        { name: "Physical Training", effect: "+4 Strength, +1 Discipline", hidden: "+1 Burnout", description: "Condition your body systematically. Builds fitness but risks fatigue.", image: "/css/images/skill/physical-training.png" },
        { name: "Basic Language", effect: "+3 Intelligence, +2 Charisma", hidden: "+1 Stress", description: "Learn new words and phrases. Builds communication but can be challenging.", image: "/css/images/skill/basic-language.png" }
      ],
      talents:[
        { name: "Resilience", effect: "+5 Strength, -2 Creativity", hidden: "-1 Stress", description: "Able to endure hardships and bounce back stronger.", image: "/css/images/talent/resilience.png" },
        { name: "Empathy", effect: "+5 Morality, -2 Luck", hidden: "-1 Stress", description: "Deeply attuned to others' emotions, fostering compassion.", image: "/css/images/talent/empathy.png" },
        { name: "Ambition", effect: "+5 Charisma, -3 Health", hidden: "+2 Stress, +1 Debt", description: "Driven to succeed, but ambition often comes at personal cost.", image: "/css/images/talent/ambition.png" },
        { name: "Honesty", effect: "+5 Morality, -2 Wealth", hidden: "-1 Corruption", description: "Guided by truth, even when it costs opportunities.", image: "/css/images/talent/honesty.png" },
        { name: "Charm", effect: "+5 Charisma, -2 Intelligence", hidden: "+1 Stress", description: "Naturally persuasive and likable, but sometimes superficial.", image: "/css/images/talent/charm.png" },
        { name: "Patience", effect: "+4 Morality, +2 Intelligence", hidden: "-1 Stress", description: "Able to wait calmly, reducing conflict and mistakes.", image: "/css/images/talent/patience.png" },
        { name: "Courage", effect: "+5 Strength, -2 Luck", hidden: "+2 Stress", description: "Bravery in the face of danger, but risk of harm is higher.", image: "/css/images/talent/courage.png" },
        { name: "Integrity", effect: "+5 Morality, -2 Wealth", hidden: "-1 Corruption", description: "Strong moral compass, but may miss financial gain.", image: "/css/images/talent/integrity.png" },
        { name: "Optimism", effect: "+4 Luck, +2 Charisma", hidden: "+1 Stress", description: "Positive outlook boosts morale, but can ignore risks.", image: "/css/images/talent/optimism.png" },
        { name: "Focus", effect: "+5 Intelligence, -2 Luck", hidden: "+1 Stress", description: "Laser-sharp concentration improves performance, but reduces spontaneity.", image: "/css/images/talent/focus.png" },
        { name: "Generosity", effect: "+4 Morality, +2 Charisma", hidden: "+1 Debt", description: "Willingness to give builds goodwill, but risks financial strain.", image: "/css/images/talent/generosity.png" },
        { name: "Creativity Spark", effect: "+5 Creativity, -2 Discipline", hidden: "+1 Fatigue", description: "Natural imagination fuels innovation, but can lack structure.", image: "/css/images/talent/creativity-spark.png" },
        { name: "Confidence", effect: "+5 Charisma, -2 Morality", hidden: "+1 Stress", description: "Self-assurance inspires others, but can lead to arrogance.", image: "/css/images/talent/confidence.png" },
        { name: "Wisdom", effect: "+5 Intelligence, +2 Morality", hidden: "-1 Stress", description: "Life experience guides decisions, but may slow adaptability.", image: "/css/images/talent/wisdom.png" },
        { name: "Humor", effect: "+4 Charisma, +2 Luck", hidden: "+1 Stress", description: "Lightheartedness builds bonds, but can mask deeper issues.", image: "/css/images/talent/humor.png" },
        { name: "Determination", effect: "+5 Strength, +2 Intelligence", hidden: "+2 Stress", description: "Unyielding drive achieves goals, but risks burnout.", image: "/css/images/talent/determination.png" },
        { name: "Compassion", effect: "+4 Morality, +2 Health", hidden: "+1 Stress", description: "Caring nature heals others, but emotional burden is heavy.", image: "/css/images/talent/compassion.png" },
        { name: "Curiosity", effect: "+5 Intelligence, +2 Creativity", hidden: "+1 Stress", description: "Natural desire to learn expands horizons, but can be distracting.", image: "/css/images/talent/curiosity.png" },
        { name: "Discipline of Mind", effect: "+5 Intelligence, -2 Luck", hidden: "+1 Stress", description: "Mental rigor improves focus, but reduces spontaneity.", image: "/css/images/talent/discipline-mind.png" },
        { name: "Charisma Aura", effect: "+6 Charisma, -2 Intelligence", hidden: "+2 Stress", description: "Magnetic personality draws people in, but can be exhausting.", image: "/css/images/talent/charisma-aura.png" },
        { name: "Stoicism", effect: "+5 Morality, +2 Strength", hidden: "-2 Stress", description: "Calm endurance of hardship builds resilience, but reduces emotional expression.", image: "/css/images/talent/stoicism.png" },
        { name: "Visionary", effect: "+5 Creativity, +2 Intelligence", hidden: "+2 Stress", description: "Sees possibilities others miss, but risks impracticality.", image: "/css/images/talent/visionary.png" },
        { name: "Loyalty", effect: "+4 Morality, +2 Charisma", hidden: "-1 Stress", description: "Faithful to allies, but can be exploited.", image: "/css/images/talent/loyalty.png" },
        { name: "Pragmatism", effect: "+5 Intelligence, -2 Morality", hidden: "+1 Stress", description: "Focus on practical solutions, but risks ethical compromise.", image: "/css/images/talent/pragmatism.png" },
        { name: "Tenacity", effect: "+5 Strength, +2 Luck", hidden: "+2 Stress", description: "Never gives up, but risks stubbornness.", image: "/css/images/talent/tenacity.png" },
        { name: "Diplomacy", effect: "+5 Charisma, +2 Morality", hidden: "+1 Stress", description: "Skilled at peacekeeping, but emotionally draining.", image: "/css/images/talent/diplomacy.png" },
        { name: "Inventiveness", effect: "+5 Creativity, -2 Wealth", hidden: "+1 Fatigue", description: "Natural knack for innovation, but often financially risky.", image: "/css/images/talent/inventiveness.png" },
        { name: "Self-Reliance", effect: "+5 Strength, +2 Intelligence", hidden: "+1 Stress", description: "Independent and resourceful, but risks isolation.", image: "/css/images/talent/self-reliance.png" },
        { name: "Adaptability Trait", effect: "+4 Luck, +2 Creativity", hidden: "+1 Stress", description: "Naturally flexible in changing environments, but can feel unstable.", image: "/css/images/talent/adaptability-trait.png" },
        { name: "Altruism", effect: "+5 Morality, -2 Wealth", hidden: "+1 Fatigue", description: "Selfless concern for others, but drains personal resources.", image: "/css/images/talent/altruism.png" },
        { name: "Honesty", effect: "+5 Morality, -2 Wealth", hidden: "-1 Corruption", description: "Guided by truth, even when it costs opportunities.", image: "/css/images/talent/honesty.png" },
        { name: "Patience", effect: "+4 Morality, +2 Intelligence", hidden: "-1 Stress", description: "Able to wait calmly, reducing conflict and mistakes.", image: "/css/images/talent/patience.png" },
        { name: "Courage", effect: "+5 Strength, -2 Luck", hidden: "+2 Stress", description: "Bravery in the face of danger, but risk of harm is higher.", image: "/css/images/talent/courage.png" },
        { name: "Focus", effect: "+5 Intelligence, -2 Luck", hidden: "+1 Stress", description: "Laser-sharp concentration improves performance, but reduces spontaneity.", image: "/css/images/talent/focus.png" },
        { name: "Generosity", effect: "+4 Morality, +2 Charisma", hidden: "+1 Debt", description: "Willingness to give builds goodwill, but risks financial strain.", image: "/css/images/talent/generosity.png" },
        { name: "Wisdom", effect: "+5 Intelligence, +2 Morality", hidden: "-1 Stress", description: "Life experience guides decisions, but may slow adaptability.", image: "/css/images/talent/wisdom.png" },
        { name: "Discipline of Mind", effect: "+5 Intelligence, -2 Luck", hidden: "+1 Stress", description: "Mental rigor improves focus, but reduces spontaneity.", image: "/css/images/talent/discipline-mind.png" },
        { name: "Charisma Aura", effect: "+6 Charisma, -2 Intelligence", hidden: "+2 Stress", description: "Magnetic personality draws people in, but can be exhausting.", image: "/css/images/talent/charisma-aura.png" },
        { name: "Stoicism", effect: "+5 Morality, +2 Strength", hidden: "-2 Stress", description: "Calm endurance of hardship builds resilience, but reduces emotional expression.", image: "/css/images/talent/stoicism.png" },
        { name: "Visionary", effect: "+5 Creativity, +2 Intelligence", hidden: "+2 Stress", description: "Sees possibilities others miss, but risks impracticality.", image: "/css/images/talent/visionary.png" },
        { name: "Loyalty", effect: "+4 Morality, +2 Charisma", hidden: "-1 Stress", description: "Faithful to allies, but can be exploited.", image: "/css/images/talent/loyalty.png" },
        { name: "Pragmatism", effect: "+5 Intelligence, -2 Morality", hidden: "+1 Stress", description: "Focus on practical solutions, but risks ethical compromise.", image: "/css/images/talent/pragmatism.png" },
        { name: "Diplomacy", effect: "+5 Charisma, +2 Morality", hidden: "+1 Stress", description: "Skilled at peacekeeping, but emotionally draining.", image: "/css/images/talent/diplomacy.png" },
        { name: "Inventiveness", effect: "+5 Creativity, -2 Wealth", hidden: "+1 Fatigue", description: "Natural knack for innovation, but often financially risky.", image: "/css/images/talent/inventiveness.png" }
      ],
      skillIndex: 0,
      talentIndex: 0,
      itemsPerPage: 5,
      showPopup: false,
      popupMessage: "",
      carouselInterval: null,
      isCarouselPaused: false
    };
  },
  computed: {
    remainingPoints() {
      return this.totalPoints - Object.values(this.character.stats).reduce((a, b) => a + b, 0);
    },
    popupVariant() {
      const msg = String(this.popupMessage || '').trim();
      if (msg.startsWith('✓')) return 'success';
      if (msg.startsWith('✗')) return 'error';
      if (msg.startsWith('⚠')) return 'warn';
      return 'info';
    },
    popupIcon() {
      const map = {
        success: 'mdi-check-circle-outline',
        error: 'mdi-alert-circle-outline',
        warn: 'mdi-alert-outline',
        info: 'mdi-information-outline',
      };
      return map[this.popupVariant] || map.info;
    },
    popupTitle() {
      const map = {
        success: 'Success',
        error: 'Error',
        warn: 'Notice',
        info: 'Notice',
      };
      return map[this.popupVariant] || 'Notice';
    },
    effectiveStats() {
      let stats = { ...this.character.stats };
      let hidden = { ...this.character.hiddenStats };

      // Apply gender bonus
      Object.entries(this.genderBonus).forEach(([stat, val]) => {
        if (stats[stat] !== undefined) stats[stat] += val;
      });

      // Apply age bonus
      Object.entries(this.ageBonus).forEach(([stat, val]) => {
        if (stats[stat] !== undefined) stats[stat] += val;
      });

      // Apply skill/talent effects
      this.character.skills.forEach(skill => {
        if (skill.effect) this.applyEffect(stats, skill.effect);
      });
      this.character.talents.forEach(talent => {
        if (talent.effect) this.applyEffect(stats, talent.effect);
      });

      return { visible: stats, hidden: hidden };
    },
    visibleSkills() {
      const looped = [...this.skills, ...this.skills];
      return looped.slice(this.skillIndex, this.skillIndex + this.itemsPerPage);
    },
    visibleTalents() {
      const looped = [...this.talents, ...this.talents];
      return looped.slice(this.talentIndex, this.talentIndex + this.itemsPerPage);
    }
  },
  mounted() {
    // Start carousel auto-play (3 second interval)
    this.carouselInterval = setInterval(() => {
      if (!this.isCarouselPaused) {
        this.nextSkillCard();
        this.nextTalentCard();
      }
    }, 3000);
  },
  beforeDestroy() {
    // Clean up carousel interval
    if (this.carouselInterval) {
      clearInterval(this.carouselInterval);
    }
  },
  methods: {
    // Carousel auto-play controls
    pauseCarousels() {
      this.isCarouselPaused = true;
    },
    resumeCarousels() {
      this.isCarouselPaused = false;
    },
    // Get stat icon mapping
    getStatIcon(stat) {
      const icons = {
        'Intelligence': '🧠',
        'Strength': '💪',
        'Charisma': '💬',
        'Creativity': '🎨',
        'Wealth': '💰',
        'Luck': '🍀',
        'Health': '❤️',
        'Morality': '⚖️',
        'Discipline': '🎯'
      };
      return icons[stat] || '⭐';
    },

    // Generate galaxy star positions with varied colors
    getStarStyle(n) {
      const colors = [
        '#00ffcc', // cyan
        '#ff00ff', // magenta/purple
        '#00ff88', // green
        '#ffcc00', // yellow
        '#88ccff', // light blue
        '#ff88ff', // pink
        '#ffffff', // white
        '#aaff00', // lime
      ]
      const color = colors[n % colors.length]
      const left = Math.random() * 100
      const top = Math.random() * 100
      const size = Math.random() * 3 + 1
      const duration = Math.random() * 5 + 3
      const delay = Math.random() * 4
      const opacity = Math.random() * 0.5 + 0.3
      return `left: ${left}%; top: ${top}%; width: ${size}px; height: ${size}px; background: ${color}; box-shadow: 0 0 ${size * 2}px ${color}; opacity: ${opacity}; animation-duration: ${duration}s; animation-delay: ${delay}s;`
    },

    getEnergyOrbStyle(n) {
      const colors = [
        'rgba(0, 255, 204, 0.6)',
        'rgba(255, 0, 255, 0.5)',
        'rgba(138, 43, 226, 0.5)',
        'rgba(0, 206, 209, 0.6)',
      ];
      const color = colors[n % colors.length];
      const left = Math.random() * 100;
      const top = Math.random() * 100;
      const size = Math.random() * 12 + 6;
      const duration = Math.random() * 8 + 6;
      const delay = Math.random() * 6;
      const xMove = Math.random() * 100 - 50;
      const yMove = Math.random() * 100 - 50;
      return `left: ${left}%; top: ${top}%; width: ${size}px; height: ${size}px; background: ${color}; box-shadow: 0 0 ${size}px ${color}, 0 0 ${size * 2}px ${color}; animation: float-orb ${duration}s ease-in-out infinite, orb-glow ${duration * 0.5}s ease-in-out infinite; animation-delay: ${delay}s, ${delay}s; --x-move: ${xMove}px; --y-move: ${yMove}px;`;
    },

    getShootingStarStyle(n) {
      const colors = ['#00ffcc', '#ff00ff', '#ffffff', '#00ccff'];
      const color = colors[n % colors.length];
      const left = Math.random() * 80 + 10;
      const top = Math.random() * 60;
      const duration = Math.random() * 3 + 2;
      const delay = Math.random() * 15 + (n * 3);
      const length = Math.random() * 150 + 100;
      return `left: ${left}%; top: ${top}%; background: linear-gradient(90deg, ${color}, transparent); width: ${length}px; height: 2px; animation: shoot ${duration}s linear infinite; animation-delay: ${delay}s; box-shadow: 0 0 10px ${color};`;
    },

    getParticleStyle(n) {
      const random = (min, max) => Math.random() * (max - min) + min;
      return {
        left: random(0, 100) + '%',
        top: random(0, 100) + '%',
        animationDelay: random(0, 5) + 's',
        animationDuration: random(3, 8) + 's',
        width: random(2, 6) + 'px',
        height: random(2, 6) + 'px',
        opacity: random(0.3, 0.8)
      };
    },

    applyEffect(stats, effect) {
      const parts = effect.split(",");
      parts.forEach(part => {
        const [signVal, stat] = part.trim().split(" ");
        const value = parseInt(signVal);
        if (stats[stat] !== undefined) stats[stat] += value;
      });
    },

    applyGenderBonus() {
      this.genderBonus = {};
      switch (this.character.gender) {
        case "male":
          this.genderBonus = { Strength: 8, Wealth: 3 };
          break;
        case "female":
          this.genderBonus = { Intelligence: 5, Charisma: 5 };
          break;
        case "non-binary":
          this.genderBonus = { Creativity: 8, Luck: 5 };
          break;
        case "transgender":
          this.genderBonus = { Charisma: 8, Intelligence: 5 };
          break;
        default:
          this.genderBonus = {};
      }
      this.updateChart();
    },

    applyAgeBonus() {
      this.ageBonus = {};
      switch (this.character.ageGroup) {
        case "child":
          // Young children have high potential - bonus to learning stats
          this.ageBonus = { Luck: 8, Creativity: 8, Intelligence: 5 };
          break;
        case "teenager":
          // Teenagers are developing physically and mentally
          this.ageBonus = { Strength: 8, Intelligence: 8, Charisma: 5 };
          break;
        case "adult":
          // Adults are in their prime - balanced strength
          this.ageBonus = { Strength: 10, Intelligence: 8, Wealth: 5 };
          break;
        case "old":
          // Elderly have wisdom but declining physical stats
          this.ageBonus = { Intelligence: 15, Charisma: 8, Strength: -8 };
          break;
        default:
          this.ageBonus = {};
      }
      this.updateChart();
    },

    increaseStat(stat) {
      if (this.character.stats[stat] >= 30) {
        this.popupMessage = "⚠ MAX 30 allocation per stat!";
        this.showPopup = true;
        return;
      }
      if (this.remainingPoints > 0) {
        this.character.stats[stat]++;
        this.updateChart();
      }
    },

    decreaseStat(stat) {
      if (this.character.stats[stat] > 0) {
        this.character.stats[stat]--;
        this.updateChart();
      }
    },

    toggleSkill(skill) {
      const index = this.character.skills.indexOf(skill);
      if (index > -1) this.character.skills.splice(index, 1);
      else this.character.skills.push(skill);
      this.updateChart();
    },
    toggleTalent(talent) {
      const index = this.character.talents.indexOf(talent);
      if (index > -1) this.character.talents.splice(index, 1);
      else this.character.talents.push(talent);
      this.updateChart();
    },

    toggleDiagram() {
      this.showDiagram = !this.showDiagram;
      if (this.showDiagram) {
        this.$nextTick(() => this.createChart());
      } else {
        if (this.chart) { this.chart.destroy(); this.chart = null; }
      }
    },

    createChart() {
      const canvas = this.$refs.statChart;
      if (!canvas) {
        console.error("Canvas not found!");
        return;
      }
      const ctx = canvas.getContext("2d");

      if (this.chart) this.chart.destroy();

      // Define per-stat colors - super vibrant neon colors
      const statColors = [
        '#22c55e',   // Intelligence - Bright Green
        '#ef4444',    // Strength - Bright Red
        '#fbbf24',   // Charisma - Golden Yellow
        '#a855f7',   // Creativity - Purple
        '#3b82f6',   // Wealth - Blue
        '#ec4899',   // Luck - Hot Pink
      ];

      // Get current effective stats including bonuses
      const stats = Object.values(this.effectiveStats.visible);
      const labels = Object.keys(this.character.stats);

      // Create outer animated rings data (shows max possible with bonuses)
      const maxStats = stats.map(s => Math.min(100, s + 20));

      // Create gender/age bonus markers
      const bonusMarkers = [];
      if (Object.keys(this.genderBonus).length > 0) {
        labels.forEach((label, i) => {
          if (this.genderBonus[label]) {
            bonusMarkers.push({ index: i, value: stats[i] + this.genderBonus[label], type: 'gender' });
          }
        });
      }
      if (Object.keys(this.ageBonus).length > 0) {
        labels.forEach((label, i) => {
          if (this.ageBonus[label]) {
            bonusMarkers.push({ index: i, value: stats[i] + this.ageBonus[label], type: 'age' });
          }
        });
      }

      // Create hexagon grid background effect
      const hexagonBackground = {
        datasets: []
      };

      // Add hexagon ring levels (25%, 50%, 75%, 100%)
      for (let level = 1; level <= 4; level++) {
        hexagonBackground.datasets.push({
          label: '',
          data: Array(8).fill(level * 25),
          backgroundColor: 'transparent',
          borderColor: level === 4 ? 'rgba(0, 255, 204, 0.15)' : 'rgba(0, 255, 204, 0.08)',
          borderWidth: level === 4 ? 2 : 1,
          borderDash: level < 4 ? [2, 4] : [],
          pointRadius: 0,
          fill: false,
          tension: 0,
        });
      }

      // Create multiple layered gradients for the fill
      const gradientOuter = ctx.createRadialGradient(
        canvas.width / 2, canvas.height / 2, 0,
        canvas.width / 2, canvas.height / 2, canvas.width / 2
      );
      gradientOuter.addColorStop(0, 'rgba(34, 197, 94, 0.5)');
      gradientOuter.addColorStop(0.4, 'rgba(34, 197, 94, 0.25)');
      gradientOuter.addColorStop(0.7, 'rgba(34, 197, 94, 0.1)');
      gradientOuter.addColorStop(1, 'rgba(34, 197, 94, 0.02)');

      // Add subtle gradient for inner glow
      const gradientInner = ctx.createRadialGradient(
        canvas.width / 2, canvas.height / 2, 0,
        canvas.width / 2, canvas.height / 2, canvas.width / 3
      );
      gradientInner.addColorStop(0, 'rgba(0, 255, 204, 0.08)');
      gradientInner.addColorStop(1, 'transparent');

      this.chart = new Chart(ctx, {
        type: "radar",
        data: {
          labels: labels,
          datasets: [
            // Hexagon grid background rings
            {
              label: '',
              data: [25, 25, 25, 25, 25, 25, 25, 25],
              backgroundColor: 'transparent',
              borderColor: 'rgba(0, 255, 204, 0.06)',
              borderWidth: 1,
              borderDash: [3, 6],
              pointRadius: 0,
              fill: false,
              tension: 0,
            },
            {
              label: '',
              data: [50, 50, 50, 50, 50, 50, 50, 50],
              backgroundColor: 'transparent',
              borderColor: 'rgba(0, 255, 204, 0.08)',
              borderWidth: 1,
              borderDash: [3, 6],
              pointRadius: 0,
              fill: false,
              tension: 0,
            },
            {
              label: '',
              data: [75, 75, 75, 75, 75, 75, 75, 75],
              backgroundColor: 'transparent',
              borderColor: 'rgba(0, 255, 204, 0.1)',
              borderWidth: 1,
              borderDash: [3, 6],
              pointRadius: 0,
              fill: false,
              tension: 0,
            },
            {
              label: '',
              data: [100, 100, 100, 100, 100, 100, 100, 100],
              backgroundColor: 'transparent',
              borderColor: 'rgba(0, 255, 204, 0.2)',
              borderWidth: 2,
              pointRadius: 0,
              fill: false,
              tension: 0,
            },
            // Main stats polygon with gradient fill
            {
              label: "Your Stats",
              data: stats,
              backgroundColor: gradientOuter,
              borderColor: "#22c55e",
              borderWidth: 4,
              hoverBorderWidth: 6,
              hoverBorderColor: "#4ade80",
              pointBackgroundColor: statColors,
              pointBorderColor: "rgba(255,255,255,0.9)",
              pointBorderWidth: 3,
              pointRadius: 10,
              pointHoverRadius: 15,
              pointStyle: 'circle',
              tension: 0.3,
            },
            // Constellation bonus markers - gender (diamond shape)
            ...(bonusMarkers.filter(m => m.type === 'gender').map(m => ({
              label: 'Gender Bonus',
              data: labels.map((_, i) => i === m.index ? m.value : null),
              pointBackgroundColor: '#ff00ff',
              pointBorderColor: '#ff66ff',
              pointBorderWidth: 2,
              pointRadius: 8,
              pointStyle: 'diamond',
              showLine: false,
            }))),
            // Constellation bonus markers - age (star shape)
            ...(bonusMarkers.filter(m => m.type === 'age').map(m => ({
              label: 'Age Bonus',
              data: labels.map((_, i) => i === m.index ? m.value : null),
              pointBackgroundColor: '#00ccff',
              pointBorderColor: '#66ddff',
              pointBorderWidth: 2,
              pointRadius: 8,
              pointStyle: 'star',
              showLine: false,
            }))),
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: 2000,
            easing: 'easeOutElastic(1, 0.5)',
            animateRotate: true,
            animateScale: true,
          },
          plugins: {
            legend: { 
              display: true,
              position: 'bottom',
              labels: {
                color: '#22c55e',
                font: { size: 14, weight: 'bold', family: 'Instrument Sans' },
                padding: 20,
                usePointStyle: true,
                pointStyle: 'circle',
                filter: function(item) {
                  // Only show main stats label, hide grid lines
                  return item.text === 'Your Stats' || item.text === 'Gender Bonus' || item.text === 'Age Bonus';
                }
              }
            },
            tooltip: { 
              enabled: true,
              backgroundColor: "rgba(15, 23, 42, 0.98)",
              titleColor: "#22c55e",
              bodyColor: "#f0fdf4",
              borderColor: "#22c55e",
              borderWidth: 2,
              padding: 16,
              cornerRadius: 16,
              titleFont: { size: 16, weight: 'bold', family: 'Instrument Sans' },
              bodyFont: { size: 14, family: 'Instrument Sans' },
              displayColors: true,
              boxPadding: 8,
              filter: function(tooltipItem) {
                // Only show tooltip for main stats dataset (index 4 after grid lines)
                return tooltipItem.datasetIndex === 4;
              },
              callbacks: {
                label: function(context) {
                  const value = context.raw;
                  let bonus = '';
                  // Check if there are gender/age bonuses for this stat
                  const statName = context.label;
                  if (this._chart.data.datasets[5]) {
                    const genderBonus = this._chart.data.datasets[5].data[context.dataIndex];
                    if (genderBonus && genderBonus !== value) {
                      bonus = ' (Gender: +' + (genderBonus - value) + ')';
                    }
                  }
                  return ' ' + value + ' pts' + bonus;
                }
              }
            }
          },
          scales: {
            r: {
              beginAtZero: true,
              max: 100,
              min: 0,
              ticks: { 
                display: true,
                color: "rgba(148, 163, 184, 1)",
                backdropColor: "rgba(15, 23, 42, 0.8)",
                font: { size: 12, weight: '700' },
                stepSize: 25,
                showLabelBackdrop: true,
                backdropColor: "rgba(34, 197, 94, 0.2)",
                backdropPadding: 4,
              },
              grid: { 
                color: function(context) {
                  // Different opacity for each grid level
                  if (context.tick?.value === 25) return "rgba(34, 197, 94, 0.08)";
                  if (context.tick?.value === 50) return "rgba(34, 197, 94, 0.12)";
                  if (context.tick?.value === 75) return "rgba(34, 197, 94, 0.16)";
                  if (context.tick?.value === 100) return "rgba(34, 197, 94, 0.25)";
                  return "rgba(34, 197, 94, 0.05)";
                },
                lineWidth: 1,
              },
              angleLines: { 
                color: "rgba(34, 197, 94, 0.15)",
                lineWidth: 1,
              },
              pointLabels: {
                color: "#f0fdf4",
                font: { size: 14, weight: '800', family: 'Instrument Sans' },
                callback: function(label) {
                  // Add icons to labels
                  const icons = {
                    'Intelligence': '🧠',
                    'Strength': '💪',
                    'Charisma': '🗣️',
                    'Creativity': '🎨',
                    'Wealth': '💰',
                    'Luck': '🍀',
                    'Social': '👥',
                    'Empathy': '❤️'
                  };
                  return icons[label] ? icons[label] + ' ' + label : label;
                }
              }
            }
          }
        }
      });

      // Mobile responsive chart adjustments
      const isMobile = window.innerWidth <= 768;
      const isSmallMobile = window.innerWidth <= 480;
      
      if (isMobile || isSmallMobile) {
        this.chart.options.plugins.legend.labels.font.size = 11;
        this.chart.options.plugins.legend.labels.padding = 12;
        this.chart.options.plugins.tooltip.titleFont.size = 12;
        this.chart.options.plugins.tooltip.bodyFont.size = 11;
        this.chart.options.scales.r.ticks.font.size = 9;
        this.chart.options.scales.r.pointLabels.font.size = isSmallMobile ? 10 : 12;
        this.chart.options.scales.r.ticks.stepSize = 20;
        this.chart.data.datasets[0].pointRadius = isSmallMobile ? 6 : 8;
        this.chart.data.datasets[0].borderWidth = isSmallMobile ? 2 : 3;
        this.chart.update();
      }
    },

    updateChart() {
      if (this.chart) {
        this.chart.data.datasets[0].data = Object.values(this.effectiveStats.visible);
        this.chart.update();
      }
    },

    nextSkillCard() {
      this.skillIndex = (this.skillIndex + 1) % this.skills.length;
    },
    prevSkillCard() {
      this.skillIndex = (this.skillIndex - 1 + this.skills.length) % this.skills.length;
    },
    nextTalentCard() {
      this.talentIndex = (this.talentIndex + 1) % this.talents.length;
    },
    prevTalentCard() {
      this.talentIndex = (this.talentIndex - 1 + this.talents.length) % this.talents.length;
    },
    finalizeCharacter() {
      if (!this.character.name) {
        this.popupMessage = "⚠ Please enter a character name!";
        this.showPopup = true;
        return;
      }
      if (!this.character.ageGroup || this.character.ageGroup === "None") {
        this.popupMessage = "⚠ Please select an age group!";
        this.showPopup = true;
        return;
      }
      if (!this.character.gender || this.character.gender === "None") {
        this.popupMessage = "⚠ Please select a gender!";
        this.showPopup = true;
        return;
      }
      if (this.remainingPoints !== 0) {
        this.popupMessage = "⚠ You must allocate all points!";
        this.showPopup = true;
        return;
      }

      this.isSaving = true;

      const characterData = {
        name: this.character.name,
        age_group: this.character.ageGroup,
        gender: this.character.gender,
        stats: this.character.stats,
        hidden_stats: this.character.hiddenStats,
        skills: this.character.skills.map(skill => skill.name),
        talents: this.character.talents.map(talent => talent.name),
        effective_stats: this.effectiveStats.visible
      };

      axios.post('/api/characters', characterData)
        .then(response => {
          console.log('Character creation response:', response);
          this.isSaving = false;
          this.popupMessage = "✓ Character created successfully!";
          this.showPopup = true;
          setTimeout(() => {
            this.$router.push('/game');
          }, 1500);
        })
        .catch(error => {
          this.isSaving = false;
          console.error("Error saving character:", error);
          console.error("Response data:", error.response?.data);
          
          let errorMessage = error.response?.data?.message || error.message;
          if (error.response?.data?.errors) {
            errorMessage = Object.values(error.response.data.errors).flat().join(', ');
          }
          
          this.popupMessage = "✗ Error creating character: " + errorMessage;
          this.showPopup = true;
        });
    },
    quitGame() {
      localStorage.removeItem('user')
      this.$router.push('/home')
    }
  }
};
</script>

<style scoped>
/* ============================================
   GALAXY BACKGROUND - MOVING STARS
   ============================================ */
.starfield {
  background: transparent;
}

.star {
  animation: twinkle 4s ease-in-out infinite, drift 20s linear infinite;
}

@keyframes twinkle {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 1; }
}

@keyframes drift {
  0% { transform: translateY(0) translateX(0); }
  100% { transform: translateY(30px) translateX(20px); }
}

.nebula {
  background: 
    radial-gradient(ellipse at 20% 20%, rgba(138, 43, 226, 0.15) 0%, transparent 40%),
    radial-gradient(ellipse at 80% 80%, rgba(0, 255, 136, 0.1) 0%, transparent 40%),
    radial-gradient(ellipse at 60% 40%, rgba(0, 206, 209, 0.1) 0%, transparent 35%),
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
  animation: grid-scroll 20s linear infinite, grid-pulse 8s ease-in-out infinite;
}

@keyframes grid-scroll {
  0% { background-position: 0 0; }
  100% { background-position: 60px 60px; }
}

@keyframes grid-pulse {
  0%, 100% { 
    opacity: 0.3;
    background-size: 60px 60px;
  }
  50% { 
    opacity: 0.6;
    background-size: 58px 58px;
  }
}

/* ============================================
   ENERGY PARTICLES - Floating Orbs
   ============================================ */
.energy-particles {
  overflow: hidden;
}

.energy-orb {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(1px);
}

@keyframes float-orb {
  0%, 100% { 
    transform: translate(0, 0) scale(1);
    opacity: 0.6;
  }
  25% { 
    transform: translate(calc(var(--x-move) * 0.3), calc(var(--y-move) * -0.3)) scale(1.1);
    opacity: 0.9;
  }
  50% { 
    transform: translate(calc(var(--x-move) * 0.6), calc(var(--y-move) * 0.4)) scale(0.9);
    opacity: 0.7;
  }
  75% { 
    transform: translate(calc(var(--x-move) * 0.4), calc(var(--y-move) * -0.2)) scale(1.05);
    opacity: 0.8;
  }
}

@keyframes orb-glow {
  0%, 100% { 
    filter: blur(1px) brightness(1);
  }
  50% { 
    filter: blur(2px) brightness(1.5);
  }
}

/* ============================================
   SHOOTING STARS
   ============================================ */
.shooting-stars {
  overflow: hidden;
}

.shooting-star {
  position: absolute;
  transform: rotate(-25deg);
  border-radius: 50%;
  opacity: 0;
}

@keyframes shoot {
  0% {
    opacity: 0;
    transform: translateX(0) rotate(-25deg) scale(0.5);
  }
  5% {
    opacity: 1;
    transform: translateX(50px) rotate(-25deg) scale(1);
  }
  15% {
    opacity: 0;
    transform: translateX(300px) rotate(-25deg) scale(0.3);
  }
  100% {
    opacity: 0;
    transform: translateX(300px) rotate(-25deg) scale(0.3);
  }
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

/* ============================================
   VIGNETTE EFFECT
   ============================================ */
.vignette {
  background: radial-gradient(
    ellipse at center,
    transparent 40%,
    rgba(0, 0, 0, 0.4) 80%,
    rgba(0, 0, 0, 0.7) 100%
  );
  animation: vignette-pulse 10s ease-in-out infinite;
}

@keyframes vignette-pulse {
  0%, 100% { opacity: 0.8; }
  50% { opacity: 1; }
}

/* ============================================
   TITLE - BIG WITH GLITCH ON LIFE
   ============================================ */
.game-title {
  letter-spacing: 0.1em;
  line-height: 1.2;
}

.title-u {
  color: #00ffcc;
  text-shadow: 0 0 30px #00ffcc, 0 0 60px #00ffcc, 4px 4px 0 #004444;
}

.title-colon {
  color: #ffffff;
  text-shadow: 3px 3px 0 #000;
}

.glitch-title {
  position: relative;
  color: #ff00ff;
  text-shadow: 0 0 30px #ff00ff, 0 0 60px #ff00ff, 4px 4px 0 #440044;
  animation: glitch-effect 2.5s infinite;
  display: inline-block;
}

.glitch-title::before,
.glitch-title::after {
  content: attr(data-text);
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.glitch-title::before {
  color: #00ffcc;
  animation: glitch-1 2.5s infinite;
  clip-path: polygon(0 0, 100% 0, 100% 30%, 0 30%);
  left: -2px;
}

.glitch-title::after {
  color: #00ccff;
  animation: glitch-2 2.5s infinite;
  clip-path: polygon(0 70%, 100% 70%, 100% 100%, 0 100%);
  left: 2px;
}

@keyframes glitch-effect {
  0%, 90%, 100% { 
    transform: translate(0);
    opacity: 1;
  }
  92% { 
    transform: translate(-3px, 1px);
    opacity: 0.8;
  }
  94% { 
    transform: translate(3px, -1px);
    opacity: 0.9;
  }
  96% { 
    transform: translate(-2px, 2px);
    opacity: 0.7;
  }
}

@keyframes glitch-1 {
  0%, 85%, 100% { 
    transform: translate(0);
    opacity: 0;
  }
  90% { 
    transform: translate(-4px, 1px);
    opacity: 0.8;
  }
}

@keyframes glitch-2 {
  0%, 85%, 100% { 
    transform: translate(0);
    opacity: 0;
  }
  92% { 
    transform: translate(4px, -1px);
    opacity: 0.8;
  }
}

.title-exclaim {
  color: #00ffcc;
  text-shadow: 0 0 30px #00ffcc, 4px 4px 0 #004444;
  animation: exclaim-glow 2s ease-in-out infinite;
}

@keyframes exclaim-glow {
  0%, 100% { text-shadow: 0 0 30px #00ffcc, 4px 4px 0 #004444; }
  50% { text-shadow: 0 0 50px #00ffcc, 0 0 80px #00ffcc, 4px 4px 0 #004444; }
}

.tagline {
  animation: tagline-shimmer 5s ease-in-out infinite;
}

@keyframes tagline-shimmer {
  0%, 100% { opacity: 0.8; }
  50% { opacity: 1; }
}

/* ========================================
   CREATION CONTAINER
   ======================================== */
.creation-container {
  width: 100%;
  max-width: 1300px;
  margin: 0 auto;
  padding: 16px;
  box-sizing: border-box;
  overflow-x: hidden;
}

.creation-layout {
  display: flex;
  flex-direction: row;
  gap: 20px;
  justify-content: center;
  align-items: stretch;
}

/* ========================================
   PANELS - Galaxy Theme
   ======================================== */
.left-panel,
.right-panel {
  background: rgba(20, 15, 35, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.5),
    0 0 60px rgba(138, 43, 226, 0.1),
    0 0 60px rgba(0, 255, 204, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  padding: 20px;
}

.left-panel {
  flex: 1 1 320px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 280px;
  max-width: 380px;
}

.right-panel {
  flex: 2 1 550px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 15px;
  min-width: 450px;
  max-width: 900px;
}

/* Tip Banner */
.tip-banner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 12px 20px;
  background: linear-gradient(
    90deg,
    rgba(138, 43, 226, 0.15),
    rgba(0, 255, 204, 0.1),
    rgba(138, 43, 226, 0.15)
  );
  border: 1px solid rgba(0, 255, 204, 0.4);
  border-radius: 12px;
  animation: tip-pulse 3s ease-in-out infinite;
}

@keyframes tip-pulse {
  0%, 100% { border-color: rgba(0, 255, 204, 0.3); box-shadow: 0 0 5px rgba(0, 255, 204, 0.1); }
  50% { border-color: rgba(0, 255, 204, 0.6); box-shadow: 0 0 15px rgba(0, 255, 204, 0.3); }
}

.tip-icon {
  font-size: 20px;
  animation: tip-bounce 2s ease-in-out infinite;
  filter: drop-shadow(0 0 5px rgba(0, 255, 204, 0.8));
}

@keyframes tip-bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}

.tip-text {
  font-family: 'VT323', monospace;
  font-size: 16px;
  color: #00ffcc;
  text-shadow: 0 0 10px rgba(0, 255, 204, 0.8);
}

/* ========================================
   FORM ELEMENTS - Galaxy Theme
   ======================================== */
/* ============================================
   CHARACTER CARD - Game-style Panel
   ============================================ */
.character-card {
  position: relative;
  background: linear-gradient(
    180deg,
    rgba(20, 20, 45, 0.7) 0%,
    rgba(10, 10, 30, 0.9) 100%
  );
  border: 1px solid rgba(138, 43, 226, 0.5);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 20px;
  overflow: hidden;
  box-shadow: 
    0 4px 30px rgba(0, 0, 0, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.character-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(138, 43, 226, 0.08) 0%,
    transparent 40%,
    rgba(0, 255, 204, 0.05) 100%
  );
  pointer-events: none;
}

.character-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 20px;
  right: 20px;
  height: 1px;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(0, 255, 204, 0.5),
    transparent
  );
}

/* Decorative Corners */
.card-corner {
  position: absolute;
  width: 24px;
  height: 24px;
  border: 2px solid #00ffcc;
  pointer-events: none;
}

.card-corner--tl {
  top: -1px;
  left: -1px;
  border-right: none;
  border-bottom: none;
  border-radius: 16px 0 0 0;
  box-shadow: 
    -3px -3px 10px rgba(0, 255, 204, 0.4),
    inset -2px -2px 5px rgba(0, 255, 204, 0.2);
}

.card-corner--tr {
  top: -1px;
  right: -1px;
  border-left: none;
  border-bottom: none;
  border-radius: 0 16px 0 0;
  box-shadow: 
    3px -3px 10px rgba(0, 255, 204, 0.4),
    inset 2px -2px 5px rgba(0, 255, 204, 0.2);
}

.card-corner--bl {
  bottom: -1px;
  left: -1px;
  border-right: none;
  border-top: none;
  border-radius: 0 0 0 16px;
  box-shadow: 
    -3px 3px 10px rgba(0, 255, 204, 0.4),
    inset -2px 2px 5px rgba(0, 255, 204, 0.2);
}

.card-corner--br {
  bottom: -1px;
  right: -1px;
  border-left: none;
  border-top: none;
  border-radius: 0 0 16px 0;
  box-shadow: 
    3px 3px 10px rgba(0, 255, 204, 0.4),
    inset 2px 2px 5px rgba(0, 255, 204, 0.2);
}

/* Character Form Group - Enhanced Input Boxes */
.character-form-group {
  margin-bottom: 18px;
}

.character-form-group:last-child {
  margin-bottom: 0;
}

.input-wrapper,
.select-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-prefix {
  position: absolute;
  left: 14px;
  color: #00ffcc;
  font-size: 14px;
  z-index: 1;
  text-shadow: 0 0 10px rgba(0, 255, 204, 0.8);
  animation: prefix-glow 2s ease-in-out infinite;
}

@keyframes prefix-glow {
  0%, 100% { opacity: 0.6; text-shadow: 0 0 5px rgba(0, 255, 204, 0.5); }
  50% { opacity: 1; text-shadow: 0 0 15px rgba(0, 255, 204, 1); }
}

.form-input,
.form-select {
  width: 100%;
  padding: 14px 16px 14px 38px !important;
  font-family: 'VT323', monospace;
  font-size: 16px;
  background: linear-gradient(
    135deg,
    rgba(15, 15, 35, 0.9) 0%,
    rgba(25, 25, 50, 0.7) 100%
  ) !important;
  color: #e0e0e0 !important;
  border: 1px solid rgba(138, 43, 226, 0.5) !important;
  border-radius: 12px !important;
  transition: all 0.3s ease;
  box-shadow: 
    inset 0 2px 4px rgba(0, 0, 0, 0.3),
    0 0 0 transparent;
}

.form-input::placeholder {
  color: #666688 !important;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #00ffcc !important;
  box-shadow: 
    0 0 25px rgba(0, 255, 204, 0.4),
    inset 0 0 15px rgba(0, 255, 204, 0.1),
    inset 0 2px 4px rgba(0, 0, 0, 0.2);
}

.form-select {
  appearance: none;
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2300ffcc' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  padding-right: 40px !important;
}

.form-select option {
  background: #1a1a2e;
  color: #e0e0e0;
  padding: 10px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  font-family: 'VT323', monospace;
  font-size: 16px;
  font-weight: 600;
  color: #8888aa;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 8px;
}

.label-icon {
  font-size: 18px;
  filter: drop-shadow(0 0 3px rgba(0, 255, 204, 0.5));
}

.form-input,
.form-select {
  font-family: 'VT323', monospace;
  font-size: 16px;
  background: rgba(10, 10, 25, 0.7) !important;
  color: #e0e0e0 !important;
  border: 1px solid rgba(138, 43, 226, 0.3) !important;
  padding: 12px 16px;
  border-radius: 12px !important;
  transition: all 0.3s ease;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #00ffcc !important;
  box-shadow: 0 0 15px rgba(0, 255, 204, 0.2);
}

.form-input::placeholder {
  color: #555577 !important;
}

.form-select option {
  background: #1e293b;
  color: #f0fdf4;
}

/* ========================================
   STATS SECTION - Galaxy Theme
   ======================================== */
.stats-container {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 16px;
  padding: 20px;
  border: 1px solid rgba(138, 43, 226, 0.2);
}

.stats-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(138, 43, 226, 0.2);
}

.stats-title {
  font-family: 'VT323', monospace;
  font-size: 18px;
  font-weight: 700;
  color: #00ffcc;
  margin: 0;
}

.points-remaining {
  font-size: 14px;
  color: #00ffcc;
  font-weight: 500;
}

.diagram-toggle {
  font-family: 'VT323', monospace;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 14px;
  background: rgba(138, 43, 226, 0.2);
  color: #00ffcc;
  border: 1px solid rgba(0, 255, 204, 0.4);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.diagram-toggle:hover {
  background: rgba(138, 43, 226, 0.3);
  transform: translateY(-2px);
}

.stats-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: rgba(10, 10, 25, 0.5);
  border-radius: 10px;
  border-left: 3px solid #00ffcc;
  transition: all 0.3s ease;
}

.stat-row:hover {
  background: rgba(138, 43, 226, 0.15);
  border-left-color: #ff00ff;
}

.stat-label {
  color: #8888aa;
  font-family: 'VT323', monospace;
  font-size: 14px;
  font-weight: 500;
  flex: 1;
  display: flex;
  align-items: center;
  gap: 6px;
}

.stat-icon {
  font-size: 16px;
  filter: drop-shadow(0 0 3px rgba(0, 255, 204, 0.5));
  transition: transform 0.3s ease;
}

.stat-label:hover .stat-icon {
  transform: scale(1.2);
}

.stat-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stat-btn {
  font-family: 'VT323', monospace;
  font-size: 18px;
  font-weight: 600;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(138, 43, 226, 0.2);
  color: #00ffcc;
  border: 1px solid rgba(0, 255, 204, 0.4);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}

.stat-btn .btn-glow {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle, rgba(0, 255, 204, 0.3) 0%, transparent 70%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-btn:hover .btn-glow {
  opacity: 1;
}

.stat-btn--minus {
  border-color: rgba(255, 100, 100, 0.4);
  color: #ff6464;
}

.stat-btn--minus:hover {
  background: rgba(255, 100, 100, 0.3);
  box-shadow: 0 0 15px rgba(255, 100, 100, 0.4);
}

.stat-btn--plus {
  border-color: rgba(0, 255, 204, 0.4);
  color: #00ffcc;
}

.stat-btn--plus:hover {
  background: rgba(0, 255, 204, 0.2);
  box-shadow: 0 0 15px rgba(0, 255, 204, 0.4);
}

.stat-btn:hover {
  background: rgba(138, 43, 226, 0.4);
  transform: scale(1.1);
}

.stat-value {
  font-family: 'VT323', monospace;
  font-size: 16px;
  font-weight: 700;
  color: #00ffcc;
  min-width: 28px;
  text-align: center;
}

/* ========================================
   ACTIVE CONTAINER (Skills/Talents Display)
   ======================================== */
.active-display-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
  justify-content: center;
  margin-top: 15px;
  width: 100%;
  box-sizing: border-box;
}

.active-section {
  width: 100%;
  padding: 16px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 16px;
  border: 1px solid rgba(138, 43, 226, 0.2);
}

.active-container {
  margin-top: 8px;
  padding: 20px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 16px;
  border: 1px solid rgba(138, 43, 226, 0.2);
}

.active-title {
  font-family: 'VT323', monospace;
  font-size: 16px;
  font-weight: 700;
  color: #00ffcc;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
}

.active-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.active-box {
  font-family: 'VT323', monospace;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  padding: 8px 14px;
  border-radius: 8px;
  letter-spacing: 0.05em;
  transition: transform 0.2s ease;
}

.active-box:hover {
  transform: scale(1.05);
}

.skill-box {
  background: linear-gradient(135deg, #8b5cf6, #6366f1);
  color: #ffffff;
}

.talent-box {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #000000;
}

.no-items {
  color: #555577;
  font-family: 'VT323', monospace;
  font-size: 14px;
  font-style: italic;
}

.section-gap {
  margin: 16px 0;
  border-top: 1px dashed rgba(138, 43, 226, 0.2);
}

/* ========================================
   CAROUSEL SECTION
   ======================================== */
.skills-container,
.talents-container {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 16px;
  padding: 10px;
  overflow: hidden;
}

.skills-container {
  margin-bottom: 15px;
}

.carousel-title {
  font-family: 'Press Start 2P', monospace;
  font-size: 14px;
  font-weight: 700;
  color: #00ffcc;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 10px 16px;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  text-shadow: 0 0 20px rgba(0, 255, 204, 0.5);
  position: relative;
  animation: glitch-title 2.5s infinite;
}

.carousel-title::before,
.carousel-title::after {
  content: attr(data-text);
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.carousel-title::before {
  color: #ff00ff;
  animation: glitch-1 2.5s infinite;
  clip-path: polygon(0 0, 100% 0, 100% 30%, 0 30%);
  left: -2px;
}

.carousel-title::after {
  color: #00ccff;
  animation: glitch-2 2.5s infinite;
  clip-path: polygon(0 70%, 100% 70%, 100% 100%, 0 100%);
  left: 2px;
}

@keyframes glitch-title {
  0%, 90%, 100% { 
    transform: translate(0);
    opacity: 1;
  }
  92% { 
    transform: translate(-2px, 1px);
    opacity: 0.8;
  }
  94% { 
    transform: translate(2px, -1px);
    opacity: 0.9;
  }
  96% { 
    transform: translate(-1px, 2px);
    opacity: 0.7;
  }
}

@keyframes glitch-1 {
  0%, 85%, 100% { 
    transform: translate(0);
    opacity: 0;
  }
  90% { 
    transform: translate(-3px, 1px);
    opacity: 0.8;
  }
}

@keyframes glitch-2 {
  0%, 85%, 100% { 
    transform: translate(0);
    opacity: 0;
  }
  92% { 
    transform: translate(3px, -1px);
    opacity: 0.8;
  }
}

.header-icon {
  color: #00ffcc !important;
}

.card-carousel {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
  overflow: hidden;
  position: relative;
  min-height: 220px;
  padding: 20px 4px;
  /* 3D Carousel Perspective */
  perspective: 1000px;
  transform-style: preserve-3d;
}

/* Carousel Stage - Creates the circular track illusion */
.card-carousel::before {
  content: '';
  position: absolute;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  width: 70%;
  height: 60px;
  background: radial-gradient(
    ellipse at center,
    rgba(0, 255, 204, 0.15) 0%,
    transparent 70%
  );
  border-radius: 50%;
  pointer-events: none;
  z-index: 0;
}

.card-track {
  padding-top: 10px;
  padding-bottom: 20px;
  height: 220px;
  display: flex;
  gap: 8px;
  transition: transform 0.5s ease;
  width: 100%;
  justify-content: center;
  flex-wrap: nowrap;
  margin-top: 15px;
  /* 3D transforms */
  transform-style: preserve-3d;
  position: relative;
}


.talent-card {
  flex: 0 0 110px;  
  margin: 0 2px;
  border: 2px solid rgba(0, 255, 204, 0.2);
  border-radius: 12px;
  background: linear-gradient(
    155deg, 
    rgba(30, 41, 59, 0.9) 0%,
    rgba(15, 23, 42, 0.95) 50%,
    rgba(10, 15, 30, 0.98) 100%
  );
  transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 
    0 4px 8px rgba(0, 0, 0, 0.4),
    0 2px 4px rgba(0, 0, 0, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
  cursor: pointer;
  position: relative;
  z-index: 1;
  /* 3D Effect */
  transform-style: preserve-3d;
  backface-visibility: hidden;
}

.skill-card {
  flex: 0 0 110px;
  margin: 0 2px;
  border: 2px solid rgba(0, 255, 204, 0.2);
  border-radius: 12px;
  background: linear-gradient(
    155deg, 
    rgba(30, 41, 59, 0.9) 0%,
    rgba(15, 23, 42, 0.95) 50%,
    rgba(10, 15, 30, 0.98) 100%
  );
  transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 
    0 4px 8px rgba(0, 0, 0, 0.4),
    0 2px 4px rgba(0, 0, 0, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
  cursor: pointer;
  position: relative;
  z-index: 1;
  /* 3D Effect */
  transform-style: preserve-3d;
  backface-visibility: hidden;
}


.skill-card:hover,
.talent-card:hover {
  border-color: #00ffcc;
  transform: translateY(-10px) scale(1.05) rotateX(8deg);
  box-shadow: 
    0 20px 40px rgba(0, 255, 204, 0.3),
    0 10px 20px rgba(0, 0, 0, 0.5),
    inset 0 0 20px rgba(0, 255, 204, 0.1);
}

.skill-card:hover::after,
.talent-card:hover::after {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(0, 255, 204, 0.3), transparent);
  z-index: -1;
  animation: card-glow-pulse 1.5s ease-in-out infinite;
}

@keyframes card-glow-pulse {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

.skill-card.selected,
.talent-card.selected {
  border-color: #00ffcc;
  box-shadow: 
    0 0 30px rgba(0, 255, 204, 0.6),
    0 15px 30px rgba(0, 0, 0, 0.5),
    inset 0 0 30px rgba(0, 255, 204, 0.15);
}

.center-card {
  transform: scale(1.12) translateZ(20px);
  border-color: #ff00ff;
  box-shadow: 
    0 0 40px rgba(255, 0, 255, 0.6),
    0 20px 40px rgba(0, 0, 0, 0.6),
    inset 0 0 30px rgba(255, 0, 255, 0.1);
  z-index: 10;
}

.center-card::before {
  content: '';
  position: absolute;
  top: -5px;
  left: 50%;
  transform: translateX(-50%);
  width: 60%;
  height: 8px;
  background: radial-gradient(ellipse, rgba(255, 0, 255, 0.5), transparent);
  border-radius: 50%;
  animation: center-glow 2s ease-in-out infinite;
}

@keyframes center-glow {
  0%, 100% { opacity: 0.5; transform: translateX(-50%) scale(1); }
  50% { opacity: 1; transform: translateX(-50%) scale(1.2); }
}

.center-card::before,
.center-card::after {
  border-color: rgba(255, 0, 255, 0.5);
}

/* Card Visual */
.card-visual {
  position: relative;
  height: 120px;
  width: 120px;
  border-bottom: 1px solid rgba(0, 255, 204, 0.1);
  overflow: hidden;
  border-radius: 6px 6px 0 0;
  aspect-ratio: 1 / 1;
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center center;
  border-radius: 6px 6px 0 0;
}

/* Info Icon */
.info-icon {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(255, 235, 59, 0.9);
  color: #000;
  font-size: 8px;
  font-weight: bold;
  border-radius: 50%;
  width: 14px;
  height: 14px;
  text-align: center;
  line-height: 14px;
  cursor: pointer;
  box-shadow: 1px 1px 0 rgba(0, 0, 0, 0.3);
  z-index: 50;
  transition: transform 0.1s ease;
}

.info-icon:hover {
  transform: scale(1.1);
}

.info-popup {
  display: none;
  position: absolute;
  top: 0;
  right: 100%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.95);
  color: #fff;
  padding: 6px 10px;
  border-radius: 6px;
  width: 120px;
  z-index: 100;
  border: 2px solid rgba(0, 255, 204, 0.6);
  box-shadow: -6px 0 15px rgba(0, 255, 204, 0.3);
  margin-right: 6px;
}

.info-popup span {
  display: block;
  font-size: 0.5rem;
  line-height: 1.2;
  animation: scroll-text 5s linear infinite;
  white-space: nowrap;
}

.info-icon:hover .info-popup {
  display: block;
  animation: fadeIn 0.15s ease;
}

@keyframes scroll-text {
  0% {
    transform: translateX(0%);
  }
  100% {
    transform: translateX(-100%);
  }
}

/* Card Content */
.card-content {
  padding: 6px;
  text-align: center;
}

.card-name {
  font-family: 'VT323', monospace;
  font-size: 12px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 2px;
}

.card-effect {
  font-family: 'VT323', monospace;
  font-size: 9px;
  color: #8888aa;
  margin: 0;
  line-height: 1.1;
}

/* ========================================
   ARROW BUTTONS - Carousel Controls
   ======================================== */
.arrow-button {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(138, 43, 226, 0.4), rgba(138, 43, 226, 0.15));
  color: #00ffcc;
  border: 2px solid rgba(0, 255, 204, 0.5);
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
  position: relative;
  overflow: hidden;
}

.arrow-button::before {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0, 255, 204, 0.3), transparent 70%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.arrow-button:hover {
  background: linear-gradient(135deg, rgba(138, 43, 226, 0.6), rgba(138, 43, 226, 0.3));
  border-color: #00ffcc;
  transform: scale(1.15);
  box-shadow: 
    0 0 25px rgba(0, 255, 204, 0.6),
    0 0 50px rgba(0, 255, 204, 0.3);
}

.arrow-button:hover::before {
  opacity: 1;
  animation: button-pulse 1s ease-in-out infinite;
}

@keyframes button-pulse {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.2); opacity: 1; }
}

.arrow-button:active {
  transform: scale(0.95);
}

/* ========================================
   MODAL - Stats Diagram
   ======================================== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  animation: fadeIn 0.3s ease;
  backdrop-filter: blur(8px);
}

.modal-content {
  background: linear-gradient(180deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.98));
  padding: 40px;
  border-radius: 32px;
  width: 98%;
  max-width: 850px;
  border: 3px solid #00ffcc;
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.6),
    0 0 80px rgba(0, 255, 204, 0.3),
    0 0 120px rgba(0, 255, 204, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.1),
    inset 0 0 60px rgba(0, 255, 204, 0.05);
  animation: diagram-appear 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  overflow: hidden;
  z-index: 100;
}

/* Enhanced hexagon grid background */
.modal-content::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 200%;
  height: 200%;
  transform: translate(-50%, -50%);
  background-image: 
    radial-gradient(circle, rgba(0, 255, 204, 0.03) 1px, transparent 1px);
  background-size: 30px 30px;
  animation: hexagon-pulse 4s ease-in-out infinite;
  pointer-events: none;
  z-index: 0;
}

/* Animated rotating ring effect */
.modal-content .ring-effect {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 90%;
  height: 90%;
  transform: translate(-50%, -50%);
  border: 1px solid rgba(0, 255, 204, 0.15);
  border-radius: 50%;
  animation: ring-rotate 20s linear infinite;
  pointer-events: none;
}

.modal-content .ring-effect::before {
  content: '';
  position: absolute;
  top: -2px;
  left: 50%;
  width: 8px;
  height: 8px;
  background: #00ffcc;
  border-radius: 50%;
  box-shadow: 0 0 15px #00ffcc, 0 0 30px #00ffcc;
}

.modal-content .ring-effect:nth-child(2) {
  width: 70%;
  height: 70%;
  border-color: rgba(255, 0, 255, 0.1);
  animation-duration: 25s;
  animation-direction: reverse;
}

.modal-content .ring-effect:nth-child(2)::before {
  background: #ff00ff;
  box-shadow: 0 0 15px #ff00ff, 0 0 30px #ff00ff;
}

.modal-content::before {
  content: '';
  position: absolute;
  top: -30%;
  left: -30%;
  width: 160%;
  height: 160%;
  background: radial-gradient(circle, rgba(0, 255, 204, 0.08) 0%, transparent 60%);
  animation: pulse-diagram 4s ease-in-out infinite;
  pointer-events: none;
}

@keyframes diagram-appear {
  0% {
    opacity: 0;
    transform: scale(0.7) rotateX(-10deg);
  }
  100% {
    opacity: 1;
    transform: scale(1) rotateX(0);
  }
}

@keyframes pulse-diagram {
  0%, 100% { opacity: 0.6; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.05); }
}

@keyframes hexagon-pulse {
  0%, 100% { 
    opacity: 0.5;
    background-size: 30px 30px;
  }
  50% { 
    opacity: 0.8;
    background-size: 32px 32px;
  }
}

@keyframes ring-rotate {
  0% { transform: translate(-50%, -50%) rotate(0deg); }
  100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Constellation marker legend */
.constellation-legend {
  display: flex;
  justify-content: center;
  gap: 24px;
  margin-top: 16px;
  padding: 12px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 12px;
  border: 1px solid rgba(0, 255, 204, 0.2);
}

.constellation-legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: 'VT323', monospace;
  font-size: 14px;
  color: #8888aa;
}

.constellation-legend-item .marker {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}

.constellation-legend-item .marker.gender {
  background: #ff00ff;
  box-shadow: 0 0 8px #ff00ff;
  transform: rotate(45deg);
}

.constellation-legend-item .marker.age {
  background: #00ccff;
  box-shadow: 0 0 8px #00ccff;
}

.modal-content .chart-container {
  width: 100%;
  aspect-ratio: 1 / 1;
  max-height: 600px;
  position: relative;
  z-index: 10;
}

.modal-content canvas {
  width: 100% !important;
  height: 100% !important;
  filter: drop-shadow(0 0 10px rgba(0, 255, 204, 0.3));
  position: relative;
  z-index: 10;
}

.modal-content .ring-effect {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 90%;
  height: 90%;
  transform: translate(-50%, -50%);
  border: 1px solid rgba(0, 255, 204, 0.15);
  border-radius: 50%;
  animation: ring-rotate 20s linear infinite;
  pointer-events: none;
  z-index: 1;
}

/* ========================================
   MODAL - MOBILE RESPONSIVE
   ======================================== */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 10px;
    align-items: flex-start;
    padding-top: 20px;
    overflow-y: auto;
  }

  .modal-content {
    padding: 20px;
    border-radius: 20px;
    width: 100%;
    max-width: 100%;
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-content::before {
    top: -20%;
    left: -20%;
    width: 140%;
    height: 140%;
  }

  .modal-content .chart-container {
    max-height: 350px;
    min-height: 280px;
  }
}

@media (max-width: 480px) {
  .modal-overlay {
    padding: 6px;
    padding-top: 10px;
  }

  .modal-content {
    padding: 14px;
    border-radius: 16px;
    border-width: 2px;
  }

  .modal-content .chart-container {
    max-height: 280px;
    min-height: 220px;
  }
}

/* ========================================
   BUTTON BAR
   ======================================== */
.button-bar {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-top: 32px;
  padding: 20px;
}

.start-btn {
  font-family: 'VT323', monospace !important;
  font-size: 18px !important;
  font-weight: 500;
  letter-spacing: 2px;
  text-align: center !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 50%, #00d4aa 100%) !important;
  color: #ffffff !important;
  border-radius: 12px !important;
  box-shadow: 
    0 4px 20px rgba(139, 92, 246, 0.3),
    0 0 30px rgba(0, 212, 170, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
  transition: all 0.25s ease;
  padding: 14px 48px !important;
  text-transform: uppercase;
}

.start-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 
    0 6px 30px rgba(139, 92, 246, 0.4),
    0 0 40px rgba(0, 212, 170, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

.start-btn:disabled {
  background: #2a2a40 !important;
  color: #555577 !important;
  box-shadow: none;
}

.logout-btn {
  font-family: 'VT323', monospace !important;
  font-weight: 600 !important;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  border-radius: 12px !important;
  border: 2px solid rgba(239, 68, 68, 0.5) !important;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(185, 28, 28, 0.3)) !important;
  color: #fca5a5 !important;
  transition: all 0.3s ease !important;
}

.logout-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.4), rgba(185, 28, 28, 0.5)) !important;
  border-color: #ef4444 !important;
  color: #fff !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
}

/* ========================================
   POPUP
   ======================================== */
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.72);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  animation: fadeIn 0.3s ease;
  backdrop-filter: blur(10px);
}

.popup-box {
  --popup-glow: 0, 255, 204;
  background:
    linear-gradient(180deg, rgba(25, 20, 45, 0.96) 0%, rgba(10, 10, 25, 0.94) 100%) padding-box,
    linear-gradient(135deg, rgba(139, 92, 246, 0.45), rgba(var(--popup-glow), 0.35), rgba(139, 92, 246, 0.35)) border-box;
  border: 2px solid transparent;
  border-radius: 24px;
  padding: 40px;
  width: 90%;
  max-width: 420px;
  text-align: center;
  box-shadow: 
    0 20px 60px rgba(0, 0, 0, 0.6),
    0 0 45px rgba(var(--popup-glow), 0.22),
    0 0 85px rgba(var(--popup-glow), 0.12),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  animation: popup-appear 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(18px);
}

.popup-box--success { --popup-glow: 0, 255, 204; }
.popup-box--info { --popup-glow: 0, 204, 255; }
.popup-box--warn { --popup-glow: 245, 158, 11; }
.popup-box--error { --popup-glow: 239, 68, 68; }

.popup-box::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(var(--popup-glow), 0.14) 0%, transparent 70%);
  animation: pulse-bg 3s ease-in-out infinite;
}

@keyframes pulse-bg {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.1); }
}

.popup-icon {
  margin: 0 auto 20px auto;
  display: block;
  filter: drop-shadow(0 0 12px rgba(var(--popup-glow), 0.35));
  animation: pulse-glow 2s infinite;
  position: relative;
  z-index: 1;
}

.popup-badge {
  width: 54px;
  height: 54px;
  margin: 0 auto 12px;
  display: grid;
  place-items: center;
  border-radius: 9999px;
  background: radial-gradient(circle at 30% 20%, rgba(var(--popup-glow), 0.18), rgba(0, 0, 0, 0.35));
  border: 1px solid rgba(var(--popup-glow), 0.35);
  box-shadow: 0 0 22px rgba(var(--popup-glow), 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.10);
  position: relative;
  z-index: 1;
}

.popup-badge :deep(.v-icon) {
  color: rgb(var(--popup-glow)) !important;
  filter: drop-shadow(0 0 12px rgba(var(--popup-glow), 0.22));
}

.popup-box h3 {
  font-family: 'VT323', monospace;
  font-size: 24px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 20px;
  text-shadow: 0 0 22px rgba(var(--popup-glow), 0.25);
  position: relative;
  z-index: 1;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.popup-box h3::before {
  content: '';
  display: none;
}


.popup-box p {
  font-family: 'VT323', monospace;
  font-size: 16px;
  color: rgba(255, 255, 255, 0.86);
  margin-bottom: 28px;
  line-height: 1.7;
  position: relative;
  z-index: 1;
  padding: 16px 20px;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.22), rgba(0, 0, 0, 0.10));
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.25);
}

.popup-btn {
  position: relative;
  z-index: 1;
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.85), rgba(99, 102, 241, 0.70), rgba(var(--popup-glow), 0.75)) !important;
  color: #ffffff !important;
  font-family: 'VT323', monospace !important;
  font-weight: 700 !important;
  font-size: 16px !important;
  padding: 12px 40px !important;
  border-radius: 12px !important;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  box-shadow: 
    0 4px 18px rgba(var(--popup-glow), 0.22),
    0 0 30px rgba(var(--popup-glow), 0.14),
    inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
  transition: all 0.3s ease !important;
  border: 1px solid rgba(255, 255, 255, 0.12) !important;
}

.popup-btn:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 
    0 10px 30px rgba(var(--popup-glow), 0.26),
    0 0 45px rgba(var(--popup-glow), 0.18),
    inset 0 1px 0 rgba(255, 255, 255, 0.4) !important;
}

/* ========================================
   ANIMATIONS
   ======================================== */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scroll-text {
  0% {
    transform: translateX(0%);
  }
  100% {
    transform: translateX(-100%);
  }
}

@keyframes popup-appear {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

@keyframes pulse-glow {
  0% { filter: drop-shadow(0 0 8px rgba(var(--popup-glow), 0.28)); }
  50% { filter: drop-shadow(0 0 16px rgba(var(--popup-glow), 0.45)); }
  100% { filter: drop-shadow(0 0 8px rgba(var(--popup-glow), 0.28)); }
}

/* ========================================
   SCROLLBAR
   ======================================== */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #00ffcc;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #00a884;
}

/* ========================================
   MOBILE RESPONSIVE STYLES
   ======================================== */
@media (max-width: 1024px) {
  .creation-container {
    max-width: 100%;
  }
  
  .creation-layout {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .left-panel {
    flex: 1 1 280px;
    min-width: 260px;
    max-width: 350px;
  }
  
  .right-panel {
    flex: 1 1 400px;
    min-width: 380px;
    max-width: 500px;
  }
  
  .skills-container,
  .talents-container {
    height: 300px;
  }
  
  .card-track {
    height: 180px;
  }
  
  .skill-card,
  .talent-card {
    flex: 0 0 90px;
  }
  
  .card-visual {
    height: 55px;
    width: 100%;
  }
}

@media (max-width: 768px) {
  .creation-container {
    padding: 10px;
    overflow-x: hidden;
    max-width: 100vw;
    box-sizing: border-box;
  }

  .title-wrapper {
    margin-bottom: 12px;
  }

  .creation-layout {
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
  }

  .left-panel {
    flex: 1 1 100%;
    min-width: 100%;
    max-width: 100%;
    padding: 12px;
    overflow: hidden;
    box-sizing: border-box;
  }

  .right-panel {
    flex: 1 1 100%;
    min-width: 100%;
    max-width: 100%;
    padding: 10px;
    overflow-x: hidden;
    box-sizing: border-box;
  }

  .skills-container,
  .talents-container {
    height: auto;
    min-height: 300px;
    padding: 10px;
    overflow: hidden;
    box-sizing: border-box;
  }

  .carousel-title {
    font-size: 10px;
    padding: 8px 10px;
    margin-bottom: 10px;
    letter-spacing: 0.1em;
  }

  .card-carousel {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    padding: 0 45px;
    overflow: visible;
    box-sizing: border-box;
    width: 100%;
    position: relative;
    gap: 10px;
  }

  .card-track {
    display: flex;
    flex-direction: row;
    gap: 10px;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    height: auto;
    min-height: 220px;
    padding: 10px 5px;
    box-sizing: border-box;
    width: 100%;
    justify-content: center;
    align-items: center;
    scrollbar-width: thin;
    scrollbar-color: #00ffcc rgba(0,0,0,0.2);
  }

  .card-track::-webkit-scrollbar {
    height: 4px;
  }

  .card-track::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.2);
    border-radius: 2px;
  }

  .card-track::-webkit-scrollbar-thumb {
    background: #00ffcc;
    border-radius: 2px;
  }

  .skill-card,
  .talent-card {
    flex: 0 0 auto;
    min-width: 110px;
    max-width: 45%;
    width: 110px;
    box-sizing: border-box;
    height: auto;
    transition: all 0.3s ease;
  }

  .skill-card.center-card,
  .talent-card.center-card {
    flex: 0 0 auto;
    min-width: 120px;
    max-width: 48%;
    width: 120px;
    transform: scale(1.06);
    border-color: #ff00ff !important;
    box-shadow: 
      0 0 20px rgba(255, 0, 255, 0.35),
      0 8px 16px rgba(0, 0, 0, 0.4) !important;
    z-index: 10;
    position: relative;
  }

  .center-card::before,
  .center-card::after {
    border-color: rgba(255, 0, 255, 0.8) !important;
  }

  .card-visual {
    height: 85px;
    width: 100%;
  }

  .card-content {
    padding: 4px;
  }

  .card-name {
    font-size: 12px;
  }

  .card-effect {
    font-size: 9px;
  }

  .info-popup {
    width: 100px;
    padding: 4px 6px;
  }

  .info-popup span {
    font-size: 0.45rem;
  }

  .arrow-button {
    width: 32px;
    height: 32px;
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    flex-shrink: 0;
    z-index: 100;
    position: absolute;
  }

  .skills-container .arrow-button:first-child {
    left: 4px;
  }

  .skills-container .arrow-button:last-child {
    right: 4px;
  }

  .talents-container .arrow-button:first-child {
    left: 4px;
  }

  .talents-container .arrow-button:last-child {
    right: 4px;
  }

  .skills-container,
  .talents-container {
    position: relative;
  }

  .button-bar {
    flex-direction: column;
    gap: 10px;
    padding: 12px;
  }

  .start-btn,
  .logout-btn {
    width: 100%;
    max-width: 100%;
    font-size: 14px !important;
    padding: 10px 20px !important;
  }
}

@media (max-width: 480px) {
  .skill-card,
  .talent-card {
    flex: 0 0 130px;
  }

  .card-visual {
    height: 100px;
    width: 100%;
  }

  .card-track {
    gap: 15px;
  }

  .form-input,
  .form-select {
    padding: 10px 12px;
    font-size: 14px;
  }

  .stat-btn {
    width: 28px;
    height: 28px;
  }

  /* Active Display Container - Mobile */
  .active-display-container {
    gap: 12px;
    margin-top: 12px;
    padding: 0 8px;
  }

  .active-section {
    max-width: 100%;
    padding: 12px;
  }

  .active-title {
    font-size: 14px;
    margin-bottom: 10px;
  }

  .active-list {
    gap: 6px;
  }

  .active-box {
    font-size: 11px;
    padding: 6px 10px;
  }
}
</style>