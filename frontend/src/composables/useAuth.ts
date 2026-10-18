import { ref, computed } from 'vue'
import type { User } from '@/types'

const token = ref<string | null>(localStorage.getItem('token'))
const user = ref<User | null>(
  localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')!) : null,
)

export function useAuth() {
  const isAuthenticated = computed(() => !!token.value)
  const isBendahara = computed(() => user.value?.role === 'bendahara' || user.value?.role === 'admin')

  function setAuth(newToken: string, newUser: User) {
    token.value = newToken
    user.value = newUser
    localStorage.setItem('token', newToken)
    localStorage.setItem('user', JSON.stringify(newUser))
  }

  function setUser(updatedUser: User) {
    user.value = updatedUser
    localStorage.setItem('user', JSON.stringify(updatedUser))
  }

  function clearAuth() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  return {
    token,
    user,
    isAuthenticated,
    isBendahara,
    setAuth,
    setUser,
    clearAuth,
  }
}
