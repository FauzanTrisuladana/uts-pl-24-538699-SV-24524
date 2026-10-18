<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import mutasiService from '@/services/mutasiService'
import akunService from '@/services/akunService'
import type { MutasiRekening, Akun } from '@/types'
import {
  ArrowRightLeft,
  Plus,
  Search,
  Pencil,
  Trash2,
  Loader2,
  X,
  ChevronLeft,
  ChevronRight,
  ArrowRight,
  Calendar,
  Filter,
} from 'lucide-vue-next'

const items = ref<MutasiRekening[]>([])
const isLoading = ref(false)
const search = ref('')
const kasFilter = ref<string>('')
const tanggalMulai = ref('')
const tanggalSelesai = ref('')

const currentPage = ref(1)
const perPage = ref(10)
const totalPages = ref(1)
const totalItems = ref(0)

const akuns = ref<Akun[]>([])

// Modal Form State
const isModalOpen = ref(false)
const isEditing = ref(false)
const editId = ref<number | null>(null)
const formDate = ref<string>(new Date().toISOString().split('T')[0] || '')
const formKas = ref('kas pemuda')
const formAkunDebitId = ref<number | ''>('')
const formAkunKreditId = ref<number | ''>('')
const formJumlah = ref<number | ''>('')
const formKeterangan = ref('')
const isFormLoading = ref(false)
const formError = ref('')

function formatRupiah(amount: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(amount)
}

// Filtered akuns for modal based on selected kas
const modalAkunOptions = computed(() => {
  return akuns.value.filter((a) => !formKas.value || a.kas === formKas.value)
})

async function loadAkuns() {
  try {
    const res = await akunService.dropdown()
    akuns.value = res.data
  } catch (err) {
    console.error('Failed to load akuns:', err)
  }
}

async function fetchData(page = 1) {
  isLoading.value = true
  currentPage.value = page
  try {
    const res = await mutasiService.list({
      search: search.value || undefined,
      tanggal_mulai: tanggalMulai.value || undefined,
      tanggal_selesai: tanggalSelesai.value || undefined,
      kas: kasFilter.value ? [kasFilter.value] : undefined,
      page: currentPage.value,
      per_page: perPage.value,
    })
    items.value = res.data
    if (res.meta) {
      totalPages.value = res.meta.last_page
      totalItems.value = res.meta.total
    }
  } catch (err) {
    console.error('Failed to fetch mutasi:', err)
  } finally {
    isLoading.value = false
  }
}

function handleSearch() {
  fetchData(1)
}

function resetFilters() {
  search.value = ''
  kasFilter.value = ''
  tanggalMulai.value = ''
  tanggalSelesai.value = ''
  fetchData(1)
}

function openAddModal() {
  isEditing.value = false
  editId.value = null
  formDate.value = new Date().toISOString().slice(0, 10)
  formKas.value = 'kas pemuda'
  formAkunDebitId.value = ''
  formAkunKreditId.value = ''
  formJumlah.value = ''
  formKeterangan.value = ''
  formError.value = ''
  isModalOpen.value = true
}

function openEditModal(item: MutasiRekening) {
  isEditing.value = true
  editId.value = item.id
  formDate.value = item.date
  formAkunDebitId.value = item.akun_debit_id
  formAkunKreditId.value = item.akun_kredit_id
  formJumlah.value = item.jumlah
  formKeterangan.value = item.keterangan || ''
  formError.value = ''
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
}

async function handleSave() {
  if (!isEditing.value && (!formAkunDebitId.value || !formAkunKreditId.value)) {
    formError.value = 'Akun asal dan akun tujuan wajib dipilih.'
    return
  }

  if (formAkunDebitId.value === formAkunKreditId.value) {
    formError.value = 'Akun tujuan transfer tidak boleh sama dengan akun asal.'
    return
  }

  if (!formJumlah.value) {
    formError.value = 'Nominal mutasi wajib diisi.'
    return
  }

  isFormLoading.value = true
  formError.value = ''

  try {
    if (isEditing.value && editId.value) {
      await mutasiService.update(editId.value, {
        jumlah: Number(formJumlah.value),
        keterangan: formKeterangan.value || null,
      })
    } else {
      await mutasiService.create({
        date: formDate.value,
        kas: formKas.value,
        akun_debit_id: Number(formAkunDebitId.value),
        akun_kredit_id: Number(formAkunKreditId.value),
        jumlah: Number(formJumlah.value),
        keterangan: formKeterangan.value || null,
      })
    }
    closeModal()
    fetchData(currentPage.value)
  } catch (err: any) {
    formError.value =
      err.response?.data?.message || err.message || 'Gagal menyimpan mutasi rekening.'
  } finally {
    isFormLoading.value = false
  }
}

async function handleDelete(item: MutasiRekening) {
  if (confirm(`Apakah Anda yakin ingin menghapus mutasi dana sebesar ${formatRupiah(item.jumlah)}?`)) {
    try {
      await mutasiService.delete(item.id)
      fetchData(currentPage.value)
    } catch (err: any) {
      alert(err.response?.data?.message || err.message || 'Gagal menghapus mutasi.')
    }
  }
}

