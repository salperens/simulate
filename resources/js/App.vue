<template>
  <div class="min-h-screen bg-gray-50">
    <LoadingOverlay
      :show="isPlayingMatches"
      message="Playing matches..."
    />
    <Header />
    <main class="container mx-auto px-4 py-8">
      <Tabs
        :tabs="tabs"
        :activeTab="activeTab"
        @tab-change="handleTabChange"
      />

      <div v-if="activeTab === 'seasons'" class="mt-6">
        <SeasonsList
          :seasons="seasons"
          :selectedSeasonId="selectedSeason?.id"
          :loading="loadingSeasons"
          @select-season="handleSelectSeason"
          @create-season="handleCreateSeasonClick"
        />
      </div>

      <div v-if="activeTab === 'create-season'" class="mt-6">
        <CreateSeasonForm
          :teams="teams"
          :loadingTeams="loadingTeams"
          :hasActiveSeason="hasActiveSeason"
          @submit="handleCreateSeason"
          @cancel="handleCancelCreate"
        />
      </div>

      <div v-if="activeTab === 'season-detail' && selectedSeason" class="mt-6">
        <LeagueTable :standings="standings" />
        <WeekMatches :week="currentWeek" :matches="matches" />
        <ChampionshipPredictions
          v-if="shouldShowPredictions"
          :predictions="predictions"
          :loading="loadingPredictions"
        />
        <ControlPanel
          :currentWeek="currentWeek"
          :totalWeeks="totalWeeks"
          :loading="loading"
          :seasonStatus="selectedSeason.status"
          :matches="matches"
          @play-week="handlePlayWeek"
          @play-all="handlePlayAll"
          @week-change="handleWeekChange"
          @next-week="handleNextWeek"
          @start-season="handleStartSeason"
          @complete-season="handleCompleteSeason"
        />
      </div>

      <div v-if="activeTab === 'season-detail' && !selectedSeason" class="mt-6 text-center py-8 text-gray-500">
        Please select a season.
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useLeague } from './composables/useLeague'
import {
  CalendarIcon,
  PlusIcon,
  TrophyIcon
} from '@heroicons/vue/24/solid'
import Header from './components/Header.vue'
import Tabs from './components/Tabs.vue'
import SeasonsList from './components/SeasonsList.vue'
import CreateSeasonForm from './components/CreateSeasonForm.vue'
import LeagueTable from './components/LeagueTable.vue'
import WeekMatches from './components/WeekMatches.vue'
import ChampionshipPredictions from './components/ChampionshipPredictions.vue'
import ControlPanel from './components/ControlPanel.vue'
import LoadingOverlay from './components/LoadingOverlay.vue'

const {
  standings,
  currentWeek,
  totalWeeks,
  matches,
  predictions,
  loading,
  seasons,
  loadingSeasons,
  selectedSeason,
  teams,
  loadingTeams,
  loadingPredictions,
  hasActiveSeason,
  fetchStandings,
  fetchSeason,
  fetchSeasons,
  selectSeason,
  fetchWeekMatches,
  fetchPredictions,
  playWeek: playWeekAction,
  playAll: playAllAction,
  fetchTeams,
  createSeason: createSeasonAction,
  startSeason: startSeasonAction,
  completeSeason: completeSeasonAction,
} = useLeague()

const activeTab = ref('seasons')

const tabs = [
  { id: 'seasons', label: 'Seasons', icon: CalendarIcon },
  { id: 'create-season', label: 'New Season', icon: PlusIcon },
  { id: 'season-detail', label: 'Season Detail', icon: TrophyIcon },
]

const shouldShowPredictions = computed(() => {
  if (!selectedSeason.value) return false
  const lastThreeWeeksStart = Math.max(1, totalWeeks.value - 2)
  return currentWeek.value >= lastThreeWeeksStart
})

const isPlayingMatches = computed(() => {
  return loading.value
})

