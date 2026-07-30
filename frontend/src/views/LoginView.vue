<template>
  <v-container class="fill-height d-flex align-center justify-center" style="min-height: 100vh;" fluid>
    <v-row align="center" justify="center" class="w-100">
      <v-col cols="12" sm="8" md="6" lg="4">
        <v-card class="pa-10 glass-card" elevation="8">
          <div class="text-center mb-6">
            <v-icon size="64" color="primary" icon="mdi-shield-bug-outline" />
            <h1 class="text-h5 mt-4 font-weight-bold">Incident Manager</h1>
            <p class="text-medium-emphasis mt-1">Inicia sesión para continuar</p>
          </div>

          <v-alert
            v-if="error"
            type="error"
            variant="tonal"
            closable
            class="mb-4"
            @click:close="error = ''"
          >
            {{ error }}
          </v-alert>

          <v-form @submit.prevent="handleLogin" ref="formRef">
            <v-text-field
              v-model="email"
              label="Correo electrónico"
              type="email"
              prepend-inner-icon="mdi-email-outline"
              :rules="[rules.required, rules.email]"
              class="mb-2"
              variant="outlined"
            />
            <v-text-field
              v-model="password"
              label="Contraseña"
              :type="showPass ? 'text' : 'password'"
              prepend-inner-icon="mdi-lock-outline"
              :append-inner-icon="showPass ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPass = !showPass"
              :rules="[rules.required]"
              class="mb-6"
              variant="outlined"
            />
            <v-btn
              type="submit"
              block
              size="large"
              color="primary"
              :loading="loading"
              elevation="2"
            >
              Iniciar sesión
            </v-btn>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const showPass = ref(false)
const loading = ref(false)
const error = ref('')
const formRef = ref(null)

const rules = {
  required: v => !!v || 'Campo obligatorio',
  email: v => /.+@.+\..+/.test(v) || 'Email inválido',
}

async function handleLogin() {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  loading.value = true
  error.value = ''

  try {
    await auth.login(email.value, password.value)
    if (auth.user?.role === 'admin') {
      router.push('/')
    } else {
      router.push({ name: 'Incidents' })
    }
  } catch (e) {
    error.value = (e.response?.data?.message || 'Error al iniciar sesión')
  } finally {
    loading.value = false
  }
}
</script>
