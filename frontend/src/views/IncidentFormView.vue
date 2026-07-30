<template>
  <div>
    <div class="d-flex align-center mb-6">
      <v-btn icon variant="text" @click="$router.back()" class="mr-2">
        <v-icon icon="mdi-arrow-left" />
      </v-btn>
      <h1 class="text-h4 font-weight-bold">{{ isEdit ? 'Editar Incidente' : 'Nuevo Incidente' }}</h1>
    </div>

    <v-card class="pa-8 glass-card hover-lift">
      <template v-if="loadingData">
        <BeautifulSkeleton type="title" width="40%" />
        <BeautifulSkeleton type="text" width="100%" />
        <BeautifulSkeleton type="text" width="100%" />
        <BeautifulSkeleton type="text" width="60%" />
      </template>
      
      <template v-else>
        <v-alert v-if="error" type="error" variant="tonal" closable class="mb-4" @click:close="error = ''">
          {{ error }}
        </v-alert>

        <IncidentForm
          :initial-data="initialData"
          :users="users"
          :loading="saving"
          :submit-text="isEdit ? 'Actualizar' : 'Crear'"
          @submit="handleSubmit"
          @cancel="$router.back()"
        />
      </template>
    </v-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useIncidents } from '../composables/useIncidents'
import BeautifulSkeleton from '../components/BeautifulSkeleton.vue'
import IncidentForm from '../components/organisms/IncidentForm.vue'

const route = useRoute()

const {
  users,
  loading: loadingData,
  saving,
  error,
  loadUsers,
  loadIncident,
  saveIncident
} = useIncidents()

const isEdit = computed(() => !!route.params.id)
const initialData = ref({})

async function handleSubmit(formData) {
  await saveIncident(route.params.id, formData)
}

onMounted(async () => {
  await loadUsers()
  if (isEdit.value) {
    const data = await loadIncident(route.params.id)
    if (data) {
      initialData.value = {
        title: data.title,
        description: data.description,
        priority: data.priority,
        status: data.status,
        assigned_id: data.assignee?.id || null,
        due_date: data.due_date,
      }
    }
  }
})
</script>
