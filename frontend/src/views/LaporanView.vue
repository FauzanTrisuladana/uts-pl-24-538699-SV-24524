<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import laporanService from '@/services/laporanService'
import akunService from '@/services/akunService'
import type { Akun, Transaksi, MutasiRekening } from '@/types'
import type { PosisiKeuanganItem, LaporanSummary } from '@/services/laporanService'
import {
  FileText,
  Download,
  Calendar,
  Loader2,
  TrendingUp,
  TrendingDown,
  Wallet,
  ArrowRight,
  Landmark,
  Scale,
} from 'lucide-vue-next'

const selectedKas = ref('kas pemuda')
const now = new Date()
const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10)
const today = now.toISOString().slice(0, 10)

const tanggalMulai = ref(startOfMonth)
const tanggalSelesai = ref(today)
const selectedAkun = ref<string>('')

const akuns = ref<Akun[]>([])
const isLoading = ref(false)
const isDownloading = ref(false)

const activeTab = ref<'posisi' | 'transaksi' | 'mutasi'>('posisi')

const transaksiList = ref<Transaksi[]>([])
const mutasiList = ref<MutasiRekening[]>([])
const posisiList = ref<PosisiKeuanganItem[]>([])
const summary = ref<LaporanSummary>({
  total_pemasukan: 0,
  total_pengeluaran: 0,
  saldo_awal: 0,
  kas_sekarang: 0,
})

function formatRupiah(amount?: number | null) {
  if (amount === null || amount === undefined) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(amount)
}

async function loadAkuns() {
  try {
    const res = await akunService.dropdown(selectedKas.value)
    akuns.value = res.data
  } catch (err) {
    console.error('Failed to load akuns:', err)
  }
}

async function fetchLaporan() {
  isLoading.value = true
  try {
    const res = await laporanService.getLaporan({
      kas: selectedKas.value,
      tanggal_mulai: tanggalMulai.value,
      tanggal_selesai: tanggalSelesai.value,
      akun: selectedAkun.value ? Number(selectedAkun.value) : undefined,
    })

    transaksiList.value = res.data.transaksi || []
    mutasiList.value = res.data.mutasi || []
    posisiList.value = res.data.posisiKeuangan || []
    if (res.summary) {
      summary.value = {
        total_pemasukan: Number(res.summary.total_pemasukan || 0),
        total_pengeluaran: Number(res.summary.total_pengeluaran || 0),
        saldo_awal: Number(res.summary.saldo_awal || 0),
        kas_sekarang: Number(res.summary.kas_sekarang || 0),
      }
    }
  } catch (err) {
    console.error('Failed to load laporan:', err)
  } finally {
    isLoading.value = false
  }
}

async function handleDownloadPdf() {
  isDownloading.value = true
  try {
    await laporanService.downloadPdf({
      kas: selectedKas.value,
      tanggal_mulai: tanggalMulai.value,
      tanggal_selesai: tanggalSelesai.value,
      akun: selectedAkun.value ? Number(selectedAkun.value) : undefined,
    })
  } catch (err: any) {
    alert(err.response?.data?.message || err.message || 'Gagal mengunduh laporan PDF.')
  } finally {
    isDownloading.value = false
  }
}

