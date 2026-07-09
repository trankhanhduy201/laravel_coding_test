<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import { ApiError } from '../api/client'
import { fetchTasks as loadTasks, type Task, type TaskStatus } from '../services/taskService'

const allTasks = ref<Task[]>([])
const loading = ref(false)
const errorMessage = ref('')

const search = ref('')
const selectedStatus = ref<'all' | TaskStatus>('all')

const page = ref(1)
const itemsPerPage = ref(10)

const statusOptions = [
  { title: 'All', value: 'all' },
  { title: 'To do', value: 'todo' },
  { title: 'In progress', value: 'in_progress' },
  { title: 'Done', value: 'done' },
]

const headers = [
  { title: 'Title', key: 'title' },
  { title: 'Assignee', key: 'assignee' },
  { title: 'Status', key: 'status' },
  { title: 'Due date', key: 'due_date' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const getStatusLabel = (status: TaskStatus) => {
  if (status === 'in_progress') return 'In progress'
  if (status === 'done') return 'Done'
  return 'To do'
}

const getStatusColor = (status: TaskStatus) => {
  if (status === 'in_progress') return 'primary'
  if (status === 'done') return 'success'
  return 'grey'
}

const formatAssignee = (assignedTo: number | null) => {
  if (!assignedTo) return 'Unassigned'
  return `User #${assignedTo}`
}

const formatDate = (value: string | null) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString()
}

const normalizedTasks = computed(() => allTasks.value.map((task) => ({
  ...task,
  assignee: formatAssignee(task.assigned_to),
})))

const filteredTasks = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  return normalizedTasks.value.filter((task) => {
    const matchesStatus = selectedStatus.value === 'all' || task.status === selectedStatus.value
    const matchesSearch = task.title.toLowerCase().includes(keyword)
    return matchesStatus && matchesSearch
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredTasks.value.length / itemsPerPage.value)))

const paginatedTasks = computed(() => {
  const start = (page.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredTasks.value.slice(start, end)
})

watch([search, selectedStatus, itemsPerPage], () => {
  page.value = 1
})

watch(totalPages, (newTotal) => {
  if (page.value > newTotal) {
    page.value = newTotal
  }
})

const fetchTasks = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    allTasks.value = await loadTasks()
  }
  catch (error: unknown) {
    if (error instanceof ApiError && error.status === 401) {
      errorMessage.value = 'Your session has expired. Please sign in again.'
    }
    else {
      errorMessage.value = error instanceof Error ? error.message : 'Unexpected error while fetching tasks'
    }
    allTasks.value = []
  }
  finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTasks()
})
</script>

<template>
  <DashboardLayout>
    <v-card rounded="xl" elevation="2">
      <v-card-title class="d-flex align-center justify-space-between flex-wrap ga-4">
        <span>Tasks</span>
        <v-btn color="primary" variant="tonal" prepend-icon="mdi-refresh" @click="fetchTasks">
          Refresh
        </v-btn>
      </v-card-title>

      <v-card-text>
        <v-row class="mb-2">
          <v-col cols="12" md="7">
            <v-text-field
              v-model="search"
              label="Search by title"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-magnify"
              hide-details
              clearable
            />
          </v-col>
          <v-col cols="12" md="5">
            <v-select
              v-model="selectedStatus"
              :items="statusOptions"
              item-title="title"
              item-value="value"
              label="Filter by status"
              variant="outlined"
              density="comfortable"
              hide-details
            />
          </v-col>
        </v-row>

        <v-alert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          class="mb-4"
          title="Cannot load tasks"
          :text="errorMessage"
        />

        <v-data-table
          :headers="headers"
          :items="paginatedTasks"
          :loading="loading"
          loading-text="Loading tasks..."
          item-value="id"
          class="elevation-0"
          hide-default-footer
        >
          <template #item.status="{ item }">
            <v-chip size="small" :color="getStatusColor(item.raw.status)" variant="tonal">
              {{ getStatusLabel(item.raw.status) }}
            </v-chip>
          </template>

          <template #item.due_date="{ item }">
            {{ formatDate(item.raw.due_date) }}
          </template>

          <template #item.actions="{ item }">
            <div class="d-flex ga-2">
              <v-btn size="small" variant="text" color="primary" icon="mdi-pencil" :title="`Edit ${item.raw.title}`" />
              <v-btn size="small" variant="text" color="error" icon="mdi-delete" :title="`Delete ${item.raw.title}`" />
            </div>
          </template>

          <template #bottom>
            <div class="d-flex align-center justify-space-between flex-wrap ga-4 px-4 py-3">
              <v-select
                v-model="itemsPerPage"
                :items="[5, 10, 20, 50]"
                label="Rows per page"
                variant="outlined"
                density="compact"
                hide-details
                style="max-width: 160px"
              />

              <div class="text-body-2 text-medium-emphasis">
                Showing {{ paginatedTasks.length }} of {{ filteredTasks.length }}
              </div>

              <v-pagination
                v-model="page"
                :length="totalPages"
                rounded="circle"
                density="comfortable"
              />
            </div>
          </template>

          <template #no-data>
            <div class="py-8 text-center text-medium-emphasis">
              No tasks found.
            </div>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </DashboardLayout>
</template>