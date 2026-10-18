<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import historyRiilService from '@/services/historyRiilService'
import type { RiilHistory } from '@/types'
import {
  History,
  Search,
  CheckCircle2,
  Clock,
  Loader2,
  Calendar,
  Filter,
  ChevronLeft,
  ChevronRight,
  ShieldCheck,
} from 'lucide-vue-next'

const items = ref<RiilHistory[]>([])
const isLoading = ref(false)
const search = ref('')
const kasFilter = ref<string>('')
const tanggalMulai = ref('')
const tanggalSelesai = ref('')

const currentPage = ref(1)
const perPage = ref(10)
const totalPages = ref(1)
const totalItems = ref(0)

const verifyingId = ref<number | null>(null)

function formatRupiah(amount: number) {
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
    const res = await historyRiilService.list({
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
    console.error('Failed to fetch history riil:', err)
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

async function handleVerify(item: RiilHistory) {
  if (
    confirm(
      `Verifikasi saldo riil "${item.akun?.nama_akun || ''}" tanggal ${item.date} (${formatRupiah(item.riil)})? Saldo akun akan diperbarui sesuai nominal ini.`,
    )
  ) {
    verifyingId.value = item.id
    try {
      await historyRiilService.verify(item.id)
      fetchData(currentPage.value)
    } catch (err: any) {
      alert(err.response?.data?.message || err.message || 'Gagal memverifikasi saldo riil.')
    } finally {
      verifyingId.value = null
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
            <History class="w-6 h-6 text-blue-600" />
            History Riil Saldo
          </h1>
          <p class="text-sm text-slate-500">Pencatatan riil saldo akun per tanggal dan verifikasi rekonsiliasi kas.</p>
        </div>
      </div>

      <!-- Info Banner -->
      <div class="bg-blue-50/70 border border-blue-200 rounded-2xl p-4 flex items-start space-x-3 text-xs sm:text-sm text-blue-800">
        <ShieldCheck class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
        <div>
          <p class="font-bold">Informasi Rekonsiliasi Saldo</p>
          <p class="text-blue-700/90 text-xs mt-0.5">
            Saat data diverifikasi, sistem akan menandai seluruh riil history pada tanggal tersebut dan tanggal sebelumnya sebagai terverifikasi, serta menyinkronkan saldo akhir akun keuangan.
          </p>
        </div>
      </div>

      <!-- Filter Controls Panel -->
      <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
            <Filter class="w-3.5 h-3.5 text-slate-500" />
            Filter History
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
              placeholder="Cari nama akun..."
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
                <th class="px-6 py-3.5">Nama Akun</th>
                <th class="px-6 py-3.5">Kategori Kas</th>
                <th class="px-6 py-3.5 text-right">Saldo Riil</th>
                <th class="px-6 py-3.5 text-center">Status Verifikasi</th>
                <th class="px-6 py-3.5 text-right w-36">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading" class="text-center">
                <td colspan="6" class="py-12">
                  <Loader2 class="w-6 h-6 animate-spin mx-auto text-blue-600 mb-2" />
                  <span class="text-xs text-slate-400">Memuat history riil...</span>
                </td>
              </tr>
              <tr v-else-if="items.length === 0" class="text-center">
                <td colspan="6" class="py-12 text-slate-400 text-sm">
                  Tidak ada rekaman riil history ditemukan.
                </td>
              </tr>
              <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-medium">
                  {{ item.date }}
                </td>
                <td class="px-6 py-4 font-bold text-slate-900">
                  {{ item.akun?.nama_akun || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-block px-2.5 py-0.5 rounded-lg text-xs font-semibold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                    {{ item.akun?.kas || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-bold text-slate-900 whitespace-nowrap">
                  {{ formatRupiah(item.riil) }}
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span
                    v-if="item.verified"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200"
                  >
                    <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
                    Terverifikasi
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200"
                  >
                    <Clock class="w-3.5 h-3.5 text-amber-600" />
                    Belum Verifikasi
                  </span>
                </td>
                <td class="px-6 py-4 text-right whitespace-nowrap">
                  <button
                    v-if="!item.verified"
                    @click="handleVerify(item)"
                    :disabled="verifyingId === item.id"
                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors disabled:opacity-50 cursor-pointer inline-flex items-center gap-1"
                  >
                    <Loader2 v-if="verifyingId === item.id" class="w-3 h-3 animate-spin" />
                    <span>Verifikasi</span>
                  </button>
                  <span v-else class="text-xs text-slate-400 italic">Selesai</span>
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
    </div>
  </AppLayout>
</template>
