<script setup lang="ts">
import { computed, ref } from 'vue'
import { ApiError } from '../api/client'
import { deleteTask } from '../services/taskService'

const props = withDefaults(defineProps<{
  taskId: number
  taskTitle?: string | null
  iconOnly?: boolean
  buttonText?: string
  buttonSize?: 'x-small' | 'small' | 'default' | 'large' | 'x-large'
  buttonVariant?: 'text' | 'outlined' | 'tonal' | 'flat' | 'elevated' | 'plain'
}>(), {
  taskTitle: null,
  iconOnly: false,
  buttonText: 'Delete',
  buttonSize: 'small',
  buttonVariant: 'text',
})

const emit = defineEmits<{
  deleted: [taskId: number]
}>()

const dialog = ref(false)
const loading = ref(false)
const errorMessage = ref('')

const titleForDisplay = computed(() => {
  if (!props.taskTitle || !props.taskTitle.trim()) {
    return 'this task'
  }

  return `'${props.taskTitle.trim()}'`
})

const openDialog = () => {
  errorMessage.value = ''
  dialog.value = true
}

const closeDialog = () => {
  if (loading.value) return
  dialog.value = false
}

const confirmDelete = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    await deleteTask(props.taskId)
    dialog.value = false
    emit('deleted', props.taskId)
  }
  catch (error) {
    errorMessage.value = error instanceof ApiError ? error.message : 'Cannot delete task.'
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="d-inline-block">
    <v-btn
      color="error"
      :size="buttonSize"
      :variant="buttonVariant"
      :icon="iconOnly"
      @click="openDialog"
    >
      <template v-if="iconOnly">
        <v-icon icon="mdi-delete" />
      </template>

      <template v-else>
        <v-icon class="mr-2" icon="mdi-delete" />
        {{ buttonText }}
      </template>
    </v-btn>

    <v-dialog v-model="dialog" max-width="520">
      <v-card>
        <v-card-title class="text-h6">Delete task</v-card-title>
        <v-card-text>
          <div>
            Are you sure you want to delete {{ titleForDisplay }}?
          </div>

          <v-alert
            v-if="errorMessage"
            type="error"
            variant="tonal"
            class="mt-4"
            :text="errorMessage"
          />
        </v-card-text>

        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            :disabled="loading"
            @click="closeDialog"
          >
            No
          </v-btn>
          <v-btn
            color="error"
            :loading="loading"
            @click="confirmDelete"
          >
            Yes, I'm sure
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
