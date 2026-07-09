import { listUsers, type UserLookupFilters } from '../api/users'
import { getAccessToken } from './authService'

export type { UserLookupFilters }

export function fetchUsers(filters: UserLookupFilters = {}) {
  return listUsers(getAccessToken(), filters)
}
