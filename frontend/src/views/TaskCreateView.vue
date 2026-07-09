<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import UserAutocomplete from '../components/UserAutocomplete.vue'
import { ApiError } from '../api/client'
import { createTask, type TaskPayload, type TaskStatus, type TaskUser } from '../services/taskService'
import { taskStatusOptions, toApiDateTime } from '../features/tasks/taskHelpers'

const router = useRouter()
const loading = ref(false)
const errorMessage = ref('')
const selectedAssignee = ref<TaskUser | null>(null)

const form = reactive({
  title: '',
  description: '',
  status: 'todo' as TaskStatus,
  due_date: '',
})

const validate = (): string | null => {
  if (!form.title.trim()) return 'Title is required.'
  if (!selectedAssignee.value?.id) return 'Assignee is required.'
  if (form.due_date && !toApiDateTime(form.due_date)) return 'Due date is invalid.'

  return null
}

const submit = async () => {
  errorMessage.value = ''

  const message = validate()
  if (message) {
    errorMessage.value = message
    return
  }

  loading.value = true

  try {
    const payload: TaskPayload = {
      title: form.title.trim(),
      description: form.description.trim() || null,
      status: form.status,
      assigned_to: selectedAssignee.value?.id,
      due_date: toApiDateTime(form.due_date),
    }

    await createTask(payload)
    await router.push({ name: 'tasks' })
  }
  catch (error) {
    errorMessage.value = error instanceof ApiError ? error.message : 'Cannot create task.'
  }
  finally {
    loading.value = false
  }
}

const goBack = async () => {
  await router.push({ name: 'tasks' })
}
</script>

<template>
  <DashboardLayout>
    <v-card rounded="xl" elevation="2">
      <v-card-title>Create task</v-card-title>
      <v-card-text>
        <v-alert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          class="mb-4"
          :text="errorMessage"
        />

        <v-row>
          <v-col cols="12">
            <v-text-field
              v-model="form.title"
              label="Title"
              variant="outlined"
              :disabled="loading"
            />
          </v-col>

          <v-col cols="12">
            <v-textarea
              v-model="form.description"
              label="Description"
              variant="outlined"
              rows="4"
              :disabled="loading"
            />
          </v-col>

          <v-col cols="12" md="4">
            <v-select
              v-model="form.status"
              :items="taskStatusOptions"
              item-title="title"
              item-value="value"
              label="Status"
              variant="outlined"
              :disabled="loading"
            />
          </v-col>

          <v-col cols="12" md="4">
            <UserAutocomplete
              v-model="selectedAssignee"
              label="Assignee"
              :disabled="loading"
            />
          </v-col>

          <v-col cols="12" md="4">
            <v-text-field
              v-model="form.due_date"
              label="Due date"
              type="datetime-local"
              variant="outlined"
              :disabled="loading"
            />
          </v-col>
        </v-row>

        <div class="d-flex justify-end ga-2">
          <v-btn variant="text" :disabled="loading" @click="goBack">Cancel</v-btn>
          <v-btn color="primary" :loading="loading" @click="submit">Create</v-btn>
        </div>
      </v-card-text>
    </v-card>
  </DashboardLayout>
</template>
