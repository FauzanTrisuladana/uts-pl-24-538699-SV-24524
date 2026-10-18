import api from './api'
import type { AuthResponse, ApiResponse, User } from '@/types'

export interface LoginPayload {
  email: string
  password: string
}

export const authService = {
  async login(payload: LoginPayload): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/login', payload)
    return response.data
  },

  async logout(): Promise<ApiResponse<null>> {
    const response = await api.post<ApiResponse<null>>('/auth/logout')
    return response.data
  },

  async getProfile(): Promise<ApiResponse<User>> {
    const response = await api.get<ApiResponse<User>>('/profile/me')
    return response.data
  },
}

export default authService
