<template>
  <div class="flex items-center gap-2">
    <CalendarIcon class="w-5 h-5 text-gray-500" />
    <select
      :value="selectedSeasonId"
      @change="handleSeasonChange"
      class="border rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
    >
      <option v-for="season in seasons" :key="season.id" :value="season.id">
        {{ season.name || `${season.year} Sezonu` }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { CalendarIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  seasons: {
    type: Array,
    required: true,
  },
  selectedSeason: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['season-change'])

const selectedSeasonId = computed(() => {
  return props.selectedSeason?.id || null
})

const handleSeasonChange = (event) => {
  const seasonId = parseInt(event.target.value)
  emit('season-change', seasonId)
}
</script>
