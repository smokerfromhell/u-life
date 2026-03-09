<template>
  <div class="game-screen">
    <!-- Menu Bar (top right) -->
    <div class="menu-bar">
      <button @click="editProfile">💾 Edit Profile</button>
      <button @click="logout">🚪 Logout</button>
    </div>

    <!-- Player Info (top left) -->
    <div class="player-info">
      <img :src="character.image" alt="player portrait" class="player-image" />
      <div class="player-details">
        <h3 class="player-name">{{ character.name }}</h3>
        <button class="stats-toggle" @click="toggleStats">👤 Player Info</button>

        <!-- Dropdown Player Info -->
        <transition name="slide-fade">
          <div v-if="showStats" class="stats-dropdown">
            <h4 class="dropdown-title">Player Info</h4>

            <!-- Gender and Age Group -->
            <div class="profile-info">
              <span class="profile-label">Gender:</span>
              <span class="profile-value">{{ character.gender }}</span>
            </div>
            <div class="profile-info">
              <span class="profile-label">Age Group:</span>
              <span class="profile-value">{{ character.ageGroup }}</span>
            </div>

            <!-- Dynamic Stats -->
            <div v-for="(value, stat) in effectiveStats.visible" :key="stat" class="stat-box">
              <span class="stat-icon">{{ getStatIcon(stat) }}</span>
              <span class="stat-label">{{ stat }}</span>
              <div class="stat-bar">
                <div class="stat-fill" :style="{ width: value + '%' }" :class="valueClass(value)"></div>
              </div>
              <span class="stat-value">{{ value }}</span>
            </div>

            <h4 class="dropdown-title">Skills</h4>
            <div class="active-list">
              <div v-for="skill in character.skills" :key="skill.name" class="active-box">
                ⭐ {{ skill.name }}
              </div>
            </div>

            <h4 class="dropdown-title">Talents</h4>
            <div class="active-list">
              <div v-for="talent in character.talents" :key="talent.name" class="active-box">
                🌟 {{ talent.name }}
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>

<!-- Main Event Cards -->
<div class="event-area">
  <h2 class="event-title">Choose Your Event</h2>
  <div class="event-cards">
    <div 
      v-for="(event, index) in availableEvents" 
      :key="index" 
      class="event-card" 
      :class="{ disabled: eventChosen }"
      @click="!eventChosen && selectEvent(event)"
    >
      <img :src="event.image" alt="event image" class="event-image" />
      <h4>{{ event.title }}</h4>
      <p>{{ event.description }}</p>
    </div>
  </div>
</div>

<!-- Selected Event Popup -->
<div v-if="selectedEvent" class="event-popup">
  <img :src="selectedEvent.image" alt="event image" class="popup-image" />
  <h3>{{ selectedEvent.title }}</h3>
  <p>{{ selectedEvent.description }}</p>

  <!-- Popup Actions -->
  <div class="popup-actions">
    <button @click="continueGame">▶ Continue</button>
    <button @click="endGame">⏹ End Game</button>
  </div>
</div>

<!-- Narration Box -->
<div class="narration-box">
  <div class="narration-scroll">
    <p v-for="(entry, i) in narrationHistory" :key="i">{{ entry }}</p>
  </div>
</div>

<!-- Game Actions -->
<div class="game-actions">
  <button @click="randomDailyEvent">🎲 Random Daily Event</button>
</div>
    </div>
</template>



