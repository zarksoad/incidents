<template>
  <div>
    <div class="d-flex align-center mb-6">
      <v-btn icon variant="text" @click="$router.back()" class="mr-2">
        <v-icon icon="mdi-arrow-left" />
      </v-btn>
      <h1 class="text-h4 font-weight-bold">Detalle del Incidente</h1>
    </div>

    <v-row v-if="loading">
      <v-col cols="12">
        <v-card class="pa-8 glass-card">
          <BeautifulSkeleton type="title" width="60%" />
          <BeautifulSkeleton type="text" width="100%" />
          <BeautifulSkeleton type="text" width="80%" />
        </v-card>
      </v-col>
    </v-row>

    <template v-else-if="incident">
      <v-card class="pa-8 glass-card">
        <div class="d-flex align-center justify-space-between mb-4">
          <h2 class="text-h5 font-weight-bold">{{ incident.title }}</h2>
          <div class="d-flex ga-2">
            <v-chip :color="priorityColor(incident.priority)" variant="tonal" class="text-capitalize">
              {{ incident.priority }}
            </v-chip>
            <v-chip :color="statusColor(incident.status)" variant="tonal" class="text-capitalize">
              {{ formatStatus(incident.status) }}
            </v-chip>
          </div>
        </div>

        <v-divider class="mb-4" />

        <div class="text-body-1 mb-6" style="white-space: pre-line;">{{ incident.description }}</div>

        <v-row>
          <v-col cols="12" sm="6" md="3">
            <div class="text-caption text-medium-emphasis">Creado por</div>
            <div class="text-body-1 font-weight-medium">{{ incident.creator?.name }}</div>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <div class="text-caption text-medium-emphasis">Asignado a</div>
            <div class="text-body-1 font-weight-medium">{{ incident.assignee?.name || 'Sin asignar' }}</div>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <div class="text-caption text-medium-emphasis">Fecha de vencimiento</div>
            <div class="text-body-1 font-weight-medium" :class="{ 'text-error': incident.is_overdue }">
              {{ incident.due_date }}
              <v-icon v-if="incident.is_overdue" size="16" color="error" icon="mdi-alert-circle" />
            </div>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <div class="text-caption text-medium-emphasis">Creado</div>
            <div class="text-body-1 font-weight-medium">{{ formatDate(incident.created_at) }}</div>
          </v-col>
        </v-row>

        <v-divider class="my-4" />

        <div class="d-flex flex-column flex-sm-row justify-end ga-3">
          <v-btn
            class="w-100 w-sm-auto"
            variant="tonal"
            color="primary"
            prepend-icon="mdi-pencil"
            :to="{ name: 'EditIncident', params: { id: incident.id } }"
          >
            Editar
          </v-btn>
          <v-btn class="w-100 w-sm-auto" variant="tonal" color="error" prepend-icon="mdi-delete" @click="deleteDialog = true">
            Eliminar
          </v-btn>
        </div>
      </v-card>
      
      <v-card class="pa-4 pa-sm-8 glass-card mt-6">
        <h3 class="text-h6 font-weight-bold mb-4">Historial de Cambios</h3>
        <v-timeline density="compact" side="end" align="start">
          <v-timeline-item
            v-for="log in incident.audit_logs"
            :key="log.id"
            :dot-color="log.action === 'Creado' ? 'success' : (log.action === 'Eliminado' ? 'error' : 'info')"
            size="small"
          >
            <div class="d-flex flex-column">
              <div class="d-flex align-center justify-space-between">
                <strong>{{ log.user ? log.user.name : 'Sistema' }}</strong>
                <span class="text-caption text-medium-emphasis">
                  {{ new Date(log.created_at).toLocaleString() }}
                </span>
              </div>
              <div class="mt-1">
                {{ log.action }} el incidente.
              </div>
              <div v-if="log.action === 'Actualizado' && log.details && Object.keys(log.details).length" class="text-caption mt-2 bg-grey-lighten-4 pa-2 rounded">
                <div v-for="(val, key) in log.details" :key="key" class="mb-1">
                  <strong class="text-capitalize">{{ formatLogKey(key) }}:</strong> <span class="text-high-emphasis">{{ val }}</span>
                </div>
              </div>
            </div>
          </v-timeline-item>
          
          <v-timeline-item v-if="!incident.audit_logs || incident.audit_logs.length === 0" dot-color="grey" size="small">
            <div class="text-medium-emphasis">No hay historial registrado.</div>
          </v-timeline-item>
        </v-timeline>
      </v-card>
    </template>

    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card color="surface" class="pa-4">
        <v-card-title>Confirmar eliminación</v-card-title>
        <v-card-text>¿Estás seguro de eliminar este incidente?</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Cancelar</v-btn>
          <v-btn color="error" variant="tonal" :loading="deleting" @click="handleDelete">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useIncidents } from '../composables/useIncidents'
import BeautifulSkeleton from '../components/BeautifulSkeleton.vue'

const route = useRoute()

const {
  incident,
  loading,
  deleting,
  loadIncident,
  deleteIncident
} = useIncidents()

const deleteDialog = ref(false)

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

function formatLogKey(key) {
  const map = {
    title: 'Título',
    description: 'Descripción',
    priority: 'Prioridad',
    status: 'Estado',
    assigned_id: 'Asignado (ID)',
    due_date: 'Fecha de vencimiento',
  }
  return map[key] || key
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-ES', {
    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
  })
}

async function handleDelete() {
  await deleteIncident(route.params.id)
  deleteDialog.value = false
}

onMounted(async () => {
  await loadIncident(route.params.id)
})
</script>
