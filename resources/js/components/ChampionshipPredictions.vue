<template>
  <div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
      <TrophyIcon class="w-6 h-6 text-yellow-500" />
      Championship Predictions
    </h2>
    <div v-if="loading" class="text-center py-8">
      <img
        :src="loadingGif"
        alt="Loading..."
        class="mx-auto w-16 h-16"
      />
      <p class="mt-4 text-gray-500">Calculating predictions...</p>
    </div>
    <div v-else-if="predictions.length === 0" class="text-center py-8 text-gray-500">
      No predictions available for this week.
    </div>
    <div v-else class="space-y-4">
      <PredictionBar
        v-for="prediction in predictions"
        :key="prediction.team_id"
        :team="prediction.team_name"
        :probability="prediction.win_probability"
      />
    </div>
  </div>
</template>

<script setup>
import { TrophyIcon } from '@heroicons/vue/24/solid'
import PredictionBar from './PredictionBar.vue'
import loadingGif from '../../images/loading.gif'

defineProps({
  predictions: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})
</script>
