import type { TaskStatus, TaskUser } from '../../api/tasks'

type TaskStatusSetting = {
  label: string
  color: string
  key: TaskStatus
}

export const taskStatusSettings: Record<TaskStatus, TaskStatusSetting> = {
  todo: {
    label: 'To do',
    color: 'grey',
    key: 'todo'
  },
  in_progress: {
    label: 'In progress',
    color: 'primary',
    key: 'in_progress'
  },
  done: {
    label: 'Done',
    color: 'success',
    key: 'done'
  },
}

export const validTaskStatuses: TaskStatus[] = Object.values(taskStatusSettings).map(item => item.key) as TaskStatus[]

export const taskStatusOptions: Array<{ title: string, value: TaskStatus }> = validTaskStatuses.map((status) => ({
  title: taskStatusSettings[status].label,
  value: status,
}))

export const taskStatusFilterOptions: Array<{ title: string, value: 'all' | TaskStatus }> = [
  { title: 'All', value: 'all' },
  ...taskStatusOptions,
]

export const getTaskStatusLabel = (status: TaskStatus) => {
  return taskStatusSettings[status].label
}

export const getTaskStatusColor = (status: TaskStatus) => {
  return taskStatusSettings[status].color
}

export const formatTaskDate = (value: string | null) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString()
}

export const formatTaskUser = (user: TaskUser | null, emptyLabel: string) => {
  if (!user) return emptyLabel
  if (user.name && user.name.trim()) return user.name
  return `User #${user.id}`
}

export const toDateTimeLocalInput = (value: string | null) => {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''

  const pad = (v: number) => String(v).padStart(2, '0')

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

export const toApiDateTime = (value: string) => {
  if (!value) return null

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return null

  const pad = (v: number) => String(v).padStart(2, '0')

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:00`
}
