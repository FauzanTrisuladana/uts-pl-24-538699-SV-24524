import api from './api'
import type { ApiResponse, PaginatedResponse, User } from '@/types'

export interface ListUserParams {
  search?: string
  role?: string
  status?: string
  page?: number
  per_page?: number
}

export interface CreateUserPayload {
  name: string
  username: string
  email: string
  role: string
  status: string
  password?: string
}

export const userService = {
  async list(params?: ListUserParams): Promise<PaginatedResponse<User>> {
    const response = await api.get<PaginatedResponse<User>>('/user', { params })
    return response.data
  },

  async create(payload: CreateUserPayload): Promise<ApiResponse<User>> {
    const response = await api.post<ApiResponse<User>>('/user', payload)
    return response.data
  },

  async updateRole(id: number, role: string): Promise<ApiResponse<User>> {
    const response = await api.put<ApiResponse<User>>(`/user/${id}/role`, { role })
    return response.data
  },

  async toggleStatus(id: number): Promise<ApiResponse<User>> {
    const response = await api.put<ApiResponse<User>>(`/user/${id}/toggle-status`)
    return response.data
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/user/${id}`)
    return response.data
  },
}

export default userService
