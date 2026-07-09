import { ApiError } from './client'

export const HTTP_STATUS = {
  UNAUTHORIZED: 401,
  FORBIDDEN: 403,
  NOT_FOUND: 404,
} as const

export const COMMON_ERROR_MESSAGES = {
  DEFAULT: 'An unexpected error occurred. Please try again later.',
  SESSION_EXPIRED: 'Your session has expired. Please sign in again.',
  FORBIDDEN: 'You do not have permission to perform this action.',
  NOT_FOUND: 'Not found',
} as const

export const COMMON_STATUS_MESSAGES: Partial<Record<number, string>> = {
  [HTTP_STATUS.UNAUTHORIZED]: COMMON_ERROR_MESSAGES.SESSION_EXPIRED,
  [HTTP_STATUS.FORBIDDEN]: COMMON_ERROR_MESSAGES.FORBIDDEN,
  [HTTP_STATUS.NOT_FOUND]: COMMON_ERROR_MESSAGES.NOT_FOUND,
}

type ResolveApiErrorMessageOptions = {
  defaultMessage: string
  statusMessages?: Partial<Record<number, string>>
}

export const resolveApiErrorMessage = (
  error: unknown,
  options: ResolveApiErrorMessageOptions,
): string => {
  if (error instanceof ApiError) {
    const statusMessages = {
      ...COMMON_STATUS_MESSAGES,
      ...options.statusMessages,
    }

    const statusMessage = statusMessages[error.status]

    if (statusMessage) {
      return statusMessage
    }

    return options.defaultMessage || COMMON_ERROR_MESSAGES.DEFAULT
  }

  if (error instanceof Error && error.message) {
    return COMMON_ERROR_MESSAGES.DEFAULT
  }

  return options.defaultMessage
}