<template>
  <div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
      <TableCellsIcon class="w-6 h-6 text-blue-500" />
      League Table
    </h2>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b">
            <th class="text-left py-2 px-2">Pos</th>
            <th class="text-left py-2 px-2">Team</th>
            <th class="text-center py-2 px-2">P</th>
            <th class="text-center py-2 px-2">W</th>
            <th class="text-center py-2 px-2">D</th>
            <th class="text-center py-2 px-2">L</th>
            <th class="text-center py-2 px-2">GF</th>
            <th class="text-center py-2 px-2">GA</th>
            <th class="text-center py-2 px-2">GD</th>
            <th class="text-center py-2 px-2 font-bold">Pts</th>
          </tr>
        </thead>
        <tbody>
          <template v-if="standings.length > 0">
            <tr
              v-for="(team, index) in standings"
              :key="team.id"
              class="border-b hover:bg-gray-50"
            >
              <td class="py-2 px-2">{{ index + 1 }}</td>
              <td class="py-2 px-2 font-semibold">{{ team.name }}</td>
              <td class="text-center py-2 px-2">{{ team.played }}</td>
              <td class="text-center py-2 px-2">{{ team.won }}</td>
              <td class="text-center py-2 px-2">{{ team.drawn }}</td>
              <td class="text-center py-2 px-2">{{ team.lost }}</td>
              <td class="text-center py-2 px-2">{{ team.goals_for }}</td>
              <td class="text-center py-2 px-2">{{ team.goals_against }}</td>
              <td class="text-center py-2 px-2">{{ team.goal_difference > 0 ? '+' : '' }}{{ team.goal_difference }}</td>
              <td class="text-center py-2 px-2 font-bold">{{ team.points }}</td>
            </tr>
          </template>
          <tr v-else>
            <td colspan="10" class="text-center py-8 text-gray-500">
              Loading league table...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { TableCellsIcon } from '@heroicons/vue/24/solid'

defineProps({
  standings: {
    type: Array,
    required: true,
  },
})
</script>
