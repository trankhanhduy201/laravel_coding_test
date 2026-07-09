<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import { COMMON_ERROR_MESSAGES, resolveApiErrorMessage } from '../api/errorMessages'
import { authState } from '../services/authService'
import { isAdminRole } from '../features/auth/userRoleHelpers'
import {
  fetchTask,
  type Task,
} from '../services/taskService'
import {
  formatTaskDate,
  formatTaskUser,
  getTaskStatusColor,
  getTaskStatusLabel,
} from '../features/tasks/taskHelpers'

const task = ref<Task | null>(null)
const loading = ref(true)
const errorMessage = ref('')
const router = useRouter()
const route = useRoute()

const canViewTask = computed(() => {
  if (!task.value || !authState.user) return false

  // Admin can view all
  if (isAdminRole(authState.user.role)) return true

  // Author can view
  if (task.value.author?.id === authState.user.id) return true

  // Assignee can view
  if (task.value.assignee?.id === authState.user.id) return true

  return false
})

const canEditTask = computed(() => {
  if (!task.value || !authState.user) return false

  // Admin can edit all
  if (isAdminRole(authState.user.role)) return true

  // Author can edit
  if (task.value.author?.id === authState.user.id) return true

  return false
})

const loadTask = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const taskId = Number(route.params.id)
    if (!taskId || taskId === 0) {
      throw new Error('Invalid task ID')
    }

    const response = await fetchTask(taskId)
    task.value = response

    if (!canViewTask.value) {
      errorMessage.value = COMMON_ERROR_MESSAGES.FORBIDDEN
      task.value = null
    }
  }
  catch (error: unknown) {
    errorMessage.value = resolveApiErrorMessage(error, {
      defaultMessage: 'Unexpected error while loading task',
    })

    task.value = null
  }
  finally {
    loading.value = false
  }
}

const goBack = async () => {
  await router.push({ name: 'tasks' })
}

onMounted(() => {
  loadTask()
})
</script>

<template>
  <DashboardLayout>
    <div v-if="loading" class="d-flex justify-center align-center" style="min-height: 400px">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <v-alert
      v-else-if="errorMessage"
      type="error"
      variant="tonal"
      title="Cannot load task"
      :text="errorMessage"
    />

    <v-card v-else-if="task" rounded="xl" elevation="2">
      <v-card-title class="d-flex align-center justify-space-between flex-wrap ga-4">
        <span>{{ task.title }}</span>
        <div class="d-flex ga-2">
          <v-btn color="default" variant="tonal" prepend-icon="mdi-arrow-left" @click="goBack">
            Back
          </v-btn>
        </div>
      </v-card-title>

      <v-card-text>
        <v-row class="mb-4">
          <v-col cols="12" md="6">
            <div class="mb-6">
              <div class="text-overline text-primary mb-2">Status</div>
              <v-chip
                :color="getTaskStatusColor(task.status)"
                variant="tonal"
              >
                {{ getTaskStatusLabel(task.status) }}
              </v-chip>
            </div>

            <div class="mb-6">
              <div class="text-overline text-primary mb-2">Author</div>
              <div class="text-body-2">{{ formatTaskUser(task.author, 'Unknown') }}</div>
            </div>
          </v-col>

          <v-col cols="12" md="6">
            <div class="mb-6">
              <div class="text-overline text-primary mb-2">Due Date</div>
              <div class="text-body-2">{{ formatTaskDate(task.due_date) }}</div>
            </div>

            <div class="mb-6">
              <div class="text-overline text-primary mb-2">Assignee</div>
              <div class="text-body-2">{{ formatTaskUser(task.assignee, 'Unassigned') }}</div>
            </div>
          </v-col>
        </v-row>

        <v-divider class="my-4" />

        <div v-if="task.description" class="mb-6">
          <div class="text-overline text-primary mb-2">Description</div>
          <div class="text-body-2" style="white-space: pre-wrap; word-break: break-word">
            {{ task.description }}
          </div>
        </div>

        <div v-else class="mb-6">
          <div class="text-body-2 text-medium-emphasis">No description provided</div>
        </div>
      </v-card-text>
    </v-card>

    <v-alert v-else type="info" variant="tonal" title="Task not found" text="The requested task could not be found." />
  </DashboardLayout>
</template>
