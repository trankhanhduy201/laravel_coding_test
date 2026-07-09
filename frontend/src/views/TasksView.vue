<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import TaskDeleteButton from '../components/TaskDeleteButton.vue'
import UserAutocomplete from '../components/UserAutocomplete.vue'
import { resolveApiErrorMessage } from '../api/errorMessages'
import { authState } from '../services/authService'
import { isAdminRole } from '../features/auth/userRoleHelpers'
import {
  fetchTasks as loadTasks,
  type Task,
  type TaskListFilters,
  type TaskListMeta,
  type TaskStatus,
  type TaskUser,
} from '../services/taskService'
import {
  formatTaskDate,
  formatTaskUser,
  getTaskStatusColor,
  getTaskStatusLabel,
  taskStatusFilterOptions,
  taskStatusSettings,
  validTaskStatuses,
} from '../features/tasks/taskHelpers'

const tasks = ref<Task[]>([])
const loading = ref(false)
const errorMessage = ref('')
const router = useRouter()

const selectedStatus = ref<'all' | TaskStatus>('all')
const assignedToFilter = ref<TaskUser | null>(null)

const page = ref(1)
const itemsPerPage = ref(10)

const meta = ref<TaskListMeta>({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
})

const statusOptions = taskStatusFilterOptions

const headers = [
  { title: 'Title', key: 'title' },
  { title: 'Author', key: 'author' },
  { title: 'Assignee', key: 'assignee' },
  { title: 'Status', key: 'status' },
  { title: 'Due date', key: 'due_date' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const formatAssignee = (assignee: Task['assignee']) => {
  return formatTaskUser(assignee, 'Unassigned')
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
  return validTaskStatuses.includes(status) ? status : taskStatusSettings.todo.key
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
    return formatTaskUser(
      {
        id: typeof a.id === 'number' ? a.id : 0,
        name: typeof a.name === 'string' ? a.name : null,
      },
      'Unknown',
    )
  }

  return 'Unknown'
}

const getRowAssignee = (item: unknown): string => {
  const assignee = resolveRow(item).assignee

  if (assignee && typeof assignee === 'object') {
    const a = assignee as { id?: unknown, name?: unknown }
    return formatTaskUser(
      {
        id: typeof a.id === 'number' ? a.id : 0,
        name: typeof a.name === 'string' ? a.name : null,
      },
      'Unassigned',
    )
  }

  return 'Unassigned'
}

const goToCreate = async () => {
  await router.push({ name: 'tasks-create' })
}

const canViewTask = (item: unknown): boolean => {
  const task = resolveRow(item)
  const currentUser = authState.user

  if (!currentUser) return false

  // Admin can view all tasks
  if (isAdminRole(currentUser.role)) return true

  // Author can view their own tasks
  if (task.author?.id === currentUser.id) return true

  // Assignee can view tasks assigned to them
  if (task.assignee?.id === currentUser.id) return true

  return false
}

const canEditTask = (item: unknown): boolean => {
  const task = resolveRow(item)
  const currentUser = authState.user

  if (!currentUser) return false

  // Admin can edit all tasks
  if (isAdminRole(currentUser.role)) return true

  // Author can edit their own tasks
  if (task.author?.id === currentUser.id) return true

  return false
}

const canDeleteTask = (item: unknown): boolean => {
  const task = resolveRow(item)
  const currentUser = authState.user

  if (!currentUser) return false

  // Admin can delete all tasks
  if (isAdminRole(currentUser.role)) return true

  // Author can delete their own tasks
  if (task.author?.id === currentUser.id) return true

  return false
}

const goToView = async (item: unknown) => {
  if (!canViewTask(item)) return

  const row = resolveRow(item)
  if (!row.id) return

  await router.push({ name: 'tasks-view', params: { id: row.id } })
}

const goToEdit = async (item: unknown) => {
  if (!canEditTask(item)) return

  const row = resolveRow(item)
  if (!row.id) return

  await router.push({ name: 'tasks-edit', params: { id: row.id } })
}

const handleTaskDeleted = async () => {
  await fetchTasks()
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
    const filters: TaskListFilters = {
      page: page.value,
      limit: itemsPerPage.value,
      status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
      assigned_to: assignedToFilter.value?.id,
    }

    const response = await loadTasks(filters)

    tasks.value = response.data
    meta.value = response.meta
    page.value = response.meta.current_page
    itemsPerPage.value = response.meta.per_page
  }
  catch (error: unknown) {
    errorMessage.value = resolveApiErrorMessage(error, {
      defaultMessage: 'Unexpected error while fetching tasks',
    })

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
        <div class="d-flex ga-2">
          <v-btn color="primary" prepend-icon="mdi-plus" @click="goToCreate">
            Create task
          </v-btn>
          <v-btn color="primary" variant="tonal" prepend-icon="mdi-refresh" @click="fetchTasks">
            Refresh
          </v-btn>
        </div>
      </v-card-title>

      <v-card-text>
        <v-row class="mb-2">
          <v-col cols="12" md="7">
            <UserAutocomplete
              v-model="assignedToFilter"
              label="Filter by assignee"
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
          :items-per-page="-1"
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
            <v-chip size="small" :color="getTaskStatusColor(getRowStatus(item))" variant="tonal">
              {{ getTaskStatusLabel(getRowStatus(item)) }}
            </v-chip>
          </template>

          <template #item.due_date="{ item }">
            {{ formatTaskDate(getRowDueDate(item)) }}
          </template>

          <template #item.actions="{ item }">
            <div class="d-flex ga-2">
              <v-btn
                v-if="canViewTask(item) && !canEditTask(item) && !canDeleteTask(item)"
                size="small"
                variant="text"
                color="info"
                icon="mdi-eye"
                :title="`View ${getRowTitle(item)}`"
                @click="goToView(item)"
              />
              <v-btn
                v-if="canEditTask(item)"
                size="small"
                variant="text"
                color="primary"
                icon="mdi-pencil"
                :title="`Edit ${getRowTitle(item)}`"
                @click="goToEdit(item)"
              />
              <TaskDeleteButton
                v-if="canDeleteTask(item)"
                :task-id="Number(resolveRow(item).id || 0)"
                :task-title="getRowTitle(item)"
                icon-only
                button-size="small"
                button-variant="text"
                @deleted="handleTaskDeleted"
              />
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