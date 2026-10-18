import api from './api'
import type { ApiResponse, PaginatedResponse, Akun } from '@/types'

export interface ListAkunParams {
  search?: string
  kas?: string[]
  page?: number
  per_page?: number
}

export interface CreateAkunPayload {
  nama_akun: string
  kas: string
}

export const akunService = {
  async list(params?: ListAkunParams): Promise<PaginatedResponse<Akun>> {
    const response = await api.get<PaginatedResponse<Akun>>('/akun', { params })
    return response.data
  },

  async dropdown(kas?: string): Promise<ApiResponse<Akun[]>> {
    const response = await api.get<ApiResponse<Akun[]>>('/akun/dropdown', {
      params: kas ? { kas } : undefined,
    })
    return response.data
  },

  async create(payload: CreateAkunPayload): Promise<ApiResponse<Akun>> {
    const response = await api.post<ApiResponse<Akun>>('/akun', payload)
    return response.data
  },

  async update(id: number, payload: CreateAkunPayload): Promise<ApiResponse<Akun>> {
    const response = await api.put<ApiResponse<Akun>>(`/akun/${id}`, payload)
    return response.data
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/akun/${id}`)
    return response.data
  },
}

export default akunService
