<template>
  <v-container
  fluid
  class="register-screen d-flex align-center justify-center"
  style="height: 100vh;
    background-image: url('/css/images/login-bacg.jpg');
    background-size: cover;
    background-position: center;">

    <div class="w-full max-w-md flex flex-col items-center">

      <h1 class="retro-title text-center mb-8">
        CREATE YOUR ACCOUNT
      </h1>

      <v-form class="retro-form w-full">
        <v-card class="retro-card pa-12" elevation="12">

          <div class="d-flex justify-center mb-6">
            <v-img src="/css/images/ulife1.png" alt="U:LIFE Logo" max-width="80" contain />
          </div>

          <v-text-field v-model="name" label="NAME" variant="outlined" density="comfortable" class="retro-input" />

          <v-text-field v-model="email" label="EMAIL" variant="outlined" density="comfortable" class="retro-input" />

          <v-text-field v-model="password" :type="showPassword ? 'text' : 'password'" label="PASSWORD"
            variant="outlined" density="comfortable" class="retro-input">
            <template #append-inner>
              <v-icon @click="showPassword = !showPassword" class="cursor-pointer retro-icon">
                {{ showPassword ? 'mdi-eye' : 'mdi-eye-off' }}
              </v-icon>
            </template>
          </v-text-field>

          <v-text-field v-model="confirmPassword" :type="showConfirmPassword ? 'text' : 'password'"
            label="CONFIRM PASSWORD" variant="outlined" density="comfortable" class="retro-input">
            <template #append-inner>
              <v-icon @click="showConfirmPassword = !showConfirmPassword" class="cursor-pointer retro-icon">
                {{ showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off' }}
              </v-icon>
            </template>
          </v-text-field>

          <v-card-actions class="register-actions flex flex-col items-center gap-4 mt-6">
            <v-btn :disabled="!canRegister || loading" class="retro-btn" type="submit" @click.prevent="register">
             REGISTER
            </v-btn>


     <v-btn variant="text" class="retro-link" @click="goToHome">
  <v-icon start size="16">mdi-arrow-left</v-icon>
  Back to Login
</v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </div>
  </v-container>
</template>



<script setup>
import { ref, computed} from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'


const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const showSuccess = ref(false)

const canRegister = computed(() =>
  name.value &&
  email.value &&
  password.value &&
  confirmPassword.value &&
  password.value === confirmPassword.value
)

const register = async () => {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await axios.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value
    })
    successMessage.value = response.data.message || 'Registration successful'
    showSuccess.value = true
    router.push({ path: '/home', query: { accountCreated: true } })
  } catch (error) {
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(', ')
    } else {
      errorMessage.value = error.response?.data?.message || 'Registration failed'
    }
  }
}
const goToHome = () => {
  router.push('/home')
}
</script>

<style scoped>
@keyframes beat {

  0%,
  100% {
    transform: scale(1);
    text-shadow: 2px 2px #000, 0 0 10px #00ff66;
  }

  50% {
    transform: scale(1.15);
    text-shadow: 4px 4px #000, 0 0 20px #00ff66;
  }
}

.retro-form {
  width: 600px;
}

.retro-card {
  background: #ececec8c !important;
  border-radius: 20px !important;
  font-family: 'Press Start 2P', monospace;
}

.retro-title {
  width: 1500px;
  font-family: "Press Start 2P", cursive;
  font-size: 70px;
  color: #53f06a;
  margin-bottom: 40px;
  text-shadow: 6px 6px #ffffff;
  animation: beat 1s infinite;
}

/* Inputs */
.retro-input input {
  font-family: 'Press Start 2P', monospace;
  color: #89df00;
}

/* Vuetify fields need deep selector when scoped */
::v-deep(.retro-input .v-field) {
  border-radius: 0 !important;
  border: 3px solid #048519;
  background-color: #82f582;
}

.retro-input label {
  font-size: 10px;
}

/* Button */
.retro-btn {
  font-family: 'Press Start 2P', monospace;
  background: #008516 !important;
  color: #000 !important;
  border: 3px solid #000000;
  border-radius: 5 !important;
  padding: 4px 28px;
  box-shadow: 4px 4px #000;
}

.retro-btn:hover {
  transform: translate(2px, 2px);
  box-shadow: 2px 2px #000;
}

/* Disabled state */
.retro-btn:disabled {
  background: #555 !important;
  color: #222 !important;
}

.retro-link {
    margin-top: 25px;
  font-family: 'Press Start 2P', monospace;
  font-size: 12px;
  color: #000000;
  cursor: pointer;
  text-shadow: 2px 2px #f7fff9;
}

.retro-link:hover {
  color: #82f582;
}

</style>