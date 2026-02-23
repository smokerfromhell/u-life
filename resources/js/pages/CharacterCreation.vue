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
        <button @click="finalizeCharacter">Start Game</button>
      </div>
      <div class="logout-button-container">
        <button @click="quitGame">Logout</button>
      </div>
    </div>
  </div>
  <div v-if="showPopup" class="popup-overlay" @click.self="showPopup = false">
    <div class="popup-box"> <!-- Centered Logo --> <v-img src="/css/images/ulife1.png" alt="U:LIFE Logo" max-width="120"
        contain class="popup-icon" />
      <h3>Notice</h3>
      <p>{{ popupMessage }}</p> <button @click="showPopup = false">OK</button>
    </div>
  </div>
</template>


<script>
import Chart from 'chart.js/auto';

export default {
  data() {
    return {
      totalPoints: 30,
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
        { name: "Reading", effect: "+3 Intelligence, +1 Creativity", hidden: "+1 Isolation", description: "Expand your mind through books. Builds knowledge but can make you retreat inward.", image: "reading.png" },
        { name: "Studying", effect: "+4 Intelligence, +1 Discipline", hidden: "+1 Stress", description: "Sharpen your intellect with focused learning. Boosts academics but can be mentally taxing.", image: "studying.png" },
        { name: "Problem-Solving", effect: "+3 Intelligence, +2 Creativity", hidden: "+1 Burnout", description: "Tackle challenges with logic and innovation. Enhances adaptability but constant puzzles can wear you down.", image: "problem-solving.png" },
        { name: "Memory Training", effect: "+3 Intelligence, +1 Luck", hidden: "+1 Stress", description: "Improve recall and retention. Strengthens intellect but can overload your mind.", image: "memory-training.png" },
        { name: "Fitness", effect: "+4 Strength, +1 Health", hidden: "+1 Discipline", description: "Train your body for endurance and resilience. Improves vitality but demands consistency.", image: "fitness.png" },
        { name: "Endurance", effect: "+3 Strength, +2 Discipline", hidden: "+1 Burnout", description: "Push your body past limits. Builds stamina but risks exhaustion.", image: "endurance.png" },
        { name: "Manual Labor", effect: "+3 Strength, +1 Wealth", hidden: "+1 Debt", description: "Work with your hands to earn and build. Strengthens body and finances but can trap you in exhausting cycles.", image: "manual-labor.png" },
        { name: "Cooking", effect: "+3 Creativity, +2 Health", hidden: "+1 Stress", description: "Prepare meals and nourish yourself. Builds independence but can be tiring.", image: "cooking.png" },
        { name: "Conversation", effect: "+4 Charisma, +1 Reputation", hidden: "+1 Stress", description: "Engage others with words and presence. Builds influence but constant interaction can drain energy.", image: "conversation.png" },
        { name: "Confidence", effect: "+3 Charisma, +2 Ego", hidden: "+1 Isolation", description: "Believe in yourself and project strength. Inspires others but can push you toward arrogance.", image: "confidence.png" },
        { name: "Teamwork", effect: "+3 Charisma, +2 Discipline", hidden: "+1 Burnout", description: "Collaborate effectively with groups. Builds trust but can lead to overreliance on others.", image: "teamwork.png" },
        { name: "Persuasion", effect: "+3 Charisma, +2 Intelligence", hidden: "+1 Ego", description: "Convince others to see your way. Builds influence but risks manipulation.", image: "persuasion.png" },
        { name: "Public Speaking", effect: "+4 Charisma, +1 Morality", hidden: "+1 Stress", description: "Address crowds with confidence. Builds leadership but can be nerve-wracking.", image: "public-speaking.png" },
        { name: "Drawing", effect: "+4 Creativity, +1 Happiness", hidden: "+1 Isolation", description: "Express ideas visually. Sparks joy but can isolate you in your own world.", image: "drawing.png" },
        { name: "Writing", effect: "+3 Creativity, +2 Intelligence", hidden: "+1 Stress", description: "Craft stories and ideas with words. Builds intellect but can be mentally draining.", image: "writing.png" },
        { name: "Improvisation", effect: "+3 Creativity, +2 Charisma", hidden: "+1 Stress", description: "Think on your feet and adapt. Builds flexibility but can be chaotic.", image: "improvisation.png" },
        { name: "Music", effect: "+4 Creativity, +1 Charisma", hidden: "+1 Isolation", description: "Play or compose music. Builds joy and influence but can isolate you in practice.", image: "music.png" },
        { name: "Budgeting", effect: "+4 Wealth, +1 Discipline", hidden: "+1 Stress", description: "Manage money wisely. Builds financial stability but constant tracking can be tiring.", image: "budgeting.png" },
        { name: "Negotiation", effect: "+3 Wealth, +2 Charisma", hidden: "+1 Ego", description: "Strike deals and gain advantage. Improves finances but can inflate self-importance.", image: "negotiation.png" },
        { name: "Planning", effect: "+3 Wealth, +2 Intelligence", hidden: "+1 Burnout", description: "Organize steps toward success. Builds foresight but can lead to overthinking.", image: "planning.png" },
        { name: "Risk Awareness", effect: "+3 Luck, +1 Intelligence", hidden: "+1 Stress", description: "Sense opportunities and dangers. Builds adaptability but can make you overly cautious.", image: "risk-awareness.png" },
        { name: "Adaptability", effect: "+4 Luck, +1 Creativity", hidden: "+1 Burnout", description: "Adjust quickly to change. Enhances resilience but constant shifting can wear you down.", image: "adaptability.png" },
        { name: "Opportunism", effect: "+3 Luck, +2 Wealth", hidden: "+1 Morality", description: "Seize chances when they appear. Builds success but can compromise ethics.", image: "opportunism.png" },
        { name: "Intuition", effect: "+3 Luck, +2 Creativity", hidden: "+1 Stress", description: "Trust your gut instincts. Builds quick decision-making but can be unreliable.", image: "intuition.png" },
        { name: "Gardening", effect: "+3 Creativity, +2 Health", hidden: "+1 Isolation", description: "Cultivate plants and nature. Builds patience and wellness but can be solitary.", image: "gardening.png" },
        { name: "Cooking Basics", effect: "+3 Creativity, +2 Health", hidden: "+1 Stress", description: "Prepare simple meals. Builds independence but can be tiring.", image: "cooking-basics.png" },
        { name: "Cleaning", effect: "+3 Discipline, +2 Health", hidden: "+1 Stress", description: "Maintain order and hygiene. Builds discipline but can feel repetitive.", image: "cleaning.png" },
        { name: "Driving", effect: "+3 Luck, +2 Intelligence", hidden: "+1 Stress", description: "Operate vehicles safely. Builds independence but can be risky.", image: "driving.png" },
        { name: "Swimming", effect: "+4 Strength, +1 Health", hidden: "+1 Stress", description: "Move confidently in water. Builds fitness but requires effort.", image: "swimming.png" },
        { name: "Meditation", effect: "+3 Discipline, +2 Morality", hidden: "-1 Stress", description: "Calm the mind and body. Builds focus but reduces spontaneity.", image: "meditation.png" },
        { name: "Basic First Aid", effect: "+3 Intelligence, +2 Health", hidden: "+1 Stress", description: "Treat minor injuries. Builds resilience but can be emotionally taxing.", image: "first-aid.png" },
        { name: "Crafting", effect: "+4 Creativity, +1 Discipline", hidden: "+1 Stress", description: "Create useful items by hand. Builds innovation but requires patience.", image: "crafting.png" },
        { name: "Storytelling", effect: "+3 Charisma, +2 Creativity", hidden: "+1 Stress", description: "Captivate others with tales. Builds influence but can drain energy.", image: "storytelling.png" },
        { name: "Observation", effect: "+3 Intelligence, +2 Luck", hidden: "+1 Stress", description: "Notice details others miss. Builds awareness but can cause overthinking.", image: "observation.png" },
        { name: "Basic Math", effect: "+4 Intelligence, +1 Wealth", hidden: "+1 Stress", description: "Handle numbers and calculations. Builds problem-solving but can be tedious.", image: "basic-math.png" },
        { name: "Organization", effect: "+3 Discipline, +2 Intelligence", hidden: "+1 Stress", description: "Keep things structured and efficient. Builds stability but reduces flexibility.", image: "organization.png" },
        { name: "Negotiation Basics", effect: "+3 Wealth, +2 Charisma", hidden: "+1 Ego", description: "Find compromises and deals. Builds financial gain but risks manipulation.", image: "negotiation-basics.png" },
        { name: "Survival Skills", effect: "+4 Strength, +1 Luck", hidden: "+1 Stress", description: "Endure harsh conditions. Builds resilience but can be dangerous.", image: "survival.png" },
        { name: "Listening", effect: "+3 Charisma, +2 Morality", hidden: "+1 Stress", description: "Pay attention to others deeply. Builds trust but can be emotionally draining.", image: "listening.png" },
        { name: "Time Management", effect: "+3 Discipline, +2 Wealth", hidden: "+1 Stress", description: "Balance priorities effectively. Builds productivity but can feel rigid.", image: "time-management.png" },
        { name: "Basic Technology", effect: "+3 Intelligence, +2 Creativity", hidden: "+1 Stress", description: "Use everyday devices. Builds adaptability but can be frustrating.", image: "basic-technology.png" },
        { name: "Negotiation Advanced", effect: "+4 Wealth, +1 Charisma", hidden: "+1 Ego", description: "Master complex deals. Builds influence but risks arrogance.", image: "negotiation-advanced.png" },
        { name: "Physical Training", effect: "+4 Strength, +1 Discipline", hidden: "+1 Burnout", description: "Condition your body systematically. Builds fitness but risks fatigue.", image: "physical-training.png" },
        { name: "Basic Language", effect: "+3 Intelligence, +2 Charisma", hidden: "+1 Stress", description: "Learn new words and phrases. Builds communication but can be challenging.", image: "basic-language.png" }

      ],
      talents: [
        { name: "Resilience", effect: "+5 Strength, -2 Creativity", hidden: "-1 Stress", description: "Able to endure hardships and bounce back stronger.", image: "resilience.png" },
        { name: "Empathy", effect: "+5 Morality, -2 Luck", hidden: "-1 Stress", description: "Deeply attuned to others’ emotions, fostering compassion.", image: "empathy.png" },
        { name: "Ambition", effect: "+5 Charisma, -3 Health", hidden: "+2 Stress, +1 Debt", description: "Driven to succeed, but ambition often comes at personal cost.", image: "ambition.png" },
        { name: "Honesty", effect: "+5 Morality, -2 Wealth", hidden: "-1 Corruption", description: "Guided by truth, even when it costs opportunities.", image: "honesty.png" },
        { name: "Charm", effect: "+5 Charisma, -2 Intelligence", hidden: "+1 Stress", description: "Naturally persuasive and likable, but sometimes superficial.", image: "charm.png" },
        { name: "Patience", effect: "+4 Morality, +2 Intelligence", hidden: "-1 Stress", description: "Able to wait calmly, reducing conflict and mistakes.", image: "patience.png" },
        { name: "Courage", effect: "+5 Strength, -2 Luck", hidden: "+2 Stress", description: "Bravery in the face of danger, but risk of harm is higher.", image: "courage.png" },
        { name: "Integrity", effect: "+5 Morality, -2 Wealth", hidden: "-1 Corruption", description: "Strong moral compass, but may miss financial gain.", image: "integrity.png" },
        { name: "Optimism", effect: "+4 Luck, +2 Charisma", hidden: "+1 Stress", description: "Positive outlook boosts morale, but can ignore risks.", image: "optimism.png" },
        { name: "Focus", effect: "+5 Intelligence, -2 Luck", hidden: "+1 Stress", description: "Laser-sharp concentration improves performance, but reduces spontaneity.", image: "focus.png" },
        { name: "Generosity", effect: "+4 Morality, +2 Charisma", hidden: "+1 Debt", description: "Willingness to give builds goodwill, but risks financial strain.", image: "generosity.png" },
        { name: "Creativity Spark", effect: "+5 Creativity, -2 Discipline", hidden: "+1 Fatigue", description: "Natural imagination fuels innovation, but can lack structure.", image: "creativity-spark.png" },
        { name: "Confidence", effect: "+5 Charisma, -2 Morality", hidden: "+1 Stress", description: "Self-assurance inspires others, but can lead to arrogance.", image: "confidence.png" },
        { name: "Wisdom", effect: "+5 Intelligence, +2 Morality", hidden: "-1 Stress", description: "Life experience guides decisions, but may slow adaptability.", image: "wisdom.png" },
        { name: "Humor", effect: "+4 Charisma, +2 Luck", hidden: "+1 Stress", description: "Lightheartedness builds bonds, but can mask deeper issues.", image: "humor.png" },
        { name: "Determination", effect: "+5 Strength, +2 Intelligence", hidden: "+2 Stress", description: "Unyielding drive achieves goals, but risks burnout.", image: "determination.png" },
        { name: "Compassion", effect: "+4 Morality, +2 Health", hidden: "+1 Stress", description: "Caring nature heals others, but emotional burden is heavy.", image: "compassion.png" },
        { name: "Curiosity", effect: "+5 Intelligence, +2 Creativity", hidden: "+1 Stress", description: "Natural desire to learn expands horizons, but can be distracting.", image: "curiosity.png" },
        { name: "Discipline of Mind", effect: "+5 Intelligence, -2 Luck", hidden: "+1 Stress", description: "Mental rigor improves focus, but reduces spontaneity.", image: "discipline-mind.png" },
        { name: "Charisma Aura", effect: "+6 Charisma, -2 Intelligence", hidden: "+2 Stress", description: "Magnetic personality draws people in, but can be exhausting.", image: "charisma-aura.png" },
        { name: "Stoicism", effect: "+5 Morality, +2 Strength", hidden: "-2 Stress", description: "Calm endurance of hardship builds resilience, but reduces emotional expression.", image: "stoicism.png" },
        { name: "Visionary", effect: "+5 Creativity, +2 Intelligence", hidden: "+2 Stress", description: "Sees possibilities others miss, but risks impracticality.", image: "visionary.png" },
        { name: "Loyalty", effect: "+4 Morality, +2 Charisma", hidden: "-1 Stress", description: "Faithful to allies, but can be exploited.", image: "loyalty.png" },
        { name: "Pragmatism", effect: "+5 Intelligence, -2 Morality", hidden: "+1 Stress", description: "Focus on practical solutions, but risks ethical compromise.", image: "pragmatism.png" },
        { name: "Tenacity", effect: "+5 Strength, +2 Luck", hidden: "+2 Stress", description: "Never gives up, but risks stubbornness.", image: "tenacity.png" },
        { name: "Diplomacy", effect: "+5 Charisma, +2 Morality", hidden: "+1 Stress", description: "Skilled at peacekeeping, but emotionally draining.", image: "diplomacy.png" },
        { name: "Inventiveness", effect: "+5 Creativity, -2 Wealth", hidden: "+1 Fatigue", description: "Natural knack for innovation, but often financially risky.", image: "inventiveness.png" },
        { name: "Self-Reliance", effect: "+5 Strength, +2 Intelligence", hidden: "+1 Stress", description: "Independent and resourceful, but risks isolation.", image: "self-reliance.png" },
        { name: "Adaptability Trait", effect: "+4 Luck, +2 Creativity", hidden: "+1 Stress", description: "Naturally flexible in changing environments, but can feel unstable.", image: "adaptability-trait.png" },
        { name: "Altruism", effect: "+5 Morality, -2 Wealth", hidden: "+1 Fatigue", description: "Selfless concern for others, but drains personal resources.", image: "altruism.png" },
        { name: "Honesty", effect: "+5 Morality, -2 Wealth", hidden: "-1 Corruption", description: "Guided by truth, even when it costs opportunities.", image: "honesty.png" },
        { name: "Patience", effect: "+4 Morality, +2 Intelligence", hidden: "-1 Stress", description: "Able to wait calmly, reducing conflict and mistakes.", image: "patience.png" },
        { name: "Courage", effect: "+5 Strength, -2 Luck", hidden: "+2 Stress", description: "Bravery in the face of danger, but risk of harm is higher.", image: "courage.png" },
        { name: "Focus", effect: "+5 Intelligence, -2 Luck", hidden: "+1 Stress", description: "Laser-sharp concentration improves performance, but reduces spontaneity.", image: "focus.png" },
        { name: "Generosity", effect: "+4 Morality, +2 Charisma", hidden: "+1 Debt", description: "Willingness to give builds goodwill, but risks financial strain.", image: "generosity.png" },
        { name: "Wisdom", effect: "+5 Intelligence, +2 Morality", hidden: "-1 Stress", description: "Life experience guides decisions, but may slow adaptability.", image: "wisdom.png" },
        { name: "Discipline of Mind", effect: "+5 Intelligence, -2 Luck", hidden: "+1 Stress", description: "Mental rigor improves focus, but reduces spontaneity.", image: "discipline-mind.png" },
        { name: "Charisma Aura", effect: "+6 Charisma, -2 Intelligence", hidden: "+2 Stress", description: "Magnetic personality draws people in, but can be exhausting.", image: "charisma-aura.png" },
        { name: "Stoicism", effect: "+5 Morality, +2 Strength", hidden: "-2 Stress", description: "Calm endurance of hardship builds resilience, but reduces emotional expression.", image: "stoicism.png" },
        { name: "Visionary", effect: "+5 Creativity, +2 Intelligence", hidden: "+2 Stress", description: "Sees possibilities others miss, but risks impracticality.", image: "visionary.png" },
        { name: "Loyalty", effect: "+4 Morality, +2 Charisma", hidden: "-1 Stress", description: "Faithful to allies, but can be exploited.", image: "loyalty.png" },
        { name: "Pragmatism", effect: "+5 Intelligence, -2 Morality", hidden: "+1 Stress", description: "Focus on practical solutions, but risks ethical compromise.", image: "pragmatism.png" },
        { name: "Diplomacy", effect: "+5 Charisma, +2 Morality", hidden: "+1 Stress", description: "Skilled at peacekeeping, but emotionally draining.", image: "diplomacy.png" },
        { name: "Inventiveness", effect: "+5 Creativity, -2 Wealth", hidden: "+1 Fatigue", description: "Natural knack for innovation, but often financially risky.", image: "inventiveness.png" }


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

      console.log("Character finalized:", {
        ...this.character,
        effectiveStats: this.effectiveStats
      });
    },
    quitGame() {
      localStorage.removeItem('user')
      this.$router.push('/home')
    }
  }
};
</script>