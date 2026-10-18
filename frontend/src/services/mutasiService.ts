import api from './api'
import type { ApiResponse, PaginatedResponse, MutasiRekening } from '@/types'

export interface ListMutasiParams {
  search?: string
  tanggal_mulai?: string
  tanggal_selesai?: string
  kas?: string[]
  akun?: number
  page?: number
  per_page?: number
}

export interface CreateMutasiPayload {
  date: string
  kas: string
  akun_debit_id: number
  akun_kredit_id: number
  jumlah: number
  keterangan?: string | null
}

export const mutasiService = {
  async list(params?: ListMutasiParams): Promise<PaginatedResponse<MutasiRekening>> {
    const response = await api.get<PaginatedResponse<MutasiRekening>>('/mutasi-rekening', { params })
    return response.data
  },

  async create(payload: CreateMutasiPayload): Promise<ApiResponse<MutasiRekening>> {
    const response = await api.post<ApiResponse<MutasiRekening>>('/mutasi-rekening', payload)
    return response.data
  },

  async update(id: number, payload: Partial<CreateMutasiPayload>): Promise<ApiResponse<MutasiRekening>> {
    const response = await api.put<ApiResponse<MutasiRekening>>(`/mutasi-rekening/${id}`, payload)
    return response.data
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/mutasi-rekening/${id}`)
    return response.data
  },
}

export default mutasiService
