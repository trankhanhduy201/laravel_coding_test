<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import { ApiError } from '../api/client'
import {
  fetchTasks as loadTasks,
  type Task,
  type TaskListFilters,
  type TaskListMeta,
  type TaskStatus,
} from '../services/taskService'

const tasks = ref<Task[]>([])
const loading = ref(false)
const errorMessage = ref('')

const selectedStatus = ref<'all' | TaskStatus>('all')
const assignedToFilter = ref<string>('')

const page = ref(1)
const itemsPerPage = ref(10)

const meta = ref<TaskListMeta>({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
})

const statusOptions = [
  { title: 'All', value: 'all' },
  { title: 'To do', value: 'todo' },
  { title: 'In progress', value: 'in_progress' },
  { title: 'Done', value: 'done' },
]

const headers = [
  { title: 'Title', key: 'title' },
  { title: 'Author', key: 'author' },
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

const formatAssignee = (assignee: Task['assignee']) => {
  if (!assignee) return 'Unassigned'
  if (assignee.name && assignee.name.trim()) return assignee.name
  return `User #${assignee.id}`
}

const formatDate = (value: string | null) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString()
}

const normalizedTasks = computed(() => tasks.value.map((task) => ({
  ...task,
  author_display: task.author?.name || (task.author ? `User #${task.author.id}` : 'Unknown'),
  assignee_display: formatAssignee(task.assignee),
})))

const resolveRow = (item: unknown): Partial<Task> => {
  if (!item || typeof item !== 'object') {
    return {}
  }

  const maybeWithRaw = item as { raw?: unknown }

  if (maybeWithRaw.raw && typeof maybeWithRaw.raw === 'object') {
    return maybeWithRaw.raw as Partial<Task>
  }

  return item as Partial<Task>
}

const getRowStatus = (item: unknown): TaskStatus => {
  const status = resolveRow(item).status
  return status === 'in_progress' || status === 'done' || status === 'todo' ? status : 'todo'
}

const getRowDueDate = (item: unknown): string | null => {
  const dueDate = resolveRow(item).due_date
  return typeof dueDate === 'string' || dueDate === null ? dueDate : null
}

const getRowTitle = (item: unknown): string => {
  const title = resolveRow(item).title
  return typeof title === 'string' ? title : 'task'
}

const getRowAuthor = (item: unknown): string => {
  const author = resolveRow(item).author

  if (author && typeof author === 'object') {
    const a = author as { id?: unknown, name?: unknown }
    if (typeof a.name === 'string' && a.name.trim()) return a.name
    if (typeof a.id === 'number') return `User #${a.id}`
  }

  return 'Unknown'
}

const getRowAssignee = (item: unknown): string => {
  const assignee = resolveRow(item).assignee

  if (assignee && typeof assignee === 'object') {
    const a = assignee as { id?: unknown, name?: unknown }
    if (typeof a.name === 'string' && a.name.trim()) return a.name
    if (typeof a.id === 'number') return `User #${a.id}`
  }

  return 'Unassigned'
}

const totalPages = computed(() => Math.max(1, meta.value.last_page))

watch([selectedStatus, assignedToFilter, itemsPerPage], () => {
  page.value = 1
  void fetchTasks()
})

watch(page, () => {
  void fetchTasks()
})

const fetchTasks = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const assignedTo = assignedToFilter.value.trim()
    const filters: TaskListFilters = {
      page: page.value,
      limit: itemsPerPage.value,
      status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
      assigned_to: assignedTo ? Number(assignedTo) : undefined,
    }

    if (assignedTo && Number.isNaN(filters.assigned_to)) {
      errorMessage.value = 'Assigned to must be a valid user ID.'
      tasks.value = []
      meta.value = {
        current_page: 1,
        per_page: itemsPerPage.value,
        total: 0,
        last_page: 1,
      }
      loading.value = false
      return
    }

    const response = await loadTasks(filters)

    tasks.value = response.data
    meta.value = response.meta
    page.value = response.meta.current_page
    itemsPerPage.value = response.meta.per_page
  }
  catch (error: unknown) {
    if (error instanceof ApiError && error.status === 401) {
      errorMessage.value = 'Your session has expired. Please sign in again.'
    }
    else {
      errorMessage.value = error instanceof Error ? error.message : 'Unexpected error while fetching tasks'
    }
    tasks.value = []
    meta.value = {
      current_page: 1,
      per_page: itemsPerPage.value,
      total: 0,
      last_page: 1,
    }
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
              v-model="assignedToFilter"
              label="Filter by assignee ID"
              type="number"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-account-outline"
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
          :items="normalizedTasks"
          :loading="loading"
          loading-text="Loading tasks..."
          item-value="id"
          class="elevation-0"
          hide-default-footer
        >
          <template #item.author="{ item }">
            {{ getRowAuthor(item) }}
          </template>

          <template #item.assignee="{ item }">
            {{ getRowAssignee(item) }}
          </template>

          <template #item.status="{ item }">
            <v-chip size="small" :color="getStatusColor(getRowStatus(item))" variant="tonal">
              {{ getStatusLabel(getRowStatus(item)) }}
            </v-chip>
          </template>

          <template #item.due_date="{ item }">
            {{ formatDate(getRowDueDate(item)) }}
          </template>

          <template #item.actions="{ item }">
            <div class="d-flex ga-2">
              <v-btn size="small" variant="text" color="primary" icon="mdi-pencil" :title="`Edit ${getRowTitle(item)}`" />
              <v-btn size="small" variant="text" color="error" icon="mdi-delete" :title="`Delete ${getRowTitle(item)}`" />
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
                Showing {{ normalizedTasks.length }} of {{ meta.total }}
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