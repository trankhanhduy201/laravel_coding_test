import { request, requestEnvelope } from './client'

export type TaskStatus = 'todo' | 'in_progress' | 'done'

export type TaskUser = {
  id: number
  name: string | null
}

export type Task = {
  id: number
  title: string
  description: string | null
  status: TaskStatus
  author: TaskUser | null
  assignee: TaskUser | null
  due_date: string | null
  created_at?: string
  updated_at?: string
}

export type TaskListResponse = {
  data: Task[]
  meta: TaskListMeta
}

export type TaskListMeta = {
  current_page: number
  per_page: number
  total: number
  last_page: number
}

export type TaskPayload = {
  title: string
  description?: string | null
  status: TaskStatus
  assigned_to?: number | null
  due_date?: string | null
  user_id?: number | null
}

export type TaskListFilters = {
  page?: number
  limit?: number
  status?: TaskStatus
  assigned_to?: number
}

const defaultTaskListMeta: TaskListMeta = {
  current_page: 1,
  per_page: 15,
  total: 0,
  last_page: 1,
}

export async function listTasks(authToken: string, filters: TaskListFilters = {}) {
  const query = new URLSearchParams()

  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      query.set(key, String(value))
    }
  })

  const suffix = query.toString() ? `?${query.toString()}` : ''
  const envelope = await requestEnvelope<Task[]>(
    `/v1/tasks${suffix}`,
    {
      method: 'GET',
      authToken,
    },
  )

  const meta = envelope.meta as Partial<TaskListMeta> | undefined

  return {
    data: Array.isArray(envelope.data) ? envelope.data : [],
    meta: {
      current_page: meta?.current_page ?? defaultTaskListMeta.current_page,
      per_page: meta?.per_page ?? defaultTaskListMeta.per_page,
      total: meta?.total ?? defaultTaskListMeta.total,
      last_page: meta?.last_page ?? defaultTaskListMeta.last_page,
    },
  }
}

export async function getTask(authToken: string, id: number) {
  return request<Task>(`/v1/tasks/${id}`, {
    method: 'GET',
    authToken,
  })
}

export async function createTask(authToken: string, payload: TaskPayload) {
  return request<Task>('/v1/tasks', {
    method: 'POST',
    authToken,
    body: payload,
  })
}

export async function updateTask(authToken: string, id: number, payload: Partial<TaskPayload>) {
  return request<Task>(`/v1/tasks/${id}`, {
    method: 'PUT',
    authToken,
    body: payload,
  })
}

export async function deleteTask(authToken: string, id: number) {
  return request<void>(`/v1/tasks/${id}`, {
    method: 'DELETE',
    authToken,
  })
}