import api from './api'
import type { ApiResponse, PaginatedResponse, RiilHistory } from '@/types'

export interface ListHistoryRiilParams {
  search?: string
  tanggal_mulai?: string
  tanggal_selesai?: string
  kas?: string[]
  page?: number
  per_page?: number
}

export const historyRiilService = {
  async list(params?: ListHistoryRiilParams): Promise<PaginatedResponse<RiilHistory>> {
    const response = await api.get<PaginatedResponse<RiilHistory>>('/history-riil', { params })
    return response.data
  },

  async verify(id: number): Promise<ApiResponse<RiilHistory>> {
    const response = await api.put<ApiResponse<RiilHistory>>(`/history-riil/${id}/verify`)
    return response.data
  },
}

export default historyRiilService
