<template>
  <MainLayout />

  <v-container class="mt-4" fluid>
    <!-- Page Title -->
    <div class="d-flex justify-space-between align-center mb-4">
      <h1 class="text-h5 font-weight-bold text-white">
        {{ $t('reservation.title') }}
      </h1>
    </div>

    <!-- Filters -->
    <v-card border class="mb-4 pa-4 rounded-lg" elevation="0" theme="dark">
      <v-row class="align-center">
        <!-- Status Filter -->
        <v-col cols="12" sm="4">
          <v-select
            v-model="filters.status"
            clearable
            density="compact"
            hide-details
            :items="statusItems"
            :label="$t('common.status')"
            variant="outlined"
            @update:model-value="fetchReservations"
          />
        </v-col>

        <!-- Equipment Select Filter -->
        <v-col cols="12" sm="4">
          <v-select
            v-model="filters.equipment_id"
            clearable
            density="compact"
            hide-details
            item-title="name"
            item-value="id"
            :items="equipmentFilterList"
            :label="$t('reservation.equipment')"
            variant="outlined"
            @update:model-value="fetchReservations"
          />
        </v-col>

        <!-- User Filter (Admin only) -->
        <v-col v-if="authStore.isAdmin" cols="12" sm="4">
          <v-select
            v-model="filters.user_id"
            clearable
            density="compact"
            hide-details
            item-title="name"
            item-value="id"
            :items="usersList"
            :label="$t('reservation.user')"
            variant="outlined"
            @update:model-value="fetchReservations"
          />
        </v-col>
      </v-row>
    </v-card>

    <!-- Table -->
    <v-card border class="rounded-lg" elevation="0" theme="dark">
      <v-progress-linear
        v-if="loading"
        color="white"
        indeterminate
      />

      <v-table>
        <thead>
          <tr class="bg-grey-lighten-4">
            <th class="font-weight-bold">{{ $t('reservation.equipment') }}</th>
            <th class="font-weight-bold">{{ $t('reservation.user') }}</th>
            <th class="font-weight-bold">{{ $t('reservation.startDate') }}</th>
            <th class="font-weight-bold">{{ $t('reservation.endDate') }}</th>
            <th class="font-weight-bold">{{ $t('common.status') }}</th>
            <th class="font-weight-bold text-center">{{ $t('common.actions') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="reservations.length === 0 && !loading">
            <td class="text-center py-6 text-grey" colspan="6">
              {{ $t('common.noData') }}
            </td>
          </tr>

          <tr v-for="item in reservations" :key="item.id" class="hover-row">
            <td class="font-weight-medium">
              <router-link class="text-indigo-darken-2 font-weight-bold text-decoration-none" :to="`/reservations/${item.id}`">
                {{ item.equipment ? item.equipment.name : '—' }}
              </router-link>
            </td>

            <td>{{ item.user ? item.user.name : '—' }}</td>
            <td>{{ formatDate(item.start_date) }}</td>
            <td>{{ formatDate(item.end_date) }}</td>

            <td>
              <v-chip class="font-weight-bold" :color="getStatusColor(item.status)" size="small" uppercase>
                {{ $t(`reservation.status.${item.status}`) }}
              </v-chip>
            </td>

            <td class="text-center">
              <!-- Admin approving/rejecting/cancelling -->
              <template v-if="authStore.isAdmin">
                <v-btn
                  v-if="item.status === 'pending'"
                  class="mr-2"
                  color="success"
                  size="small"
                  variant="flat"
                  @click="updateStatus(item.id, 'approved')"
                >
                  {{ $t('reservation.actions.approve') }}
                </v-btn>

                <v-btn
                  v-if="item.status === 'pending'"
                  class="mr-2"
                  color="error"
                  size="small"
                  variant="flat"
                  @click="updateStatus(item.id, 'rejected')"
                >
                  {{ $t('reservation.actions.reject') }}
                </v-btn>

                <v-btn
                  v-if="item.status === 'approved'"
                  class="mr-2"
                  color="grey-darken-2"
                  size="small"
                  variant="outlined"
                  @click="updateStatus(item.id, 'cancelled')"
                >
                  {{ $t('reservation.actions.cancel') }}
                </v-btn>
              </template>

              <!-- Regular user edit/cancel pending -->
              <template v-else>
                <v-btn
                  v-if="item.status === 'pending'"
                  color="blue-darken-2"
                  icon="mdi-pencil"
                  size="small"
                  variant="text"
                  @click="openEditDialog(item)"
                />

                <v-btn
                  v-if="item.status === 'pending'"
                  class="ml-2"
                  color="grey"
                  size="small"
                  variant="outlined"
                  @click="updateStatus(item.id, 'cancelled')"
                >
                  {{ $t('reservation.actions.cancel') }}
                </v-btn>
              </template>
            </td>
          </tr>
        </tbody>
      </v-table>

      <!-- Pagination Footer -->
      <v-divider />

      <div class="d-flex flex-wrap justify-space-between align-center px-6 py-4">
        <!-- Per Page selector and Page Info -->
        <div class="d-flex align-center flex-wrap" style="gap: 16px;">
          <div class="d-flex align-center" style="max-width: 180px;">
            <span class="text-caption text-grey-lighten-1 mr-2">{{ $t('common.perPage') }}</span>

            <v-select
              v-model="pagination.per_page"
              density="compact"
              hide-details
              :items="[5, 10, 15, 25, 50]"
              variant="outlined"
              @update:model-value="changePerPage"
            />
          </div>

          <span class="text-caption text-grey-lighten-1">
            {{ $t('common.showingInfo', { from: fromItem, to: toItem, total: pagination.total }) }}
          </span>
        </div>

        <v-pagination
          v-model="pagination.page"
          active-color="indigo-darken-2"
          :length="pagination.last_page"
          :total-visible="7"
          @update:model-value="fetchReservations"
        />
      </div>
    </v-card>

    <!-- User Edit Dialog Modal -->
    <v-dialog v-model="editDialog" max-width="500px" persistent>
      <v-card class="rounded-lg">
        <v-card-title class="bg-indigo-darken-2 text-white px-6 py-4">
          <span class="text-h6 font-weight-bold">
            {{ $t('reservation.editTitle') }}
          </span>
        </v-card-title>

        <v-form ref="editFormRef" v-model="editFormValid" @submit.prevent="submitEdit">
          <v-card-text class="pa-6">
            <v-alert
              v-if="editError"
              class="mb-4"
              density="compact"
              type="error"
              variant="tonal"
            >
              {{ editError }}
            </v-alert>

            <v-text-field
              v-model="editForm.start_date"
              color="indigo-darken-2"
              :label="$t('reservation.startDate') + ' *'"
              :rules="[v => !!v || $t('reservation.rules.requiredStartDate')]"
              type="date"
              variant="outlined"
            />

            <v-text-field
              v-model="editForm.end_date"
              class="mt-2"
              color="indigo-darken-2"
              :label="$t('reservation.endDate') + ' *'"
              :rules="[v => !!v || $t('reservation.rules.requiredEndDate')]"
              type="date"
              variant="outlined"
            />
          </v-card-text>

          <v-card-actions class="px-6 pb-6">
            <v-spacer />

            <v-btn color="grey-darken-1" variant="outlined" @click="closeEditDialog">
              {{ $t('common.cancel') }}
            </v-btn>

            <v-btn
              class="ml-2"
              color="indigo-darken-2"
              :disabled="!editFormValid"
              type="submit"
              variant="flat"
            >
              {{ $t('common.save') }}
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script lang="ts" setup>
  import { computed, onMounted, reactive, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import MainLayout from '../components/MainLayout.vue'
  import apiClient from '../plugins/axios'
  import { useAuthStore } from '../stores/auth'

  const authStore = useAuthStore()
  const { t } = useI18n()

  // State
  const reservations = ref<any[]>([])
  const equipmentFilterList = ref<any[]>([])
  const usersList = ref<any[]>([])
  const loading = ref(false)

  const pagination = reactive({
    page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  const fromItem = computed(() => {
    if (pagination.total === 0) return 0
    return (pagination.page - 1) * pagination.per_page + 1
  })

  const toItem = computed(() => {
    return Math.min(pagination.page * pagination.per_page, pagination.total)
  })

  const filters = reactive({
    status: null as string | null,
    equipment_id: null as number | null,
    user_id: null as number | null,
  })

  // Dynamic translated select items using computed
  const statusItems = computed(() => [
    { title: t('reservation.status.pending'), value: 'pending' },
    { title: t('reservation.status.approved'), value: 'approved' },
    { title: t('reservation.status.rejected'), value: 'rejected' },
    { title: t('reservation.status.cancelled'), value: 'cancelled' },
  ])

  // Modal/Dialog controllers
  const editDialog = ref(false)
  const editFormValid = ref(false)
  const editFormRef = ref<any>(null)
  const editError = ref('')
  const selectedReservation = ref<any>(null)

  const editForm = reactive({
    id: null as number | null,
    start_date: '',
    end_date: '',
  })

  // Fetch API
  async function fetchReservations () {
    loading.value = true
    try {
      const params = {
        status: filters.status || undefined,
        equipment_id: filters.equipment_id || undefined,
        user_id: filters.user_id || undefined,
        page: pagination.page,
        per_page: pagination.per_page,
      }

      const response = await apiClient.get('/reservations', { params })
      const paginationData = response.data
      reservations.value = paginationData.data
      if (paginationData.pagination) {
        pagination.page = paginationData.pagination.current_page
        pagination.last_page = paginationData.pagination.last_page
        pagination.per_page = paginationData.pagination.per_page
        pagination.total = paginationData.pagination.total
      }
    } catch (error) {
      console.error('Failed to fetch reservations:', error)
    } finally {
      loading.value = false
    }
  }

  // Fetch all equipment for filter select options
  async function fetchEquipmentOptions () {
    try {
      const response = await apiClient.get('/equipment', { params: { perPage: 100 } })
      equipmentFilterList.value = response.data.data || response.data
    } catch (error) {
      console.error('Failed to fetch equipment filter options:', error)
    }
  }

  // Fetch user options for admin select
  async function fetchUsersOptions () {
    if (!authStore.isAdmin) return
    try {
      const response = await apiClient.get('/users')
      usersList.value = response.data.data || response.data
    } catch (error) {
      console.error('Failed to fetch user list:', error)
    }
  }

  // Change items per page size
  function changePerPage (size: number) {
    pagination.per_page = size
    pagination.page = 1
    fetchReservations()
  }

  // Quick status updater (admin approval/rejection/cancellation)
  async function updateStatus (id: number, status: 'approved' | 'rejected' | 'cancelled') {
    try {
      await apiClient.put(`/reservations/${id}`, { status })
      fetchReservations()
    } catch (error) {
      console.error('Failed to update status:', error)
    }
  }

  // Edit forms modal
  function openEditDialog (item: any) {
    selectedReservation.value = item
    editForm.id = item.id
    editForm.start_date = item.start_date.slice(0, 10)
    editForm.end_date = item.end_date.slice(0, 10)
    editError.value = ''
    editDialog.value = true
  }

  function closeEditDialog () {
    editDialog.value = false
    if (editFormRef.value) editFormRef.value.resetValidation()
  }

  async function submitEdit () {
    if (!editFormValid.value || !editForm.id) return
    editError.value = ''
    try {
      await apiClient.put(`/reservations/${editForm.id}`, editForm)
      fetchReservations()
      closeEditDialog()
    } catch (error: any) {
      editError.value = error.response && error.response.data && error.response.data.message ? error.response.data.message : t('reservation.errors.editFailed')
    }
  }

  // Helpers
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
    fetchReservations()
    fetchEquipmentOptions()
    fetchUsersOptions()
  })
</script>

<style scoped>
.hover-row:hover {
  background-color: #334155 !important;
  transition: background-color 0.2s ease;
}
.text-grey-darken-4 {
  color: #f1f5f9 !important;
}
.bg-grey-lighten-4 {
  background-color: #1e293b !important;
}
</style>
