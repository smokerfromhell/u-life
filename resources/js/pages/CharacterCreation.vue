<template>
  <div class="character-creation">
    <!-- Animated Background -->
    <div class="bg-particles">
      <div v-for="n in 30" :key="n" class="particle" :style="getParticleStyle(n)"></div>
    </div>
    <div class="bg-grid"></div>

    <!-- Main Container -->
    <div class="creation-container">
      <h2 class="page-title">Create Your Character</h2>

      <div class="creation-layout">
        <!-- Left Panel -->
        <div class="left-panel">
          <!-- Character Info -->
          <div class="form-group">
            <label class="form-label">Character Name</label>
            <input type="text" v-model="character.name" placeholder="Enter name" class="form-input" />
          </div>

          <div class="form-group">
            <label class="form-label">Choose Age Group</label>
            <select v-model="character.ageGroup" @change="applyAgeBonus" class="form-select">
              <option value="None">None</option>
              <option value="child">Child</option>
              <option value="teenager">Teenager</option>
              <option value="adult">Adult</option>
              <option value="old">Old</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Choose Gender</label>
            <select v-model="character.gender" @change="applyGenderBonus" class="form-select">
              <option value="None">None</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="non-binary">Non-Binary</option>
              <option value="transgender">Transgender</option>
            </select>
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
                <label class="stat-label">{{ stat }}</label>
                <div class="stat-controls">
                  <button class="stat-btn" @click="decreaseStat(stat)">−</button>
                  <span class="stat-value">{{ effectiveStats.visible[stat] }}</span>
                  <button class="stat-btn" @click="increaseStat(stat)">+</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Active Skills and Talents -->
          <div class="active-container">
            <h3 class="active-title">
              <v-icon size="16" class="mr-1">mdi-star</v-icon>
              Active Skills
            </h3>
            <div class="active-list">
              <div v-for="skill in character.skills" :key="skill.name" class="active-box skill-box">
                {{ skill.name }}
              </div>
              <span v-if="character.skills.length === 0" class="no-items">No skills selected</span>
            </div>

            <div class="section-gap"></div>

            <h3 class="active-title">
              <v-icon size="16" class="mr-1">mdi-sparkles</v-icon>
              Active Talents
            </h3>
            <div class="active-list">
              <div v-for="talent in character.talents" :key="talent.name" class="active-box talent-box">
                {{ talent.name }}
              </div>
              <span v-if="character.talents.length === 0" class="no-items">No talents selected</span>
            </div>
          </div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
          <!-- Skills Carousel -->
          <div class="skills-container">
            <h3 class="carousel-title">
              <v-icon class="header-icon">mdi-star-circle</v-icon>
              Choose Skills
            </h3>
            <div class="card-carousel">
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
            <h3 class="carousel-title">
              <v-icon class="header-icon">mdi-sparkles</v-icon>
              Choose Talents
            </h3>
            <div class="card-carousel">
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

      <!-- Modal Popup -->
      <div v-if="showDiagram" class="modal-overlay" @click.self="toggleDiagram">
        <div class="modal-content">
          <div class="chart-container"> <canvas ref="statChart"></canvas> </div>
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
      <div class="popup-box">
        <v-img src="/css/images/ulife1.png" alt="U:LIFE Logo" max-width="120" contain class="popup-icon" />
        <h3>Notice</h3>
        <p>{{ popupMessage }}</p>
        <v-btn color="success" @click="showPopup = false">OK</v-btn>
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
      totalPoints: 30,
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
          Luck: 0
        },
        hiddenStats: {
          Debt: 0,
          Health: 0,
          Addiction: 0,
          Burnout: 0,
          Morality: 0,
          Happiness: 0,
          Reputation: 0,
          Discipline: 0,
          Isolation: 0,
          Ego: 0,
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
      popupMessage: ""
    };
  },
  computed: {
    remainingPoints() {
      return this.totalPoints - Object.values(this.character.stats).reduce((a, b) => a + b, 0);
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
  methods: {
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
          this.genderBonus = { Strength: 2 };
          break;
        case "female":
          this.genderBonus = { Intelligence: 2 };
          break;
        case "non-binary":
          this.genderBonus = { Creativity: 1, Luck: 1 };
          break;
        case "transgender":
          this.genderBonus = { Charisma: 1, Luck: 1 };
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
          this.ageBonus = { Luck: 1, Creativity: 1 };
          break;
        case "teenager":
          this.ageBonus = { Strength: 2, Intelligence: 1 };
          break;
        case "adult":
          this.ageBonus = { Strength: 4, Intelligence: 3 };
          break;
        case "old":
          this.ageBonus = { Strength: -2, Intelligence: 6 };
          break;
        default:
          this.ageBonus = {};
      }
      this.updateChart();
    },

    increaseStat(stat) {
      if (this.remainingPoints > 0 && this.character.stats[stat] < 20) {
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

      // Create multiple layered gradients for the fill
      const gradientOuter = ctx.createRadialGradient(
        canvas.width / 2, canvas.height / 2, 0,
        canvas.width / 2, canvas.height / 2, canvas.width / 2
      );
      gradientOuter.addColorStop(0, 'rgba(34, 197, 94, 0.5)');
      gradientOuter.addColorStop(0.4, 'rgba(34, 197, 94, 0.25)');
      gradientOuter.addColorStop(0.7, 'rgba(34, 197, 94, 0.1)');
      gradientOuter.addColorStop(1, 'rgba(34, 197, 94, 0.02)');

      this.chart = new Chart(ctx, {
        type: "radar",
        data: {
          labels: Object.keys(this.character.stats),
          datasets: [
            {
              label: "Your Stats",
              data: Object.values(this.effectiveStats.visible),
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
            }
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
              callbacks: {
                label: function(context) {
                  return ' ' + context.raw + ' pts';
                }
              }
            }
          },
          scales: {
            r: {
              beginAtZero: true,
              max: 100,
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
                color: "rgba(34, 197, 94, 0.2)",
                lineWidth: 1,
              },
              angleLines: { 
                color: "rgba(34, 197, 94, 0.3)",
                lineWidth: 2,
              },
              pointLabels: {
                color: "#f0fdf4",
                font: { size: 15, weight: '800', family: 'Instrument Sans' },
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
/* ========================================
   MAIN SCREEN - Enhanced Game Background
   ======================================== */
.character-creation {
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
   CREATION CONTAINER
   ======================================== */
.creation-container {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
  padding: 16px;
  box-sizing: border-box;
  overflow-x: hidden;
}

.page-title {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  text-align: center;
  font-size: 1.8rem;
  font-weight: 700;
  color: #22c55e;
  margin-bottom: 24px;
  text-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
}

.creation-layout {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
  align-items: flex-start;
}

/* ========================================
   PANELS
   ======================================== */
.left-panel,
.right-panel {
  background: linear-gradient(155deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
  border: 2px solid rgba(134, 238, 135, 0.4);
  border-radius: 16px;
  padding: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 
    0 10px 40px rgba(0, 0, 0, 0.5),
    0 0 20px rgba(34, 197, 94, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.left-panel {
  flex: 1 1 380px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 340px;
  max-width: 450px;
}

.right-panel {
  flex: 2 1 780px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  min-width: 720px;
}

/* ========================================
   FORM ELEMENTS
   ======================================== */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.form-input,
.form-select {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1rem;
  background: rgba(0, 0, 0, 0.3);
  color: #f0fdf4;
  border: 2px solid rgba(134, 238, 135, 0.3);
  padding: 12px 16px;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #22c55e;
  box-shadow: 0 0 15px rgba(34, 197, 94, 0.2);
}

.form-input::placeholder {
  color: #64748b;
}

.form-select option {
  background: #1e293b;
  color: #f0fdf4;
}

/* ========================================
   STATS SECTION
   ======================================== */
.stats-container {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 16px;
  padding: 20px;
  border: 1px solid rgba(134, 238, 135, 0.2);
}

.stats-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(134, 238, 135, 0.2);
}

.stats-title {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1rem;
  font-weight: 700;
  color: #f0fdf4;
  margin: 0;
}

.points-remaining {
  font-size: 0.8rem;
  color: #22c55e;
  font-weight: 500;
}

.diagram-toggle {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 6px 14px;
  background: rgba(34, 197, 94, 0.15);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.4);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.diagram-toggle:hover {
  background: rgba(34, 197, 94, 0.25);
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
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
  border-left: 3px solid #22c55e;
}

.stat-label {
  color: #94a3b8;
  font-size: 0.85rem;
  font-weight: 500;
  flex: 1;
}

.stat-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stat-btn {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1.2rem;
  font-weight: 600;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(34, 197, 94, 0.15);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.4);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.stat-btn:hover {
  background: rgba(34, 197, 94, 0.3);
  transform: scale(1.1);
}

.stat-value {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1rem;
  font-weight: 700;
  color: #22c55e;
  min-width: 28px;
  text-align: center;
}

/* ========================================
   ACTIVE CONTAINER (Skills/Talents Display)
   ======================================== */
.active-container {
  margin-top: 8px;
  padding: 20px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 16px;
  border: 1px solid rgba(134, 238, 135, 0.2);
}

.active-title {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 0.85rem;
  font-weight: 700;
  color: #22c55e;
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
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 0.7rem;
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
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: #000;
}

.talent-box {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #000;
}

.no-items {
  color: #64748b;
  font-size: 0.8rem;
  font-style: italic;
}

.section-gap {
  margin: 16px 0;
  border-top: 1px dashed rgba(134, 238, 135, 0.2);
}

/* ========================================
   CAROUSEL SECTION
   ======================================== */
.skills-container,
.talents-container {
  background: rgba(0, 0, 0, 0.15);
  border-radius: 16px;
  padding: 16px;
  height: 520px;
  overflow: visible;
}

.carousel-title {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1rem;
  font-weight: 700;
  color: #f0fdf4;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  background: linear-gradient(90deg, rgba(34, 197, 94, 0.15), transparent);
  border-radius: 10px;
  border-left: 4px solid #22c55e;
}

.header-icon {
  color: #22c55e !important;
}

.card-carousel {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  overflow: visible;
  position: relative;
  min-height: 280px;
  padding: 0 4px;
}

.card-track {
  padding-top: 20px;
  padding-bottom: 50px;
  height: 340px;
  display: flex;
  gap: 35px;
  transition: transform 0.5s ease;
  width: 100%;
  justify-content: center;
  flex-wrap: nowrap;
  margin-top: 30px;
}


.talent-card {
  flex: 0 0 190px;
  margin: 0 3px;
  border: 2px solid rgba(134, 238, 135, 0.3);
  border-radius: 12px;
  background: linear-gradient(155deg, #1e293b 0%, #0f172a 100%);
  transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 
    0 4px 6px rgba(0, 0, 0, 0.3),
    0 1px 3px rgba(0, 0, 0, 0.2);
  cursor: pointer;
  position: relative;
  z-index: 1;
}

.skill-card {
  flex: 0 0 190px;
  margin: 0 3px;
  border: 2px solid rgba(134, 238, 135, 0.3);
  border-radius: 12px;
  background: linear-gradient(155deg, #1e293b 0%, #0f172a 100%);
  transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 
    0 4px 6px rgba(0, 0, 0, 0.3),
    0 1px 3px rgba(0, 0, 0, 0.2);
  cursor: pointer;
  position: relative;
  z-index: 1;
}

/* Card corner decorations */
.skill-card::before,
.talent-card::before {
  content: '';
  position: absolute;
  top: 8px;
  left: 8px;
  width: 20px;
  height: 20px;
  border: 2px solid rgba(134, 238, 135, 0.3);
  border-radius: 4px;
  z-index: 2;
}

.skill-card::after,
.talent-card::after {
  content: '';
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 20px;
  height: 20px;
  border: 2px solid rgba(134, 238, 135, 0.3);
  border-radius: 4px;
  transform: rotate(180deg);
  z-index: 2;
}

.skill-card:hover,
.talent-card:hover {
  border-color: #22c55e;
  transform: translateY(-12px) scale(1.02) rotateX(5deg);
  box-shadow: 
    0 20px 40px rgba(34, 197, 94, 0.3),
    0 8px 16px rgba(0, 0, 0, 0.4);
}

.skill-card.selected,
.talent-card.selected {
  border-color: #22c55e;
  box-shadow: 
    0 0 25px rgba(34, 197, 94, 0.5),
    0 8px 16px rgba(0, 0, 0, 0.4);
}

.center-card {
  transform: scale(1.15);
  border-color: #fbbf24;
  box-shadow: 
    0 0 30px rgba(251, 191, 36, 0.4),
    0 12px 24px rgba(0, 0, 0, 0.5);
  z-index: 5;
}

.center-card::before,
.center-card::after {
  border-color: rgba(251, 191, 36, 0.5);
}

/* Card Visual */
.card-visual {
  position: relative;
  height: 160px;
  border-bottom: 1px solid rgba(134, 238, 135, 0.1);
  overflow: hidden;
  border-radius: 10px 10px 0 0;
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 10px 10px 0 0;
}

/* Info Icon */
.info-icon {
  position: absolute;
  top: 6px;
  right: 6px;
  background: rgba(255, 235, 59, 0.9);
  color: #000;
  font-size: 10px;
  font-weight: bold;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  text-align: center;
  line-height: 18px;
  cursor: pointer;
  box-shadow: 2px 2px 0 rgba(0, 0, 0, 0.3);
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
  padding: 8px 12px;
  border-radius: 8px;
  width: 160px;
  z-index: 100;
  border: 2px solid rgba(34, 197, 94, 0.6);
  box-shadow: -8px 0 20px rgba(34, 197, 94, 0.3);
  margin-right: 8px;
}

.info-popup span {
  display: block;
  font-size: 0.6rem;
  line-height: 1.3;
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
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 0.65rem;
  font-weight: 700;
  color: #f0fdf4;
  margin-bottom: 2px;
}

.card-effect {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 0.55rem;
  color: #94a3b8;
  margin: 0;
  line-height: 1.1;
}

/* ========================================
   ARROW BUTTONS
   ======================================== */
.arrow-button {
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0.1));
  color: #22c55e;
  border: 2px solid rgba(34, 197, 94, 0.5);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
}

.arrow-button:hover {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.4), rgba(34, 197, 94, 0.2));
  border-color: #22c55e;
  transform: scale(1.1);
  box-shadow: 0 0 20px rgba(34, 197, 94, 0.5);
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
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  padding: 40px;
  border-radius: 32px;
  width: 98%;
  max-width: 850px;
  border: 3px solid #22c55e;
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.6),
    0 0 80px rgba(34, 197, 94, 0.4),
    0 0 120px rgba(34, 197, 94, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.1),
    inset 0 0 60px rgba(34, 197, 94, 0.05);
  animation: diagram-appear 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  overflow: hidden;
}

.modal-content::before {
  content: '';
  position: absolute;
  top: -30%;
  left: -30%;
  width: 160%;
  height: 160%;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.08) 0%, transparent 60%);
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

.modal-content .chart-container {
  width: 100%;
  aspect-ratio: 1 / 1;
  max-height: 600px;
  position: relative;
  z-index: 1;
}

.modal-content canvas {
  width: 100% !important;
  height: 100% !important;
  filter: drop-shadow(0 0 10px rgba(34, 197, 94, 0.3));
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
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color: #000 !important;
  font-weight: 700 !important;
  font-size: 1rem !important;
  padding: 14px 48px !important;
  border-radius: 12px !important;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  box-shadow: 
    0 6px 20px rgba(34, 197, 94, 0.4),
    0 0 30px rgba(34, 197, 94, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.3),
    inset 0 -2px 0 rgba(0, 0, 0, 0.2) !important;
  transition: all 0.3s ease !important;
  position: relative;
  overflow: hidden;
  border: 2px solid #4ade80 !important;
}

.start-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.5s ease;
}

.start-btn:hover:not(:disabled)::before {
  left: 100%;
}

.start-btn:hover:not(:disabled) {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 
    0 12px 40px rgba(34, 197, 94, 0.5),
    0 0 50px rgba(34, 197, 94, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.4),
    inset 0 -2px 0 rgba(0, 0, 0, 0.2) !important;
}

.start-btn:active:not(:disabled) {
  transform: translateY(1px) scale(0.98);
}

.logout-btn {
  font-weight: 600 !important;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  border-radius: 12px !important;
  border: 2px solid rgba(239, 68, 68, 0.5) !important;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(185, 28, 28, 0.3)) !important;
  color: #fca5a5 !important;
  transition: all 0.3s ease !important;
  position: relative;
  overflow: hidden;
}

.logout-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.3), transparent);
  transition: left 0.5s ease;
}

.logout-btn:hover:not(:disabled)::before {
  left: 100%;
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
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  animation: fadeIn 0.3s ease;
  backdrop-filter: blur(5px);
}

.popup-box {
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  border: 3px solid #22c55e;
  border-radius: 24px;
  padding: 40px;
  width: 90%;
  max-width: 420px;
  text-align: center;
  box-shadow: 
    0 20px 60px rgba(0, 0, 0, 0.6),
    0 0 40px rgba(34, 197, 94, 0.3),
    0 0 80px rgba(34, 197, 94, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  animation: popup-appear 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  overflow: hidden;
}

.popup-box::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.1) 0%, transparent 70%);
  animation: pulse-bg 3s ease-in-out infinite;
}

