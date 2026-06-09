<template>
  <v-app class="app-dark-theme">
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>

<script lang="ts" setup>
  //
</script>

<style>
.app-dark-theme {
  background-color: #0f172a !important;
  color: #f1f5f9 !important;
}
body {
  background-color: #0f172a !important;
}
</style>
