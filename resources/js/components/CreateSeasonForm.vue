<template>
  <div class="max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Create New Season</h2>

    <div v-if="hasActiveSeason" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
      <p class="text-yellow-800">
        <strong>Warning:</strong> An active season exists. You must complete the current season before creating a new one.
      </p>
    </div>

    <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-md p-6 space-y-6" :class="{ 'opacity-50 pointer-events-none': hasActiveSeason }">
      <div>
        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">
          Season Year *
        </label>
        <input
          id="year"
          v-model.number="form.year"
          type="number"
          min="2000"
          max="2100"
          required
          class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="e.g. 2025"
        />
      </div>

      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
          Season Name (Optional)
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          maxlength="255"
          class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="e.g. 2025-2026 Season"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Teams * (You must select at least 2 teams)
        </label>
        <div v-if="loadingTeams" class="text-gray-500 py-4">
          Loading teams...
        </div>
        <div v-else class="border rounded p-4 max-h-96 overflow-y-auto">
          <div class="space-y-2">
            <label
              v-for="team in teams"
              :key="team.id"
              class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer"
            >
              <input
                type="checkbox"
                :value="team.id"
                v-model="form.teamIds"
                class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <span class="text-sm text-gray-700">{{ team.name }}</span>
            </label>
          </div>
        </div>
        <p v-if="form.teamIds.length < 2" class="mt-2 text-sm text-red-600">
          You must select at least 2 teams.
        </p>
      </div>

      <div class="flex gap-4">
        <button
          type="submit"
          :disabled="!isFormValid || submitting || hasActiveSeason"
          class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ submitting ? 'Creating...' : 'Create Season' }}
        </button>
        <button
          type="button"
          @click="$emit('cancel')"
          class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300"
        >
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  teams: {
    type: Array,
    required: true,
  },
  loadingTeams: {
    type: Boolean,
    default: false,
  },
  hasActiveSeason: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const form = ref({
  year: new Date().getFullYear(),
  name: '',
  teamIds: [],
})

const submitting = ref(false)

const isFormValid = computed(() => {
  return form.value.year >= 2000 && form.value.year <= 2100 && form.value.teamIds.length >= 2
})

const handleSubmit = async () => {
  if (!isFormValid.value) return

  submitting.value = true
  try {
    await emit('submit', {
      year: form.value.year,
      name: form.value.name || null,
      team_ids: form.value.teamIds,
    })
  } finally {
    submitting.value = false
  }
}
</script>
