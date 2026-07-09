import { computed, reactive } from 'vue'
import type { AuthPayload, AuthUser, LoginPayload } from '../api/auth'
import { login as loginApi } from '../api/auth'

const AUTH_STORAGE_KEY = 'access_token';

type StoredAuthState = {
  token: string
  user: AuthUser | null
}

const readStoredState = (): StoredAuthState => {
  if (typeof window === 'undefined') {
    return { token: '', user: null }
  }

  const raw = window.localStorage.getItem(AUTH_STORAGE_KEY)

  if (!raw) {
    return { token: '', user: null }
  }

  try {
    const parsed = JSON.parse(raw) as Partial<StoredAuthState>
    return {
      token: typeof parsed.token === 'string' ? parsed.token : '',
      user: parsed.user ?? null,
    }
  }
  catch {
    return { token: '', user: null }
  }
}

const writeStoredState = (state: StoredAuthState) => {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(state))
}

const clearStoredState = () => {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.removeItem(AUTH_STORAGE_KEY)
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