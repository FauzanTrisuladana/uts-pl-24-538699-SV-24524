import api from './api'
import type { ApiResponse, User } from '@/types'

export interface UpdateProfilePayload {
  name: string
  username: string
  email: string
}

export interface UpdatePasswordPayload {
  current_password: string
  password: string
  password_confirmation: string
}

export const profileService = {
  async getProfile(): Promise<ApiResponse<User>> {
    const response = await api.get<ApiResponse<User>>('/profile/me')
    return response.data
  },

  async updateProfile(payload: UpdateProfilePayload): Promise<ApiResponse<User>> {
    const response = await api.put<ApiResponse<User>>('/profile/update-profile', payload)
    return response.data
  },

  async updatePassword(payload: UpdatePasswordPayload): Promise<ApiResponse<null>> {
    const response = await api.put<ApiResponse<null>>('/profile/update-password', payload)
    return response.data
  },

  async deleteAccount(): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>('/profile/delete')
    return response.data
  },
}

export default profileService
