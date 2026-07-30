<template>
  <div :class="['shimmer-container', type, { 'rounded-xl': rounded }]" :style="{ height, width }"></div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'card', // Options: card, text, title, table-row
  },
  height: {
    type: String,
    default: '100%',
  },
  width: {
    type: String,
    default: '100%',
  },
  rounded: {
    type: Boolean,
    default: true,
  },
})
</script>

<style scoped>
.shimmer-container {
  background: #f4f5f7;
  background-image: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0,
    rgba(255, 255, 255, 0.6) 20%,
    rgba(255, 255, 255, 0) 40%
  );
  background-size: 800px 100%;
  background-repeat: no-repeat;
  animation: shimmer 1.5s infinite linear;
  opacity: 0.7;
}

.card {
  border-radius: 16px;
  min-height: 120px;
}

.text {
  height: 16px;
  border-radius: 8px;
  margin-bottom: 12px;
}

.title {
  height: 28px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.table-row {
  height: 48px;
  border-radius: 8px;
  margin-bottom: 8px;
}

@keyframes shimmer {
  0% {
    background-position: -400px 0;
  }
  100% {
    background-position: 800px 0;
  }
}
</style>
