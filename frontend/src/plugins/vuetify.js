import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'
import { es } from 'vuetify/locale'

export default createVuetify({
  locale: {
    locale: 'es',
    messages: { es }
  },
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        dark: false,
        colors: {
          background: '#f8fafc',
          surface: '#ffffff',
          primary: '#8b5cf6',
          secondary: '#0ea5e9',
          error: '#ef4444',
          info: '#3b82f6',
          success: '#10b981',
          warning: '#f59e0b',
          // Custom semantic colors
          abierto: '#3b82f6',
          en_progreso: '#f59e0b',
          cerrado: '#10b981',
          vencido: '#ef4444',
          baja: '#10b981',
          media: '#3b82f6',
          alta: '#f59e0b',
          critica: '#ef4444'
        },
        variables: {
          'border-color': '#e2e8f0',
          'border-opacity': 1,
        }
      },
      dark: {
        dark: true,
        colors: {
          background: '#0f172a',
          surface: '#1e293b',
          primary: '#a78bfa',
          secondary: '#38bdf8',
          error: '#f87171',
          info: '#60a5fa',
          success: '#34d399',
          warning: '#fbbf24',
          // Custom semantic colors
          abierto: '#60a5fa',
          en_progreso: '#fbbf24',
          cerrado: '#34d399',
          vencido: '#f87171',
          baja: '#34d399',
          media: '#60a5fa',
          alta: '#fbbf24',
          critica: '#f87171'
        },
        variables: {
          'border-color': '#334155',
          'border-opacity': 1,
        }
      }
    },
  },
  defaults: {
    VCard: {
      rounded: 'xl',
      elevation: 0,
    },
    VBtn: {
      rounded: 'lg',
      elevation: 0,
      class: 'text-none font-weight-medium'
    },
    VTextField: {
      variant: 'outlined',
      density: 'comfortable',
      rounded: 'lg',
      color: 'primary'
    },
    VSelect: {
      variant: 'outlined',
      density: 'comfortable',
      rounded: 'lg',
      color: 'primary'
    },
    VChip: {
      variant: 'tonal',
      size: 'small',
      class: 'text-capitalize font-weight-medium'
    }
  }
})
