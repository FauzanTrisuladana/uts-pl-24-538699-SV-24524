import api from './api'
import type { ApiResponse, PaginatedResponse, Transaksi } from '@/types'

export interface ListTransaksiParams {
  search?: string
  tanggal_mulai?: string
  tanggal_selesai?: string
  jenis_transaksi?: string[]
  kas?: string[]
  akun?: number
  page?: number
  per_page?: number
}

export interface CreateTransaksiPayload {
  deskripsi: string
  date: string
  jenis_transaksi: 'pemasukan' | 'pengeluaran'
  akun_id: number
  penanggung_jawab_id?: number | null
  jumlah: number
}

export interface TransaksiSummary {
  total_pemasukan: number
  total_pengeluaran: number
}

export const transaksiService = {
  async list(params?: ListTransaksiParams): Promise<PaginatedResponse<Transaksi>> {
    const response = await api.get<PaginatedResponse<Transaksi>>('/transaksi', { params })
    return response.data
  },

  async create(payload: CreateTransaksiPayload): Promise<ApiResponse<Transaksi>> {
    const response = await api.post<ApiResponse<Transaksi>>('/transaksi', payload)
    return response.data
  },

  async update(id: number, payload: Partial<CreateTransaksiPayload>): Promise<ApiResponse<Transaksi>> {
    const response = await api.put<ApiResponse<Transaksi>>(`/transaksi/${id}`, payload)
    return response.data
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/transaksi/${id}`)
    return response.data
  },
}

export default transaksiService
