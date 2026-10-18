<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import transaksiService from '@/services/transaksiService'
import akunService from '@/services/akunService'
import penanggungJawabService from '@/services/penanggungJawabService'
import type { Transaksi, Akun, PenanggungJawab } from '@/types'
import {
  ArrowLeftRight,
  Plus,
  Search,
  Pencil,
  Trash2,
  Loader2,
  X,
  ChevronLeft,
  ChevronRight,
  TrendingUp,
  TrendingDown,
  Calendar,
  Filter,
} from 'lucide-vue-next'

const items = ref<Transaksi[]>([])
const isLoading = ref(false)
const search = ref('')
const jenisFilter = ref<string>('')
const kasFilter = ref<string>('')
const akunFilter = ref<string>('')
const tanggalMulai = ref('')
const tanggalSelesai = ref('')

const currentPage = ref(1)
const perPage = ref(10)
const totalPages = ref(1)
const totalItems = ref(0)
const summaryPemasukan = ref(0)
const summaryPengeluaran = ref(0)

// Dropdown Options
const akuns = ref<Akun[]>([])
const pjs = ref<PenanggungJawab[]>([])

// Modal Form State
const isModalOpen = ref(false)
const isEditing = ref(false)
const editId = ref<number | null>(null)
const formJenis = ref<'pemasukan' | 'pengeluaran'>('pemasukan')
const formDate = ref(new Date().toISOString().split('T')[0])
const formAkunId = ref<number | ''>('')
const formPjId = ref<number | ''>('')
const formJumlah = ref<number | ''>('')
const formDeskripsi = ref('')
const isFormLoading = ref(false)
const formError = ref('')

function formatRupiah(amount: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(amount)
}

async function loadDropdowns() {
  try {
    const [resAkun, resPj] = await Promise.all([
      akunService.dropdown(),
      penanggungJawabService.dropdown(),
    ])
    akuns.value = resAkun.data
    pjs.value = resPj.data
  } catch (err) {
    console.error('Failed to load dropdowns:', err)
  }
}

async function fetchData(page = 1) {
  isLoading.value = true
  currentPage.value = page
  try {
    const res = await transaksiService.list({
      search: search.value || undefined,
      tanggal_mulai: tanggalMulai.value || undefined,
      tanggal_selesai: tanggalSelesai.value || undefined,
      jenis_transaksi: jenisFilter.value ? [jenisFilter.value] : undefined,
      kas: kasFilter.value ? [kasFilter.value] : undefined,
      akun: akunFilter.value ? Number(akunFilter.value) : undefined,
      page: currentPage.value,
      per_page: perPage.value,
    })
    items.value = res.data
    if (res.meta) {
      totalPages.value = res.meta.last_page
      totalItems.value = res.meta.total
    }
    if (res.summary) {
      summaryPemasukan.value = Number(res.summary.total_pemasukan || 0)
      summaryPengeluaran.value = Number(res.summary.total_pengeluaran || 0)
    }
  } catch (err) {
    console.error('Failed to fetch transaksi:', err)
  } finally {
    isLoading.value = false
  }
}

function handleSearch() {
  fetchData(1)
}

function resetFilters() {
  search.value = ''
  jenisFilter.value = ''
  kasFilter.value = ''
  akunFilter.value = ''
  tanggalMulai.value = ''
  tanggalSelesai.value = ''
  fetchData(1)
}

function openAddModal() {
  isEditing.value = false
  editId.value = null
  formJenis.value = 'pemasukan'
  formDate.value = new Date().toISOString().split('T')[0]
  formAkunId.value = akuns.value[0]?.id ?? ''
  formPjId.value = ''
  formJumlah.value = ''
  formDeskripsi.value = ''
  formError.value = ''
  isModalOpen.value = true
}

function openEditModal(item: Transaksi) {
  isEditing.value = true
  editId.value = item.id
  formJenis.value = item.jenis_transaksi
  formDate.value = item.date
  formAkunId.value = item.akun_id
  formPjId.value = item.penanggung_jawab_id || ''
  formJumlah.value = item.jumlah
  formDeskripsi.value = item.deskripsi
  formError.value = ''
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
}

