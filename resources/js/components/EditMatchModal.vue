<template>
  <Transition name="modal">
    <div
      v-if="show"
      class="modal-background fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="handleClose"
    >
      <div class="modal-content bg-white rounded-xl shadow-2xl max-w-md w-full relative z-10">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Edit Match Score</h3>
            <button
              @click="handleClose"
              class="text-gray-400 hover:text-gray-600 transition"
            >
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>

          <div class="mb-6">
            <div class="text-center mb-4">
              <div class="font-semibold text-lg">{{ match.home_team.name }}</div>
              <div class="text-sm text-gray-500">Home</div>
            </div>

            <div class="flex items-center justify-center gap-4 mb-4">
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Home Score
                </label>
                <input
                  v-model.number="homeScore"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  placeholder="0"
                />
              </div>
              <div class="text-2xl font-bold text-gray-400">-</div>
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Away Score
                </label>
                <input
                  v-model.number="awayScore"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  placeholder="0"
                />
              </div>
            </div>

            <div class="text-center">
              <div class="font-semibold text-lg">{{ match.away_team.name }}</div>
              <div class="text-sm text-gray-500">Away</div>
            </div>
          </div>

          <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ error }}</p>
          </div>

          <div class="flex gap-3">
            <button
              @click="handleClose"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              @click="handleSave"
              :disabled="loading || homeScore === null || awayScore === null"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading">Saving...</span>
              <span v-else>Save</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  match: {
    type: Object,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'save'])

const homeScore = ref(null)
const awayScore = ref(null)
const error = ref('')

watch(() => props.show, (newVal) => {
  if (newVal) {
    homeScore.value = props.match.home_score ?? null
    awayScore.value = props.match.away_score ?? null
    error.value = ''
    // Modal açıldığında body scroll'unu engelle
    document.body.style.overflow = 'hidden'
  } else {
    // Modal kapandığında body scroll'unu geri getir
    document.body.style.overflow = ''
  }
})

onUnmounted(() => {
  // Component unmount olduğunda body scroll'unu geri getir
  document.body.style.overflow = ''
})

watch(() => props.match, (newMatch) => {
  if (props.show) {
    homeScore.value = newMatch.home_score ?? null
    awayScore.value = newMatch.away_score ?? null
  }
}, { deep: true })

const handleClose = () => {
  error.value = ''
  emit('close')
}

const handleSave = () => {
  if (homeScore.value === null || awayScore.value === null) {
    error.value = 'Please enter both scores.'
    return
  }

  if (homeScore.value < 0 || awayScore.value < 0) {
    error.value = 'Scores cannot be negative.'
    return
  }

  error.value = ''
  emit('save', {
    fixtureId: props.match.id,
    homeScore: homeScore.value,
    awayScore: awayScore.value,
  })
}
</script>

<style scoped>
.modal-background {
  background-color: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  position: fixed;
  inset: 0;
  overflow-y: auto;
}

.modal-content {
  position: relative;
  z-index: 10;
  background-color: rgba(255, 255, 255, 0.98);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.95) translateY(-10px);
  opacity: 0;
}
</style>