import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/',
      redirect: '/dashboard',
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/transaksi',
      name: 'transaksi',
      component: () => import('@/views/TransaksiView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/mutasi',
      name: 'mutasi',
      component: () => import('@/views/MutasiView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/penanggung-jawab',
      name: 'penanggung-jawab',
      component: () => import('@/views/PenanggungJawabView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/akun',
      name: 'akun',
      component: () => import('@/views/AkunView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/history-riil',
      name: 'history-riil',
      component: () => import('@/views/HistoryRiilView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/laporan',
      name: 'laporan',
      component: () => import('@/views/LaporanView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/ProfileView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('@/views/UsersView.vue'),
      meta: { requiresAuth: true, requiresBendahara: true },
    },
  ],
})

router.beforeEach((to, from, next) => {
  const { isAuthenticated, isBendahara } = useAuth()

  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.guestOnly && isAuthenticated.value) {
    return next({ name: 'dashboard' })
  }

  if (to.meta.requiresBendahara && !isBendahara.value) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
