<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import userService from '@/services/userService'
import type { User } from '@/types'
import {
  Users,
  Plus,
  Search,
  Trash2,
  Loader2,
  X,
  ChevronLeft,
  ChevronRight,
  Shield,
  UserCog,
  Power,
  UserCheck,
} from 'lucide-vue-next'

const items = ref<User[]>([])
const isLoading = ref(false)
const search = ref('')
const roleFilter = ref<string>('')
const statusFilter = ref<string>('')

const currentPage = ref(1)
const perPage = ref(10)
const totalPages = ref(1)
const totalItems = ref(0)

// Add Modal State
const isAddModalOpen = ref(false)
const formName = ref('')
const formUsername = ref('')
const formEmail = ref('')
const formRole = ref('warga')
const formStatus = ref('Aktif')
const formPassword = ref('')
const isAddLoading = ref(false)
const addError = ref('')

// Edit Role Modal State
const isRoleModalOpen = ref(false)
const editUserId = ref<number | null>(null)
const editUserName = ref('')
const editUserRole = ref('warga')
const isRoleLoading = ref(false)
const roleError = ref('')

// Toggling / Deleting State
const actionLoadingId = ref<number | null>(null)

async function fetchData(page = 1) {
  isLoading.value = true
  currentPage.value = page
  try {
    const res = await userService.list({
      search: search.value || undefined,
      role: roleFilter.value || undefined,
      status: statusFilter.value || undefined,
      page: currentPage.value,
      per_page: perPage.value,
    })
    items.value = res.data
    if (res.meta) {
      totalPages.value = res.meta.last_page
      totalItems.value = res.meta.total
    }
  } catch (err) {
    console.error('Failed to fetch users:', err)
  } finally {
    isLoading.value = false
  }
}

function handleSearch() {
  fetchData(1)
}

function resetFilters() {
  search.value = ''
  roleFilter.value = ''
  statusFilter.value = ''
  fetchData(1)
}

function openAddModal() {
  formName.value = ''
  formUsername.value = ''
  formEmail.value = ''
  formRole.value = 'warga'
  formStatus.value = 'Aktif'
  formPassword.value = ''
  addError.value = ''
  isAddModalOpen.value = true
}

function closeAddModal() {
  isAddModalOpen.value = false
}

async function handleCreateUser() {
  if (!formName.value || !formUsername.value || !formEmail.value) {
    addError.value = 'Nama, username, dan email wajib diisi.'
    return
  }

  isAddLoading.value = true
  addError.value = ''

  try {
    await userService.create({
      name: formName.value,
      username: formUsername.value,
      email: formEmail.value,
      role: formRole.value,
      status: formStatus.value,
      password: formPassword.value || undefined,
    })
    closeAddModal()
    fetchData(currentPage.value)
  } catch (err: any) {
    addError.value =
      err.response?.data?.message || err.message || 'Gagal menambahkan user baru.'
  } finally {
    isAddLoading.value = false
  }
}

function openRoleModal(user: User) {
  editUserId.value = user.id
  editUserName.value = user.name
  editUserRole.value = user.role
  roleError.value = ''
  isRoleModalOpen.value = true
}

function closeRoleModal() {
  isRoleModalOpen.value = false
}

async function handleUpdateRole() {
  if (!editUserId.value) return
  isRoleLoading.value = true
  roleError.value = ''

  try {
    await userService.updateRole(editUserId.value, editUserRole.value)
    closeRoleModal()
    fetchData(currentPage.value)
  } catch (err: any) {
    roleError.value =
      err.response?.data?.message || err.message || 'Gagal mengubah role.'
  } finally {
    isRoleLoading.value = false
  }
}

async function handleToggleStatus(user: User) {
  const nextStatus = user.status === 'Aktif' ? 'Nonaktif' : 'Aktif'
  if (confirm(`Ubah status pengguna "${user.name}" menjadi "${nextStatus}"?`)) {
    actionLoadingId.value = user.id
    try {
      await userService.toggleStatus(user.id)
      fetchData(currentPage.value)
    } catch (err: any) {
      alert(err.response?.data?.message || err.message || 'Gagal mengubah status.')
    } finally {
      actionLoadingId.value = null
    }
  }
}

