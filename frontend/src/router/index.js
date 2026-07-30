import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('../components/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('../views/DashboardView.vue'),
      },
      {
        path: 'incidents',
        name: 'Incidents',
        component: () => import('../views/IncidentListView.vue'),
      },
      {
        path: 'incidents/create',
        name: 'CreateIncident',
        component: () => import('../views/IncidentFormView.vue'),
      },
      {
        path: 'incidents/:id/edit',
        name: 'EditIncident',
        component: () => import('../views/IncidentFormView.vue'),
      },
      {
        path: 'incidents/:id',
        name: 'IncidentDetail',
        component: () => import('../views/IncidentDetailView.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && auth.isAuthenticated) {
    next(auth.user?.role === 'admin' ? '/' : '/incidents')
  } else if (to.name === 'Dashboard' && auth.user?.role !== 'admin') {
    next('/incidents')
  } else {
    next()
  }
})

export default router
