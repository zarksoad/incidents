<template>
  <v-card class="mb-4 pa-6 glass-card">
    <v-row density="comfortable" class="align-center">
      <v-col cols="12" sm="4" md="2" class="flex-grow-1">
        <v-text-field
          v-model="modelValue.search"
          label="Buscar..."
          prepend-inner-icon="mdi-magnify"
          clearable
          hide-details
          density="compact"
          variant="outlined"
          @update:modelValue="debouncedFetch"
        />
      </v-col>
      <v-col cols="6" sm="4" md="2">
        <v-select
          v-model="modelValue.status"
          label="Estado"
          :items="statusOptions"
          clearable
          hide-details
          density="compact"
          variant="outlined"
          @update:modelValue="fetchIncidents"
        />
      </v-col>
      <v-col cols="6" sm="4" md="2">
        <v-select
          v-model="modelValue.priority"
          label="Prioridad"
          :items="priorityOptions"
          clearable
          hide-details
          density="compact"
          variant="outlined"
          @update:modelValue="fetchIncidents"
        />
      </v-col>
      <v-col v-if="isAdmin" cols="12" sm="4" md="2">
        <v-select
          v-model="modelValue.assigned_id"
          label="Asignado a"
          :items="users"
          item-title="name"
          item-value="id"
          clearable
          hide-details
          density="compact"
          variant="outlined"
          @update:modelValue="fetchIncidents"
        />
      </v-col>
      <v-col cols="6" sm="4" md="2">
        <v-text-field
          v-model="modelValue.from"
          label="Desde"
          type="date"
          clearable
          hide-details
          density="compact"
          variant="outlined"
          @click="openDatePicker"
          @update:modelValue="fetchIncidents"
        />
      </v-col>
      <v-col cols="6" sm="4" md="2">
        <v-text-field
          v-model="modelValue.to"
          label="Hasta"
          type="date"
          clearable
          hide-details
          density="compact"
          variant="outlined"
          @click="openDatePicker"
          @update:modelValue="fetchIncidents"
        />
      </v-col>
      <v-col v-if="hasActiveFilters" cols="12" md="auto" class="d-flex align-center mt-2 mt-md-0 ml-md-2">
        <v-btn
          color="primary"
          variant="tonal"
          prepend-icon="mdi-filter-off-outline"
          @click="resetFilters"
          class="text-none font-weight-bold"
          height="40"
        >
          Limpiar
        </v-btn>
      </v-col>
    </v-row>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  },
  users: {
    type: Array,
    default: () => []
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'fetch'])

const statusOptions = [
  { title: 'Abierto', value: 'abierto' },
  { title: 'En Progreso', value: 'en_progreso' },
  { title: 'Cerrado', value: 'cerrado' },
  { title: 'Vencido', value: 'vencido' },
]

const priorityOptions = [
  { title: 'Baja', value: 'baja' },
  { title: 'Media', value: 'media' },
  { title: 'Alta', value: 'alta' },
  { title: 'Crítica', value: 'critica' },
]



const hasActiveFilters = computed(() => {
  const f = props.modelValue
  return !!(f.search || f.status || f.priority || f.assigned_id || f.from || f.to)
})

const openDatePicker = (e) => {
  const input = e.currentTarget.querySelector('input[type="date"]')
  if (input && typeof input.showPicker === 'function') {
    input.showPicker()
  }
}

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emit('fetch')
  }, 400)
}

function fetchIncidents() {
  emit('fetch')
}

const resetFilters = () => {
  emit('update:modelValue', {
    search: '',
    status: null,
    priority: null,
    assigned_id: null,
    from: '',
    to: ''
  })
  emit('fetch', { resetPage: true })
}
</script>
