export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data: T;         // T means some type of data come from BE
}

export interface MessageResponse {
  success: boolean;
  message: string;
}
