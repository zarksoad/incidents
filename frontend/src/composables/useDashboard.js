import { ref, onMounted, onUnmounted } from 'vue'
import api from '../services/api'

export function useDashboard() {
  const stats = ref({})
  const loading = ref(true)

  const fetchDashboardStats = async () => {
    try {
      const { data } = await api.get('/dashboard')
      stats.value = data
    } catch (e) {
      console.error('Error fetching dashboard stats:', e)
    } finally {
      loading.value = false
    }
  }

  const setupEchoListeners = () => {
    if (window.Echo) {
      window.Echo.channel('incidents')
        .listen('IncidentSaved', () => {
          fetchDashboardStats()
        })
        .listen('IncidentDeleted', () => {
          fetchDashboardStats()
        })
    }
  }

  const teardownEchoListeners = () => {
    if (window.Echo) {
      window.Echo.leaveChannel('incidents')
    }
  }

  onMounted(() => {
    fetchDashboardStats()
    setupEchoListeners()
  })

  onUnmounted(() => {
    teardownEchoListeners()
  })

  return {
    stats,
    loading,
    fetchDashboardStats
  }
}
