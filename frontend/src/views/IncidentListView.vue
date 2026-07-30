<template>
  <div>
    <div class="d-flex flex-column flex-sm-row align-start align-sm-center justify-space-between mb-6 ga-4">
      <h1 class="text-h4 font-weight-bold">Incidentes</h1>
      <div class="d-flex ga-2 w-100 w-sm-auto justify-end">
        <v-btn color="secondary" prepend-icon="mdi-download" @click="exportCSV" :loading="exporting">
          Exportar
        </v-btn>
        <v-btn color="primary" prepend-icon="mdi-plus" :to="{ name: 'CreateIncident' }">
          Nuevo
        </v-btn>
      </div>
    </div>

    <v-card class="mb-4 pa-6 glass-card">
      <v-row density="comfortable" class="align-center">
        <v-col cols="12" sm="4" md="2" class="flex-grow-1">
          <v-text-field
            v-model="filters.search"
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
            v-model="filters.status"
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
            v-model="filters.priority"
            label="Prioridad"
            :items="priorityOptions"
            clearable
            hide-details
            density="compact"
            variant="outlined"
            @update:modelValue="fetchIncidents"
          />
        </v-col>
        <v-col v-if="auth.user?.role === 'admin'" cols="12" sm="4" md="2">
          <v-select
            v-model="filters.assigned_id"
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
        <v-col cols="6" sm="4" md="auto" style="width: 150px">
          <v-text-field
            v-model="filters.from"
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
        <v-col cols="6" sm="4" md="auto" style="width: 150px">
          <v-text-field
            v-model="filters.to"
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

    <v-card class="glass-card">
      <template v-if="loading && incidents.length === 0">
        <BeautifulSkeleton type="table-row" v-for="n in 5" :key="n" />
      </template>
      <template v-else>
        <div class="table-responsive">
          <v-data-table-server
            :headers="headers"
            :items="incidents"
            :items-length="totalItems"
            :loading="loading"
            :items-per-page="itemsPerPage"
            :items-per-page-options="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' }
            ]"
            :page="page"
            :mobile-breakpoint="0"
            @update:page="page = $event; fetchIncidents()"
            @update:items-per-page="itemsPerPage = $event; fetchIncidents()"
            @update:sort-by="handleSort"
            hover
          >
            <template v-slot:item.priority="{ item }">
              <v-chip :color="priorityColor(item.priority)" size="small" variant="tonal" class="text-capitalize">
                {{ item.priority }}
              </v-chip>
            </template>

            <template v-slot:item.status="{ item }">
              <v-chip :color="statusColor(item.status)" size="small" variant="tonal" class="text-capitalize">
                {{ formatStatus(item.status) }}
              </v-chip>
            </template>

            <template v-slot:item.assignee="{ item }">
              {{ item.assignee?.name || '—' }}
            </template>

            <template v-slot:item.due_date="{ item }">
              <span :class="{ 'text-error': item.is_overdue }">
                {{ item.due_date }}
                <v-icon v-if="item.is_overdue" size="16" color="error" icon="mdi-alert-circle" class="ml-1" />
              </span>
            </template>

            <template v-slot:item.actions="{ item }">
              <div class="d-flex flex-nowrap">
                <v-btn icon size="small" variant="text" :to="{ name: 'IncidentDetail', params: { id: item.id } }">
                  <v-icon icon="mdi-eye" size="18" />
                </v-btn>
                <v-btn icon size="small" variant="text" :to="{ name: 'EditIncident', params: { id: item.id } }">
                  <v-icon icon="mdi-pencil" size="18" />
                </v-btn>
                <v-btn
                  v-if="auth.user?.role === 'admin'"
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  @click="confirmDelete(item)"
                >
                  <v-icon icon="mdi-delete" size="18" />
                </v-btn>
              </div>
            </template>
          </v-data-table-server>
        </div>
      </template>
    </v-card>

    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card color="surface" class="pa-4">
        <v-card-title>Confirmar eliminación</v-card-title>
        <v-card-text>
          ¿Estás seguro de eliminar "{{ selectedIncident?.title }}"?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Cancelar</v-btn>
          <v-btn color="error" variant="tonal" :loading="deleting" @click="deleteIncident">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'