onMounted(async () => {
  await loadAkuns()
  fetchLaporan()
})
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
            <FileText class="w-6 h-6 text-blue-600" />
            Laporan Keuangan
          </h1>
          <p class="text-sm text-slate-500">Laporan pertanggungjawaban arus kas, posisi keuangan, dan cetak dokumen resmi PDF.</p>
        </div>
        <button
          @click="handleDownloadPdf"
          :disabled="isDownloading || isLoading"
          class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50"
        >
          <Loader2 v-if="isDownloading" class="w-4 h-4 animate-spin" />
          <Download v-else class="w-4 h-4" />
          <span>{{ isDownloading ? 'Menyiapkan PDF...' : 'Download PDF' }}</span>
        </button>
      </div>

      <!-- Filter Controls Panel -->
      <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <!-- Kategori Kas -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Kas *</label>
            <select
              v-model="selectedKas"
              @change="loadAkuns(); fetchLaporan()"
              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
              <option value="kas pemuda">Kas Pemuda</option>
              <option value="kas rtrw">Kas RTRW</option>
              <option value="kas kematian">Kas Kematian</option>
              <option value="kas karang taruna">Kas Karang Taruna</option>
            </select>
          </div>

          <!-- Periode Mulai -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai *</label>
            <input
              v-model="tanggalMulai"
              type="date"
              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none"
            />
          </div>

          <!-- Periode Selesai -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Selesai *</label>
            <input
              v-model="tanggalSelesai"
              type="date"
              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none"
            />
          </div>

          <!-- Akun Filter -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Akun (Opsional)</label>
            <select
              v-model="selectedAkun"
              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
              <option value="">Semua Akun</option>
              <option v-for="ak in akuns" :key="ak.id" :value="ak.id">
                {{ ak.nama_akun }}
              </option>
            </select>
          </div>
        </div>

        <div class="flex justify-end pt-1">
          <button
            @click="fetchLaporan"
            class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl transition-colors cursor-pointer"
          >
            Terapkan Filter
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center space-x-3.5">
          <div class="p-3 bg-slate-100 text-slate-700 rounded-xl">
            <Landmark class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Saldo Awal</p>
            <p class="text-lg font-bold text-slate-900">{{ formatRupiah(summary.saldo_awal) }}</p>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center space-x-3.5">
          <div class="p-3 bg-emerald-100 text-emerald-700 rounded-xl">
            <TrendingUp class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pemasukan</p>
            <p class="text-lg font-bold text-emerald-600">{{ formatRupiah(summary.total_pemasukan) }}</p>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center space-x-3.5">
          <div class="p-3 bg-rose-100 text-rose-700 rounded-xl">
            <TrendingDown class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pengeluaran</p>
            <p class="text-lg font-bold text-rose-600">{{ formatRupiah(summary.total_pengeluaran) }}</p>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center space-x-3.5">
          <div class="p-3 bg-blue-100 text-blue-700 rounded-xl">
            <Wallet class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Kas Saat Ini</p>
            <p class="text-lg font-bold text-blue-600">{{ formatRupiah(summary.kas_sekarang) }}</p>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button
          @click="activeTab = 'posisi'"
          :class="[
            'px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5',
            activeTab === 'posisi'
              ? 'bg-blue-600 text-white shadow-xs'
              : 'text-slate-600 hover:bg-slate-100',
          ]"
        >
          <Scale class="w-4 h-4" />
          <span>Posisi Keuangan ({{ posisiList.length }})</span>
        </button>
        <button
          @click="activeTab = 'transaksi'"
          :class="[
            'px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5',
            activeTab === 'transaksi'
              ? 'bg-blue-600 text-white shadow-xs'
              : 'text-slate-600 hover:bg-slate-100',
          ]"
        >
          <FileText class="w-4 h-4" />
          <span>Daftar Transaksi ({{ transaksiList.length }})</span>
        </button>
        <button
          @click="activeTab = 'mutasi'"
          :class="[
            'px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5',
            activeTab === 'mutasi'
              ? 'bg-blue-600 text-white shadow-xs'
              : 'text-slate-600 hover:bg-slate-100',
          ]"
        >
          <ArrowRight class="w-4 h-4" />
          <span>Mutasi Rekening ({{ mutasiList.length }})</span>
        </button>
      </div>

      <!-- Tab 1: Posisi Keuangan per Akun -->
      <div v-if="activeTab === 'posisi'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
              <tr>
                <th class="px-6 py-3.5">Akun Keuangan</th>
                <th class="px-6 py-3.5 text-right">Saldo Awal</th>
                <th class="px-6 py-3.5 text-right text-emerald-700">Pemasukan</th>
                <th class="px-6 py-3.5 text-right text-rose-700">Pengeluaran</th>
                <th class="px-6 py-3.5 text-right font-bold">Saldo Akhir</th>
                <th class="px-6 py-3.5 text-right">Saldo Riil</th>
                <th class="px-6 py-3.5 text-right">Selisih</th>
                <th class="px-6 py-3.5 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading">
                <td colspan="8" class="text-center py-12">
                  <Loader2 class="w-6 h-6 animate-spin mx-auto text-blue-600 mb-2" />
                  <span class="text-xs text-slate-400">Memuat posisi keuangan...</span>
                </td>
              </tr>
              <tr v-else-if="posisiList.length === 0">
                <td colspan="8" class="text-center py-12 text-slate-400 text-sm">
                  Tidak ada data posisi keuangan.
                </td>
              </tr>
              <tr v-for="item in posisiList" :key="item.nama_akun" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 font-bold text-slate-900">
                  {{ item.nama_akun }}
                </td>
                <td class="px-6 py-4 text-right text-xs">
                  {{ formatRupiah(item.saldo_awal) }}
                </td>
                <td class="px-6 py-4 text-right text-xs text-emerald-600 font-semibold">
                  +{{ formatRupiah(item.pemasukan) }}
                </td>
                <td class="px-6 py-4 text-right text-xs text-rose-600 font-semibold">
                  -{{ formatRupiah(item.pengeluaran) }}
                </td>
                <td class="px-6 py-4 text-right font-bold text-slate-900">
                  {{ formatRupiah(item.saldo_akhir) }}
                </td>
                <td class="px-6 py-4 text-right text-xs text-slate-700 font-semibold">
                  {{ formatRupiah(item.riil) }}
                </td>
                <td class="px-6 py-4 text-right text-xs">
                  <span :class="item.selisih === 0 ? 'text-slate-400' : 'text-amber-600 font-bold'">
                    {{ formatRupiah(item.selisih) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span
                    :class="[
                      'px-2.5 py-0.5 rounded-full text-xs font-semibold',
                      item.keterangan === 'Seimbang'
                        ? 'bg-emerald-100 text-emerald-800 border border-emerald-200'
                        : item.keterangan === 'Uang Lebih'
                          ? 'bg-blue-100 text-blue-800 border border-blue-200'
                          : 'bg-rose-100 text-rose-800 border border-rose-200',
                    ]"
                  >
                    {{ item.keterangan }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab 2: Transaksi Table -->
      <div v-if="activeTab === 'transaksi'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
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
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading">
                <td colspan="6" class="text-center py-12">
                  <Loader2 class="w-6 h-6 animate-spin mx-auto text-blue-600 mb-2" />
                  <span class="text-xs text-slate-400">Memuat transaksi...</span>
                </td>
              </tr>
              <tr v-else-if="transaksiList.length === 0">
                <td colspan="6" class="text-center py-12 text-slate-400 text-sm">
                  Tidak ada transaksi dalam periode ini.
                </td>
              </tr>
              <tr v-for="tx in transaksiList" :key="tx.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-medium">
                  {{ tx.date }}
                </td>
                <td class="px-6 py-4 font-bold text-slate-900">
                  {{ tx.deskripsi }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2.5 py-0.5 rounded-lg text-xs font-semibold uppercase',
                      tx.jenis_transaksi === 'pemasukan'
                        ? 'bg-emerald-100 text-emerald-800'
                        : 'bg-rose-100 text-rose-800',
                    ]"
                  >
                    {{ tx.jenis_transaksi }}
                  </span>
                </td>
                <td class="px-6 py-4 text-xs font-medium text-slate-700">
                  {{ tx.akun?.nama_akun || '-' }}
                </td>
                <td class="px-6 py-4 text-xs text-slate-600">
                  {{ tx.penanggung_jawab?.nama || '-' }}
                </td>
                <td
                  :class="[
                    'px-6 py-4 text-right font-bold whitespace-nowrap',
                    tx.jenis_transaksi === 'pemasukan' ? 'text-emerald-600' : 'text-rose-600',
                  ]"
                >
                  {{ tx.jenis_transaksi === 'pemasukan' ? '+' : '-' }} {{ formatRupiah(tx.jumlah) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab 3: Mutasi Table -->
      <div v-if="activeTab === 'mutasi'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
              <tr>
                <th class="px-6 py-3.5">Tanggal</th>
                <th class="px-6 py-3.5">Keterangan</th>
                <th class="px-6 py-3.5">Dari Akun (Kredit)</th>
                <th class="px-6 py-3.5 text-center w-8"></th>
                <th class="px-6 py-3.5">Ke Akun (Debit)</th>
                <th class="px-6 py-3.5 text-right">Jumlah</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading">
                <td colspan="6" class="text-center py-12">
                  <Loader2 class="w-6 h-6 animate-spin mx-auto text-blue-600 mb-2" />
                  <span class="text-xs text-slate-400">Memuat mutasi...</span>
                </td>
              </tr>
              <tr v-else-if="mutasiList.length === 0">
                <td colspan="6" class="text-center py-12 text-slate-400 text-sm">
                  Tidak ada data transfer mutasi dalam periode ini.
                </td>
              </tr>
              <tr v-for="mut in mutasiList" :key="mut.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-medium">
                  {{ mut.date }}
                </td>
                <td class="px-6 py-4 font-bold text-slate-900">
                  {{ mut.keterangan || 'Pemindahan Saldo Kas' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-rose-700">
                  {{ mut.akun_kredit?.nama_akun || '-' }}
                </td>
                <td class="px-2 py-4 text-center">
                  <ArrowRight class="w-4 h-4 text-slate-400 inline" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-emerald-700">
                  {{ mut.akun_debit?.nama_akun || '-' }}
                </td>
                <td class="px-6 py-4 text-right font-bold text-slate-900 whitespace-nowrap">
                  {{ formatRupiah(mut.jumlah) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
