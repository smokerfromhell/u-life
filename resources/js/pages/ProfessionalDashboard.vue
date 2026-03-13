<template>
  <div class="professional-dashboard min-h-screen" style="background: linear-gradient(135deg, #0a0a1a 0%, #1a0a2e 50%, #0d1a2e 100%); color: white; padding: 2rem;">
    <div class="max-w-7xl mx-auto">
      <div class="dashboard-header mb-8">
        <h1 class="text-4xl font-bold mb-2 glitch" data-text="PROFESSIONAL DASHBOARD">PROFESSIONAL DASHBOARD</h1>
        <p class="text-xl opacity-80">User Behavior Analytics & Decision Insights</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
        <!-- Decision Types Chart -->
        <div class="bg-white/10 backdrop-blur-xl p-6 rounded-2xl border border-white/20">
          <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
            <v-icon color="cyan">mdi-chart-line</v-icon>
            Decision Patterns
          </h2>
          <div ref="decisionChart" style="height: 300px;"></div>
        </div>

        <!-- Age Group Distribution -->
        <div class="bg-white/10 backdrop-blur-xl p-6 rounded-2xl border border-white/20">
          <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
            <v-icon color="purple">mdi-account-group</v-icon>
            Age Groups
          </h2>
          <div ref="ageChart" style="height: 300px;"></div>
        </div>

        <!-- Profession Stats -->
        <div class="bg-white/10 backdrop-blur-xl p-6 rounded-2xl border border-white/20 lg:col-span-2 xl:col-span-1">
          <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
            <v-icon color="orange">mdi-briefcase-account</v-icon>
            Professions
          </h2>
          <div ref="professionChart" style="height: 300px;"></div>
        </div>

        <!-- Recent Decisions Heatmap -->
        <div class="lg:col-span-2 bg-white/10 backdrop-blur-xl p-6 rounded-2xl border border-white/20">
          <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
            <v-icon color="red">mdi-fire</v-icon>
            Recent Activity Heatmap
          </h2>
          <div ref="heatmapChart" style="height: 400px;"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Chart from 'chart.js/auto'

const decisionChart = ref(null)
const ageChart = ref(null)
const professionChart = ref(null)
const heatmapChart = ref(null)

onMounted(async () => {
  // Mock data - replace with API calls
  await loadAnalytics()
})

const loadAnalytics = async () => {
  let data = {
    decision_types: {},
    age_groups: {},
    trait_tendencies: { EI: {Extrovert: 0, Introvert: 0}, NS: {Intuitive: 0, Sensing: 0}, TF: {Thinking: 0, Feeling: 0}, JP: {Judging: 0, Perceiving: 0} },
    mbti_distribution: {},
    recent_mbti_changes: []
  }
  
  try {
    const response = await fetch('/api/professional/analytics', {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
    })
    if (response.ok) {
      data = await response.json()
    }
  } catch (error) {
    console.log('Using demo data (no logs yet)')
  }
  
  createDecisionChart(Object.entries(data.decision_types || {}).slice(0,3))
  createAgeChart(Object.entries(data.age_groups || {}).slice(0,4))
  createTraitCharts(data.trait_tendencies)
  createMBTIDistributionChart(Object.entries(data.mbti_distribution || {}).slice(0,8))
  createHeatmap([
    {x: 1, y: 78, label: 'INTP Health'},
    {x: 2, y: 92, label: 'ENTJ Career'},
    {x: 3, y: 85, label: 'ISFP Social'},
    {x: 4, y: 88, label: 'ESTJ Family'},
    {x: 5, y: 95, label: 'INFJ Milestone'}
  ])
}

const createTraitCharts = (tendencies) => {
  // E/I Extro/Intro Bar
  new Chart(document.getElementById('eiChart'), {
    type: 'bar',
    data: {
      labels: ['Extrovert', 'Introvert'],
      datasets: [{
        data: [tendencies.EI.Extrovert, tendencies.EI.Introvert],
        backgroundColor: ['#10b981', '#6b7280']
      }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
  })

  // N/S Intuitive/Sensing
  new Chart(document.getElementById('nsChart'), {
    type: 'doughnut',
    data: {
      labels: ['Intuitive', 'Sensing'],
      datasets: [{ data: [tendencies.NS.Intuitive, tendencies.NS.Sensing], backgroundColor: ['#8b5cf6', '#f59e0b'] }]
    }
  })

  // T/F Thinking/Feeling Radar
  new Chart(document.getElementById('tfChart'), {
    type: 'radar',
    data: {
      labels: ['Thinking', 'Feeling'],
      datasets: [{ data: [tendencies.TF.Thinking, tendencies.TF.Feeling], backgroundColor: 'rgba(239,68,68,0.2)' }]
    }
  })

  // J/P Judging/Perceiving Polar
  new Chart(document.getElementById('jpChart'), {
    type: 'polarArea',
    data: {
      labels: ['Judging', 'Perceiving'],
      datasets: [{ data: [tendencies.JP.Judging, tendencies.JP.Perceiving], backgroundColor: ['#3b82f6', '#ec4899'] }]
    }
  })
}

const createDecisionChart = (data) => {
  new Chart(decisionChart.value, {
    type: 'doughnut',
    data: {
      labels: ['Health', 'Career', 'Social'],
      datasets: [{
        data: Object.values(data),
        backgroundColor: ['#ef4444', '#f59e0b', '#10b981']
      }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  })
}

// Similar for other charts...
const createAgeChart = (data) => {
  new Chart(ageChart.value, {
    type: 'bar',
    data: {
      labels: Object.keys(data),
      datasets: [{
        label: 'Users',
        data: Object.values(data),
        backgroundColor: 'rgba(0, 255, 204, 0.6)'
      }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  })
}

const createProfessionChart = (data) => {
  new Chart(professionChart.value, {
    type: 'polarArea',
    data: {
      labels: data,
      datasets: [{
        data: [25, 20, 15, 10, 30],
        backgroundColor: [
          'rgba(59, 130, 246, 0.5)',
          'rgba(34, 197, 94, 0.5)',
          'rgba(251, 191, 36, 0.5)',
          'rgba(168, 85, 247, 0.5)',
          'rgba(239, 68, 68, 0.5)'
        ]
      }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  })
}

const createHeatmap = () => {
  new Chart(heatmapChart.value, {
    type: 'scatter',
    data: {
      datasets: [{
        label: 'Recent Decisions',
        data: [
          { x: 1, y: 85 },
          { x: 2, y: 92 },
          { x: 3, y: 78 },
          { x: 4, y: 95 }
        ],
        backgroundColor: 'rgba(0, 255, 204, 0.8)'
      }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { x: { title: { display: true, text: 'Time' } }, y: { title: { display: true, text: 'Impact %' } } } }
  })
}
</script>

<style scoped>
.glitch {
  position: relative;
}
.glitch::before, .glitch::after {
  content: attr(data-text);
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
.glitch::before {
  animation: glitch-anim 2s infinite;
  left: 2px;
  color: #00ffcc;
}
.glitch::after {
  animation: glitch-anim 2s infinite reverse;
  left: -2px;
  color: #8b5cf6;
}
@keyframes glitch-anim {
  0% { clip: rect(43px, 9999px, 49px, 0); }
  5% { clip: rect(39px, 9999px, 55px, 0); }
  /* ... */
}
</style>