<script>
export default {
  name: "MainGame",
    props: {
    character: {
      type: Object,
      default: () => ({
        name: "Player One", // now dynamic, should be set by player input
        image: "player.jpg",
        gender: "Male",     // chosen by player
        ageGroup: "Adult",  // chosen by player
        skills: [
          { name: "Cooking" },
          { name: "Negotiation" },
          { name: "Survival" }
        ],
        talents: [
          { name: "Quick Learner" },
          { name: "Charismatic" },
          { name: "Resilient" }
        ]
      })
    },
    effectiveStats: {
      type: Object,
      default: () => ({
        visible: {
            Health: 70,
          Charisma: 65,
          Burnout: 20,
          Wealth: 50,
          Happiness: 85
        }
      })
    }
  },
 data() {
   return {
    availableEvents: [
      { title: "Village Festival", description: "Join the celebration and gain happiness.", image: "festival.jpg" },
      { title: "Forest Exploration", description: "Explore the woods, maybe find treasure.", image: "forest.jpg" },
      { title: "Training Session", description: "Improve your skills and stats.", image: "training.jpg" },
      { title: "Market Day", description: "Trade goods and earn wealth.", image: "market.jpg" },
      { title: "Community Service", description: "Help others and boost charisma.", image: "service.jpg" }
    ],
    randomEvents: [
      { title: "Unexpected Rainstorm", description: "You got caught in the rain, losing energy.", image: "rain.jpg" },
      { title: "Lucky Find", description: "You stumbled upon some coins.", image: "coins.jpg" },
      { title: "Street Performer", description: "You enjoyed a show, boosting happiness.", image: "performer.jpg" },
      { title: "Lost Item", description: "You misplaced something valuable.", image: "lost.jpg" },
      { title: "Friendly Stranger", description: "A stranger helped you, boosting charisma.", image: "stranger.jpg" }
    ],
    selectedEvent: null,
    narration: "",
    narrationHistory: [],
    showStats: false,
    eventChosen: false
  };
},
methods: {
  toggleStats() { this.showStats = !this.showStats; },

   selectEvent(event) {
    this.selectedEvent = event;
    this.narration = `You chose "${event.title}". ${event.description}`;
    this.narrationHistory.push(this.narration);
    this.eventChosen = true;
  },
   continueGame() {
    this.narration = "The adventure continues...";
    this.narrationHistory.push(this.narration);
    this.selectedEvent = null;
    this.eventChosen = false;
    this.refreshEvents();
  },
  endGame() {
    this.narration = "Game over. Thanks for playing!";
    this.narrationHistory.push(this.narration);
  },

  randomDailyEvent() {
    const randomEvent = this.randomEvents[Math.floor(Math.random() * this.randomEvents.length)];
    this.selectEvent(randomEvent);
  },

  refreshEvents() {
    this.availableEvents = [
      { title: "Town Meeting", description: "Discuss issues with locals.", image: "meeting.jpg" },
      { title: "Library Study", description: "Gain knowledge from books.", image: "library.jpg" },
      { title: "Sports Day", description: "Boost health and energy.", image: "sports.jpg" },
      { title: "Work Shift", description: "Earn money through labor.", image: "work.jpg" },
      { title: "Family Dinner", description: "Increase happiness and charisma.", image: "dinner.jpg" }
    ];
  },

  getStatIcon(stat) {
    const icons = {
      Health: "❤️",
      Charisma: "✨",
      Burnout: "🔥",
      Wealth: "💰",
      Happiness: "😊"
    };
    return icons[stat] || "📊";
  },

  valueClass(value) {
    if (value > 70) return "high";
    if (value < 30) return "low";
    return "mid";
  },

  editProfile() { alert("Profile editor opened!"); },
  logout() { alert("You have logged out."); }
}
};
</script>



<style scoped>
.player-info {
  display: flex;
  align-items: flex-start;
  position: absolute;
  top: 20px;
  left: 20px;
}
.profile-label {
  color: #c6db8f;
}
.profile-value {
  color: #d6e6b5;
}
.player-image {
  width: 100px; /* bigger image */
  height: 100px;
  border-radius: 6px;
  border: 2px solid #a3c76d;
}
.player-details {
  display: flex;
  flex-direction: column;
}
.player-name {
  margin-left: 20px;
  font-size: 16px; /* slightly bigger name */
  color: #fff;
  font-family: 'Press Start 2P', monospace;
}

/* Compact Stats Button */
.stats-toggle {
    margin-top: 10px;
    margin-left: 20px;
  background: #222;
  color: #d6e6b5;
  border: 2px solid #a3c76d;
  padding: 6px 10px;
  font-family: 'Press Start 2P', monospace;
  font-size: 10px;
  cursor: pointer;
  border-radius: 3px;
  transition: background 0.2s ease, box-shadow 0.2s ease;
}
.stats-toggle:hover {
  background: #333;
  box-shadow: 0 2px 6px rgba(163, 199, 109, 0.5);
}
/* Dropdown Stats */
.stats-dropdown {
       margin-left: 20px;
  background: #222;
  border: 2px solid #FFD700;
  padding: 6px;
  margin-top: 5px;
  box-shadow: 2px 2px 0 #333;
  font-size: 9px;
  border-radius: 4px;
}
.dropdown-title {
  font-size: 10px;
  margin: 4px 0;
  color: #FFD700;
  text-transform: uppercase;
}
.stat-box {
  display: flex;
  align-items: center;
  background: #111;
  border: 1px solid #FFD700;
  padding: 3px 5px;
  margin-bottom: 3px;
  border-radius: 3px;
}
.stat-icon {
  margin-right: 6px;
}
.stat-label {
  color: #FFD700;
  flex: 1;
}
.stat-bar {
  flex: 2;
  height: 6px;
  background: #333;
  border: 1px solid #444;
  margin: 0 6px;
  border-radius: 3px;
  overflow: hidden;
}
.stat-fill {
  height: 100%;
  transition: width 0.3s ease;
}
.stat-fill.high {
  background: #00ff99;
}
.stat-fill.mid {
  background: #FFD700;
}
.stat-fill.low {
  background: #ff4444;
}
.stat-value {
  font-weight: bold;
  font-size: 9px;
}

