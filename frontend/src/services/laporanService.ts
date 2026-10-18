import api from './api'
import type { ApiResponse, Transaksi, MutasiRekening } from '@/types'

export interface LaporanParams {
  tanggal_mulai: string
  tanggal_selesai: string
  kas: string
  jenis_transaksi?: string[]
  akun?: number
}

export interface PosisiKeuanganItem {
  nama_akun: string
  saldo_awal: number
  pemasukan: number
  pengeluaran: number
  saldo_akhir: number
  riil: number
  selisih: number
  keterangan: 'Seimbang' | 'Uang Lebih' | 'Uang Kurang' | string
}

export interface LaporanData {
  transaksi: Transaksi[]
  mutasi: MutasiRekening[]
  posisiKeuangan: PosisiKeuanganItem[]
}

export interface LaporanSummary {
  total_pemasukan: number
  total_pengeluaran: number
  saldo_awal: number
  kas_sekarang: number
}

export const laporanService = {
  async getLaporan(params: LaporanParams): Promise<ApiResponse<LaporanData>> {
    const response = await api.get<ApiResponse<LaporanData>>('/laporan', { params })
    return response.data
  },

  async downloadPdf(params: LaporanParams): Promise<void> {
    const response = await api.get('/laporan/pdf', {
      params,
      responseType: 'blob',
    })

    const blob = new Blob([response.data], { type: 'application/pdf' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `laporan-keuangan-${params.kas.replace(/\s+/g, '-')}-${params.tanggal_mulai}-sd-${params.tanggal_selesai}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(link.href)
  },
}

export default laporanService
