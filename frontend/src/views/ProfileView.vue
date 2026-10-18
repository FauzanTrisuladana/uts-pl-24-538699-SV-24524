<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useAuth } from '@/composables/useAuth'
import profileService from '@/services/profileService'
import {
  User as UserIcon,
  Shield,
  KeyRound,
  Trash2,
  CheckCircle2,
  AlertCircle,
  Loader2,
} from 'lucide-vue-next'

const router = useRouter()
const { user, setUser, clearAuth } = useAuth()

// Profile Form State
const name = ref(user.value?.name || '')
const username = ref(user.value?.username || '')
const email = ref(user.value?.email || '')
const isProfileLoading = ref(false)
const profileSuccess = ref('')
const profileError = ref('')

// Password Form State
const currentPassword = ref('')
const newPassword = ref('')
const passwordConfirmation = ref('')
const isPasswordLoading = ref(false)
const passwordSuccess = ref('')
const passwordError = ref('')

// Delete Account State
const isDeleteLoading = ref(false)

onMounted(async () => {
  try {
    const res = await profileService.getProfile()
    setUser(res.data)
    name.value = res.data.name
    username.value = res.data.username
    email.value = res.data.email
  } catch (err) {
    console.error('Failed to load profile:', err)
  }
})

async function handleUpdateProfile() {
  isProfileLoading.value = true
  profileSuccess.value = ''
  profileError.value = ''

  try {
    const res = await profileService.updateProfile({
      name: name.value,
      username: username.value,
      email: email.value,
    })
    setUser(res.data)
    profileSuccess.value = 'Profil berhasil diperbarui.'
  } catch (err: any) {
    profileError.value =
      err.response?.data?.message || err.message || 'Gagal memperbarui profil.'
  } finally {
    isProfileLoading.value = false
  }
}

async function handleUpdatePassword() {
  if (newPassword.value !== passwordConfirmation.value) {
    passwordError.value = 'Konfirmasi password baru tidak cocok.'
    return
  }

  isPasswordLoading.value = true
  passwordSuccess.value = ''
  passwordError.value = ''

  try {
    await profileService.updatePassword({
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: passwordConfirmation.value,
    })
    passwordSuccess.value = 'Password berhasil diubah.'
    currentPassword.value = ''
    newPassword.value = ''
    passwordConfirmation.value = ''
  } catch (err: any) {
    passwordError.value =
      err.response?.data?.message || err.message || 'Gagal mengubah password.'
  } finally {
    isPasswordLoading.value = false
  }
}

async function handleDeleteAccount() {
  if (
    confirm(
      'PERINGATAN: Apakah Anda benar-benar yakin ingin menghapus akun Anda? Tindakan ini tidak dapat dibatalkan.',
    )
  ) {
    isDeleteLoading.value = true
    try {
      await profileService.deleteAccount()
      clearAuth()
      router.push({ name: 'login' })
    } catch (err: any) {
      alert(
        err.response?.data?.message || err.message || 'Gagal menghapus akun.',
      )
    } finally {
      isDeleteLoading.value = false
    }
  }
}
</script>

