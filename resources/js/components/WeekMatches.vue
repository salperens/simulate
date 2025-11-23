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
        :canEdit="seasonStatus !== 'completed' && match.played_at !== null"
        @edit="handleEdit"
      />
    </div>
    <div v-else class="text-gray-500 text-center py-8">
      No matches found for this week yet.
    </div>

    <EditMatchModal
      :show="showEditModal"
      :match="selectedMatch"
      :loading="updatingFixture"
      @close="handleCloseModal"
      @save="handleSaveFixture"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { CalendarIcon } from '@heroicons/vue/24/solid'
import MatchCard from './MatchCard.vue'
import EditMatchModal from './EditMatchModal.vue'

const props = defineProps({
  week: {
    type: Number,
    required: true,
  },
  matches: {
    type: Array,
    required: true,
  },
  seasonStatus: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['update'])

const showEditModal = ref(false)
const selectedMatch = ref(null)
const updatingFixture = ref(false)

const handleEdit = (match) => {
  selectedMatch.value = match
  showEditModal.value = true
}

const handleCloseModal = () => {
  showEditModal.value = false
  selectedMatch.value = null
}

const handleSaveFixture = async (data) => {
  updatingFixture.value = true
  try {
    await new Promise((resolve, reject) => {
      emit('update', data, resolve, reject)
    })
    handleCloseModal()
  } catch (error) {
    throw error
  } finally {
    updatingFixture.value = false
  }
}
</script>
