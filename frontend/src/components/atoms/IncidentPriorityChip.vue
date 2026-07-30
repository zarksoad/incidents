<template>
  <v-chip :color="priority" class="text-capitalize">
    {{ priority }}
  </v-chip>
</template>

<script setup>
defineProps({
  priority: {
    type: String,
    required: true,
  }
})
</script>
