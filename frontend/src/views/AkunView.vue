<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import akunService from '@/services/akunService'
import type { Akun } from '@/types'
import {
  CreditCard,
  Plus,
  Search,
  Pencil,
  Trash2,
  Loader2,
  X,
  ChevronLeft,
  ChevronRight,
  Wallet,
} from 'lucide-vue-next'

const items = ref<Akun[]>([])
const isLoading = ref(false)
const search = ref('')
const selectedKas = ref<string>('')
const currentPage = ref(1)
const perPage = ref(10)
const totalPages = ref(1)
const totalItems = ref(0)

const kasOptions = [
  { label: 'Semua Kas', value: '' },
  { label: 'Kas Pemuda', value: 'kas pemuda' },
  { label: 'Kas RTRW', value: 'kas rtrw' },
  { label: 'Kas Kematian', value: 'kas kematian' },
  { label: 'Kas Karang Taruna', value: 'kas karang taruna' },
]

// Modal Form State
const isModalOpen = ref(false)
const isEditing = ref(false)
const editId = ref<number | null>(null)
const formNamaAkun = ref('')
const formKas = ref('kas pemuda')
const isFormLoading = ref(false)
const formError = ref('')

function formatRupiah(amount?: number | null) {
  if (amount === null || amount === undefined) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(amount)
}

async function fetchData(page = 1) {
  isLoading.value = true
  currentPage.value = page
  try {
    const res = await akunService.list({
      search: search.value || undefined,
      kas: selectedKas.value ? [selectedKas.value] : undefined,
      page: currentPage.value,
      per_page: perPage.value,
    })
    items.value = res.data
    if (res.meta) {
      totalPages.value = res.meta.last_page
      totalItems.value = res.meta.total
    }
  } catch (err) {
    console.error('Failed to fetch akun list:', err)
  } finally {
    isLoading.value = false
  }
}

function handleSearch() {
  fetchData(1)
}

function handleFilterKas(kasValue: string) {
  selectedKas.value = kasValue
  fetchData(1)
}

function openAddModal() {
  isEditing.value = false
  editId.value = null
  formNamaAkun.value = ''
  formKas.value = 'kas pemuda'
  formError.value = ''
  isModalOpen.value = true
}

function openEditModal(item: Akun) {
  isEditing.value = true
  editId.value = item.id
  formNamaAkun.value = item.nama_akun
  formKas.value = item.kas
  formError.value = ''
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
}

async function handleSave() {
  if (!formNamaAkun.value) {
    formError.value = 'Nama akun wajib diisi.'
    return
  }

  isFormLoading.value = true
  formError.value = ''

  try {
    if (isEditing.value && editId.value) {
      await akunService.update(editId.value, {
        nama_akun: formNamaAkun.value,
        kas: formKas.value,
      })
    } else {
      await akunService.create({
        nama_akun: formNamaAkun.value,
        kas: formKas.value,
      })
    }
    closeModal()
    fetchData(currentPage.value)
  } catch (err: any) {
    formError.value =
      err.response?.data?.message || err.message || 'Gagal menyimpan akun.'
  } finally {
    isFormLoading.value = false
  }
}

async function handleDelete(item: Akun) {
  if (confirm(`Apakah Anda yakin ingin menghapus akun "${item.nama_akun}"?`)) {
    try {
      await akunService.delete(item.id)
      fetchData(currentPage.value)
    } catch (err: any) {
      alert(err.response?.data?.message || err.message || 'Gagal menghapus akun.')
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
            <CreditCard class="w-6 h-6 text-blue-600" />
            Pengaturan Akun Keuangan
          </h1>
          <p class="text-sm text-slate-500">Kelola rekening bank, dompet kas fisik, dan pos rekening keuangan.</p>
        </div>
        <button
          @click="openAddModal"
          class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-xs transition-colors cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Akun Baru</span>
        </button>
      </div>

      <!-- Filter Tabs by Kas Category -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <button
          v-for="opt in kasOptions"
          :key="opt.value"
          @click="handleFilterKas(opt.value)"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors cursor-pointer',
            selectedKas === opt.value
              ? 'bg-blue-600 text-white shadow-xs'
              : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50',
          ]"
        >
          {{ opt.label }}
        </button>
      </div>

      <!-- Filter / Search Bar -->
      <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200 flex items-center gap-3">
        <div class="relative flex-1">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari nama akun keuangan..."
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
          />
        </div>
        <button
          @click="handleSearch"
          class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm rounded-xl transition-colors cursor-pointer"
        >
          Cari
        </button>
      </div>

      <!-- Data Table -->
      <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
              <tr>
                <th class="px-6 py-3.5 w-16">No</th>
                <th class="px-6 py-3.5">Nama Akun</th>
                <th class="px-6 py-3.5">Kategori Kas</th>
                <th class="px-6 py-3.5 text-right">Saldo Riil Terkini</th>
                <th class="px-6 py-3.5 text-right w-32">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading" class="text-center">
                <td colspan="5" class="py-12">
                  <Loader2 class="w-6 h-6 animate-spin mx-auto text-blue-600 mb-2" />
                  <span class="text-xs text-slate-400">Memuat data...</span>
                </td>
              </tr>
              <tr v-else-if="items.length === 0" class="text-center">
                <td colspan="5" class="py-12 text-slate-400 text-sm">
                  Tidak ada akun keuangan yang ditemukan.
                </td>
              </tr>
              <tr v-for="(item, idx) in items" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 text-slate-400 text-xs">
                  {{ (currentPage - 1) * perPage + idx + 1 }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <div class="p-2 bg-slate-100 text-slate-600 rounded-xl">
                      <Wallet class="w-4 h-4" />
                    </div>
                    <span class="font-bold text-slate-900">{{ item.nama_akun }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-block px-2.5 py-0.5 rounded-lg text-xs font-semibold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                    {{ item.kas }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-bold text-slate-900">
                  {{ formatRupiah(item.jumlah) }}
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="inline-flex items-center gap-1">
                    <button
                      @click="openEditModal(item)"
                      class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                      title="Ubah"
                    >
                      <Pencil class="w-4 h-4" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                      title="Hapus"
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
            Menampilkan <span class="font-bold text-slate-700">{{ items.length }}</span> dari <span class="font-bold text-slate-700">{{ totalItems }}</span> data
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

      <!-- Add/Edit Modal Dialog -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="closeModal" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" />
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-10">
          <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-base text-slate-900">
              {{ isEditing ? 'Ubah Akun Keuangan' : 'Tambah Akun Keuangan Baru' }}
            </h3>
            <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="handleSave" class="p-6 space-y-4">
            <div v-if="formError" class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200">
              {{ formError }}
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Akun *</label>
              <input
                v-model="formNamaAkun"
                type="text"
                required
                placeholder="Contoh: Bank BRI Pemuda / Kas Tunai"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Kas *</label>
              <select
                v-model="formKas"
                required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option value="kas pemuda">Kas Pemuda</option>
                <option value="kas rtrw">Kas RTRW</option>
                <option value="kas kematian">Kas Kematian</option>
                <option value="kas karang taruna">Kas Karang Taruna</option>
              </select>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-sm font-semibold transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="isFormLoading"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors disabled:opacity-50 cursor-pointer flex items-center space-x-1.5"
              >
                <Loader2 v-if="isFormLoading" class="w-4 h-4 animate-spin" />
                <span>{{ isFormLoading ? 'Menyimpan...' : 'Simpan' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
