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

    <IncidentFilters
      v-model="filters"
      :users="users"
      :is-admin="auth.user?.role === 'admin'"
      @fetch="fetchIncidentsWrapper"
    />

    <IncidentTable
      :incidents="incidents"
      :loading="loading"
      :total-items="totalItems"
      :items-per-page="itemsPerPage"
      :page="page"
      :is-admin="auth.user?.role === 'admin'"
      @update:page="page = $event; fetchIncidents()"
      @update:items-per-page="itemsPerPage = $event; fetchIncidents()"
      @sort="handleSort"
      @delete="confirmDelete"
    />

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
import { ref, onMounted, onUnmounted } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'
import IncidentFilters from '../components/molecules/IncidentFilters.vue'
import IncidentTable from '../components/organisms/IncidentTable.vue'

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

const fetchIncidentsWrapper = (options = {}) => {
  if (options.resetPage) page.value = 1
  fetchIncidents()
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

