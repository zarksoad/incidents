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

      <v-form ref="formRef" @submit.prevent="handleSubmit">
        <v-row>
          <v-col cols="12">
            <v-text-field
              v-model="form.title"
              label="Título"
              color="primary"
              :rules="[rules.required]"
            />
          </v-col>
          <v-col cols="12">
            <v-textarea
              v-model="form.description"
              label="Descripción"
              rows="4"
              variant="outlined"
              color="primary"
              :rules="[rules.required]"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-select
              v-model="form.priority"
              label="Prioridad"
              :items="priorityOptions"
              color="primary"
              :rules="[rules.required]"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-select
              v-model="form.status"
              label="Estado"
              :items="statusOptions"
              color="primary"
              :rules="[rules.required]"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-select
              v-model="form.assigned_id"
              label="Asignar a"
              :items="users"
              item-title="name"
              item-value="id"
              color="primary"
              clearable
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="form.due_date"
              label="Fecha de vencimiento"
              type="date"
              color="primary"
              :rules="[rules.required]"
            />
          </v-col>
        </v-row>

        <div class="d-flex flex-column-reverse flex-sm-row justify-end mt-4 ga-3">
          <v-btn class="w-100 w-sm-auto" variant="text" @click="$router.back()" size="large">Cancelar</v-btn>
          <v-btn class="w-100 w-sm-auto" type="submit" color="primary" :loading="saving" size="large">
            {{ isEdit ? 'Actualizar' : 'Crear' }}
          </v-btn>
        </div>
      </v-form>
      </template>
    </v-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useIncidents } from '../composables/useIncidents'
import BeautifulSkeleton from '../components/BeautifulSkeleton.vue'

const route = useRoute()
const router = useRouter()

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
const formRef = ref(null)

const form = ref({
  title: '',
  description: '',
  priority: null,
  status: 'abierto',
  assigned_id: null,
  due_date: '',
})

const rules = {
  required: v => !!v || 'Campo obligatorio',
}

const priorityOptions = [
  { title: 'Baja', value: 'baja' },
  { title: 'Media', value: 'media' },
  { title: 'Alta', value: 'alta' },
  { title: 'Crítica', value: 'critica' },
]

const statusOptions = [
  { title: 'Abierto', value: 'abierto' },
  { title: 'En Progreso', value: 'en_progreso' },
  { title: 'Cerrado', value: 'cerrado' },
  { title: 'Vencido', value: 'vencido' },
]

async function handleSubmit() {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  await saveIncident(route.params.id, form.value)
}

onMounted(async () => {
  await loadUsers()
  if (isEdit.value) {
    const data = await loadIncident(route.params.id)
    if (data) {
      form.value = {
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
