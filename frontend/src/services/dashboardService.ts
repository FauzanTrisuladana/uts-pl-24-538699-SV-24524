import api from './api'
import type { ApiResponse } from '@/types'

export interface SaldoDailyItem {
  tanggal: string
  pemasukan: number
  pengeluaran: number
  saldo: number
}

export interface SaldoPerAkunItem {
  tanggal: string
  akun: {
    nama_akun: string
    saldo: number
  }[]
}

export interface RekonsiliasiItem {
  tanggal: string
  sistem: number
  riil: number
  verified: number | null
}

export interface DashboardData {
  saldo_awal: number
  saldo_daily: SaldoDailyItem[]
  saldo_per_akun: SaldoPerAkunItem[]
  rekonsiliasi: RekonsiliasiItem[]
}

export interface DashboardSummary {
  pemasukan: {
    total: number
    change: number
  }
  pengeluaran: {
    total: number
    change: number
  }
  totalSaldo: {
    total: number
  }
}

export const dashboardService = {
  async getDashboard(kas = 'kas pemuda'): Promise<ApiResponse<DashboardData>> {
    const response = await api.get<ApiResponse<DashboardData>>('/dashboard', {
      params: { kas },
    })
    return response.data
  },
}

export default dashboardService
