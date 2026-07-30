import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'

export default createVuetify({
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
        },
        variables: {
          'border-color': '#e2e8f0',
          'border-opacity': 1,
          'high-emphasis-opacity': 0.87,
          'medium-emphasis-opacity': 0.60,
          'disabled-opacity': 0.38,
          'idle-opacity': 0.04,
          'hover-opacity': 0.04,
          'focus-opacity': 0.12,
          'selected-opacity': 0.08,
          'activated-opacity': 0.12,
          'pressed-opacity': 0.12,
          'dragged-opacity': 0.08,
          'theme-kbd': '#212529',
          'theme-on-kbd': '#FFFFFF',
          'theme-code': '#F5F5F5',
          'theme-on-code': '#000000',
        }
      },
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
    }
  }
})
