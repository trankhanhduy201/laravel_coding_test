<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'
import { logout } from '../services/authService'

const drawer = ref(true)
const route = useRoute()
const router = useRouter()

const title = computed(() => (route.name === 'tasks' ? 'Task Dashboard' : 'Workspace'))

const handleLogout = async () => {
  logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <v-navigation-drawer v-model="drawer" color="surface" width="280">
    <div class="pa-4">
      <div class="text-overline text-primary">Laravel Coding Test</div>
      <div class="text-h6 font-weight-bold">Task Manager</div>
      <div class="text-body-2 text-medium-emphasis mt-1">Vue 3 + Vuetify SPA</div>
    </div>

    <v-divider />

    <v-list nav density="comfortable">
      <v-list-item
        title="Login"
        subtitle="Authentication entry"
        prepend-icon="mdi-login"
        to="/login"
      />
      <v-list-item
        title="Tasks"
        subtitle="Task overview"
        prepend-icon="mdi-format-list-checks"
        to="/tasks"
      />
    </v-list>
  </v-navigation-drawer>

  <v-app-bar elevation="0" color="surface" border>
    <v-app-bar-nav-icon @click="drawer = !drawer" />
    <v-toolbar-title>{{ title }}</v-toolbar-title>
    <v-spacer />
    <v-btn variant="text" prepend-icon="mdi-logout" @click="handleLogout">Logout</v-btn>
  </v-app-bar>

  <v-main>
    <v-container fluid class="pa-6">
      <slot />
    </v-container>
  </v-main>
</template>