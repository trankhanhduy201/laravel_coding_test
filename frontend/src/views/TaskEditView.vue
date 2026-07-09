<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import TaskDeleteButton from '../components/TaskDeleteButton.vue'
import UserAutocomplete from '../components/UserAutocomplete.vue'
import { resolveApiErrorMessage } from '../api/errorMessages'
import { fetchTask, updateTask, type TaskPayload, type TaskStatus, type TaskUser } from '../services/taskService'
import { taskStatusOptions, toApiDateTime, toDateTimeLocalInput } from '../features/tasks/taskHelpers'

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const loadingTask = ref(false)
const errorMessage = ref('')
const selectedAssignee = ref<TaskUser | null>(null)

const form = reactive({
  title: '',
  description: '',
  status: 'todo' as TaskStatus,
  due_date: '',
})

const taskId = Number(route.params.id)

const validate = (): string | null => {
  if (!Number.isFinite(taskId) || taskId <= 0) return 'Invalid task ID.'
  if (!form.title.trim()) return 'Title is required.'
  if (!selectedAssignee.value?.id) return 'Assignee is required.'
  if (form.due_date && !toApiDateTime(form.due_date)) return 'Due date is invalid.'

  return null
}

const loadTask = async () => {
  loadingTask.value = true
  errorMessage.value = ''

  try {
    const task = await fetchTask(taskId)

    form.title = task.title
    form.description = task.description || ''
    form.status = task.status
    selectedAssignee.value = task.assignee
    form.due_date = toDateTimeLocalInput(task.due_date)
  }
  catch (error) {
    errorMessage.value = resolveApiErrorMessage(error, {
      defaultMessage: 'Cannot load task.',
    })
  }
  finally {
    loadingTask.value = false
  }
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
    const payload: Partial<TaskPayload> = {
      title: form.title.trim(),
      description: form.description.trim() || null,
      status: form.status,
      assigned_to: selectedAssignee.value?.id,
      due_date: toApiDateTime(form.due_date),
    }

    await updateTask(taskId, payload)
    await router.push({ name: 'tasks' })
  }
  catch (error) {
    errorMessage.value = resolveApiErrorMessage(error, {
      defaultMessage: 'Cannot update task.',
    })
  }
  finally {
    loading.value = false
  }
}

const goBack = async () => {
  await router.push({ name: 'tasks' })
}

const handleTaskDeleted = async () => {
  await router.push({ name: 'tasks' })
}

onMounted(() => {
  void loadTask()
})
</script>

<template>
  <DashboardLayout>
    <v-card rounded="xl" elevation="2">
      <v-card-title>Edit task</v-card-title>
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
              :disabled="loading || loadingTask"
            />
          </v-col>

          <v-col cols="12">
            <v-textarea
              v-model="form.description"
              label="Description"
              variant="outlined"
              rows="4"
              :disabled="loading || loadingTask"
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
              :disabled="loading || loadingTask"
            />
          </v-col>

          <v-col cols="12" md="4">
            <UserAutocomplete
              v-model="selectedAssignee"
              label="Assignee"
              :disabled="loading || loadingTask"
            />
          </v-col>

          <v-col cols="12" md="4">
            <v-text-field
              v-model="form.due_date"
              label="Due date"
              type="datetime-local"
              variant="outlined"
              :disabled="loading || loadingTask"
            />
          </v-col>
        </v-row>

        <div class="d-flex justify-space-between align-center ga-2">
          <TaskDeleteButton
            :task-id="taskId"
            :task-title="form.title"
            button-text="Delete task"
            button-variant="tonal"
            :button-size="'default'"
            @deleted="handleTaskDeleted"
          />

          <div class="d-flex justify-end ga-2">
            <v-btn variant="text" :disabled="loading || loadingTask" @click="goBack">Cancel</v-btn>
            <v-btn color="primary" :loading="loading" :disabled="loadingTask" @click="submit">Save changes</v-btn>
          </div>
        </div>
      </v-card-text>
    </v-card>
  </DashboardLayout>
</template>
