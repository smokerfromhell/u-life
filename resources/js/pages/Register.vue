<template>
  <v-container class="retro-screen flex items-center justify-center min-h-screen">
    <v-form class="retro-form">
      <v-card class="retro-card pa-12">
        <h1 class="retro-title text-center">
          CREATE YOUR ACCOUNT
        </h1>

        <v-card-text class="space-y-6">
          <!-- Name -->
          <v-text-field
            v-model="name"
            label="NAME"
            variant="outlined"
            density="comfortable"
            class="retro-input"
          />

          <!-- Email -->
          <v-text-field
            v-model="email"
            label="EMAIL"
            variant="outlined"
            density="comfortable"
            class="retro-input"
          />

          <!-- Password -->
          <v-text-field
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            label="PASSWORD"
            variant="outlined"
            density="comfortable"
            class="retro-input"
          >
            <template #append-inner>
              <v-icon
                @click="showPassword = !showPassword"
                class="cursor-pointer retro-icon"
              >
                {{ showPassword ? 'mdi-eye' : 'mdi-eye-off' }}
              </v-icon>
            </template>
          </v-text-field>

          <!-- Confirm Password -->
          <v-text-field
            v-model="confirmPassword"
            :type="showConfirmPassword ? 'text' : 'password'"
            label="CONFIRM PASSWORD"
            variant="outlined"
            density="comfortable"
            class="retro-input"
          >
            <template #append-inner>
              <v-icon
                @click="showConfirmPassword = !showConfirmPassword"
                class="cursor-pointer retro-icon"
              >
                {{ showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off' }}
              </v-icon>
            </template>
          </v-text-field>
        </v-card-text>

        <v-card-actions class="flex flex-col items-center gap-4 mt-6">
          <!-- Register -->
          <v-btn
            :disabled="!canRegister"
            class="retro-btn"
            type="submit"
          >
            ▶ REGISTER
          </v-btn>

          <!-- Back to Login -->
          <v-btn
            class="retro-link"
            @click="goToHome"
          >
            ◀ BACK TO LOGIN
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-container>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Simple validation: enable register only if fields are filled and passwords match
const canRegister = computed(() =>
  name.value &&
  email.value &&
  password.value &&
  confirmPassword.value &&
  password.value === confirmPassword.value
)

const goToHome = () => {
  router.push('/home')
}
</script>