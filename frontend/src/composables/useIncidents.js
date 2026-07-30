import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useNotificationStore } from '../stores/notifications'

export function useIncidents() {
  const router = useRouter()
  const notify = useNotificationStore()

  const incident = ref(null)
  const users = ref([])
  const loading = ref(false)
  const saving = ref(false)
  const deleting = ref(false)
  const error = ref('')

  const loadUsers = async () => {
    try {
      const { data } = await api.get('/users')
      users.value = data
    } catch (e) {
      console.error('Error loading users:', e)
    }
  }

  const loadIncident = async (id) => {
    loading.value = true
    error.value = ''
    try {
      const { data } = await api.get(`/incidents/${id}`)
      incident.value = data.data
      return data.data
    } catch (e) {
      error.value = 'No se pudo cargar el incidente.'
      throw e
    } finally {
      loading.value = false
    }
  }

  const saveIncident = async (id, formData) => {
    saving.value = true
    error.value = ''
    try {
      if (id) {
        await api.put(`/incidents/${id}`, formData)
        notify.success('Incidente actualizado correctamente')
      } else {
        await api.post('/incidents', formData)
        notify.success('Incidente creado correctamente')
      }
      router.push({ name: 'Incidents' })
    } catch (e) {
      const errors = e.response?.data?.errors
      if (errors) {
        error.value = Object.values(errors).flat().join('. ')
      } else {
        error.value = e.response?.data?.message || 'Error al guardar el incidente.'
      }
    } finally {
      saving.value = false
    }
  }

  const deleteIncident = async (id) => {
    deleting.value = true
    try {
      await api.delete(`/incidents/${id}`)
      notify.success('Incidente eliminado correctamente')
      router.push({ name: 'Incidents' })
    } catch (e) {
      notify.error('Error al eliminar el incidente')
    } finally {
      deleting.value = false
    }
  }

  return {
    incident,
    users,
    loading,
    saving,
    deleting,
    error,
    loadUsers,
    loadIncident,
    saveIncident,
    deleteIncident
  }
}
