import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notifications', () => {
  const show = ref(false)
  const message = ref('')
  const color = ref('success')

  function success(msg) {
    message.value = msg
    color.value = 'success'
    show.value = true
  }

  function error(msg) {
    message.value = msg
    color.value = 'error'
    show.value = true
  }

  function hide() {
    show.value = false
  }

  return { show, message, color, success, error, hide }
})
