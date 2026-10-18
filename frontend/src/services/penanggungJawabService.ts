import api from './api'
import type { ApiResponse, PaginatedResponse, PenanggungJawab } from '@/types'

export interface ListPJParams {
  search?: string
  page?: number
  per_page?: number
}

export interface CreatePJPayload {
  nama: string
}

export const penanggungJawabService = {
  async list(params?: ListPJParams): Promise<PaginatedResponse<PenanggungJawab>> {
    const response = await api.get<PaginatedResponse<PenanggungJawab>>('/penanggung-jawab', { params })
    return response.data
  },

  async dropdown(): Promise<ApiResponse<PenanggungJawab[]>> {
    const response = await api.get<ApiResponse<PenanggungJawab[]>>('/penanggung-jawab/dropdown')
    return response.data
  },

  async create(payload: CreatePJPayload): Promise<ApiResponse<PenanggungJawab>> {
    const response = await api.post<ApiResponse<PenanggungJawab>>('/penanggung-jawab', payload)
    return response.data
  },

  async update(id: number, payload: CreatePJPayload): Promise<ApiResponse<PenanggungJawab>> {
    const response = await api.put<ApiResponse<PenanggungJawab>>(`/penanggung-jawab/${id}`, payload)
    return response.data
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/penanggung-jawab/${id}`)
    return response.data
  },
}

export default penanggungJawabService
