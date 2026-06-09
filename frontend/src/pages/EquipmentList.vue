<template>
  <MainLayout />

  <v-container class="mt-4" fluid>
    <!-- Page Header & Create Button -->
    <div class="d-flex justify-space-between align-center mb-4">
      <h1 class="text-h5 font-weight-bold text-grey-darken-4">
        {{ $t('equipment.title') }}
      </h1>

      <v-btn
        v-if="authStore.isAdmin"
        color="grey-darken-3"
        prepend-icon="mdi-plus"
        size="small"
        variant="flat"
        @click="openCreateDialog"
      >
        {{ $t('common.create') }}
      </v-btn>
    </div>

    <!-- Filters Section -->
    <v-card border class="mb-4 pa-4 rounded-lg" elevation="0" theme="dark">
      <v-row class="align-center">
        <!-- Search -->
        <v-col cols="12" sm="4">
          <v-text-field
            v-model="filters.search"
            clearable
            density="compact"
            hide-details
            :label="$t('common.search')"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            @click:clear="triggerSearch"
            @keyup.enter="triggerSearch"
          />
        </v-col>

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
            @update:model-value="triggerSearch"
          />
        </v-col>

        <!-- Category Search/Filter -->
        <v-col cols="12" sm="4">
          <v-text-field
            v-model="filters.category"
            clearable
            density="compact"
            hide-details
            :label="$t('common.category')"
            prepend-inner-icon="mdi-tag-outline"
            variant="outlined"
            @click:clear="triggerSearch"
            @keyup.enter="triggerSearch"
          />
        </v-col>
      </v-row>
    </v-card>

    <!-- Content Table -->
    <v-card border class="rounded-lg" elevation="0" theme="dark">
      <v-progress-linear
        v-if="loading"
        color="white"
        indeterminate
      />

      <v-table>
        <thead>
          <tr class="bg-grey-lighten-4">
            <th class="font-weight-bold">{{ $t('common.name') }}</th>
            <th class="font-weight-bold">{{ $t('equipment.serialNumber') }}</th>
            <th class="font-weight-bold">{{ $t('common.category') }}</th>
            <th class="font-weight-bold">{{ $t('common.status') }}</th>
            <th class="font-weight-bold">{{ $t('equipment.assignedTo') }}</th>
            <th class="font-weight-bold">{{ $t('equipment.dueDate') }}</th>
            <th class="font-weight-bold text-center">{{ $t('common.actions') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="equipmentList.length === 0 && !loading">
            <td class="text-center py-6 text-grey" colspan="7">
              {{ $t('common.noData') }}
            </td>
          </tr>

          <tr v-for="item in equipmentList" :key="item.id" class="hover-row">
            <td class="font-weight-medium">
              <router-link class="text-grey-darken-4 font-weight-bold text-decoration-none" :to="`/equipment/${item.id}`">
                {{ item.name }}
              </router-link>
            </td>

            <td><code>{{ item.serial_number }}</code></td>
            <td>{{ item.category }}</td>

            <td>
              <v-chip class="font-weight-bold" :color="getStatusColor(item.status)" size="small" uppercase>
                {{ $t(`equipment.status.${item.status}`) }}
              </v-chip>
            </td>

            <td>
              {{ item.assignee ? item.assignee.name : '—' }}
            </td>

            <td>
              {{ item.due_date ? formatDate(item.due_date) : '—' }}
            </td>

            <td class="text-center">
              <v-btn
                v-if="!authStore.isAdmin && item.status === 'available'"
                class="mr-2"
                color="grey-darken-3"
                prepend-icon="mdi-calendar-plus"
                size="small"
                variant="flat"
                @click="openReserveModal(item)"
              >
                {{ $t('reservation.createTitle') }}
              </v-btn>

              <v-btn
                v-if="authStore.isAdmin"
                color="blue-darken-2"
                icon="mdi-pencil"
                size="small"
                variant="text"
                @click="openEditDialog(item)"
              />

              <v-btn
                v-if="authStore.isAdmin"
                color="red-darken-2"
                icon="mdi-delete"
                size="small"
                variant="text"
                @click="confirmDelete(item)"
              />
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
          active-color="grey-darken-3"
          :length="pagination.last_page"
          :total-visible="5"
          @update:model-value="fetchEquipment"
        />
      </div>
    </v-card>

    <!-- Create/Edit Equipment Dialog -->
    <v-dialog v-model="dialog" max-width="600px" persistent>
      <v-card class="rounded-lg">
        <v-card-title class="bg-grey-darken-3 text-white px-6 py-4">
          <span class="text-h6 font-weight-bold">
            {{ isEditMode ? $t('equipment.editTitle') : $t('equipment.createTitle') }}
          </span>
        </v-card-title>

        <v-form ref="form" v-model="formValid" @submit.prevent="saveEquipment">
          <v-card-text class="pa-6">
            <v-text-field
              v-model="equipmentForm.name"
              color="grey-darken-3"
              :label="$t('common.name') + ' *'"
              :rules="[v => !!v || $t('equipment.rules.requiredName')]"
              variant="outlined"
            />

            <v-text-field
              v-model="equipmentForm.serial_number"
              class="mt-2"
              color="grey-darken-3"
              :label="$t('equipment.serialNumber') + ' *'"
              :rules="[v => !!v || $t('equipment.rules.requiredSerial')]"
              variant="outlined"
            />

            <v-text-field
              v-model="equipmentForm.category"
              class="mt-2"
              color="grey-darken-3"
              :label="$t('common.category') + ' *'"
              :rules="[v => !!v || $t('equipment.rules.requiredCategory')]"
              variant="outlined"
            />

            <v-select
              v-model="equipmentForm.status"
              class="mt-2"
              color="grey-darken-3"
              :items="formStatusItems"
              :label="$t('common.status') + ' *'"
              variant="outlined"
            />

            <!-- Assigned to -->
            <v-select
              v-model="equipmentForm.assigned_to"
              class="mt-2"
              clearable
              color="grey-darken-3"
              item-title="name"
              item-value="id"
              :items="usersList"
              :label="$t('equipment.assignedTo')"
              variant="outlined"
            />

            <v-text-field
              v-model="equipmentForm.due_date"
              class="mt-2"
              color="grey-darken-3"
              :label="$t('equipment.dueDate')"
              type="date"
              variant="outlined"
            />
          </v-card-text>

          <v-card-actions class="px-6 pb-6">
            <v-spacer />

            <v-btn color="grey-darken-1" variant="outlined" @click="closeDialog">
              {{ $t('common.cancel') }}
            </v-btn>

            <v-btn
              class="ml-2"
              color="grey-darken-3"
              :disabled="!formValid"
              type="submit"
              variant="flat"
            >
              {{ $t('common.save') }}
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>

    <!-- Reservation Dialog Modal -->
    <v-dialog v-model="reserveDialog" max-width="500px" persistent>
      <v-card class="rounded-lg">
        <v-card-title class="bg-grey-darken-3 text-white px-6 py-4">
          <span class="text-h6 font-weight-bold">
            {{ $t('reservation.createTitle') }}
          </span>
        </v-card-title>

        <v-form ref="reserveFormRef" v-model="reserveFormValid" @submit.prevent="submitReservation">
          <v-card-text class="pa-6">
            <v-text-field
              disabled
              :label="$t('reservation.equipment')"
              :model-value="selectedEquipment?.name"
              variant="outlined"
            />

            <v-alert
              v-if="reserveError"
              class="mb-4"
              density="compact"
              type="error"
              variant="tonal"
            >
              {{ reserveError }}
            </v-alert>

            <!-- Admin can create reservation on behalf of another user -->
            <v-select
              v-if="authStore.isAdmin"
              v-model="reservationForm.user_id"
              class="mt-2"
              color="grey-darken-3"
              item-title="name"
              item-value="id"
              :items="usersList"
              :label="$t('reservation.user') + ' *'"
              :rules="[v => !!v || $t('reservation.rules.requiredUser')]"
              variant="outlined"
            />

            <v-text-field
              v-model="reservationForm.start_date"
              class="mt-2"
              color="grey-darken-3"
              :label="$t('reservation.startDate') + ' *'"
              :rules="[v => !!v || $t('reservation.rules.requiredStartDate')]"
              type="date"
              variant="outlined"
            />

            <v-text-field
              v-model="reservationForm.end_date"
              class="mt-2"
              color="grey-darken-3"
              :label="$t('reservation.endDate') + ' *'"
              :rules="[v => !!v || $t('reservation.rules.requiredEndDate')]"
              type="date"
              variant="outlined"
            />
          </v-card-text>

          <v-card-actions class="px-6 pb-6">
            <v-spacer />

            <v-btn color="grey-darken-1" variant="outlined" @click="closeReserveDialog">
              {{ $t('common.cancel') }}
            </v-btn>

            <v-btn
              class="ml-2"
              color="grey-darken-3"
              :disabled="!reserveFormValid"
              type="submit"
              variant="flat"
            >
              {{ $t('common.submit') }}
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation -->
    <v-dialog v-model="deleteDialog" max-width="400px">
      <v-card class="rounded-lg pa-4">
        <v-card-title class="text-h6 font-weight-bold text-red-darken-4">
          {{ $t('equipment.deleteConfirmTitle') }}
        </v-card-title>

        <v-card-text>
          {{ $t('equipment.deleteConfirmText', { name: selectedEquipment?.name }) }}
        </v-card-text>

        <v-card-actions>
          <v-spacer />
          <v-btn color="grey-darken-1" variant="outlined" @click="deleteDialog = false">{{ $t('common.cancel') }}</v-btn>
          <v-btn color="red-darken-3" variant="flat" @click="deleteEquipment">{{ $t('common.delete') }}</v-btn>
        </v-card-actions>
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

  // Lists
  const equipmentList = ref<any[]>([])
  const usersList = ref<any[]>([])
  const loading = ref(false)

  // Pagination state
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

  // Query filters
  const filters = reactive({
    search: '',
    status: null as string | null,
    category: '',
  })

  // Dynamic translated select items using computed
  const statusItems = computed(() => [
    { title: t('equipment.status.available'), value: 'available' },
    { title: t('equipment.status.reserved'), value: 'reserved' },
    { title: t('equipment.status.maintenance'), value: 'maintenance' },
  ])

  const formStatusItems = computed(() => [
    { title: t('equipment.status.available'), value: 'available' },
    { title: t('equipment.status.reserved'), value: 'reserved' },
    { title: t('equipment.status.maintenance'), value: 'maintenance' },
  ])

  // Modal & dialogs controls
  const dialog = ref(false)
  const isEditMode = ref(false)
  const formValid = ref(false)
  const form = ref<any>(null)

  const reserveDialog = ref(false)
  const reserveFormValid = ref(false)
  const reserveFormRef = ref<any>(null)
  const reserveError = ref('')

  const deleteDialog = ref(false)
  const selectedEquipment = ref<any>(null)

  // Forms schemas
  const equipmentForm = reactive({
    id: null as number | null,
    name: '',
    serial_number: '',
    category: '',
    status: 'available',
    assigned_to: null as number | null,
    due_date: '',
  })

  const reservationForm = reactive({
    equipment_id: null as number | null,
    user_id: null as number | null,
    start_date: '',
    end_date: '',
  })

  // Fetch equipment list
  async function fetchEquipment () {
    loading.value = true
    try {
      const params = {
        search: filters.search || undefined,
        status: filters.status || undefined,
        category: filters.category || undefined,
        page: pagination.page,
        per_page: pagination.per_page,
      }

      const response = await apiClient.get('/equipment', { params })
      const paginationData = response.data
      equipmentList.value = paginationData.data
      if (paginationData.pagination) {
        pagination.page = paginationData.pagination.current_page
        pagination.last_page = paginationData.pagination.last_page
        pagination.per_page = paginationData.pagination.per_page
        pagination.total = paginationData.pagination.total
      }
    } catch (error) {
      console.error('Failed to fetch equipment:', error)
    } finally {
      loading.value = false
    }
  }

  // Fetch users for admin select options
  async function fetchUsers () {
    if (!authStore.isAdmin) return
    try {
      const response = await apiClient.get('/users')
      usersList.value = response.data.data || response.data
    } catch (error) {
      console.error('Failed to fetch users list:', error)
    }
  }

  // Trigger Search execution
  function triggerSearch () {
    pagination.page = 1
    fetchEquipment()
  }

  // Change items per page size
  function changePerPage (size: number) {
    pagination.per_page = size
    pagination.page = 1
    fetchEquipment()
  }

  // Form actions
  function openCreateDialog () {
    isEditMode.value = false
    equipmentForm.id = null
    equipmentForm.name = ''
    equipmentForm.serial_number = ''
    equipmentForm.category = ''
    equipmentForm.status = 'available'
    equipmentForm.assigned_to = null
    equipmentForm.due_date = ''
    dialog.value = true
  }

  function openEditDialog (item: any) {
    isEditMode.value = true
    equipmentForm.id = item.id
    equipmentForm.name = item.name
    equipmentForm.serial_number = item.serial_number
    equipmentForm.category = item.category
    equipmentForm.status = item.status
    equipmentForm.assigned_to = item.assigned_to
    equipmentForm.due_date = item.due_date ? item.due_date.slice(0, 10) : ''
    dialog.value = true
  }

  function closeDialog () {
    dialog.value = false
    if (form.value) form.value.resetValidation()
  }

  async function saveEquipment () {
    if (!formValid.value) return

    const payload = { ...equipmentForm }
    if (payload.due_date === '') payload.due_date = null as any

    try {
      await (isEditMode.value && equipmentForm.id ? apiClient.put(`/equipment/${equipmentForm.id}`, payload) : apiClient.post('/equipment', payload))
      fetchEquipment()
      closeDialog()
    } catch (error) {
      console.error('Failed to save equipment:', error)
    }
  }

  function confirmDelete (item: any) {
    selectedEquipment.value = item
    deleteDialog.value = true
  }

  async function deleteEquipment () {
    if (!selectedEquipment.value) return
    try {
      await apiClient.delete(`/equipment/${selectedEquipment.value.id}`)
      fetchEquipment()
      deleteDialog.value = false
    } catch (error) {
      console.error('Failed to delete equipment:', error)
    }
  }

  // Reservation Handlers
  function openReserveModal (item: any) {
    selectedEquipment.value = item
    reservationForm.equipment_id = item.id
    reservationForm.user_id = authStore.user?.id || null
    reservationForm.start_date = new Date().toISOString().slice(0, 10)
    reservationForm.end_date = ''
    reserveError.value = ''
    reserveDialog.value = true
  }

  function closeReserveDialog () {
    reserveDialog.value = false
    if (reserveFormRef.value) reserveFormRef.value.resetValidation()
  }

  async function submitReservation () {
    if (!reserveFormValid.value) return
    reserveError.value = ''
    try {
      await apiClient.post('/reservations', reservationForm)
      fetchEquipment()
      closeReserveDialog()
    } catch (error: any) {
      reserveError.value = error.response && error.response.data && error.response.data.message ? error.response.data.message : t('reservation.errors.createFailed')
    }
  }

  // Helpers
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

  function formatDate (dateString: string) {
    return new Date(dateString).toLocaleDateString('vi-VN')
  }

  onMounted(() => {
    fetchEquipment()
    fetchUsers()
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
