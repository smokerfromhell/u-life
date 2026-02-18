<template>
  <div class="character-creation">
    <h2>Create Your Character</h2>

    <div class="creation-layout">
      <!-- Left Panel -->
      <div class="left-panel">
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
        <div>
          <h3>Allocate Stats (Remaining: {{ remainingPoints }})</h3>
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
        <!-- Skills -->
        <div class="skills-container">
          <h3>Choose Skills</h3>
          <div class="card-grid">
            <div v-for="skill in skills" :key="skill.name" class="skill-card" @click="toggleSkill(skill)"
              :class="{ selected: character.skills.includes(skill) }">
              <img :src="skill.image" alt="skill image" class="card-image" />
              <div class="card-content">
                <h4>{{ skill.name }}</h4>
                <div class="description-popup">
                  {{ skill.description }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Talents -->
        <div class="talents-container">
          <h3>Choose Talents</h3>
          <div class="card-grid">
            <div v-for="talent in talents" :key="talent.name" class="talent-card" @click="toggleTalent(talent)"
              :class="{ selected: character.talents.includes(talent) }">
              <img :src="talent.image" alt="talent image" class="card-image" />
              <div class="card-content">
                <h4>{{ talent.name }}</h4>
                <div class="description-popup">
                  {{ talent.description }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Start Game button -->
    <div class ="button-bar">
      <div class="start-button-container">
        <button @click="finalizeCharacter">Start Game</button>
      </div>
      <div class="logout-button-container">
        <button @click="quitGame">Logout</button>
      </div>
    </div>

  </div>

</template>

<script >
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
          Luck: 0,
          Health: 0,
          Morality: 0,
        },
        hiddenStats: {
          Sickness: 0,
          Stress: 0,
          Fatigue: 0,
          Corruption: 0,
          Debt: 0,
        },
        skills: [],
        talents: []
      },
      genderBonus: {},   // store gender modifiers separately
      ageBonus: {},      // store age modifiers separately
      skills: [
        { name: "Communication", effect: "+4 Charisma, +1 Morality", hidden: "+1 Stress", description: "Express yourself clearly and connect with others. Builds trust and influence, but constant interaction can be draining.", image: "communication.png" },
        { name: "Financial Literacy", effect: "+5 Wealth, +2 Intelligence", hidden: "+1 Stress", description: "Understanding money management and investments. Creates stability, but responsibility can be stressful.", image: "finance.png" },
        { name: "Time Management", effect: "+3 Intelligence, +2 Luck", hidden: "-1 Fatigue", description: "Organizing your day to maximize productivity. Frees time and reduces fatigue, but requires discipline.", image: "time.png" },
        { name: "Cooking", effect: "+3 Creativity, +2 Health", hidden: "+1 Fatigue", description: "Preparing meals improves health and sparks creativity, but takes effort and energy.", image: "cooking.png" },
        { name: "Fitness", effect: "+5 Strength, +2 Health", hidden: "+1 Fatigue", description: "Regular exercise builds strength and resilience, but can tire you out if overdone.", image: "fitness.png" },
        { name: "Networking", effect: "+4 Charisma, +2 Wealth", hidden: "+1 Corruption", description: "Building connections opens opportunities, but risks shady dealings or exploitation.", image: "networking.png" },
        { name: "Problem-Solving", effect: "+5 Intelligence, +2 Creativity", hidden: "+1 Stress", description: "Tackling challenges sharpens your mind, but can be mentally taxing.", image: "problem-solving.png" },
        { name: "Mindfulness", effect: "+3 Morality, +2 Health", hidden: "-2 Stress", description: "Practicing calmness improves well-being and reduces stress, but may lower ambition.", image: "mindfulness.png" },
        { name: "Adaptability", effect: "+3 Luck, +2 Creativity", hidden: "+1 Stress", description: "Rolling with change helps survival and innovation, but uncertainty can be stressful.", image: "adaptability.png" },
        { name: "Leadership", effect: "+5 Charisma, +2 Strength", hidden: "+2 Stress", description: "Inspiring others builds influence and power, but responsibility is heavy.", image: "leadership.png" },
        { name: "Critical Thinking", effect: "+4 Intelligence, +2 Morality", hidden: "+1 Stress", description: "Analyzing situations carefully helps make better decisions, but can lead to overthinking.", image: "critical-thinking.png" },
        { name: "Creativity", effect: "+5 Creativity, +1 Charisma", hidden: "+1 Fatigue", description: "Innovative thinking sparks new ideas and solutions, but can be mentally draining.", image: "creativity.png" },
        { name: "Empathy", effect: "+4 Morality, +2 Charisma", hidden: "+1 Stress", description: "Understanding others builds strong relationships, but emotional weight can be heavy.", image: "empathy.png" },
        { name: "Discipline", effect: "+4 Strength, +3 Intelligence", hidden: "+1 Stress", description: "Sticking to routines builds resilience and focus, but can feel restrictive.", image: "discipline.png" },
        { name: "Negotiation", effect: "+4 Wealth, +2 Charisma", hidden: "+1 Corruption", description: "Sharp deal-making improves wealth and influence, but risks moral compromise.", image: "negotiation.png" },
        { name: "Self-Care", effect: "+3 Health, +2 Morality", hidden: "-2 Stress", description: "Taking care of yourself improves well-being, but may reduce time for other pursuits.", image: "self-care.png" },
        { name: "Driving", effect: "+3 Luck, +2 Strength", hidden: "+1 Stress", description: "Operating vehicles improves mobility, but traffic and accidents are stressful.", image: "driving.png" },
        { name: "Technology Use", effect: "+4 Intelligence, +2 Creativity", hidden: "+1 Fatigue", description: "Mastering tech boosts productivity, but screen time can be draining.", image: "technology.png" },
        { name: "Artistry", effect: "+5 Creativity, +2 Charisma", hidden: "+1 Stress", description: "Creating art inspires others, but emotional vulnerability is high.", image: "artistry.png" },
        { name: "Parenting", effect: "+4 Morality, +3 Charisma", hidden: "+2 Stress", description: "Raising children builds love and responsibility, but is exhausting.", image: "parenting.png" },
        { name: "Volunteering", effect: "+4 Morality, +2 Charisma", hidden: "+1 Fatigue", description: "Helping others builds community, but can drain personal resources.", image: "volunteering.png" },
        { name: "Housekeeping", effect: "+3 Health, +2 Discipline", hidden: "+1 Fatigue", description: "Maintaining a clean home improves health and order, but takes effort.", image: "housekeeping.png" },
        { name: "Gardening", effect: "+3 Health, +2 Creativity", hidden: "+1 Fatigue", description: "Working with plants improves well-being, but requires patience and energy.", image: "gardening.png" },
        { name: "Language Learning", effect: "+5 Intelligence, +2 Charisma", hidden: "+1 Stress", description: "Learning new languages expands horizons, but takes dedication.", image: "language.png" },
        { name: "Storytelling", effect: "+4 Charisma, +2 Creativity", hidden: "+1 Stress", description: "Captivating audiences builds influence, but can be mentally draining.", image: "storytelling.png" },
        { name: "Traveling", effect: "+4 Luck, +2 Creativity", hidden: "+2 Fatigue", description: "Exploring new places broadens perspective, but can be exhausting.", image: "traveling.png" },
        { name: "Meditation", effect: "+3 Morality, +2 Health", hidden: "-2 Stress", description: "Practicing calmness improves well-being, but may reduce ambition.", image: "meditation.png" },
        { name: "DIY Skills", effect: "+4 Creativity, +2 Strength", hidden: "+1 Fatigue", description: "Fixing and building things saves money and sparks creativity, but requires effort.", image: "diy.png" },
        { name: "Cultural Awareness", effect: "+3 Morality, +2 Intelligence", hidden: "+1 Stress", description: "Understanding diverse cultures builds empathy, but can be overwhelming.", image: "culture.png" },
        { name: "Event Planning", effect: "+4 Charisma, +2 Intelligence", hidden: "+2 Stress", description: "Organizing events builds leadership and influence, but is highly stressful.", image: "event.png" }
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
        { name: "Altruism", effect: "+5 Morality, -2 Wealth", hidden: "+1 Fatigue", description: "Selfless concern for others, but drains personal resources.", image: "altruism.png" }
      ]
    };
  },
  computed: {
    remainingPoints() {
      return (
        this.totalPoints -
        Object.values(this.character.stats).reduce((a, b) => a + b, 0)
      );
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
        this.applyEffect(stats, skill.effect);
        if (skill.hidden) this.applyEffect(hidden, skill.hidden);
      });
      this.character.talents.forEach(talent => {
        this.applyEffect(stats, talent.effect);
        if (talent.hidden) this.applyEffect(hidden, talent.hidden);
      });

      return { visible: stats, hidden: hidden };
    }
  },
  methods: {
    applyEffect(stats, effect) {
      const parts = effect.split(",");
      parts.forEach(part => {
        const [signVal, stat] = part.trim().split(" ");
        const value = parseInt(signVal);
        if (stats[stat] !== undefined) {
          stats[stat] += value;
        }
      });
    },
    applyGenderBonus() {
      this.genderBonus = {}; // reset
      let bonus = Math.floor(Math.random() * 3) + 1;

      switch (this.character.gender) {
        case "male":
          this.genderBonus = { Strength: bonus, Intelligence: Math.max(0, bonus - 2) };
          break;
        case "female":
          this.genderBonus = { Intelligence: bonus, Strength: Math.max(0, bonus - 2) };
          break;
        case "non-binary":
          this.genderBonus = { Creativity: bonus, Luck: 1 };
          break;
        case "transgender":
          this.genderBonus = { Charisma: bonus, Luck: 1 };
          break;
      }
    },
    applyAgeBonus() {
      this.ageBonus = {}; // reset

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
      }
    },
    increaseStat(stat) {
      if (this.remainingPoints > 0 && this.character.stats[stat] < 20) {
        this.character.stats[stat]++;
      }
    },
    decreaseStat(stat) {
      if (this.character.stats[stat] > 0) {
        this.character.stats[stat]--;
      }
    },
    toggleSkill(skill) {
      const index = this.character.skills.indexOf(skill);
      if (index > -1) {
        this.character.skills.splice(index, 1);
      } else {
        this.character.skills.push(skill);
      }
    },
    toggleTalent(talent) {
      const index = this.character.talents.indexOf(talent);
      if (index > -1) {
        this.character.talents.splice(index, 1);
      } else {
        this.character.talents.push(talent);
      }
    },
    finalizeCharacter() {
      if (!this.character.name) {
        alert("Please enter a character name!")
        return
      }
      if (!this.character.ageGroup || this.character.ageGroup === "None") {
        alert("Please select an age group!")
        return
      }
      if (!this.character.gender || this.character.gender === "None") {
        alert("Please select a gender!")
        return
      }
      if (this.remainingPoints !== 0) {
        alert("You must allocate all points!")
        return
      }

      console.log("Character finalized:", {
        ...this.character,
        effectiveStats: this.effectiveStats
      })
    },
    quitGame() {
      localStorage.removeItem('user')
      this.$router.push('/home')
    }
  }
}

</script>