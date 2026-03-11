<template>
  <div class="character-creation">
    <h2>Create Your Character</h2>

    <div class="creation-layout">
      <!-- Left Panel -->
      <div class="left-panel">
        <!-- Character Info -->
        <div>
          <label>Character Name:</label>
          <input type="text" v-model="character.name" placeholder="Enter name" />
        </div>

        <div>
          <label>Choose Age Group:</label>
          <select v-model="character.ageGroup" @change="applyAgeBonus">
            <option value="None">None</option>
            <option value="child">Child</option>
            <option value="teenager">Teenager</option>
            <option value="adult">Adult</option>
            <option value="old">Old</option>
          </select>
        </div>

        <div>
          <label>Choose Gender:</label>
          <select v-model="character.gender" @change="applyGenderBonus">
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
            <h3>Allocate Stats (Remaining: {{ remainingPoints }})</h3>
            <button class="diagram-toggle" @click="toggleDiagram">
              {{ showDiagram ? 'Hide Diagram' : 'Show Diagram' }}
            </button>
          </div>

          <div v-for="(value, stat) in character.stats" :key="stat" class="stat-row">
            <label>{{ stat }}:</label>
            <div class="stat-box">
              <button @click="decreaseStat(stat)">-</button>
              <span>{{ effectiveStats.visible[stat] }}</span>
              <button @click="increaseStat(stat)">+</button>
            </div>
          </div>
        </div>

       <!-- Active Skills and Talents -->
<div class="active-container">
  <h3 class="active-title">Active Skills</h3>
  <div class="active-list">
    <div v-for="skill in character.skills" :key="skill.name" class="active-box">
      {{ skill.name }}
    </div>
  </div>

  <!-- Divider / Gap -->
  <div class="section-gap"></div>

  <h3 class="active-title">Active Talents</h3>
  <div class="active-list">
    <div v-for="talent in character.talents" :key="talent.name" class="active-box">
      {{ talent.name }}
    </div>
  </div>
