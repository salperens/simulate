<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-900">Seasons</h2>
      <button
        @click="$emit('create-season')"
        :disabled="hasActiveSeason"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <PlusIcon class="w-5 h-5" />
        Create New Season
      </button>
    </div>

    <div v-if="loading" class="text-center py-8 text-gray-500">
      Loading...
    </div>

    <div v-else-if="seasons.length === 0" class="text-center py-8 text-gray-500">
      No seasons found yet.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="season in seasons"
        :key="season.id"
        @click="$emit('select-season', season.id)"
        :class="[
          'border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md',
          selectedSeasonId === season.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200'
        ]"
      >
        <div class="flex justify-between items-start mb-2">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ season.name || `${season.year} Season` }}
          </h3>
          <span :class="getStatusBadgeClass(season.status)">
            {{ getStatusLabel(season.status) }}
          </span>
        </div>
        <div class="text-sm text-gray-600 space-y-1">
          <div>Year: {{ season.year }}</div>
          <div>Week: {{ season.current_week }} / {{ season.total_weeks }}</div>
          <div v-if="season.start_date">
            Start: {{ formatDate(season.start_date) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { PlusIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  seasons: {
    type: Array,
    required: true,
  },
  selectedSeasonId: {
    type: Number,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['select-season', 'create-season'])

const hasActiveSeason = computed(() => {
  return props.seasons.some(s => s.status === 'active')
})

  const getStatusLabel = (status) => {
    const labels = {
      draft: 'Draft',
      active: 'Active',
      completed: 'Completed',
    }
    return labels[status] || status
  }

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
  }
  return `px-2 py-1 rounded text-xs font-medium ${classes[status] || 'bg-gray-100 text-gray-800'}`
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    })
  } catch (e) {
    return dateString
  }
}
</script>
