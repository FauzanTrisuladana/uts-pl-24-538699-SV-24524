<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import authService from '@/services/authService'
import { AlertCircle, Loader2, Lock, Mail } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const { setAuth } = useAuth()

const email = ref('')
const password = ref('')
const isLoading = ref(false)
const errorMessage = ref('')

async function handleSubmit() {
  if (!email.value || !password.value) {
    errorMessage.value = 'Silakan isi email dan password.'
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await authService.login({
      email: email.value,
      password: password.value,
    })

    setAuth(response.auth.token, response.data)

    const redirectPath = (route.query.redirect as string) || '/dashboard'
    router.push(redirectPath)
  } catch (err: any) {
    errorMessage.value =
      err.response?.data?.message ||
      err.message ||
      'Login gagal. Periksa kembali email dan password Anda.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="relative min-h-screen flex items-center justify-center p-4 sm:p-6 overflow-hidden">
    <!-- Background Image with Overlay -->
    <div
      class="absolute inset-0 bg-cover bg-center bg-no-repeat blur-[3px] scale-105"
      style="background-image: url('/ngt5.webp');"
    />
    <div class="absolute inset-0 bg-slate-950/75" />

    <!-- Login Card Container -->
    <div class="relative w-full max-w-md z-10">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
        <!-- Header Banner with Logo -->
        <div class="p-6 sm:p-8 text-center border-b border-slate-100 bg-linear-to-b from-slate-50 to-white">
          <div class="inline-flex items-center justify-center p-2 bg-white rounded-2xl shadow-md border border-slate-100 mb-4">
            <img src="/Logo.webp" alt="Logo Nogotirto V" class="w-16 h-16 object-contain" />
          </div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900">Keuangan Nogotirto V</h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-1.5 max-w-xs mx-auto">
            Sistem Informasi Pengelolaan Akuntansi & Kas Pemuda Nogotirto V
          </p>
        </div>

        <!-- Form Area -->
        <div class="p-6 sm:p-8">
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Error Alert -->
            <div
              v-if="errorMessage"
              class="flex items-start space-x-2.5 p-3.5 bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm rounded-xl"
            >
              <AlertCircle class="w-5 h-5 shrink-0 text-red-500" />
              <span>{{ errorMessage }}</span>
            </div>

            <!-- Email Field -->
            <div>
              <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Alamat Email
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <Mail class="w-4 h-4" />
                </div>
                <input
                  id="email"
                  v-model="email"
                  type="email"
                  required
                  placeholder="fauzantrisuladana@gmail.com"
                  :disabled="isLoading"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all disabled:opacity-60"
                />
              </div>
            </div>

            <!-- Password Field -->
            <div>
              <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <Lock class="w-4 h-4" />
                </div>
                <input
                  id="password"
                  v-model="password"
                  type="password"
                  required
                  placeholder="••••••••"
                  :disabled="isLoading"
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all disabled:opacity-60"
                />
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="isLoading"
              class="w-full mt-2 flex items-center justify-center space-x-2 py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-blue-500/25 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 cursor-pointer"
            >
              <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
              <span>{{ isLoading ? 'Memverifikasi...' : 'Masuk ke Dashboard' }}</span>
            </button>
          </form>

          <!-- System Info Footer -->
          <div class="mt-6 text-center text-xs text-slate-400 border-t border-slate-100 pt-4">
            UTS Konstruksi dan Evolusi Perangkat Lunak 2026
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
