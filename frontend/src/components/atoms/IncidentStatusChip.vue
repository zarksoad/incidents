<template>
  <v-chip :color="status" class="text-capitalize">
    {{ formattedStatus }}
  </v-chip>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
  }
})

const formattedStatus = computed(() => {
  if (!props.status) return ''
  return props.status.replace('_', ' ')
})
</script>
