import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/equipment',
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('../pages/Login.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/equipment',
      name: 'EquipmentList',
      component: () => import('../pages/EquipmentList.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/equipment/:id',
      name: 'EquipmentDetails',
      component: () => import('../pages/EquipmentDetails.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/reservations',
      name: 'ReservationsList',
      component: () => import('../pages/ReservationsList.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/reservations/:id',
      name: 'ReservationDetails',
      component: () => import('../pages/ReservationDetails.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

// Route Protection Navigation Guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guestOnly && authStore.isAuthenticated) {
    next('/equipment')
  } else {
    next()
  }
})

export default router