import BeautifulSkeleton from '../components/BeautifulSkeleton.vue'

const auth = useAuthStore()
const notify = useNotificationStore()


const incidents = ref([])
const totalItems = ref(0)
const loading = ref(true)
const exporting = ref(false)
const page = ref(1)
const itemsPerPage = ref(15)
const sortBy = ref('created_at')
const sortDir = ref('desc')
const deleteDialog = ref(false)
const selectedIncident = ref(null)
const deleting = ref(false)

const filters = ref({
  search: '',
  status: null,
  priority: null,
  from: '',
  to: '',
})

const headers = [
  { title: 'Título', key: 'title', sortable: true },
  { title: 'Prioridad', key: 'priority', sortable: true, width: '120px' },
  { title: 'Estado', key: 'status', sortable: true, width: '130px' },
  { title: 'Asignado', key: 'assignee', sortable: false, width: '150px' },
  { title: 'Vencimiento', key: 'due_date', sortable: true, width: '140px' },
  { title: 'Acciones', key: 'actions', sortable: false, width: '140px', align: 'center' },
]

const hasActiveFilters = computed(() => {
  return !!(filters.value.search || filters.value.status || filters.value.priority || filters.value.assigned_id || filters.value.from || filters.value.to)
})

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

const users = ref([])

const fetchUsers = async () => {
  try {
    if (auth.user?.role === 'admin') {
      const response = await api.get('/users')
      users.value = response.data
    }
  } catch (e) {
    console.error('Error fetching users:', e)
  }
}

const openDatePicker = (e) => {
  const input = e.currentTarget.querySelector('input[type="date"]')
  if (input && typeof input.showPicker === 'function') {
    input.showPicker()
  }
}

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

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchIncidents(), 400)
}

function handleSort(sortOptions) {
  if (sortOptions.length > 0) {
    sortBy.value = sortOptions[0].key
    sortDir.value = sortOptions[0].order
  } else {
    sortBy.value = 'created_at'
    sortDir.value = 'desc'
  }
  fetchIncidents()
}

const buildFilters = () => {
  const params = {
    page: page.value,
    per_page: itemsPerPage.value,
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
  }
  if (filters.value.search) params.search = filters.value.search
  if (filters.value.status) params.status = filters.value.status
  if (filters.value.priority) params.priority = filters.value.priority
  if (filters.value.assigned_id) params.assigned_id = filters.value.assigned_id
  if (filters.value.from) params.from = filters.value.from
  if (filters.value.to) params.to = filters.value.to
  return params
}

const resetFilters = () => {
  filters.value = {
    search: '',
    status: null,
    priority: null,
    assigned_id: null,
    from: '',
    to: ''
  }
  page.value = 1
  fetchIncidents()
}

const fetchIncidents = async () => {
  loading.value = true
  try {
    const response = await api.get('/incidents', { params: buildFilters() })
    incidents.value = response.data.data
    totalItems.value = response.data.meta.total
  } catch (e) {
    notify.error('Error al cargar incidentes')
  } finally {
    loading.value = false
  }
}

const exportCSV = async () => {
  exporting.value = true
  try {
    const response = await api.get('/incidents/export', {
      params: buildFilters(),
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'incidentes.csv')
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    notify.success('Exportación completada')
  } catch (e) {
    notify.error('Error al exportar')  
  } finally {
    exporting.value = false
  }
}

function confirmDelete(incident) {
  selectedIncident.value = incident
  deleteDialog.value = true
}

async function deleteIncident() {
  deleting.value = true
  try {
    await api.delete(`/incidents/${selectedIncident.value.id}`)
    deleteDialog.value = false
    notify.success('Incidente eliminado correctamente')
    fetchIncidents()
  } catch (e) {
    notify.error('Error al eliminar el incidente')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchUsers()
  fetchIncidents()

  if (window.Echo) {
    window.Echo.channel('incidents')
      .listen('IncidentSaved', () => {
        fetchIncidents()
      })
      .listen('IncidentDeleted', () => {
        fetchIncidents()
      })
  }
})

onUnmounted(() => {
  if (window.Echo) {
    window.Echo.leaveChannel('incidents')
  }
})
</script>

<style scoped>
.table-responsive {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
</style>
