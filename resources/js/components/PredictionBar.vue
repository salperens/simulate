<template>
  <div>
    <div class="flex justify-between mb-1">
      <span class="font-semibold">{{ team }}</span>
      <span class="text-gray-600">{{ probability.toFixed(1) }}%</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-4">
      <div
        class="bg-blue-600 h-4 rounded-full transition-all duration-500"
        :style="{ width: probability + '%' }"
      ></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  team: {
    type: String,
    required: true,
  },
  probability: {
    type: Number,
    required: true,
  },
})
</script>
