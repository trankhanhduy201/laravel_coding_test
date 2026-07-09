export type ApiEnvelope<T> = {
  success: boolean
  message: string
  data: T
  meta?: Record<string, unknown>
  errors?: Record<string, string[]>
}

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status: number,
    public readonly errors?: Record<string, string[]>,
    public readonly payload?: unknown,
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

export type ApiRequestOptions = Omit<RequestInit, 'body'> & {
  authToken?: string
  body?: BodyInit | Record<string, unknown> | null
}

export const getApiBaseUrl = () => import.meta.env.VITE_API_BASE_URL.replace(/\/$/, '')

const buildUrl = (path: string) => {
  if (/^https?:\/\//i.test(path)) {
    return path
  }

  const normalizedPath = path.startsWith('/') ? path : `/${path}`
  return `${getApiBaseUrl()}${normalizedPath}`
}

const isJsonLike = (value: unknown): value is Record<string, unknown> => (
  typeof value === 'object' && value !== null && !(value instanceof FormData)
)

export async function request<T>(
  path: string,
  options: ApiRequestOptions = {},
): Promise<T> {
  const envelope = await requestEnvelope<T>(path, options)
  return envelope.data
}

export async function requestEnvelope<T>(
  path: string,
  options: ApiRequestOptions = {},
): Promise<ApiEnvelope<T>> {
  const { authToken, body: requestBody, ...initOptions } = options
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')

  if (authToken) {
    headers.set('Authorization', `Bearer ${authToken}`)
  }

  let body: BodyInit | undefined

  if (isJsonLike(requestBody)) {
    headers.set('Content-Type', 'application/json')
    body = JSON.stringify(requestBody)
  }
  else if (requestBody != null) {
    body = requestBody
  }

  const init: RequestInit = {
    ...initOptions,
    headers,
  }

  if (body !== undefined) {
    init.body = body
  }

  const response = await fetch(buildUrl(path), init)

  const rawText = await response.text()
  const payload = rawText ? JSON.parse(rawText) as ApiEnvelope<T> : null

  if (!response.ok) {
    const errorMessage = payload?.message || response.statusText || 'Request failed'
    throw new ApiError(errorMessage, response.status, payload?.errors, payload)
  }

  return (payload ?? { success: true, message: '', data: null as T }) as ApiEnvelope<T>
}