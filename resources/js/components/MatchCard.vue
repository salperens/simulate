<template>
  <div class="border rounded-lg p-4 hover:shadow-md transition">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <div class="font-semibold">{{ match.home_team.name }}</div>
        <div class="text-sm text-gray-500 flex items-center gap-1">
          <HomeIcon class="w-4 h-4" />
          Home
        </div>
      </div>
      <div class="text-2xl font-bold mx-4">
        <span v-if="match.played_at">
          {{ match.home_score }} - {{ match.away_score }}
        </span>
        <span v-else class="text-gray-400">-</span>
      </div>
      <div class="flex-1 text-right">
        <div class="font-semibold">{{ match.away_team.name }}</div>
        <div class="text-sm text-gray-500 flex items-center gap-1 justify-end">
          <TruckIcon class="w-4 h-4" />
          Away
        </div>
      </div>
    </div>
    <div v-if="match.played_at" class="mt-2 text-sm text-gray-500">
      Played: {{ formatDate(match.played_at) }}
    </div>
  </div>
</template>

<script setup>
import { HomeIcon, TruckIcon } from '@heroicons/vue/24/outline'

defineProps({
  match: {
    type: Object,
    required: true,
  },
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch (e) {
    return dateString
  }
}
</script>
