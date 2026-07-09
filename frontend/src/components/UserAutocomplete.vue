<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import type { TaskUser } from '../api/tasks'
import { fetchUsers } from '../services/userService'

const props = withDefaults(defineProps<{
  modelValue: TaskUser | null
  label?: string
  disabled?: boolean
  clearable?: boolean
  emptyLabel?: string
}>(), {
  label: 'User',
  disabled: false,
  clearable: true,
  emptyLabel: 'No users found',
})

const emit = defineEmits<{
  'update:modelValue': [value: TaskUser | null]
}>()

const items = ref<TaskUser[]>([])
const loading = ref(false)
const search = ref('')
const internalValue = ref<TaskUser | null>(props.modelValue)
const lookupCache = new Map<string, TaskUser[]>()
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const cacheUser = (user: TaskUser | null) => {
  if (!user) return

  const normalizedName = user.name?.trim().toLowerCase()
  if (!normalizedName) return

  const existing = lookupCache.get(normalizedName) ?? []
  if (existing.some((item) => item.id === user.id)) return

  lookupCache.set(normalizedName, [user, ...existing])
}

const ensureCurrentInItems = (user: TaskUser | null) => {
  if (!user) return

  cacheUser(user)

  if (!items.value.some((item) => item.id === user.id)) {
    items.value = [user, ...items.value]
  }
}

const loadUsers = async (query = '') => {
  const keyword = query.trim()
  const cacheKey = keyword.toLowerCase()

  if (!keyword) {
    items.value = []
    ensureCurrentInItems(internalValue.value)
    return
  }

  if (lookupCache.has(cacheKey)) {
    items.value = lookupCache.get(cacheKey) ?? []
    ensureCurrentInItems(internalValue.value)
    return
  }

  loading.value = true

  try {
    const users = await fetchUsers({ q: keyword })
    lookupCache.set(cacheKey, users)
    items.value = users
    ensureCurrentInItems(internalValue.value)
  }
  finally {
    loading.value = false
  }
}

watch(() => props.modelValue, (value) => {
  internalValue.value = value
  ensureCurrentInItems(value)
})

watch(internalValue, (value) => {
  emit('update:modelValue', value)
})

watch(search, (value) => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }

  if (!value.trim()) {
    items.value = []
    ensureCurrentInItems(internalValue.value)
    return
  }

  debounceTimer = setTimeout(() => {
    void loadUsers(value)
  }, 750)
})

onMounted(() => {
  ensureCurrentInItems(internalValue.value)
})
</script>

<template>
  <v-autocomplete
    v-model="internalValue"
    v-model:search="search"
    :items="items"
    :label="label"
    item-title="name"
    return-object
    :loading="loading"
    :disabled="disabled"
    :clearable="clearable"
    :no-data-text="search ? emptyLabel : 'Type to search users'"
    prepend-inner-icon="mdi-account-search-outline"
    variant="outlined"
  />
</template>
