export interface User {
  id: number
  name: string
  username: string
  email: string
  role: 'admin' | 'bendahara' | 'warga' | string
  status: 'Aktif' | 'Nonaktif' | string
  created_at?: string
  updated_at?: string
}

export interface AuthResponse {
  status: string
  message: string
  data: User
  auth: {
    token: string
    token_type: string
  }
}

export interface ApiResponse<T = any> {
  status: string
  message: string
  data: T
  [key: string]: any
}

export interface PaginatedResponse<T = any> {
  status: string
  message: string
  data: T[]
  meta?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  links?: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
  summary?: any
}

export interface Akun {
  id: number
  nama_akun: string
  kas: 'kas pemuda' | 'kas rtrw' | 'kas kematian' | 'kas karang taruna' | string
  jumlah?: number
  keterangan?: string | null
}

export interface PenanggungJawab {
  id: number
  nama: string
}

export interface Transaksi {
  id: number
  akun_id: number
  penginput_id: number
  penanggung_jawab_id?: number | null
  deskripsi: string
  date: string
  jenis_transaksi: 'pemasukan' | 'pengeluaran'
  jumlah: number
  akun?: Akun
  penanggung_jawab?: PenanggungJawab
  penginput?: User
}

export interface MutasiRekening {
  id: number
  akun_debit_id: number
  akun_kredit_id: number
  date: string
  jumlah: number
  keterangan?: string | null
  akun_debit?: Akun
  akun_kredit?: Akun
}

export interface RiilHistory {
  id: number
  akun_id: number
  date: string
  riil: number
  verified: boolean
  akun?: Akun
}