<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900">Profil Saya</h1>
          <p class="text-sm text-slate-500">Kelola informasi data akun dan keamanan login Anda.</p>
        </div>
      </div>

      <!-- User Card Summary -->
      <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
          <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl shadow-xs">
            {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-900">{{ user?.name }}</h2>
            <p class="text-sm text-slate-500">@{{ user?.username }} • {{ user?.email }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span
            :class="[
              'px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider',
              user?.role === 'bendahara' || user?.role === 'admin'
                ? 'bg-amber-100 text-amber-800 border border-amber-200'
                : 'bg-blue-100 text-blue-800 border border-blue-200',
            ]"
          >
            <Shield class="w-3.5 h-3.5 inline mr-1" />
            {{ user?.role }}
          </span>
          <span class="px-3 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-full text-xs font-semibold">
            {{ user?.status || 'Aktif' }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Update Profile Information -->
        <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 flex flex-col justify-between">
          <div>
            <div class="flex items-center space-x-2.5 pb-4 mb-4 border-b border-slate-100">
              <UserIcon class="w-5 h-5 text-blue-600" />
              <h3 class="font-bold text-base text-slate-900">Informasi Pribadi</h3>
            </div>

            <form @submit.prevent="handleUpdateProfile" class="space-y-4">
              <!-- Alerts -->
              <div v-if="profileSuccess" class="flex items-center space-x-2 p-3 bg-emerald-50 text-emerald-700 text-xs rounded-xl border border-emerald-200">
                <CheckCircle2 class="w-4 h-4 shrink-0" />
                <span>{{ profileSuccess }}</span>
              </div>
              <div v-if="profileError" class="flex items-center space-x-2 p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200">
                <AlertCircle class="w-4 h-4 shrink-0" />
                <span>{{ profileError }}</span>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input
                  v-model="name"
                  type="text"
                  required
                  class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Username</label>
                <input
                  v-model="username"
                  type="text"
                  required
                  class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                <input
                  v-model="email"
                  type="email"
                  required
                  class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
              </div>

              <button
                type="submit"
                :disabled="isProfileLoading"
                class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl transition-colors disabled:opacity-50 cursor-pointer shadow-xs"
              >
                <Loader2 v-if="isProfileLoading" class="w-4 h-4 animate-spin" />
                <span>{{ isProfileLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
              </button>
            </form>
          </div>
        </div>

        <!-- Update Password -->
        <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 flex flex-col justify-between">
          <div>
            <div class="flex items-center space-x-2.5 pb-4 mb-4 border-b border-slate-100">
              <KeyRound class="w-5 h-5 text-amber-600" />
              <h3 class="font-bold text-base text-slate-900">Ubah Password</h3>
            </div>

            <form @submit.prevent="handleUpdatePassword" class="space-y-4">
              <!-- Alerts -->
              <div v-if="passwordSuccess" class="flex items-center space-x-2 p-3 bg-emerald-50 text-emerald-700 text-xs rounded-xl border border-emerald-200">
                <CheckCircle2 class="w-4 h-4 shrink-0" />
                <span>{{ passwordSuccess }}</span>
              </div>
              <div v-if="passwordError" class="flex items-center space-x-2 p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200">
                <AlertCircle class="w-4 h-4 shrink-0" />
                <span>{{ passwordError }}</span>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Password Saat Ini</label>
                <input
                  v-model="currentPassword"
                  type="password"
                  required
                  placeholder="••••••••"
                  class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Password Baru</label>
                <input
                  v-model="newPassword"
                  type="password"
                  required
                  placeholder="Minimal 8 karakter"
                  class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                <input
                  v-model="passwordConfirmation"
                  type="password"
                  required
                  placeholder="Ulangi password baru"
                  class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
              </div>

              <button
                type="submit"
                :disabled="isPasswordLoading"
                class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm rounded-xl transition-colors disabled:opacity-50 cursor-pointer shadow-xs"
              >
                <Loader2 v-if="isPasswordLoading" class="w-4 h-4 animate-spin" />
                <span>{{ isPasswordLoading ? 'Memperbarui...' : 'Perbarui Password' }}</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Danger Zone: Delete Account -->
      <div class="bg-red-50/50 rounded-2xl p-6 border border-red-200/80">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <h4 class="text-sm font-bold text-red-900 flex items-center gap-1.5">
              <Trash2 class="w-4 h-4 text-red-600" />
              Zona Berbahaya: Hapus Akun
            </h4>
            <p class="text-xs text-red-600 mt-1">
              Menghapus akun Anda secara permanen beserta sesi yang sedang aktif.
            </p>
          </div>
          <button
            @click="handleDeleteAccount"
            :disabled="isDeleteLoading"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors disabled:opacity-50 cursor-pointer"
          >
            {{ isDeleteLoading ? 'Menghapus...' : 'Hapus Akun Saya' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