@keyframes pulse-bg {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.1); }
}

.popup-icon {
  margin: 0 auto 20px auto;
  display: block;
  filter: drop-shadow(0 0 12px rgba(34, 197, 94, 0.6));
  animation: pulse-glow 2s infinite;
  position: relative;
  z-index: 1;
}

.popup-box h3 {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1.6rem;
  font-weight: 700;
  color: #22c55e;
  margin-bottom: 20px;
  text-shadow: 0 0 20px rgba(34, 197, 94, 0.8), 0 0 40px rgba(34, 197, 94, 0.4);
  position: relative;
  z-index: 1;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.popup-box h3::before {
  content: '⚠';
  display: block;
  font-size: 2rem;
  margin-bottom: 8px;
  animation: shake 0.5s ease-in-out infinite;
}

@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-5deg); }
  75% { transform: rotate(5deg); }
}

.popup-box p {
  font-family: 'Instrument Sans', 'Segoe UI', sans-serif;
  font-size: 1.15rem;
  color: #fef3c7;
  margin-bottom: 28px;
  line-height: 1.7;
  position: relative;
  z-index: 1;
  padding: 16px 20px;
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(245, 158, 11, 0.1));
  border-radius: 12px;
  border: 1px solid rgba(251, 191, 36, 0.4);
  box-shadow: inset 0 0 20px rgba(251, 191, 36, 0.1), 0 0 15px rgba(251, 191, 36, 0.2);
  text-shadow: 0 0 10px rgba(254, 243, 199, 0.3);
}

