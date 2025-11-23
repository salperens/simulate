<template>
  <Transition name="fade">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-white/70 backdrop-blur-md">
      <div class="text-center">
        <img
          :src="penaltyGif"
          alt="Playing matches..."
          class="mx-auto w-32 h-32"
        />
        <p class="mt-4 text-gray-900 text-lg font-semibold">{{ message }}</p>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import penaltyGif from '../../images/penalty.gif'

defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  message: {
    type: String,
    default: 'Playing matches...',
  },
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
