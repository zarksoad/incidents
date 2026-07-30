<template>
  <v-app-bar color="surface" elevation="1" class="d-lg-none">
    <v-app-bar-nav-icon @click="drawer = !drawer" />
    <v-toolbar-title>Incident Manager</v-toolbar-title>
  </v-app-bar>

  <v-navigation-drawer v-model="drawer" :permanent="!$vuetify.display.mdAndDown" color="surface" width="260">
    <v-list-item
      prepend-icon="mdi-shield-bug-outline"
      title="Incident Manager"
      subtitle="Panel de Control"
      class="py-4 d-none d-lg-flex"
    />
    <v-divider class="d-none d-lg-block" />
    <v-list density="compact" nav>
      <v-list-item
        v-if="auth.user?.role === 'admin'"
        prepend-icon="mdi-view-dashboard"
        title="Dashboard"
        :to="{ name: 'Dashboard' }"
        color="primary"
        exact
      />
      <v-list-item
        prepend-icon="mdi-format-list-bulleted"
        title="Incidentes"
        :to="{ name: 'Incidents' }"
        color="primary"
      />
      <v-list-item
        prepend-icon="mdi-plus-circle-outline"
        title="Nuevo Incidente"
        :to="{ name: 'CreateIncident' }"
        color="primary"
      />
    </v-list>
    <template v-slot:append>
      <div class="pa-4">
        <div class="text-caption text-medium-emphasis mb-2">
          {{ auth.user?.name }}
        </div>
        <v-btn
          block
          variant="tonal"
          color="error"
          prepend-icon="mdi-logout"
          @click="handleLogout"
          :loading="loggingOut"
        >
          Cerrar sesión
        </v-btn>
      </div>
    </template>
  </v-navigation-drawer>

  <v-main class="bg-background">
    <v-container fluid class="pa-4 pa-sm-6">
      <router-view />
    </v-container>
  </v-main>

  
  <v-snackbar
    v-model="notifications.show"
    :color="notifications.color"
    timeout="3000"
    location="top right"
    rounded="pill"
    elevation="4"
  >
    <div class="d-flex align-center font-weight-medium">
      <v-icon
        :icon="notifications.color === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'"
        class="mr-2"
      />
      {{ notifications.message }}
    </div>
    <template v-slot:actions>
      <v-btn variant="text" icon="mdi-close" @click="notifications.hide()" size="small" />
    </template>
  </v-snackbar>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'

const auth = useAuthStore()
const notifications = useNotificationStore()
const router = useRouter()

const loggingOut = ref(false)
const drawer = ref(null)

async function handleLogout() {
  loggingOut.value = true
  await auth.logout()
  router.push('/login')
}
</script>

<style>
@media (min-width: 1280px) {
  main.v-main {
    padding-top: 0 !important;
  }
}
</style>