async function handleSave() {
  if (!formAkunId.value || !formJumlah.value || !formDeskripsi.value || !formDate.value) {
    formError.value = 'Mohon lengkapi semua field yang wajib diisi.'
    return
  }

  isFormLoading.value = true
  formError.value = ''

  try {
    const payload = {
      jenis_transaksi: formJenis.value,
      date: formDate.value,
      akun_id: Number(formAkunId.value),
      penanggung_jawab_id: formPjId.value ? Number(formPjId.value) : null,
      jumlah: Number(formJumlah.value),
      deskripsi: formDeskripsi.value,
    }

    if (isEditing.value && editId.value) {
      await transaksiService.update(editId.value, payload)
    } else {
      await transaksiService.create(payload)
    }
    closeModal()
    fetchData(currentPage.value)
  } catch (err: any) {
    formError.value =
      err.response?.data?.message || err.message || 'Gagal menyimpan transaksi.'
  } finally {
    isFormLoading.value = false
  }
}

async function handleDelete(item: Transaksi) {
  if (confirm(`Apakah Anda yakin ingin menghapus transaksi "${item.deskripsi}" senilai ${formatRupiah(item.jumlah)}?`)) {
    try {
      await transaksiService.delete(item.id)
      fetchData(currentPage.value)
    } catch (err: any) {
      alert(err.response?.data?.message || err.message || 'Gagal menghapus transaksi.')
    }
  }
}

