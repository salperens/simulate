import { ref, computed } from 'vue'
import axios from 'axios'

export function useLeague() {
  const standings = ref([])
  const currentWeek = ref(1)
  const totalWeeks = ref(6)
  const matches = ref([])
  const predictions = ref([])
  const loading = ref(false)
  const seasons = ref([])
  const loadingSeasons = ref(false)
  const selectedSeason = ref(null)
  const teams = ref([])
  const loadingTeams = ref(false)
  const loadingPredictions = ref(false)

  const fetchStandings = async (week = null, skipLoading = false) => {
    if (!skipLoading) {
    loading.value = true
    }
    try {
      const params = new URLSearchParams()
      
      if (selectedSeason.value?.id) {
        params.append('season_id', selectedSeason.value.id)
      }
      
      if (week !== null) {
        params.append('week', week.toString())
      }
      
      const queryString = params.toString()
      const url = `/api/v1/standings${queryString ? `?${queryString}` : ''}`
      
      const response = await axios.get(url)
      standings.value = response.data.data
    } catch (error) {
      console.error('Standings fetch error:', error)
    } finally {
      if (!skipLoading) {
      loading.value = false
      }
    }
  }

  const fetchSeason = async () => {
    try {
      const response = await axios.get('/api/v1/season/current')
      const seasonData = response.data.data
      selectedSeason.value = seasonData
      currentWeek.value = seasonData.current_week || 1
      totalWeeks.value = seasonData.total_weeks || 6
    } catch (error) {
      console.error('Season fetch error:', error)
    }
  }

  const fetchSeasons = async () => {
    loadingSeasons.value = true
    try {
      const response = await axios.get('/api/v1/seasons')
      // Laravel Resource collection returns data in 'data' key
      seasons.value = response.data?.data || []
      console.log('Fetched seasons:', seasons.value)
    } catch (error) {
      console.error('Seasons fetch error:', error)
      console.error('Error response:', error.response?.data)
      seasons.value = []
      throw error
    } finally {
      loadingSeasons.value = false
    }
  }

  const selectSeason = async (seasonId) => {
    try {
      const response = await axios.get(`/api/v1/seasons/${seasonId}`)
      const seasonData = response.data.data
      selectedSeason.value = seasonData
      currentWeek.value = seasonData.current_week || 1
      totalWeeks.value = seasonData.total_weeks || 6
    } catch (error) {
      console.error('Select season error:', error)
      throw error
    }
  }

  const fetchWeekMatches = async (week, skipLoading = false) => {
    if (!skipLoading) {
    loading.value = true
    }
    try {
      const url = selectedSeason.value?.id
        ? `/api/v1/fixtures/week/${week}?season_id=${selectedSeason.value.id}`
        : `/api/v1/fixtures/week/${week}`
      const response = await axios.get(url)
      matches.value = response.data.data
    } catch (error) {
      console.error('Matches fetch error:', error)
    } finally {
      if (!skipLoading) {
      loading.value = false
      }
    }
  }

  const fetchPredictions = async (week, skipLoading = false) => {
    loadingPredictions.value = true
    try {
      const url = selectedSeason.value?.id
        ? `/api/v1/predictions/week/${week}?season_id=${selectedSeason.value.id}`
        : `/api/v1/predictions/week/${week}`
      const response = await axios.get(url)
      predictions.value = response.data?.data?.predictions || response.data?.predictions || []
      console.log('Fetched predictions:', predictions.value)
      console.log('Full response:', response.data)
    } catch (error) {
      // Predictions might not be available for this week
      if (error.response?.status === 400) {
        console.log('Predictions not available for this week:', error.response.data)
        predictions.value = []
      } else {
        console.error('Predictions fetch error:', error)
        console.error('Error response:', error.response?.data)
        predictions.value = []
      }
    } finally {
      loadingPredictions.value = false
    }
  }

  const playWeek = async (week) => {
    try {
      const url = selectedSeason.value?.id
        ? `/api/v1/league/week/${week}/play?season_id=${selectedSeason.value.id}`
        : `/api/v1/league/week/${week}/play`
      await axios.post(url)
      
      if (selectedSeason.value?.id) {
        const response = await axios.get(`/api/v1/seasons/${selectedSeason.value.id}`)
        const seasonData = response.data.data
        selectedSeason.value = seasonData
        totalWeeks.value = seasonData.total_weeks || 6
      } else {
        await fetchSeason()
      }
    } catch (error) {
      console.error('Play week error:', error)
      throw error
    }
  }

  const playAll = async () => {
    try {
      const url = selectedSeason.value?.id
        ? `/api/v1/league/play-all?season_id=${selectedSeason.value.id}`
        : '/api/v1/league/play-all'
      await axios.post(url)
      
      if (selectedSeason.value?.id) {
        await selectSeason(selectedSeason.value.id)
      } else {
        await fetchSeason()
      }
    } catch (error) {
      console.error('Play all error:', error)
      throw error
    }
  }

  const fetchTeams = async () => {
    loadingTeams.value = true
    try {
      const response = await axios.get('/api/v1/teams')
      teams.value = response.data.data || []
    } catch (error) {
      console.error('Teams fetch error:', error)
    } finally {
      loadingTeams.value = false
    }
  }

  const createSeason = async (data) => {
    loading.value = true
    try {
      const response = await axios.post('/api/v1/seasons', data)
      const seasonData = response.data.data
      
      await fetchSeasons()
      await selectSeason(seasonData.id)
      
      return seasonData
    } catch (error) {
      console.error('Create season error:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  const startSeason = async (seasonId) => {
    loading.value = true
    try {
      const response = await axios.post(`/api/v1/seasons/${seasonId}/start`)
      const seasonData = response.data.data
      
      await fetchSeasons()
      await selectSeason(seasonId)
      
      return seasonData
    } catch (error) {
      console.error('Start season error:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  const completeSeason = async (seasonId) => {
    loading.value = true
    try {
      const response = await axios.post(`/api/v1/seasons/${seasonId}/complete`)
      const seasonData = response.data.data
      
      await fetchSeasons()
      await selectSeason(seasonId)
      
      return seasonData
    } catch (error) {
      console.error('Complete season error:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  const updateFixture = async (fixtureId, homeScore, awayScore) => {
    loading.value = true
    try {
      const response = await axios.put(`/api/v1/fixtures/${fixtureId}`, {
        home_score: homeScore,
        away_score: awayScore,
      })
      return response.data.data
    } catch (error) {
      console.error('Update fixture error:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  const hasActiveSeason = computed(() => {
    return seasons.value.some(s => s.status === 'active')
  })

  return {
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
    playWeek,
    playAll,
    fetchTeams,
    createSeason,
    startSeason,
    completeSeason,
    updateFixture,
  }
}