.popup-box .v-btn {
  position: relative;
  z-index: 1;
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color: #000 !important;
  font-weight: 700 !important;
  font-size: 1rem !important;
  padding: 12px 40px !important;
  border-radius: 12px !important;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  box-shadow: 
    0 4px 15px rgba(34, 197, 94, 0.4),
    0 0 25px rgba(34, 197, 94, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
  transition: all 0.3s ease !important;
  border: 2px solid #4ade80 !important;
}

.popup-box .v-btn:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 
    0 8px 25px rgba(34, 197, 94, 0.5),
    0 0 40px rgba(34, 197, 94, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.4) !important;
}

.popup-box .v-btn:active {
  transform: translateY(0) scale(0.98);
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
  0% { filter: drop-shadow(0 0 8px rgba(34, 197, 94, 0.5)); }
  50% { filter: drop-shadow(0 0 16px rgba(34, 197, 94, 0.8)); }
  100% { filter: drop-shadow(0 0 8px rgba(34, 197, 94, 0.5)); }
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
  background: #22c55e;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #16a34a;
}

/* ========================================
   MOBILE RESPONSIVE STYLES
   ======================================== */
@media (max-width: 768px) {
  .creation-container {
    padding: 10px;
    overflow-x: hidden;
    max-width: 100vw;
    box-sizing: border-box;
  }

  .page-title {
    font-size: 1.3rem;
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
    font-size: 0.9rem;
    padding: 8px 10px;
    margin-bottom: 10px;
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
    scrollbar-color: #22c55e rgba(0,0,0,0.2);
  }

  .card-track::-webkit-scrollbar {
    height: 4px;
  }

  .card-track::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.2);
    border-radius: 2px;
  }

  .card-track::-webkit-scrollbar-thumb {
    background: #22c55e;
    border-radius: 2px;
  }

  .skill-card,
  .talent-card {
    flex: 0 0 auto;
    min-width: 140px;
    max-width: 45%;
    width: 140px;
    box-sizing: border-box;
    height: auto;
    transition: all 0.3s ease;
  }

  .skill-card.center-card,
  .talent-card.center-card {
    flex: 0 0 auto;
    min-width: 150px;
    max-width: 48%;
    width: 150px;
    transform: scale(1.08);
    border-color: #fbbf24 !important;
    box-shadow: 
      0 0 25px rgba(251, 191, 36, 0.4),
      0 10px 20px rgba(0, 0, 0, 0.5) !important;
    z-index: 10;
    position: relative;
  }

  .center-card::before,
  .center-card::after {
    border-color: rgba(251, 191, 36, 0.8) !important;
  }

  .card-visual {
    height: 85px;
  }

  .card-content {
    padding: 4px;
  }

  .card-name {
    font-size: 0.55rem;
  }

  .card-effect {
    font-size: 0.45rem;
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
    font-size: 0.9rem !important;
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
  }

  .card-track {
    gap: 15px;
  }

  .form-input,
  .form-select {
    padding: 10px 12px;
    font-size: 0.9rem;
  }

  .stat-btn {
    width: 28px;
    height: 28px;
  }
}
</style>

