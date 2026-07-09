type UserRoleSetting = {
  label: string
  key: string
}

export const userRoleSettings = {
  admin: {
    label: 'Admin',
    key: 'admin',
  },
  user: {
    label: 'User',
    key: 'user',
  },
} as const satisfies Record<string, UserRoleSetting>

export const isAdminRole = (role: string | null | undefined): boolean => {
  return role === userRoleSettings.admin.key
}
