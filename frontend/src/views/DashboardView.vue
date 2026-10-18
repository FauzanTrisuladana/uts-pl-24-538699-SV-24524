<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import dashboardService from '@/services/dashboardService'
import type { DashboardData, DashboardSummary } from '@/services/dashboardService'
import {
  LayoutDashboard,
  Wallet,
  TrendingUp,
  TrendingDown,
  Loader2,
  Calendar,
  CreditCard,
  Scale,
  CheckCircle2,
  Clock,
  ArrowUpRight,
  ArrowDownRight,
} from 'lucide-vue-next'

const selectedKas = ref('kas pemuda')
const isLoading = ref(false)

const kasOptions = [
  { label: 'Kas Pemuda', value: 'kas pemuda' },
  { label: 'Kas RTRW', value: 'kas rtrw' },
  { label: 'Kas Kematian', value: 'kas kematian' },
  { label: 'Kas Karang Taruna', value: 'kas karang taruna' },
]

const dashboardData = ref<DashboardData>({
  saldo_awal: 0,
  saldo_daily: [],
  saldo_per_akun: [],
  rekonsiliasi: [],
})

const summary = ref<DashboardSummary>({
  pemasukan: { total: 0, change: 0 },
  pengeluaran: { total: 0, change: 0 },
  totalSaldo: { total: 0 },
})

function formatRupiah(amount?: number | null) {
  if (amount === null || amount === undefined) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(amount)
}

// Latest saldo per akun from last available date
const latestAkunBalances = computed(() => {
  const perAkunList = dashboardData.value.saldo_per_akun
  if (!perAkunList || perAkunList.length === 0) return []
  const lastEntry = perAkunList[perAkunList.length - 1]
  return lastEntry?.akun || []
})

// Reverse daily flow so latest days appear on top
const recentDaily = computed(() => {
  const daily = [...(dashboardData.value.saldo_daily || [])]
  return daily.reverse().slice(0, 10)
})

// Reverse rekonsiliasi so latest appears first
const recentRekonsiliasi = computed(() => {
  const rekon = [...(dashboardData.value.rekonsiliasi || [])]
  return rekon.reverse().slice(0, 7)
})

