import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'

import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'

const lightTheme = {
  dark: false,
  colors: {
    background: '#f8fafc',
    surface: '#ffffff',
    primary: '#4f46e5',
    'primary-darken-1': '#4338ca',
    secondary: '#64748b',
    success: '#16a34a',
    warning: '#f59e0b',
    error: '#dc2626',
    info: '#0ea5e9',
  },
}

export const vuetify = createVuetify({
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },
  theme: {
    defaultTheme: 'lightTheme',
    themes: {
      lightTheme,
    },
  },
})