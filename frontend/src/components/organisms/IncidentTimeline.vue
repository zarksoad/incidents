<template>
  <v-card class="pa-4 pa-sm-8 glass-card mt-6">
    <h3 class="text-h6 font-weight-bold mb-4">Historial de Cambios</h3>
    <v-timeline density="compact" side="end" align="start">
      <v-timeline-item
        v-for="log in logs"
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
      
      <v-timeline-item v-if="!logs || logs.length === 0" dot-color="grey" size="small">
        <div class="text-medium-emphasis">No hay historial registrado.</div>
      </v-timeline-item>
    </v-timeline>
  </v-card>
</template>

<script setup>
defineProps({
  logs: {
    type: Array,
    default: () => []
  }
})

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
</script>