onMounted(async () => {
  await loadDropdowns()
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
            <ArrowLeftRight class="w-6 h-6 text-blue-600" />
            Transaksi Keuangan
          </h1>
          <p class="text-sm text-slate-500">Pencatatan arus kas masuk (pemasukan) dan keluar (pengeluaran).</p>
        </div>
        <button
          @click="openAddModal"
          class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-xs transition-colors cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Transaksi</span>
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center space-x-4">
          <div class="p-3 bg-emerald-100 text-emerald-700 rounded-xl">
            <TrendingUp class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pemasukan</p>
            <p class="text-xl font-bold text-emerald-600">{{ formatRupiah(summaryPemasukan) }}</p>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center space-x-4">
          <div class="p-3 bg-rose-100 text-rose-700 rounded-xl">
            <TrendingDown class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pengeluaran</p>
            <p class="text-xl font-bold text-rose-600">{{ formatRupiah(summaryPengeluaran) }}</p>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center space-x-4">
          <div class="p-3 bg-blue-100 text-blue-700 rounded-xl">
            <ArrowLeftRight class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selisih Arus Kas</p>
            <p
              :class="[
                'text-xl font-bold',
                summaryPemasukan - summaryPengeluaran >= 0 ? 'text-blue-600' : 'text-rose-600',
              ]"
            >
              {{ formatRupiah(summaryPemasukan - summaryPengeluaran) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Filter Controls Panel -->
      <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
            <Filter class="w-3.5 h-3.5 text-slate-500" />
            Filter Transaksi
          </span>
          <button
            @click="resetFilters"
            class="text-xs text-blue-600 hover:text-blue-800 font-medium cursor-pointer"
          >
            Reset Filter
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          <!-- Search -->
          <div class="relative lg:col-span-2">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="search"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Cari deskripsi / PJ..."
              class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <!-- Jenis Transaksi -->
          <select
            v-model="jenisFilter"
            @change="handleSearch"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
            <option value="">Semua Jenis</option>
            <option value="pemasukan">Pemasukan</option>
            <option value="pengeluaran">Pengeluaran</option>
          </select>

          <!-- Kas -->
          <select
            v-model="kasFilter"
            @change="handleSearch"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
            <option value="">Semua Kas</option>
            <option value="kas pemuda">Kas Pemuda</option>
            <option value="kas rtrw">Kas RTRW</option>
            <option value="kas kematian">Kas Kematian</option>
            <option value="kas karang taruna">Kas Karang Taruna</option>
          </select>

          <!-- Akun -->
          <select
            v-model="akunFilter"
            @change="handleSearch"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
            <option value="">Semua Akun</option>
            <option v-for="ak in akuns" :key="ak.id" :value="ak.id">
              {{ ak.nama_akun }} ({{ ak.kas }})
            </option>
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
                <th class="px-6 py-3.5">Deskripsi</th>
                <th class="px-6 py-3.5">Jenis</th>
                <th class="px-6 py-3.5">Akun</th>
                <th class="px-6 py-3.5">Penanggung Jawab</th>
                <th class="px-6 py-3.5 text-right">Jumlah</th>
                <th class="px-6 py-3.5 text-right w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading" class="text-center">
                <td colspan="7" class="py-12">
                  <Loader2 class="w-6 h-6 animate-spin mx-auto text-blue-600 mb-2" />
                  <span class="text-xs text-slate-400">Memuat transaksi...</span>
                </td>
              </tr>
              <tr v-else-if="items.length === 0" class="text-center">
                <td colspan="7" class="py-12 text-slate-400 text-sm">
                  Tidak ada data transaksi yang cocok.
                </td>
              </tr>
              <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-medium">
                  {{ item.date }}
                </td>
                <td class="px-6 py-4">
                  <p class="font-bold text-slate-900">{{ item.deskripsi }}</p>
                  <p class="text-[11px] text-slate-400">Diinput oleh: {{ item.penginput?.name || '-' }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-block px-2.5 py-0.5 rounded-lg text-xs font-semibold uppercase',
                      item.jenis_transaksi === 'pemasukan'
                        ? 'bg-emerald-100 text-emerald-800 border border-emerald-200'
                        : 'bg-rose-100 text-rose-800 border border-rose-200',
                    ]"
                  >
                    {{ item.jenis_transaksi }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700">
                  <span class="font-semibold">{{ item.akun?.nama_akun || '-' }}</span>
                  <span class="block text-[11px] text-slate-400 uppercase">{{ item.akun?.kas || '' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700">
                  {{ item.penanggung_jawab?.nama || '-' }}
                </td>
                <td
                  :class="[
                    'px-6 py-4 text-right font-bold whitespace-nowrap',
                    item.jenis_transaksi === 'pemasukan' ? 'text-emerald-600' : 'text-rose-600',
                  ]"
                >
                  {{ item.jenis_transaksi === 'pemasukan' ? '+' : '-' }} {{ formatRupiah(item.jumlah) }}
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
            Menampilkan <span class="font-bold text-slate-700">{{ items.length }}</span> dari <span class="font-bold text-slate-700">{{ totalItems }}</span> transaksi
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
              {{ isEditing ? 'Ubah Data Transaksi' : 'Catat Transaksi Baru' }}
            </h3>
            <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="handleSave" class="p-6 space-y-4">
            <div v-if="formError" class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200">
              {{ formError }}
            </div>

            <!-- Jenis Transaksi Radio Buttons -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis Transaksi *</label>
              <div class="grid grid-cols-2 gap-3">
                <label
                  :class="[
                    'flex items-center justify-center space-x-2 p-3 rounded-xl border text-sm font-semibold cursor-pointer transition-all',
                    formJenis === 'pemasukan'
                      ? 'border-emerald-500 bg-emerald-50 text-emerald-700 ring-2 ring-emerald-500/20'
                      : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100',
                  ]"
                >
                  <input type="radio" value="pemasukan" v-model="formJenis" class="sr-only" />
                  <TrendingUp class="w-4 h-4" />
                  <span>Pemasukan (+)</span>
                </label>
                <label
                  :class="[
                    'flex items-center justify-center space-x-2 p-3 rounded-xl border text-sm font-semibold cursor-pointer transition-all',
                    formJenis === 'pengeluaran'
                      ? 'border-rose-500 bg-rose-50 text-rose-700 ring-2 ring-rose-500/20'
                      : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100',
                  ]"
                >
                  <input type="radio" value="pengeluaran" v-model="formJenis" class="sr-only" />
                  <TrendingDown class="w-4 h-4" />
                  <span>Pengeluaran (-)</span>
                </label>
              </div>
            </div>

            <!-- Tanggal & Jumlah -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal *</label>
                <input
                  v-model="formDate"
                  type="date"
                  required
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal (Rp) *</label>
                <input
                  v-model="formJumlah"
                  type="number"
                  min="1"
                  required
                  placeholder="Contoh: 150000"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
              </div>
            </div>

            <!-- Akun Keuangan -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Keuangan *</label>
              <select
                v-model="formAkunId"
                required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option value="" disabled>Pilih Akun Rekening/Kas</option>
                <option v-for="ak in akuns" :key="ak.id" :value="ak.id">
                  {{ ak.nama_akun }} ({{ ak.kas }})
                </option>
              </select>
            </div>

            <!-- Penanggung Jawab -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Penanggung Jawab (Opsional)</label>
              <select
                v-model="formPjId"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option value="">Tidak Ada / Umum</option>
                <option v-for="pj in pjs" :key="pj.id" :value="pj.id">
                  {{ pj.nama }} {{ pj.no_telepon ? `(${pj.no_telepon})` : '' }}
                </option>
              </select>
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi / Keperluan *</label>
              <textarea
                v-model="formDeskripsi"
                rows="3"
                required
                placeholder="Tuliskan keterangan detail transaksi..."
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
                <span>{{ isFormLoading ? 'Menyimpan...' : 'Simpan Transaksi' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