async function handleDeleteUser(user: User) {
  if (confirm(`Apakah Anda yakin ingin menghapus pengguna "${user.name}" (@${user.username})?`)) {
    actionLoadingId.value = user.id
    try {
      await userService.delete(user.id)
      fetchData(currentPage.value)
    } catch (err: any) {
      alert(err.response?.data?.message || err.message || 'Gagal menghapus pengguna.')
    } finally {
      actionLoadingId.value = null
    }
  }
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
            <Users class="w-6 h-6 text-blue-600" />
            Manajemen Pengguna
          </h1>
          <p class="text-sm text-slate-500">Kelola hak akses role, status keanggotaan, dan akun warga pemuda.</p>
        </div>
        <button
          @click="openAddModal"
          class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-xs transition-colors cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Pengguna</span>
        </button>
      </div>

      <!-- Filter Controls Panel -->
      <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Filter Pengguna</span>
          <button
            @click="resetFilters"
            class="text-xs text-blue-600 hover:text-blue-800 font-medium cursor-pointer"
          >
            Reset Filter
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
          <div class="relative sm:col-span-2">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="search"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Cari nama, username, atau email..."
              class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <!-- Role Filter -->
          <select
            v-model="roleFilter"
            @change="handleSearch"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
            <option value="">Semua Role</option>
            <option value="bendahara">Bendahara</option>
            <option value="warga">Warga</option>
            <option value="admin">Admin</option>
          </select>

          <!-- Status Filter -->
          <select
            v-model="statusFilter"
            @change="handleSearch"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
            <option value="">Semua Status</option>
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
          </select>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
              <tr>
                <th class="px-6 py-3.5 w-16">No</th>
                <th class="px-6 py-3.5">Pengguna</th>
                <th class="px-6 py-3.5">Role</th>
                <th class="px-6 py-3.5 text-center">Status Akun</th>
                <th class="px-6 py-3.5 text-right w-44">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading">
                <td colspan="5" class="text-center py-12">
                  <Loader2 class="w-6 h-6 animate-spin mx-auto text-blue-600 mb-2" />
                  <span class="text-xs text-slate-400">Memuat daftar pengguna...</span>
                </td>
              </tr>
              <tr v-else-if="items.length === 0">
                <td colspan="5" class="text-center py-12 text-slate-400 text-sm">
                  Tidak ada pengguna ditemukan.
                </td>
              </tr>
              <tr v-for="(u, idx) in items" :key="u.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 text-slate-400 text-xs">
                  {{ (currentPage - 1) * perPage + idx + 1 }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">
                      {{ u.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="font-bold text-slate-900">{{ u.name }}</p>
                      <p class="text-xs text-slate-400">@{{ u.username }} • {{ u.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2.5 py-0.5 rounded-lg text-xs font-semibold uppercase',
                      u.role === 'bendahara' || u.role === 'admin'
                        ? 'bg-amber-100 text-amber-800 border border-amber-200'
                        : 'bg-blue-100 text-blue-800 border border-blue-200',
                    ]"
                  >
                    <Shield class="w-3 h-3 inline mr-1" />
                    {{ u.role }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span
                    :class="[
                      'px-2.5 py-0.5 rounded-full text-xs font-semibold',
                      u.status === 'Aktif'
                        ? 'bg-emerald-100 text-emerald-800 border border-emerald-200'
                        : 'bg-slate-100 text-slate-600 border border-slate-200',
                    ]"
                  >
                    {{ u.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right whitespace-nowrap">
                  <div class="inline-flex items-center gap-1">
                    <!-- Ubah Role -->
                    <button
                      @click="openRoleModal(u)"
                      class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors cursor-pointer"
                      title="Ubah Role"
                    >
                      <UserCog class="w-4 h-4" />
                    </button>
                    <!-- Toggle Status -->
                    <button
                      @click="handleToggleStatus(u)"
                      :disabled="actionLoadingId === u.id"
                      :class="[
                        'p-1.5 rounded-lg transition-colors cursor-pointer',
                        u.status === 'Aktif'
                          ? 'text-slate-500 hover:text-rose-600 hover:bg-rose-50'
                          : 'text-slate-500 hover:text-emerald-600 hover:bg-emerald-50',
                      ]"
                      :title="u.status === 'Aktif' ? 'Nonaktifkan Akun' : 'Aktifkan Akun'"
                    >
                      <Power class="w-4 h-4" />
                    </button>
                    <!-- Hapus User -->
                    <button
                      @click="handleDeleteUser(u)"
                      :disabled="actionLoadingId === u.id"
                      class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                      title="Hapus Pengguna"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
          <div>
            Menampilkan <span class="font-bold text-slate-700">{{ items.length }}</span> dari <span class="font-bold text-slate-700">{{ totalItems }}</span> pengguna
          </div>
          <div class="flex items-center space-x-2">
            <button
              :disabled="currentPage <= 1 || isLoading"
              @click="fetchData(currentPage - 1)"
              class="p-1.5 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-40 transition-colors cursor-pointer"
            >
              <ChevronLeft class="w-4 h-4" />
            </button>
            <span class="px-2 font-medium">Halaman {{ currentPage }} / {{ totalPages || 1 }}</span>
            <button
              :disabled="currentPage >= totalPages || isLoading"
              @click="fetchData(currentPage + 1)"
              class="p-1.5 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-40 transition-colors cursor-pointer"
            >
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Add User Modal Dialog -->
      <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div @click="closeAddModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" />
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-10 my-8">
          <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-base text-slate-900">Tambah Pengguna Baru</h3>
            <button @click="closeAddModal" class="text-slate-400 hover:text-slate-600">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="handleCreateUser" class="p-6 space-y-4">
            <div v-if="addError" class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200">
              {{ addError }}
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
              <input
                v-model="formName"
                type="text"
                required
                placeholder="Contoh: Muhammad Ilham"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Username *</label>
              <input
                v-model="formUsername"
                type="text"
                required
                placeholder="Contoh: ilham_pemuda"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email *</label>
              <input
                v-model="formEmail"
                type="email"
                required
                placeholder="ilham@example.com"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Role *</label>
                <select
                  v-model="formRole"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
                  <option value="warga">Warga</option>
                  <option value="bendahara">Bendahara</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Akun *</label>
                <select
                  v-model="formStatus"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
                  <option value="Aktif">Aktif</option>
                  <option value="Nonaktif">Nonaktif</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Password Awal (Opsional)</label>
              <input
                v-model="formPassword"
                type="password"
                placeholder="Default: password"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
              <p class="text-[11px] text-slate-400 mt-1">Jika dikosongkan, password awal otomatis "password".</p>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
              <button
                type="button"
                @click="closeAddModal"
                class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-sm font-semibold transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="isAddLoading"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors disabled:opacity-50 cursor-pointer flex items-center space-x-1.5"
              >
                <Loader2 v-if="isAddLoading" class="w-4 h-4 animate-spin" />
                <span>{{ isAddLoading ? 'Menyimpan...' : 'Tambah Pengguna' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Role Modal Dialog -->
      <div v-if="isRoleModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="closeRoleModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" />
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-10">
          <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-base text-slate-900">Ubah Role Pengguna</h3>
            <button @click="closeRoleModal" class="text-slate-400 hover:text-slate-600">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="handleUpdateRole" class="p-6 space-y-4">
            <div v-if="roleError" class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200">
              {{ roleError }}
            </div>

            <p class="text-xs text-slate-600">
              Ubah kewenangan hak akses untuk pengguna <strong class="text-slate-900">{{ editUserName }}</strong>:
            </p>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Role</label>
              <select
                v-model="editUserRole"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option value="warga">Warga (Akses View)</option>
                <option value="bendahara">Bendahara (Akses Penuh CRUD)</option>
              </select>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
              <button
                type="button"
                @click="closeRoleModal"
                class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-sm font-semibold transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="isRoleLoading"
                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition-colors disabled:opacity-50 cursor-pointer flex items-center space-x-1.5"
              >
                <Loader2 v-if="isRoleLoading" class="w-4 h-4 animate-spin" />
                <span>Simpan Role</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
