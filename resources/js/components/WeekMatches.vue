<template>
  <div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
      <CalendarIcon class="w-6 h-6 text-green-500" />
      Week Matches (Week {{ week }})
    </h2>
    <div v-if="matches.length > 0" class="space-y-4">
      <MatchCard
        v-for="match in matches"
        :key="match.id"
        :match="match"
      />
    </div>
    <div v-else class="text-gray-500 text-center py-8">
      No matches found for this week yet.
    </div>
  </div>
</template>

<script setup>
import { CalendarIcon } from '@heroicons/vue/24/solid'
import MatchCard from './MatchCard.vue'

defineProps({
  week: {
    type: Number,
    required: true,
  },
  matches: {
    type: Array,
    required: true,
  },
})
</script>
