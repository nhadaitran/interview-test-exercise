<template>
  <v-container class="d-flex justify-center align-center login-container" fluid>
    <v-card
      border
      class="rounded-lg pa-6 login-card"
      elevation="0"
      theme="dark"
      width="400"
    >
      <v-card-item class="justify-center text-center">
        <v-avatar class="mb-4" color="grey-darken-3" size="70">
          <v-icon color="white" icon="mdi-lock-open-outline" size="40" />
        </v-avatar>

        <v-card-title class="text-h5 font-weight-bold text-white">
          {{ $t('auth.loginTitle') }}
        </v-card-title>

        <v-card-subtitle class="mt-1 text-grey-lighten-1">
          {{ $t('common.login') }}
        </v-card-subtitle>
      </v-card-item>

      <v-form ref="form" v-model="isValid" @submit.prevent="handleLogin">
        <v-card-text class="pt-4">
          <!-- Language Select -->
          <div class="d-flex justify-end mb-4">
            <v-btn-toggle
              v-model="activeLocale"
              color="white"
              density="compact"
              divided
              mandatory
              variant="outlined"
            >
              <v-btn size="small" value="vi" @click="authStore.setLocale('vi')">VI</v-btn>
              <v-btn size="small" value="en" @click="authStore.setLocale('en')">EN</v-btn>
            </v-btn-toggle>
          </div>

          <!-- Alert for Login Error -->
          <v-alert
            v-if="authStore.error"
            class="mb-4"
            closable
            density="compact"
            type="error"
            variant="tonal"
          >
            {{ authStore.error }}
          </v-alert>

          <!-- Input Fields -->
          <v-text-field
            v-model="email"
            color="white"
            density="compact"
            :label="$t('common.email')"
            :rules="[rules.emailRequired, rules.email]"
            type="email"
            variant="outlined"
          />

          <v-text-field
            v-model="password"
            class="mt-2"
            color="white"
            density="compact"
            :label="$t('common.password')"
            :rules="[rules.passwordRequired]"
            type="password"
            variant="outlined"
          />
        </v-card-text>

        <v-card-actions class="pb-4">
          <v-btn
            block
            class="text-black font-weight-bold"
            color="white"
            :disabled="!isValid"
            :loading="authStore.loading"
            type="submit"
            variant="flat"
          >
            {{ $t('common.login') }}
          </v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </v-container>
</template>

<script lang="ts" setup>
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '../stores/auth'

  const authStore = useAuthStore()
  const router = useRouter()
  const { t } = useI18n()

  // Initialize locale
  authStore.initLocale()

  const email = ref('')
  const password = ref('')
  const isValid = ref(false)
  const activeLocale = ref(authStore.currentLocale)

  const rules = computed(() => ({
    emailRequired: (value: any) => !!value || t('auth.emailRequired'),
    email: (value: any) => /.+@.+\..+/.test(value) || t('auth.emailInvalid'),
    passwordRequired: (value: any) => !!value || t('auth.passwordRequired'),
  }))

  async function handleLogin () {
    if (!isValid.value) return

    const success = await authStore.login({
      email: email.value as any,
      password: password.value as any,
    })

    if (success) {
      router.push('/equipment')
    }
  }
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  background-color: #0f172a;
}
.login-card {
  background-color: #1e293b !important;
  border-color: #334155 !important;
}
</style>
