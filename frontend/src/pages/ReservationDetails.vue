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

    <div v-else-if="!reservation" class="text-center my-12">
      <v-alert class="mx-auto" max-width="600" type="warning" variant="tonal">
        {{ $t('reservation.notFound') }}
      </v-alert>
    </div>

    <v-row v-else class="justify-center">
      <v-col cols="12" md="8">
        <v-card class="elevation-4 rounded-lg overflow-hidden">
          <v-card-title class="bg-teal-darken-3 text-white px-6 py-4">
            <div class="d-flex justify-space-between align-center">
              <span class="font-weight-bold text-h6">{{ $t('reservation.detailTitle') }} #{{ reservation.id }}</span>

              <v-chip class="font-weight-bold" :color="getStatusColor(reservation.status)" size="small">
                {{ $t(`reservation.status.${reservation.status}`) }}
              </v-chip>
            </div>
          </v-card-title>

          <v-card-text class="pa-6">
            <v-row>
              <!-- Equipment Info -->
              <v-col cols="12" sm="6">
                <h3 class="text-subtitle-1 font-weight-bold text-teal-darken-4 mb-3">
                  <v-icon class="mr-2" icon="mdi-devices" />{{ $t('reservation.equipmentInfo') }}
                </h3>

                <v-list density="compact" disabled>
                  <v-list-item>
                    <v-list-item-title class="text-grey-darken-1">{{ $t('common.name') }}</v-list-item-title>

                    <v-list-item-subtitle class="text-body-1 font-weight-medium text-black mt-1">
                      {{ reservation.equipment ? reservation.equipment.name : '—' }}
                    </v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item class="mt-2">
                    <v-list-item-title class="text-grey-darken-1">{{ $t('equipment.serialNumber') }}</v-list-item-title>

                    <v-list-item-subtitle class="text-body-1 font-weight-medium text-black mt-1">
                      {{ reservation.equipment ? reservation.equipment.serial_number : '—' }}
                    </v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item class="mt-2">
                    <v-list-item-title class="text-grey-darken-1">{{ $t('common.category') }}</v-list-item-title>

                    <v-list-item-subtitle class="text-body-1 font-weight-medium text-black mt-1">
                      {{ reservation.equipment ? reservation.equipment.category : '—' }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-col>

              <!-- User Info -->
              <v-col cols="12" sm="6">
                <h3 class="text-subtitle-1 font-weight-bold text-teal-darken-4 mb-3">
                  <v-icon class="mr-2" icon="mdi-account-outline" />{{ $t('reservation.userInfo') }}
                </h3>

                <v-list density="compact" disabled>
                  <v-list-item>
                    <v-list-item-title class="text-grey-darken-1">{{ $t('reservation.fullName') }}</v-list-item-title>

                    <v-list-item-subtitle class="text-body-1 font-weight-medium text-black mt-1">
                      {{ reservation.user ? reservation.user.name : '—' }}
                    </v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item class="mt-2">
                    <v-list-item-title class="text-grey-darken-1">{{ $t('common.email') }}</v-list-item-title>

                    <v-list-item-subtitle class="text-body-1 font-weight-medium text-black mt-1">
                      {{ reservation.user ? reservation.user.email : '—' }}
                    </v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item class="mt-2">
                    <v-list-item-title class="text-grey-darken-1">{{ $t('auth.role') }}</v-list-item-title>

                    <v-list-item-subtitle class="text-body-1 font-weight-medium text-black mt-1">
                      {{ reservation.user ? reservation.user.role.toUpperCase() : '—' }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-col>
            </v-row>

            <v-divider class="my-6" />

            <!-- Timing Details -->
            <v-row class="bg-teal-lighten-5 rounded-lg py-4 px-2 align-center">
              <v-col class="text-center" cols="12" sm="6">
                <div class="text-subtitle-2 text-teal-darken-4 font-weight-bold">{{ $t('reservation.startDate') }}</div>

                <div class="text-h5 font-weight-black text-teal-darken-3 mt-1">
                  {{ formatDate(reservation.start_date) }}
                </div>
              </v-col>

              <v-col class="text-center border-sm-left" cols="12" sm="6">
                <div class="text-subtitle-2 text-teal-darken-4 font-weight-bold">{{ $t('reservation.endDate') }}</div>

                <div class="text-h5 font-weight-black text-teal-darken-3 mt-1">
                  {{ formatDate(reservation.end_date) }}
                </div>
              </v-col>
            </v-row>
          </v-card-text>

          <!-- Action buttons -->
          <v-divider />

          <v-card-actions class="pa-6">
            <v-spacer />

            <template v-if="authStore.isAdmin && reservation.status === 'pending'">
              <v-btn class="mr-2" color="success" variant="flat" @click="updateStatus('approved')">
                {{ $t('reservation.actions.approve') }}
              </v-btn>

              <v-btn class="mr-2" color="error" variant="flat" @click="updateStatus('rejected')">
                {{ $t('reservation.actions.reject') }}
              </v-btn>
            </template>

            <v-btn
              v-if="reservation.status === 'pending'"
              color="grey-darken-1"
              variant="outlined"
              @click="updateStatus('cancelled')"
            >
              {{ $t('reservation.actions.cancel') }}
            </v-btn>
          </v-card-actions>
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
  import { useAuthStore } from '../stores/auth'

  const route = useRoute()
  const router = useRouter()
  const authStore = useAuthStore()

  const reservation = ref<any>(null)
  const loading = ref(true)

  async function fetchDetails () {
    loading.value = true
    try {
      const id = route.params.id
      const response = await apiClient.get(`/reservations/${id}`)

      // Unified response structure (payload inside data key)
      reservation.value = response.data.data || response.data
    } catch (error) {
      console.error('Failed to load reservation details:', error)
    } finally {
      loading.value = false
    }
  }

  async function updateStatus (status: 'approved' | 'rejected' | 'cancelled') {
    if (!reservation.value) return
    try {
      await apiClient.put(`/reservations/${reservation.value.id}`, { status })
      fetchDetails()
    } catch (error) {
      console.error('Failed to update reservation status:', error)
    }
  }

  function getStatusColor (status: string) {
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

<style scoped>
.border-sm-left {
  border-left: 1px solid #b2dfdb;
}
@media (max-width: 600px) {
  .border-sm-left {
    border-left: none;
    border-top: 1px solid #b2dfdb;
    margin-top: 10px;
    padding-top: 15px;
  }
}
</style>