</div>


      </div>

      <!-- Right Panel -->
      <div class="right-panel">
        <!-- Skills Carousel -->
        <div class="skills-container">
          <h3>Choose Skills</h3>
          <div class="card-carousel">
            <button class="arrow-button" @click="prevSkillCard">◄</button>
            <div class="card-track">
              <div v-for="(skill, index) in visibleSkills" :key="skill.name" class="skill-card"
                :class="{ selected: character.skills.includes(skill), 'center-card': index === 2 }"
                @click="toggleSkill(skill)">
                <img :src="skill.image" alt="skill image" class="card-image" />
                <div class="info-icon">
                  ℹ
                  <div class="info-popup">
                    <span>{{ skill.description }}</span>
                  </div>
                </div>
                <div class="card-content">
                  <h4>{{ skill.name }}</h4>
                </div>
              </div>
            </div>
            <button class="arrow-button" @click="nextSkillCard">►</button>
          </div>
        </div>

        <!-- Talents Carousel -->
        <div class="talents-container">
          <h3>Choose Talents</h3>
          <div class="card-carousel">
            <button class="arrow-button" @click="prevTalentCard">◄</button>
            <div class="card-track">
              <div v-for="(talent, index) in visibleTalents" :key="talent.name" class="talent-card"
                :class="{ selected: character.talents.includes(talent), 'center-card': index === 2 }"
                @click="toggleTalent(talent)">
                <div class="info-icon">
                  ℹ
                  <div class="info-popup">
                    <span>{{ talent.description }}</span>
                  </div>
                </div>
                <img :src="talent.image" alt="talent image" class="card-image" />
                <div class="card-content">
                  <h4>{{ talent.name }}</h4>
                </div>
              </div>
            </div>
            <button class="arrow-button" @click="nextTalentCard">►</button>
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

    <!-- Start Game button -->
    <div class="button-bar">
      <div class="start-button-container">
        <button @click="finalizeCharacter" :disabled="isSaving">{{ isSaving ? 'Creating...' : 'Start Game' }}</button>
      </div>
      <div class="logout-button-container">
        <button @click="quitGame" :disabled="isSaving">Logout</button>
      </div>
    </div>
  </div>

  <div v-if="showPopup" class="popup-overlay" @click.self="showPopup = false">
    <div class="popup-box">
      <v-img src="/css/images/ulife1.png" alt="U:LIFE Logo" max-width="120" contain class="popup-icon" />
      <h3>Notice</h3>
      <p>{{ popupMessage }}</p>
      <button @click="showPopup = false">OK</button>
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
        { name: "Manual Labor", effect: "+3 Strength, +1 Wealth", hidden: "+1 Debt", description: "Work with your hands to earn and build. Strengthens body and finances but can trap you in exhausting cycles.", image: "/css/images/skill/manual-labor.png" },
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
  { name: "Empathy", effect: "+5 Morality, -2 Luck", hidden: "-1 Stress", description: "Deeply attuned to others’ emotions, fostering compassion.", image: "/css/images/talent/empathy.png" },
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

      // Define per-stat colors
      const statColors = [
        'rgba(54, 162, 235, 0.6)', // Intelligence
        'rgba(255, 99, 132, 0.6)', // Strength
        'rgba(255, 206, 86, 0.6)', // Charisma
        'rgba(75, 192, 192, 0.6)', // Creativity
        'rgba(153, 102, 255, 0.6)', // Wealth
        'rgba(255, 159, 64, 0.6)', // Luck
        'rgba(99, 255, 132, 0.6)', // Health
        'rgba(200, 200, 200, 0.6)' // Morality
      ];

      this.chart = new Chart(ctx, {
        type: "radar",
        data: {
          labels: Object.keys(this.character.stats),
          datasets: [
            {
              label: "Stats",
              data: Object.values(this.effectiveStats.visible),
              backgroundColor: "rgba(0, 200, 255, 0.2)",
              borderColor: "rgba(0, 200, 255, 0.8)",
              pointBackgroundColor: statColors,
              pointRadius: 4
            }
          ]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false }, // hide legend
            tooltip: { enabled: true }
          },
          scales: {
            r: {
              beginAtZero: true,
              max: 100,
              ticks: { display: false }, // hide numbers
              grid: { color: function (context) { return context.index % 2 === 0 ? "#FFFFFF" : "#00a000"; } },
              angleLines: { color: "#00a000" }
            }
          }
        }
      });
    },

    updateChart() {
      if (this.chart) {
        this.chart.data.datasets[0].data = Object.values(this.effectiveStats.visible);
        this.chart.data.datasets[1].data = Object.values(this.effectiveStats.hidden);
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
/* Character Creation Layout */
.character-creation {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  width: 100%;
  font-family: 'Press Start 2P', cursive;
  color: #fff;
  background: #1e1e2f;
  padding: 25px 20px;
  overflow-x: hidden;
  /* prevent horizontal scrollbar */
}

.character-creation h2 {
  text-align: center;
  font-size: 20px;
  margin-bottom: 20px;
  text-shadow: 2px 2px #000;
}

.creation-layout {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  justify-content: center;
  max-width: 100%;
  /* ensure no overflow */
  overflow-x: hidden;
  /* extra safeguard */
}

/* Panels */
.left-panel,
.right-panel {
  background: #2a2a3d;
  padding: 20px;
  border: 4px solid #555;
  border-radius: 12px;
  box-shadow: inset 4px 4px #000;
}

.left-panel {
  flex: 1 1 300px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.right-panel {
  flex: 2 1 500px;
  display: flex;
  flex-direction: column;
  gap: 40px;
  padding-top: 20px;
}

/* Section Headers */
.skills-container h3,
.talents-container h3 {
  text-align: center;
  font-size: 25px;
  margin-bottom: 10px;
  text-shadow: 1px 1px #000;
  border-bottom: 2px solid #444;
  padding-bottom: 6px;
  color: #ffd700;
  /* bright yellow */
  text-shadow: 1px 1px #000;
  /* keep retro shadow */
}

/* Inputs */
.left-panel label {
  display: block;
  font-size: 12px;
  margin-bottom: 6px;
  color: #00a000;
}

.left-panel input[type="text"],
.left-panel select,
.left-panel input[type="number"] {
  font-family: 'Press Start 2P', cursive;
  background: #333;
  color: #fff;
  border: 2px solid #555;
  padding: 8px;
  border-radius: 6px;
  width: 100%;
  box-sizing: border-box;
}

/* Stats */
.stats-header {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid #ccc;
  margin-bottom: 10px;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.stat-row label {
  flex: 1;
  color: #ffd700;
  font-size: 12px;
}

.stat-box {
  display: flex;
  align-items: center;
  background: #333;
  border: 2px solid #555;
  border-radius: 6px;
  padding: 4px 8px;
}

.stat-box span {
  font-family: 'Press Start 2P', cursive;
  color: #fff;
  margin: 0 10px;
  min-width: 24px;
  text-align: center;
}

.stat-box button {
  font-family: 'Press Start 2P', cursive;
  background: #444;
  color: #fff;
  border: 2px solid #666;
  padding: 4px 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.stat-box button:hover {
  background: #00a000;
}

/* Diagram Toggle */
.diagram-toggle {
  font-size: 0.75rem;
  padding: 3px 8px;
  background: #0af;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.diagram-toggle:hover {
  background: #08c;
}

/* Carousel */
.card-carousel {
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
  width: 100%;
  min-height: 32vh;
  padding-top: 5px;
}

.card-track {
  display: flex;
  gap: 20px;
  transition: transform 0.5s ease;
  width: max-content;
  margin: 0 auto;
}

.skill-card,
.talent-card {
  flex: 0 0 155px;
  margin: 0 10px;
  border: 3px solid #888;
  border-radius: 8px;
  background: #2a2a3d;
  transition: transform 0.3s, box-shadow 0.3s;
  box-shadow: 4px 4px #000;
  position: relative;
}

.skill-card:hover,
.talent-card:hover {
  transform: scale(1.1);
  box-shadow: 0 0 12px #0af;
}

.skill-card.selected,
.talent-card.selected {
  border-color: #00ff88;
  box-shadow: 0 0 15px #00ff88;
  transform: scale(1.15);
}

.center-card {
  transform: scale(1.25);
  z-index: 2;
  box-shadow: 0 0 20px #ffd700;
  border-color: #ffd700;
}

/* Card Content */
.card-image {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-bottom: 2px solid #555;
}

.card-content {
  position: relative;
  padding: 10px;
  text-align: center;
  color: #f0f0f0;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 0 0 6px 6px;
  max-height: 5vh;
}

.card-content h4 {
  font-size: 10px;
  margin-bottom: 10px;
  color: #ffd700;
}

.card-content p {
  font-size: 10px;
  margin: 0;
}


.arrow-button {
  font-size: 28px;
  font-family: 'Press Start 2P', cursive;
  background: none;
  color: #00ff88;
  border: none;
  cursor: pointer;
  text-shadow: 0 0 8px #00ff88;
  transition: transform 0.2s, color 0.2s, text-shadow 0.2s;
}

.arrow-button:hover {
  color: #ffffff;
  text-shadow: 0 0 12px #00ff88, 0 0 20px #00ff88;
  transform: scale(1.3);
}

.arrow-button:active {
  transform: scale(0.9);
  text-shadow: 0 0 6px #008000;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  animation: fadeIn 0.3s ease;
}

.modal-content {
  background: #111;
  padding: 20px;
  border-radius: 8px;
  width: 85%;
  max-width: 1000px;
  box-shadow: 0 0 25px rgb(124, 248, 99), inset 0 0 10px #333;
}

.chart-container {
  width: 90%;
  max-width: 700px;
  height: auto;
  aspect-ratio: 1 / 1;
  /* keep square shape */
  margin: 0 auto;
}

.modal-content canvas {
  width: 100% !important;
  height: 100% !important;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}
/* Buttons */
.button-bar {
  display: flex;
  justify-content: center;
  gap: 12px;
  background: #1e1e2f;
  padding: 12px;
}

.button-bar button {
  font-family: 'Press Start 2P', cursive;
  background: linear-gradient(180deg, #7dff7d, #014e05);
  color: #000000;
  border: 2px solid #000000;
  padding: 8px 16px;
  /* smaller size */
  border-radius: 4px;
  cursor: pointer;
  box-shadow: 2px 2px #000;
  transition: transform 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
  font-size: 12px;
  /* smaller text */
}

.button-bar button:hover {
  transform: scale(1.05) rotate(-1deg);
  /* subtle tilt for cool effect */
  background: linear-gradient(180deg, #ffd700, #ff8800);
  color: #000;
  box-shadow: 3px 3px #222;
}

.button-bar button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  background: linear-gradient(180deg, #7dff7d, #014e05);
}


.v-snackbar {
  font-family: 'Press Start 2P', monospace;
  text-transform: uppercase;
  box-shadow: 0 0 8px #00ff99;
}

.v-snackbar.success {
  background-color: #00ff99;
  color: #000;
}

.v-snackbar.error {
  background-color: #ff0044;
  color: #fff;
}
.stats-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
  border-bottom: 2px solid #444141;
  /* full-width line under header */
  padding-bottom: 4px;
}

.stats-header h3 {
  margin: 0;
  font-size: 13px;
}


.skill-card.selected,
.talent-card.selected {
  border-color: #4caf50;
  /* green border when selected */
  box-shadow: 0 0 15px #4caf50;
  transform: scale(1.1);
}

.center-card {
  transform: scale(1.2);
  border-color: #ffd700;
  /* gold highlight for center */
  box-shadow: 0 0 20px #ffd700;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 16px;

  height: 16px;

}

::-webkit-scrollbar-track {
  background: #1e1e2f;

}

::-webkit-scrollbar-thumb {
  background: #008000;

}

::-webkit-scrollbar-thumb:hover {
  background: #00a000;

}
.info-icon {
  position: absolute;
  display: inline-block;
  top: 6px;
  right: 6px;
  background: rgba(255, 235, 59, 0.7);
  color: #000;
  font-size: 10px;
  font-weight: bold;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  text-align: center;
  line-height: 18px;
  cursor: pointer;
  box-shadow: 2px 2px 0 #000;
  font-family: 'Press Start 2P', monospace;
}

.info-popup {
  display: none;
  position: absolute;
  top: 50%;
  right: 120%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.85);
  color: #fff;
  padding: 4px 8px;
  border-radius: 4px;
  width: 200px;
  overflow: hidden;
  white-space: nowrap;
  text-align: center;
}

.info-popup span {
  display: inline-block;
  min-width: 100%;
  animation: scroll-text 20s linear infinite;
}

.info-icon:hover .info-popup {
  display: block;
}

@keyframes scroll-text {
  0% {
    transform: translateX(0%);
  }

  100% {
    transform: translateX(-100%);
  }
}
.active-container {
  margin-top: 20px;
  padding: 12px;
  background: #333;
  border: 4px solid #000000;
  box-shadow: 6px 6px 0 #333;
}

.active-title {
  font-family: 'Press Start 2P', monospace;
  font-size: 14px;
  color: #ffffff;
  text-shadow: 2px 2px #000000;
  margin-bottom: 8px;
  text-transform: uppercase;
}

.active-list {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 15px;
}

.active-box {
  background-color: #008000;
  color: #fdfa2d;
  padding: 10px 14px;
  border-radius: 5px;
  font-family: 'Press Start 2P', monospace;
  font-size: 12px;
  text-transform: uppercase;
  text-align: center;
  min-width: 100px;
  box-shadow: 4px 4px 0 #333;
  transition: transform 0.1s ease-in-out;
}

.active-box:hover {
  transform: scale(1.1);
  box-shadow: 6px 6px 0 #222;
}

/* Gap between sections */
.section-gap {
  margin: 20px 0;
  border-top: 2px dashed #000000; /* optional retro divider */
}
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.popup-box {
  background: linear-gradient(180deg, #222, #111); /* darker neutral background */
  border: 3px solid #000000;
  border-radius: 8px;
  padding: 20px;
  width: 680px; /* wider popup */
  text-align: center;
  font-family: 'Press Start 2P', cursive;
  color: #fff; /* neutral text color */
  box-shadow: 0 0 16px #7dff7d, 0 0 32px #014e05; /* keep green glow accent */
  animation: popup-appear 0.3s ease-out;
}
.popup-icon {
  margin: 0 auto 20px auto;
  display: block;
  max-width: 140px; /* bigger logo */
  filter: drop-shadow(0 0 8px #7dff7d) drop-shadow(0 0 16px #014e05);
  animation: pulse-glow 2s infinite, glitch 1.5s infinite;
}

.popup-box h3 {
  margin-bottom: 12px;
  font-size: 16px;
  text-shadow: 2px 2px #000;
}

.popup-box p {
  font-size: 14px;
  margin-bottom: 20px;
}

.popup-box button {
  font-family: 'Press Start 2P', cursive;
  background: linear-gradient(180deg, #7dff7d, #014e05);
  color: #000;
  border: 2px solid #000000;
  padding: 8px 16px;
  cursor: pointer;
  transition: transform 0.2s, background 0.2s;
}

.popup-box button:hover {
  transform: scale(1.1);
  background: linear-gradient(180deg, #ffd700, #ff8800);
  color: #000;
}

@keyframes pulse-glow {
  0% { filter: drop-shadow(0 0 6px #7dff7d) drop-shadow(0 0 12px #014e05); }
  50% { filter: drop-shadow(0 0 14px #7dff7d) drop-shadow(0 0 24px #014e05); }
  100% { filter: drop-shadow(0 0 6px #7dff7d) drop-shadow(0 0 12px #014e05); }
}

@keyframes glitch {
  0% { transform: translate(0,0) scale(1); }
  20% { transform: translate(-2px, 1px) scale(1.05); }
  40% { transform: translate(2px, -1px) scale(0.98); }
  60% { transform: translate(-1px, 2px) scale(1.02); }
  80% { transform: translate(1px, -2px) scale(1.04); }
  100% { transform: translate(0,0) scale(1); }
}

@keyframes popup-appear {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>