onMounted(async () => {
  await loadAkuns()
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
            <ArrowRightLeft class="w-6 h-6 text-blue-600" />
            Mutasi Rekening
          </h1>
          <p class="text-sm text-slate-500">Pencatatan transfer saldo antar rekening/pos kas pemuda.</p>
        </div>
        <button
          @click="openAddModal"
          class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-xs transition-colors cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Mutasi Dana</span>
        </button>
      </div>

      <!-- Filter Controls Panel -->
      <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
            <Filter class="w-3.5 h-3.5 text-slate-500" />
            Filter Mutasi
          </span>
          <button
            @click="resetFilters"
            class="text-xs text-blue-600 hover:text-blue-800 font-medium cursor-pointer"
          >
            Reset Filter
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <!-- Search -->
          <div class="relative sm:col-span-2">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="search"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Cari keterangan mutasi / nama akun..."
              class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <!-- Kas -->
          <select
            v-model="kasFilter"
            @change="handleSearch"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
            <option value="">Semua Kategori Kas</option>
            <option value="kas pemuda">Kas Pemuda</option>
            <option value="kas rtrw">Kas RTRW</option>
            <option value="kas kematian">Kas Kematian</option>
            <option value="kas karang taruna">Kas Karang Taruna</option>
          </select>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 pt-1">
          <div class="flex items-center space-x-2 w-full sm:w-auto">
            <Calendar class="w-4 h-4 text-slate-400 shrink-0" />
            <span class="text-xs text-slate-500 shrink-0">Periode:</span>
            <input
              v-model="tanggalMulai"
              type="date"
              class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none"
            />
            <span class="text-xs text-slate-400">s/d</span>
            <input
              v-model="tanggalSelesai"
              type="date"
              class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none"
            />
          </div>
          <button
            @click="handleSearch"
            class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl transition-colors cursor-pointer w-full sm:w-auto"
          >
            Terapkan Filter
          </button>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
              <tr>
                <th class="px-6 py-3.5">Tanggal</th>
                <th class="px-6 py-3.5">Keterangan</th>
                <th class="px-6 py-3.5">Dari Akun (Kredit)</th>
                <th class="px-6 py-3.5 text-center w-8"></th>
                <th class="px-6 py-3.5">Ke Akun (Debit)</th>
                <th class="px-6 py-3.5 text-right">Nominal Transfer</th>
                <th class="px-6 py-3.5 text-right w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading" class="text-center">
                <td colspan="7" class="py-12">
                  <Loader2 class="w-6 h-6 animate-spin mx-auto text-blue-600 mb-2" />
                  <span class="text-xs text-slate-400">Memuat mutasi rekening...</span>
                </td>
              </tr>
              <tr v-else-if="items.length === 0" class="text-center">
                <td colspan="7" class="py-12 text-slate-400 text-sm">
                  Tidak ada data mutasi rekening yang cocok.
                </td>
              </tr>
              <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-medium">
                  {{ item.date }}
                </td>
                <td class="px-6 py-4 font-semibold text-slate-900">
                  {{ item.keterangan || 'Pemindahan Saldo Kas' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs">
                  <span class="font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                    {{ item.akun_kredit?.nama_akun || '-' }}
                  </span>
                </td>
                <td class="px-2 py-4 text-center">
                  <ArrowRight class="w-4 h-4 text-slate-400 inline" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs">
                  <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                    {{ item.akun_debit?.nama_akun || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-bold text-slate-900 whitespace-nowrap">
                  {{ formatRupiah(item.jumlah) }}
                </td>
                <td class="px-6 py-4 text-right whitespace-nowrap">
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
            Menampilkan <span class="font-bold text-slate-700">{{ items.length }}</span> dari <span class="font-bold text-slate-700">{{ totalItems }}</span> mutasi
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

      <!-- Add / Edit Modal Dialog -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div @click="closeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" />
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-10 my-8">
          <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-base text-slate-900">
              {{ isEditing ? 'Ubah Data Mutasi' : 'Tambah Transfer Saldo Baru' }}
            </h3>
            <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="handleSave" class="p-6 space-y-4">
            <div v-if="formError" class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200">
              {{ formError }}
            </div>

            <!-- Kategori Kas & Tanggal (hanya pada mode tambah) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" v-if="!isEditing">
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
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal *</label>
                <input
                  v-model="formDate"
                  type="date"
                  required
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
              </div>
            </div>

            <!-- Akun Asal (Kredit) & Akun Tujuan (Debit) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" v-if="!isEditing">
              <div>
                <label class="block text-xs font-semibold text-rose-700 mb-1">Dari Akun (Kredit) *</label>
                <select
                  v-model="formAkunKreditId"
                  required
                  class="w-full px-3.5 py-2.5 bg-rose-50/50 border border-rose-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
                >
                  <option value="" disabled>Pilih Sumber Dana</option>
                  <option v-for="ak in modalAkunOptions" :key="ak.id" :value="ak.id">
                    {{ ak.nama_akun }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-emerald-700 mb-1">Ke Akun (Debit) *</label>
                <select
                  v-model="formAkunDebitId"
                  required
                  class="w-full px-3.5 py-2.5 bg-emerald-50/50 border border-emerald-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                >
                  <option value="" disabled>Pilih Penerima Dana</option>
                  <option v-for="ak in modalAkunOptions" :key="ak.id" :value="ak.id">
                    {{ ak.nama_akun }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Jumlah -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Transfer (Rp) *</label>
              <input
                v-model="formJumlah"
                type="number"
                min="1"
                required
                placeholder="Contoh: 250000"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>

            <!-- Keterangan -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Transfer (Opsional)</label>
              <textarea
                v-model="formKeterangan"
                rows="3"
                placeholder="Contoh: Setor tunai hasil kas ke rekening BRI..."
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              ></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
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
                <span>{{ isFormLoading ? 'Menyimpan...' : 'Simpan Mutasi' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
