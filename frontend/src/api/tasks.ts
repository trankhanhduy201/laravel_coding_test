import { request } from './client'

export type TaskStatus = 'todo' | 'in_progress' | 'done'

export type Task = {
  id: number
  title: string
  description: string | null
  status: TaskStatus
  assigned_to: number | null
  user_id: number | null
  due_date: string | null
  created_at?: string
  updated_at?: string
}

export type TaskListResponse = {
  data: Task[]
}

export type TaskPayload = {
  title: string
  description?: string | null
  status: TaskStatus
  assigned_to?: number | null
  due_date?: string | null
  user_id?: number | null
}

const normalizeTaskList = (payload: unknown): Task[] => {
  if (!payload || typeof payload !== 'object') {
    return []
  }

  const root = payload as { data?: unknown }

  if (Array.isArray(root.data)) {
    return root.data as Task[]
  }

  if (root.data && typeof root.data === 'object') {
    const nested = root.data as { data?: unknown }

    if (Array.isArray(nested.data)) {
      return nested.data as Task[]
    }
  }

  return []
}

export async function listTasks(authToken: string, filters: Record<string, string | number | undefined> = {}) {
  const query = new URLSearchParams()

  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      query.set(key, String(value))
    }
  })

  const suffix = query.toString() ? `?${query.toString()}` : ''
  const payload = await request<TaskListResponse | { data: TaskListResponse } | { data: Task[] }>(
    `/v1/tasks${suffix}`,
    {
      method: 'GET',
      authToken,
    },
  )

  return normalizeTaskList(payload)
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