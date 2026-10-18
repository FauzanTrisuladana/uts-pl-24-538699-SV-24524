<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import authService from '@/services/authService'
import {
  LayoutDashboard,
  ArrowLeftRight,
  ArrowRightLeft,
  UserCheck,
  CreditCard,
  History,
  FileText,
  Users,
  User as UserIcon,
  LogOut,
  Menu,
  X,
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const { user, isBendahara, clearAuth } = useAuth()

const isMobileMenuOpen = ref(false)
const isLoggingOut = ref(false)

const navigationItems = [
  { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
  { name: 'Transaksi Keuangan', path: '/transaksi', icon: ArrowLeftRight },
  { name: 'Mutasi Rekening', path: '/mutasi', icon: ArrowRightLeft },
  { name: 'Penanggung Jawab', path: '/penanggung-jawab', icon: UserCheck },
  { name: 'Akun Keuangan', path: '/akun', icon: CreditCard },
  { name: 'History Riil', path: '/history-riil', icon: History },
  { name: 'Laporan Keuangan', path: '/laporan', icon: FileText },
  { name: 'Manajemen Pengguna', path: '/users', icon: Users, bendaharaOnly: true },
  { name: 'Profil Saya', path: '/profile', icon: UserIcon },
]

async function handleLogout() {
  if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
    isLoggingOut.value = true
    try {
      await authService.logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      clearAuth()
      isLoggingOut.value = false
      router.push({ name: 'login' })
    }
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex flex-col md:flex-row">
    <!-- Mobile Header -->
    <header class="md:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-40">
      <div class="flex items-center space-x-3">
        <img src="/Logo.webp" alt="Logo" class="w-8 h-8 object-contain" />
        <span class="font-bold text-slate-900 text-sm">Akuntansi Pemuda</span>
      </div>
      <button
        @click="isMobileMenuOpen = !isMobileMenuOpen"
        class="p-2 text-slate-600 hover:text-slate-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
        <X v-else class="w-6 h-6" />
      </button>
    </header>

    <!-- Sidebar Navigation -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-100 flex flex-col transition-transform duration-200 ease-in-out md:static md:translate-x-0',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
      ]"
    >
      <!-- Brand Logo & Title -->
      <div class="p-5 border-b border-slate-800 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <img src="/Logo.webp" alt="Logo" class="w-9 h-9 object-contain drop-shadow" />
          <div>
            <h1 class="font-bold text-base tracking-tight text-white leading-tight">Akuntansi Pemuda</h1>
            <p class="text-xs text-slate-400">Nogotirto V</p>
          </div>
        </div>
        <button
          @click="isMobileMenuOpen = false"
          class="md:hidden text-slate-400 hover:text-white"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- User Info Badge -->
      <div class="px-5 py-3.5 bg-slate-950/60 border-b border-slate-800/80 flex items-center justify-between">
        <div class="truncate">
          <p class="text-xs font-semibold text-white truncate">{{ user?.name || 'User' }}</p>
          <p class="text-[11px] text-slate-400 truncate">{{ user?.email || '' }}</p>
        </div>
        <span
          :class="[
            'text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider',
            isBendahara ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30'
          ]"
        >
          {{ user?.role || 'Warga' }}
        </span>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <template v-for="item in navigationItems" :key="item.path">
          <RouterLink
            v-if="!item.bendaharaOnly || isBendahara"
            :to="item.path"
            @click="isMobileMenuOpen = false"
            :class="[
              'flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
              route.path.startsWith(item.path)
                ? 'bg-blue-600 text-white font-semibold shadow-sm shadow-blue-500/20'
                : 'text-slate-300 hover:bg-slate-800 hover:text-white',
            ]"
          >
            <component :is="item.icon" class="w-4 h-4 shrink-0" />
            <span class="truncate">{{ item.name }}</span>
          </RouterLink>
        </template>
      </nav>

      <!-- Logout Button Footer -->
      <div class="p-3 border-t border-slate-800">
        <button
          @click="handleLogout"
          :disabled="isLoggingOut"
          class="w-full flex items-center justify-center space-x-2 px-3 py-2.5 rounded-lg text-sm font-medium text-red-300 bg-red-950/30 hover:bg-red-900/50 hover:text-red-200 border border-red-800/40 transition-colors disabled:opacity-50"
        >
          <LogOut class="w-4 h-4 shrink-0" />
          <span>{{ isLoggingOut ? 'Keluar...' : 'Keluar Sistem' }}</span>
        </button>
      </div>
    </aside>

    <!-- Overlay backdrop for mobile menu -->
    <div
      v-if="isMobileMenuOpen"
      @click="isMobileMenuOpen = false"
      class="fixed inset-0 bg-slate-900/60 z-40 md:hidden backdrop-blur-xs"
    />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
      <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