const handleTabChange = (tabId) => {
  activeTab.value = tabId

  if (tabId === 'season-detail' && selectedSeason.value) {
    loadSeasonData()
  }

  if (tabId === 'create-season' && teams.value.length === 0) {
    fetchTeams()
  }
}

const handleSelectSeason = async (seasonId) => {
  await selectSeason(seasonId)
  activeTab.value = 'season-detail'
  await loadSeasonData()
}

const handleCreateSeasonClick = () => {
  if (hasActiveSeason.value) {
    alert('An active season exists. You must complete the current season before creating a new one.')
    return
  }
  activeTab.value = 'create-season'
  if (teams.value.length === 0) {
    fetchTeams()
  }
}

const handleCreateSeason = async (data) => {
  try {
    await createSeasonAction(data)
    activeTab.value = 'season-detail'
    await loadSeasonData()
  } catch (error) {
    const errorMessage = error.response?.data?.message ||
                        (error.response?.data?.errors ?
                          Object.values(error.response.data.errors).flat().join(', ') :
                          'An error occurred while creating the season.')
    alert(errorMessage)
  }
}

const handleCancelCreate = () => {
  activeTab.value = 'seasons'
}

const loadSeasonData = async () => {
  await Promise.all([
    fetchStandings(currentWeek.value),
    fetchWeekMatches(currentWeek.value),
  ])

  if (shouldShowPredictions.value) {
    await fetchPredictions(currentWeek.value)
  }
}

const handlePlayWeek = async (week) => {
  loading.value = true
  const startTime = Date.now()
  const minLoadingTime = 3000

  try {
    await playWeekAction(week)
    await Promise.all([
      fetchStandings(week),
      fetchWeekMatches(week),
    ])

    if (shouldShowPredictions.value) {
      await fetchPredictions(week)
    }

    const elapsedTime = Date.now() - startTime
    if (elapsedTime < minLoadingTime) {
      await new Promise(resolve => setTimeout(resolve, minLoadingTime - elapsedTime))
    }
  } finally {
    loading.value = false
  }
}

const handlePlayAll = async () => {
  loading.value = true
  const startTime = Date.now()
  const minLoadingTime = 3000

  try {
    await playAllAction()
    await Promise.all([
      fetchStandings(currentWeek.value),
      fetchWeekMatches(currentWeek.value),
    ])

    if (shouldShowPredictions.value) {
      await fetchPredictions(currentWeek.value)
    }

    const elapsedTime = Date.now() - startTime
    if (elapsedTime < minLoadingTime) {
      await new Promise(resolve => setTimeout(resolve, minLoadingTime - elapsedTime))
    }
  } finally {
    loading.value = false
  }
}

const handleWeekChange = async (week) => {
  await Promise.all([
    fetchWeekMatches(week),
    fetchStandings(week),
  ])
  if (shouldShowPredictions.value) {
    await fetchPredictions(week)
  }
}

const handleNextWeek = async (week) => {
  currentWeek.value = week
  await loadSeasonData()
}

const handleStartSeason = async () => {
  if (!selectedSeason.value?.id) return

  try {
    await startSeasonAction(selectedSeason.value.id)
    await fetchSeasons()
    await selectSeason(selectedSeason.value.id)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'An error occurred while starting the season.'
    alert(errorMessage)
  }
}

const handleCompleteSeason = async () => {
  if (!selectedSeason.value?.id) return

  if (!confirm('Are you sure you want to complete the season? All matches must be played.')) {
    return
  }

  try {
    await completeSeasonAction(selectedSeason.value.id)
    await fetchSeasons()
    await selectSeason(selectedSeason.value.id)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'An error occurred while completing the season.'
    alert(errorMessage)
  }
}

onMounted(async () => {
  await fetchSeasons()
  if (seasons.value.length > 0) {
    const activeSeason = seasons.value.find(s => s.status === 'active')
    if (activeSeason) {
      await handleSelectSeason(activeSeason.id)
    } else {
      const latestSeason = seasons.value[0]
      if (latestSeason) {
        await handleSelectSeason(latestSeason.id)
      }
    }
  }
})
</script>
