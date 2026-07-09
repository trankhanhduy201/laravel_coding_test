import { computed, reactive } from 'vue'
import type { AuthPayload, AuthUser, LoginPayload } from '../api/auth'
import { login as loginApi } from '../api/auth'

const ACCESS_TOKEN_STORAGE_KEY = 'access_token'
const AUTH_USER_STORAGE_KEY = 'auth_user'

type StoredAuthState = {
  token: string
  user: AuthUser | null
}

const readStoredState = (): StoredAuthState => {
  if (typeof window === 'undefined') {
    return { token: '', user: null }
  }

  const rawToken = window.localStorage.getItem(ACCESS_TOKEN_STORAGE_KEY)
  const rawUser = window.localStorage.getItem(AUTH_USER_STORAGE_KEY)

  try {
    const token = rawToken || ''
    const user = rawUser ? JSON.parse(rawUser) as AuthUser : null

    if (token || user) {
      return {
        token,
        user,
      }
    }

    // Legacy format fallback: access_token = { token, user }
    if (!rawToken) {
      return { token: '', user: null }
    }

    const parsed = JSON.parse(rawToken) as Partial<StoredAuthState>
    const migratedState = {
      token: typeof parsed.token === 'string' ? parsed.token : '',
      user: parsed.user ?? null,
    }

    if (migratedState.token) {
      window.localStorage.setItem(ACCESS_TOKEN_STORAGE_KEY, migratedState.token)
    }
    else {
      window.localStorage.removeItem(ACCESS_TOKEN_STORAGE_KEY)
    }

    if (migratedState.user) {
      window.localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(migratedState.user))
    }
    else {
      window.localStorage.removeItem(AUTH_USER_STORAGE_KEY)
    }

    return migratedState
  }
  catch {
    return { token: '', user: null }
  }
}

const writeStoredState = (state: StoredAuthState) => {
  if (typeof window === 'undefined') {
    return
  }

  if (state.token) {
    window.localStorage.setItem(ACCESS_TOKEN_STORAGE_KEY, state.token)
  }
  else {
    window.localStorage.removeItem(ACCESS_TOKEN_STORAGE_KEY)
  }

  if (state.user) {
    window.localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(state.user))
  }
  else {
    window.localStorage.removeItem(AUTH_USER_STORAGE_KEY)
  }
}

const clearStoredState = () => {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.removeItem(ACCESS_TOKEN_STORAGE_KEY)
  window.localStorage.removeItem(AUTH_USER_STORAGE_KEY)
}

export const authState = reactive<StoredAuthState>(readStoredState())

export const isAuthenticated = computed(() => Boolean(authState.token && authState.user))

export const initializeAuthState = () => {
  Object.assign(authState, readStoredState())
}

export const setAuthState = (payload: AuthPayload) => {
  authState.token = payload.token
  authState.user = payload.user
  writeStoredState({ token: payload.token, user: payload.user })
}

export const logout = () => {
  authState.token = ''
  authState.user = null
  clearStoredState()
}

export const getAccessToken = () => authState.token || readStoredState().token

export async function signIn(credentials: LoginPayload) {
  const payload = await loginApi(credentials)
  setAuthState(payload)
  return payload
}