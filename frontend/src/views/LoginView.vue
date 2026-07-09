<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ApiError } from '../api/client'
import { signIn } from '../services/authService'

const router = useRouter()
const form = ref<HTMLFormElement | null>(null)
const loading = ref(false)
const errorMessage = ref('')

const credentials = reactive({
  email: '',
  password: '',
})

const rules = {
  required: (value: string) => !!value || 'This field is required',
  email: (value: string) => /.+@.+\..+/.test(value) || 'Enter a valid email address',
}

const canSubmit = computed(() => credentials.email.length > 0 && credentials.password.length > 0)

const submit = async () => {
  const result = await form.value?.validate()

  if (result?.valid) {
    loading.value = true
    errorMessage.value = ''

    try {
      await signIn({ ...credentials })
      await router.push({ name: 'tasks' })
    }
    catch (error) {
      if (error instanceof ApiError) {
        errorMessage.value = error.message
      }
      else {
        errorMessage.value = 'Unable to sign in. Please try again.'
      }
    }
    finally {
      loading.value = false
    }
  }
}
</script>

<template>
  <v-container class="fill-height login-page" fluid>
    <v-row justify="center" align="center">
      <v-col cols="12" sm="10" md="8" lg="5" xl="4">
        <v-card rounded="xl" elevation="12" class="pa-2 pa-sm-4">
          <v-card-text class="pa-6 pa-sm-8">
            <div class="d-flex align-center mb-6">
              <v-avatar color="primary" size="48" class="mr-4">
                <v-icon icon="mdi-view-dashboard" />
              </v-avatar>
              <div>
                <div class="text-h5 font-weight-bold">Welcome back</div>
                <div class="text-body-2 text-medium-emphasis">Sign in to continue to your tasks</div>
              </div>
            </div>

            <v-form ref="form" :disabled="loading" @submit.prevent="submit">
              <v-alert
                v-if="errorMessage"
                type="error"
                variant="tonal"
                class="mb-4"
                :text="errorMessage"
              />

              <v-text-field
                v-model="credentials.email"
                label="Email"
                type="email"
                :disabled="loading"
                prepend-inner-icon="mdi-email-outline"
                :rules="[rules.required, rules.email]"
                variant="outlined"
                class="mb-4"
              />

              <v-text-field
                v-model="credentials.password"
                label="Password"
                type="password"
                :disabled="loading"
                prepend-inner-icon="mdi-lock-outline"
                :rules="[rules.required]"
                variant="outlined"
                class="mb-6"
              />

              <v-btn
                type="submit"
                block
                size="large"
                color="primary"
                :loading="loading"
                :disabled="!canSubmit || loading"
              >
                Sign in
              </v-btn>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>