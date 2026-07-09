import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { isAuthenticated } from '../services/authService'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/tasks',
    name: 'tasks',
    component: () => import('../views/TasksView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/tasks/create',
    name: 'tasks-create',
    component: () => import('../views/TaskCreateView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/tasks/:id/edit',
    name: 'tasks-edit',
    component: () => import('../views/TaskEditView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to) => {
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && isAuthenticated.value) {
    return { name: 'tasks' }
  }

  return true
})

export default router