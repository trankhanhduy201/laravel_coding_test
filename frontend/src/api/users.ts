import { request } from './client'
import type { TaskUser } from './tasks'

export type UserLookupFilters = {
  q?: string
  limit?: number
}

export async function listUsers(authToken: string, filters: UserLookupFilters = {}) {
  const query = new URLSearchParams()

  if (filters.q && filters.q.trim()) {
    query.set('q', filters.q.trim())
  }

  if (filters.limit && Number.isFinite(filters.limit)) {
    query.set('limit', String(filters.limit))
  }

  const suffix = query.toString() ? `?${query.toString()}` : ''
  const payload = await request<TaskUser[]>(`/v1/users${suffix}`, {
    method: 'GET',
    authToken,
  })

  return Array.isArray(payload) ? payload : []
}
