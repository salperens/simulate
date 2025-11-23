<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
      <Cog6ToothIcon class="w-6 h-6 text-gray-500" />
      Controls
    </h2>

    <div v-if="seasonStatus === 'draft'" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
      <p class="text-yellow-800 text-sm">
        <strong>Season is in Draft Status:</strong> Click the "Start Season" button to start the season.
      </p>
    </div>

    <div v-if="seasonStatus === 'completed'" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded">
      <p class="text-blue-800 text-sm">
        <strong>Season Completed:</strong> All matches for this season have been played. You can view past weeks but cannot make changes.
      </p>
    </div>

    <div v-if="seasonStatus === 'active' || seasonStatus === 'completed'" class="space-y-4">
      <div class="flex flex-wrap gap-4 items-center">
        <label class="font-medium">Week:</label>
        <select
          v-model="selectedWeek"
          class="border rounded px-4 py-2"
          @change="handleWeekChange"
        >
          <option v-for="w in totalWeeks" :key="w" :value="w">
            Week {{ w }}{{ seasonStatus === 'active' && w === nextPlayableWeek ? ' (Next)' : '' }}
          </option>
        </select>
        <button
          v-if="seasonStatus === 'active'"
          @click="playWeek"
          :disabled="loading || !canPlaySelectedWeek"
          class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Play This Week
        </button>
        <button
          v-if="seasonStatus === 'active'"
          @click="nextWeek"
          :disabled="loading || !canGoToNextWeek"
          class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Go to Next Week
        </button>
      </div>

      <div v-if="seasonStatus === 'active'" class="flex gap-4">
        <button
          @click="playAll"
          :disabled="loading || isSeasonCompleted || canCompleteSeason"
          class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Play All Matches
        </button>
        <button
          v-if="canCompleteSeason"
          @click="completeSeason"
          :disabled="loading"
          class="bg-orange-600 text-white px-6 py-2 rounded hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Complete Season
        </button>
      </div>
    </div>

    <div v-if="seasonStatus === 'draft'" class="flex gap-4">
      <button
        @click="startSeason"
        :disabled="loading"
        class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Start Season
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { Cog6ToothIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  currentWeek: {
    type: Number,
    required: true,
  },
  totalWeeks: {
    type: Number,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  seasonStatus: {
    type: String,
    default: 'draft',
  },
  matches: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits([
  'play-week',
  'play-all',
  'week-change',
  'next-week',
  'start-season',
  'complete-season'
])

const selectedWeek = ref(props.currentWeek)

watch(() => props.currentWeek, (newWeek) => {
  selectedWeek.value = newWeek
})

const handleWeekChange = () => {
  emit('week-change', selectedWeek.value)
}

const playWeek = () => {
  emit('play-week', selectedWeek.value)
}

const playAll = () => {
  emit('play-all')
}

const nextWeek = () => {
  if (selectedWeek.value < props.totalWeeks) {
    selectedWeek.value = selectedWeek.value + 1
    emit('next-week', selectedWeek.value)
  }
}

const startSeason = () => {
  emit('start-season')
}

const completeSeason = () => {
  emit('complete-season')
}

const isSeasonCompleted = computed(() => {
  return props.seasonStatus === 'completed'
})

const isCurrentWeekPlayed = computed(() => {
  if (props.matches.length === 0) return false
  return props.matches.every(match => match.played_at !== null)
})

const nextPlayableWeek = computed(() => {
  if (isCurrentWeekPlayed.value && props.matches.length > 0) {
    return props.currentWeek + 1
  }

  return props.currentWeek
})

const canPlaySelectedWeek = computed(() => {
  return selectedWeek.value === nextPlayableWeek.value && props.seasonStatus === 'active'
})

const canGoToNextWeek = computed(() => {
  return selectedWeek.value < props.totalWeeks && isCurrentWeekPlayed.value
})

const canCompleteSeason = computed(() => {
  return props.seasonStatus === 'active' && selectedWeek.value >= props.totalWeeks
})
</script>
