import { request } from './client'

export type AuthUser = {
  id: number
  name: string
  email: string
  role: string
}

export type LoginPayload = {
  email: string
  password: string
}

export type AuthPayload = {
  token: string
  user: AuthUser
}

export function login(payload: LoginPayload) {
  return request<AuthPayload>('/login', {
    method: 'POST',
    body: payload,
  })
}