async function fetchDashboard() {
  isLoading.value = true
  try {
    const res = await dashboardService.getDashboard(selectedKas.value)
    dashboardData.value = res.data
    if (res.summary) {
      summary.value = res.summary
    }
  } catch (err) {
    console.error('Failed to load dashboard:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchDashboard()
})
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Page Header & Kas Selector -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
            <LayoutDashboard class="w-6 h-6 text-blue-600" />
            Dashboard Keuangan
          </h1>
          <p class="text-sm text-slate-500">Ringkasan kas, mutasi harian, dan rekonsiliasi saldo riil.</p>
        </div>

        <!-- Kas Category Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
          <button
            v-for="opt in kasOptions"
            :key="opt.value"
            @click="selectedKas = opt.value; fetchDashboard()"
            :class="[
              'px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all cursor-pointer',
              selectedKas === opt.value
                ? 'bg-blue-600 text-white shadow-xs'
                : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50',
            ]"
          >
            {{ opt.label }}
          </button>
        </div>
      </div>

      <!-- Loading State Overlay -->
      <div v-if="isLoading" class="text-center py-20 bg-white rounded-2xl border border-slate-200">
        <Loader2 class="w-8 h-8 animate-spin mx-auto text-blue-600 mb-2" />
        <p class="text-sm text-slate-500 font-medium">Memuat data dashboard...</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Top Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <!-- Total Kas / Saldo -->
          <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 relative overflow-hidden">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kas Saat Ini</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">
                  {{ formatRupiah(summary.totalSaldo?.total) }}
                </h3>
              </div>
              <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <Wallet class="w-6 h-6" />
              </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-xs text-slate-500">
              <span class="font-semibold text-slate-700 mr-1.5">Saldo Awal Bulan:</span>
              <span>{{ formatRupiah(dashboardData.saldo_awal) }}</span>
            </div>
          </div>

          <!-- Pemasukan Bulan Ini -->
          <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 relative overflow-hidden">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pemasukan Bulan Ini</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">
                  {{ formatRupiah(summary.pemasukan?.total) }}
                </h3>
              </div>
              <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <TrendingUp class="w-6 h-6" />
              </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-xs">
              <span
                :class="[
                  'inline-flex items-center font-bold mr-1.5',
                  summary.pemasukan?.change >= 0 ? 'text-emerald-600' : 'text-rose-600',
                ]"
              >
                <ArrowUpRight v-if="summary.pemasukan?.change >= 0" class="w-3.5 h-3.5 mr-0.5" />
                <ArrowDownRight v-else class="w-3.5 h-3.5 mr-0.5" />
                {{ summary.pemasukan?.change }}%
              </span>
              <span class="text-slate-400">dibanding bulan lalu</span>
            </div>
          </div>

          <!-- Pengeluaran Bulan Ini -->
          <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 relative overflow-hidden">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengeluaran Bulan Ini</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">
                  {{ formatRupiah(summary.pengeluaran?.total) }}
                </h3>
              </div>
              <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <TrendingDown class="w-6 h-6" />
              </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-xs">
              <span
                :class="[
                  'inline-flex items-center font-bold mr-1.5',
                  summary.pengeluaran?.change <= 0 ? 'text-emerald-600' : 'text-rose-600',
                ]"
              >
                <ArrowUpRight v-if="summary.pengeluaran?.change > 0" class="w-3.5 h-3.5 mr-0.5" />
                <ArrowDownRight v-else class="w-3.5 h-3.5 mr-0.5" />
                {{ summary.pengeluaran?.change }}%
              </span>
              <span class="text-slate-400">dibanding bulan lalu</span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Saldo per Akun List -->
          <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200">
            <h3 class="font-bold text-base text-slate-900 mb-4 flex items-center gap-2">
              <CreditCard class="w-5 h-5 text-blue-600" />
              Saldo Pos / Rekening
            </h3>
            <div v-if="latestAkunBalances.length === 0" class="py-8 text-center text-slate-400 text-xs">
              Belum ada data rekening dalam kategori kas ini.
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="ak in latestAkunBalances"
                :key="ak.nama_akun"
                class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 rounded-lg bg-blue-100/70 text-blue-700 flex items-center justify-center font-bold text-xs">
                    {{ ak.nama_akun.charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-semibold text-slate-900 text-xs sm:text-sm">{{ ak.nama_akun }}</span>
                </div>
                <span class="font-bold text-slate-900 text-xs sm:text-sm">
                  {{ formatRupiah(ak.saldo) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Status Rekonsiliasi (Sistem vs Riil) -->
          <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-xs border border-slate-200">
            <h3 class="font-bold text-base text-slate-900 mb-4 flex items-center gap-2">
              <Scale class="w-5 h-5 text-indigo-600" />
              Rekonsiliasi Saldo Riil Harian
            </h3>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-wider font-semibold border-b border-slate-200">
                  <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3 text-right">Saldo Sistem</th>
                    <th class="px-4 py-3 text-right">Saldo Riil Fisik</th>
                    <th class="px-4 py-3 text-center">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-if="recentRekonsiliasi.length === 0">
                    <td colspan="4" class="text-center py-6 text-slate-400">
                      Tidak ada rekaman rekonsiliasi.
                    </td>
                  </tr>
                  <tr v-for="rek in recentRekonsiliasi" :key="rek.tanggal" class="hover:bg-slate-50/80">
                    <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">
                      {{ rek.tanggal }}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-700 whitespace-nowrap">
                      {{ formatRupiah(rek.sistem) }}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-700 whitespace-nowrap">
                      {{ formatRupiah(rek.riil) }}
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                      <span
                        v-if="rek.verified !== null"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800"
                      >
                        <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                        Sinkron
                      </span>
                      <span
                        v-else
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800"
                      >
                        <Clock class="w-3 h-3 text-amber-600" />
                        Pending
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Daily Cashflow Summary Table -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200">
          <h3 class="font-bold text-base text-slate-900 mb-4 flex items-center gap-2">
            <Calendar class="w-5 h-5 text-blue-600" />
            Riwayat Arus Kas Harian (10 Hari Terakhir)
          </h3>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
              <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider font-semibold border-b border-slate-200">
                <tr>
                  <th class="px-6 py-3.5">Tanggal</th>
                  <th class="px-6 py-3.5 text-right text-emerald-700">Pemasukan</th>
                  <th class="px-6 py-3.5 text-right text-rose-700">Pengeluaran</th>
                  <th class="px-6 py-3.5 text-right font-bold">Saldo Berjalan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="recentDaily.length === 0">
                  <td colspan="4" class="text-center py-6 text-slate-400 text-xs">
                    Belum ada riwayat arus kas harian.
                  </td>
                </tr>
                <tr v-for="d in recentDaily" :key="d.tanggal" class="hover:bg-slate-50/80 transition-colors">
                  <td class="px-6 py-4 font-semibold text-slate-900 text-xs">
                    {{ d.tanggal }}
                  </td>
                  <td class="px-6 py-4 text-right text-xs text-emerald-600 font-semibold">
                    {{ d.pemasukan > 0 ? '+' + formatRupiah(d.pemasukan) : '-' }}
                  </td>
                  <td class="px-6 py-4 text-right text-xs text-rose-600 font-semibold">
                    {{ d.pengeluaran > 0 ? '-' + formatRupiah(d.pengeluaran) : '-' }}
                  </td>
                  <td class="px-6 py-4 text-right font-bold text-slate-900 text-xs">
                    {{ formatRupiah(d.saldo) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
