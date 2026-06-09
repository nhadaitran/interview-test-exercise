<template>
  <MainLayout />

  <v-container class="mt-4" fluid>
    <!-- Back Button -->
    <v-btn
      class="mb-4"
      color="teal-darken-3"
      prepend-icon="mdi-arrow-left"
      variant="text"
      @click="router.back()"
    >
      {{ $t('common.back') }}
    </v-btn>

    <div v-if="loading" class="d-flex justify-center my-12">
      <v-progress-circular color="teal" indeterminate size="64" />
    </div>

    <div v-else-if="!equipment" class="text-center my-12">
      <v-alert class="mx-auto" max-width="600" type="warning" variant="tonal">
        {{ $t('equipment.notFound') }}
      </v-alert>
    </div>

    <v-row v-else>
      <!-- Equipment Details Panel -->
      <v-col cols="12" md="5">
        <v-card class="elevation-4 rounded-lg overflow-hidden">
          <v-card-title class="bg-teal-darken-3 text-white px-6 py-4">
            <div class="d-flex justify-space-between align-center">
              <span class="font-weight-bold text-h6">{{ equipment.name }}</span>

              <v-chip class="font-weight-bold" :color="getStatusColor(equipment.status)" size="small">
                {{ $t(`equipment.status.${equipment.status}`) }}
              </v-chip>
            </div>
          </v-card-title>

          <v-divider />

          <v-card-text class="pa-6">
            <v-list density="comfortable">
              <v-list-item>
                <template #prepend>
                  <v-icon class="mr-3" icon="mdi-barcode" />
                </template>

                <v-list-item-title class="font-weight-bold text-grey-darken-1">{{ $t('equipment.serialNumber') }}</v-list-item-title>

                <v-list-item-subtitle class="text-body-1 text-black font-weight-medium mt-1">
                  {{ equipment.serial_number }}
                </v-list-item-subtitle>
              </v-list-item>

              <v-list-item class="mt-3">
                <template #prepend>
                  <v-icon class="mr-3" icon="mdi-shape-outline" />
                </template>

                <v-list-item-title class="font-weight-bold text-grey-darken-1">{{ $t('common.category') }}</v-list-item-title>

                <v-list-item-subtitle class="text-body-1 text-black font-weight-medium mt-1">
                  {{ equipment.category }}
                </v-list-item-subtitle>
              </v-list-item>

              <v-list-item class="mt-3">
                <template #prepend>
                  <v-icon class="mr-3" icon="mdi-account-arrow-right-outline" />
                </template>

                <v-list-item-title class="font-weight-bold text-grey-darken-1">{{ $t('equipment.assignedTo') }}</v-list-item-title>

                <v-list-item-subtitle class="text-body-1 text-black font-weight-medium mt-1">
                  {{ equipment.assignee ? equipment.assignee.name : $t('equipment.unassigned') }}
                </v-list-item-subtitle>
              </v-list-item>

              <v-list-item v-if="equipment.due_date" class="mt-3">
                <template #prepend>
                  <v-icon class="mr-3" icon="mdi-calendar-clock" />
                </template>

                <v-list-item-title class="font-weight-bold text-grey-darken-1">{{ $t('equipment.dueDate') }}</v-list-item-title>

                <v-list-item-subtitle class="text-body-1 text-red-darken-4 font-weight-bold mt-1">
                  {{ formatDate(equipment.due_date) }}
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Reservations History for this Equipment -->
      <v-col cols="12" md="7">
        <v-card class="elevation-4 rounded-lg">
          <v-card-title class="bg-grey-lighten-4 px-6 py-4 font-weight-bold text-teal-darken-4">
            {{ $t('equipment.reservationHistory') }}
          </v-card-title>

          <v-divider />

          <v-table>
            <thead>
              <tr>
                <th class="font-weight-bold">{{ $t('reservation.user') }}</th>
                <th class="font-weight-bold">{{ $t('reservation.startDate') }}</th>
                <th class="font-weight-bold">{{ $t('reservation.endDate') }}</th>
                <th class="font-weight-bold">{{ $t('common.status') }}</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="!equipment.reservations || equipment.reservations.length === 0">
                <td class="text-center py-6 text-grey" colspan="4">
                  {{ $t('equipment.noReservations') }}
                </td>
              </tr>

              <tr v-for="res in equipment.reservations" :key="res.id">
                <td>{{ res.user ? res.user.name : '—' }}</td>
                <td>{{ formatDate(res.start_date) }}</td>
                <td>{{ formatDate(res.end_date) }}</td>

                <td>
                  <v-chip class="font-weight-bold" :color="getResStatusColor(res.status)" size="small">
                    {{ $t(`reservation.status.${res.status}`) }}
                  </v-chip>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts" setup>
  import { onMounted, ref } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import MainLayout from '../components/MainLayout.vue'
  import apiClient from '../plugins/axios'

  const route = useRoute()
  const router = useRouter()

  const equipment = ref<any>(null)
  const loading = ref(true)

  async function fetchDetails () {
    loading.value = true
    try {
      const id = route.params.id
      const response = await apiClient.get(`/equipment/${id}`)

      // Unified response format has payload in response.data.data
      equipment.value = response.data.data || response.data
    } catch (error) {
      console.error('Failed to load equipment details:', error)
    } finally {
      loading.value = false
    }
  }

  function getStatusColor (status: string) {
    switch (status) {
      case 'available': { return 'success'
      }
      case 'reserved': { return 'info'
      }
      case 'maintenance': { return 'warning'
      }
      default: { return 'grey'
      }
    }
  }

  function getResStatusColor (status: string) {
    switch (status) {
      case 'approved': { return 'success'
      }
      case 'pending': { return 'warning'
      }
      case 'rejected': { return 'error'
      }
      case 'cancelled': { return 'grey'
      }
      default: { return 'grey'
      }
    }
  }

  function formatDate (dateString: string) {
    return new Date(dateString).toLocaleDateString('vi-VN')
  }

  onMounted(() => {
    fetchDetails()
  })
</script>
