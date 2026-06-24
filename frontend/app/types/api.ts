export interface ApiResponse<T> {
  success: boolean
  message?: string
  data: T
}


export interface MessageResponse {
  success: boolean
  message: string
}