/* Skills & Talents */
.active-list {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 6px;
}
.active-box {
  background: #008000;
  color: #FFD700;
  padding: 3px 6px;
  border: 1px solid #FFD700;
  font-size: 8px;
  text-transform: uppercase;
  border-radius: 3px;
  transition: background 0.2s ease, transform 0.2s ease;
}
.active-box:hover {
  background: #00a000;
  transform: scale(1.05);
}

/* Transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.25s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
/* Event Cards */
.event-title {
  font-size: 18px; /* bigger heading */
  margin-bottom: 20px; /* gap below */
  color: #fff;
  font-family: 'Press Start 2P', monospace;
}   
.event-area {
  margin-top: 160px;
  text-align: center;
}
.event-cards {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
  animation: fadeInUp 0.6s ease;
}
.event-card {
  width: 260px; /* bigger cards */
  padding: 16px;
  background: #1a1a1a;
  border: 2px solid #a3c76d;
  border-radius: 6px;
  cursor: pointer;
}
.event-card:hover {
  transform: scale(1.05);
  box-shadow: 0 0 12px #FFD700;
}
.event-image {
  width: 100%;
  height: 220px;
  object-fit: cover;
  border-radius: 4px;
  margin-bottom: 8px;
  border: 2px solid #FFD700;
}
.event-card h4, .event-card p {
  color: #fff;
  font-family: 'Press Start 2P', monospace;
}

/* Badge/Icon overlay */
.event-card::before {
  content: attr(data-icon);
  position: absolute;
  top: 8px;
  right: 8px;
  font-size: 16px;
  color: #FFD700;
}

/* Animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Game Screen */
.game-screen {
  background: linear-gradient(180deg, #111 0%, #222 100%);
  color: #FFD700;
  font-family: 'Press Start 2P', monospace;
  padding: 20px;
  min-height: 100vh;
  border: 4px solid #FFD700;
  box-shadow: 0 0 20px #FFD700;
  position: relative;
}

/* Narration Box */
.narration-box {
    margin-left: 235px;
  margin-top: 30px;
  background: #222;
  border: 2px solid #a3c76d;
  padding: 15px;
  border-radius: 6px;
  height: 120px; /* fixed height */
  overflow: hidden;
  width: 1380px;
}
.narration-box p {
  font-size: 21px;
  color: #eee;
}
.narration-scroll {
  height: 100%;
  overflow-y: auto; /* scrollable */
  font-family: 'Press Start 2P', monospace;
  font-size: 11px;
  color: #fff; /* pixelated white convo */
}

/* Game Actions */
.game-actions {
  margin-top: 30px;
  text-align: center;
  display: flex;
  justify-content: center;
  gap: 15px;
}
.game-actions button {
  background: #FFD700;
  color: #111;
  border: 3px solid #008000;
  padding: 10px 18px;
  font-family: 'Press Start 2P', monospace;
  font-size: 10px;
  cursor: pointer;
  border-radius: 4px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.game-actions button:hover {
  transform: scale(1.08);
  box-shadow: 0 0 10px #FFD700;
}
.game-actions button:nth-child(1) {
  background: #00cc66; /* Continue */
  border-color: #00994d;
}
.game-actions button:nth-child(2) {
  background: #ff4444; /* End Game */
  border-color: #cc0000;
}
.game-actions button:nth-child(3) {
  background: #3399ff; /* Random Event */
  border-color: #0066cc;
}

/* Animations */
@keyframes fadeInText {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.menu-bar {
  position: absolute;
  top: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
}
.menu-bar button {
  background: #222;
  color: #d6e6b5;
  border: 2px solid #a3c76d;
  padding: 6px 10px;
  font-family: 'Press Start 2P', monospace;
  font-size: 9px;
  cursor: pointer;
  border-radius: 3px;
  transition: background 0.2s ease, box-shadow 0.2s ease;
}
.menu-bar button:hover {
  background: #333;
  box-shadow: 0 2px 6px rgba(163, 199, 109, 0.5);
}
.event-card.disabled {
  pointer-events: none;
  opacity: 0.5;
}

.event-popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #1a1a1a;
  border: 2px solid #a3c76d;
  padding: 20px;
  border-radius: 6px;
  z-index: 1000;
  text-align: center;
  color: #fff;
  font-family: 'Press Start 2P', monospace;
  width: 320px;
}

.popup-image {
  width: 100%;
  height: auto;
  border-radius: 4px;
  margin-bottom: 12px;
  border: 2px solid #a3c76d;
}

.popup-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 15px;
}

.popup-actions button {
  background: #222;
  color: #d6e6b5;
  border: 2px solid #a3c76d;
  padding: 6px 12px;
  font-family: 'Press Start 2P', monospace;
  font-size: 10px;
  cursor: pointer;
  border-radius: 3px;
  transition: background 0.2s ease, box-shadow 0.2s ease;
}

.popup-actions button:hover {
  background: #333;
  box-shadow: 0 2px 6px rgba(163, 199, 109, 0.5);
}


</style>
