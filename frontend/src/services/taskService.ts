import { getAccessToken } from './authService'
import {
  createTask as createTaskApi,
  deleteTask as deleteTaskApi,
  getTask as getTaskApi,
  listTasks as listTasksApi,
  updateTask as updateTaskApi,
  type Task,
  type TaskPayload,
  type TaskStatus,
} from '../api/tasks'

export type { Task, TaskPayload, TaskStatus }

export function fetchTasks(filters: Record<string, string | number | undefined> = {}) {
  return listTasksApi(getAccessToken(), filters)
}

export function fetchTask(id: number) {
  return getTaskApi(getAccessToken(), id)
}

export function createTask(payload: TaskPayload) {
  return createTaskApi(getAccessToken(), payload)
}

export function updateTask(id: number, payload: Partial<TaskPayload>) {
  return updateTaskApi(getAccessToken(), id, payload)
}

export function deleteTask(id: number) {
  return deleteTaskApi(getAccessToken(), id)
}