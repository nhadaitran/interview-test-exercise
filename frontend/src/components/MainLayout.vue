<template>
  <v-app-bar
    border="b"
    class="main-app-bar"
    color="#1e293b"
    elevation="0"
    theme="dark"
  >
    <v-app-bar-title class="font-weight-bold text-white">
      <v-icon class="mr-2" icon="mdi-devices" />
      {{ $t('common.appTitle') }}
    </v-app-bar-title>

    <v-spacer />

    <!-- Navigation links -->
    <v-btn class="text-grey-lighten-2 mr-2" to="/equipment" variant="text">
      {{ $t('equipment.title') }}
    </v-btn>

    <v-btn class="text-grey-lighten-2 mr-4" to="/reservations" variant="text">
      {{ $t('reservation.title') }}
    </v-btn>

    <!-- Language Swapper -->
    <v-menu>
      <template #activator="{ props }">
        <v-btn
          class="mr-4"
          color="white"
          v-bind="props"
          size="small"
          variant="outlined"
        >
          {{ authStore.currentLocale.toUpperCase() }}
        </v-btn>
      </template>

      <v-list theme="dark">
        <v-list-item @click="authStore.setLocale('vi')">
          <v-list-item-title>TIẾNG VIỆT</v-list-item-title>
        </v-list-item>

        <v-list-item @click="authStore.setLocale('en')">
          <v-list-item-title>ENGLISH</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>

    <!-- User Info -->
    <v-chip
      v-if="authStore.user"
      class="mr-4 font-weight-medium"
      color="grey-darken-3"
      size="small"
      variant="flat"
    >
      {{ authStore.user.name }} ({{ authStore.user.role.toUpperCase() }})
    </v-chip>

    <!-- Logout -->
    <v-btn
      class="mr-2"
      color="white"
      icon="mdi-logout"
      variant="text"
      @click="handleLogout"
    />
  </v-app-bar>
</template>

<script lang="ts" setup>
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '../stores/auth'

  const authStore = useAuthStore()
  const router = useRouter()

  // Initialize locale on setup
  authStore.initLocale()

  async function handleLogout () {
    await authStore.logout()
    router.push('/login')
  }
</script>

<style scoped>
.main-app-bar {
  border-color: #334155 !important;
}
</style>
