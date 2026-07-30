<template>
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
      <v-btn class="w-100 w-sm-auto" variant="text" @click="$emit('cancel')" size="large">Cancelar</v-btn>
      <v-btn class="w-100 w-sm-auto" type="submit" color="primary" :loading="loading" size="large">
        {{ submitText }}
      </v-btn>
    </div>
  </v-form>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  },
  users: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  submitText: {
    type: String,
    default: 'Guardar'
  }
})

const emit = defineEmits(['submit', 'cancel'])

const formRef = ref(null)

const form = ref({
  title: '',
  description: '',
  priority: null,
  status: 'abierto',
  assigned_id: null,
  due_date: '',
})

// Update local form when initialData changes (e.g. after data loads from API)
watch(() => props.initialData, (newVal) => {
  if (newVal && Object.keys(newVal).length) {
    form.value = { ...form.value, ...newVal }
  }
}, { immediate: true })

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

  emit('submit', { ...form.value })
}
</script>
