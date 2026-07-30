<template>
  <div>
    <h1 class="text-h4 font-weight-bold mb-6">Dashboard</h1>

    <v-row v-if="loading">
      <v-col v-for="n in 4" :key="n" cols="12" sm="6" md="3">
        <BeautifulSkeleton type="card" height="120px" />
      </v-col>
    </v-row>

    <template v-else>
      <v-row class="mb-4">
        <v-col cols="12" sm="6" md="3">
          <v-card class="pa-6 glass-card hover-lift">
            <div class="d-flex align-center justify-space-between">
              <div>
                <div class="text-caption text-medium-emphasis">Total Incidentes</div>
                <div class="text-h4 font-weight-bold text-primary">{{ stats.total || 0 }}</div>
              </div>
              <v-icon size="40" color="primary" opacity="0.7" icon="mdi-file-document-multiple" />
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card class="pa-6 glass-card hover-lift">
            <div class="d-flex align-center justify-space-between">
              <div>
                <div class="text-caption text-medium-emphasis">Vencidos</div>
                <div class="text-h4 font-weight-bold text-error">{{ stats.overdue || 0 }}</div>
              </div>
              <v-icon size="40" color="error" opacity="0.7" icon="mdi-clock-alert-outline" />
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card class="pa-6 glass-card hover-lift">
            <div class="d-flex align-center justify-space-between">
              <div>
                <div class="text-caption text-medium-emphasis">Abiertos</div>
                <div class="text-h4 font-weight-bold text-secondary">{{ stats.by_status?.abierto || 0 }}</div>
              </div>
              <v-icon size="40" color="secondary" opacity="0.7" icon="mdi-folder-open-outline" />
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <v-card class="pa-6 glass-card hover-lift">
            <div class="d-flex align-center justify-space-between">
              <div>
                <div class="text-caption text-medium-emphasis">Cerrados</div>
                <div class="text-h4 font-weight-bold text-success">{{ stats.by_status?.cerrado || 0 }}</div>
              </div>
              <v-icon size="40" color="success" opacity="0.7" icon="mdi-check-circle-outline" />
            </div>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" md="6">
          <v-card color="surface" class="pa-6" style="border: 1px solid rgba(108, 99, 255, 0.1);">
            <h3 class="text-h6 mb-4">Por Estado</h3>
            <v-list density="compact" bg-color="transparent">
              <v-list-item v-for="(val, key) in stats.by_status" :key="key">
                <template v-slot:prepend>
                  <v-icon :color="statusColor(key)" icon="mdi-circle" size="12" class="mr-3" />
                </template>
                <v-list-item-title class="text-capitalize">{{ formatStatus(key) }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip size="small" :color="statusColor(key)" variant="tonal">{{ val }}</v-chip>
                </template>
              </v-list-item>
            </v-list>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card color="surface" class="pa-6" style="border: 1px solid rgba(108, 99, 255, 0.1);">
            <h3 class="text-h6 mb-4">Por Prioridad</h3>
            <v-list density="compact" bg-color="transparent">
              <v-list-item v-for="(val, key) in stats.by_priority" :key="key">
                <template v-slot:prepend>
                  <v-icon :color="priorityColor(key)" icon="mdi-circle" size="12" class="mr-3" />
                </template>
                <v-list-item-title class="text-capitalize">{{ key }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip size="small" :color="priorityColor(key)" variant="tonal">{{ val }}</v-chip>
                </template>
              </v-list-item>
            </v-list>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </div>
</template>

<script setup>
import { useDashboard } from '../composables/useDashboard'
import BeautifulSkeleton from '../components/BeautifulSkeleton.vue'

const { stats, loading } = useDashboard()

function statusColor(status) {
  const map = { abierto: 'info', en_progreso: 'warning', cerrado: 'success', vencido: 'error' }
  return map[status] || 'grey'
}

function priorityColor(priority) {
  const map = { baja: 'success', media: 'info', alta: 'warning', critica: 'error' }
  return map[priority] || 'grey'
}

function formatStatus(status) {
  return status.replace('_', ' ')
}
</script>
