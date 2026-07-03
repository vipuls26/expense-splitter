export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data: T;         // T means some type of data come from BE
}

export interface MessageResponse {
  success: boolean;
  message: string;
}

export interface PaginatedMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  links: { url: string | null; label: string; active: boolean }[];
  path: string;
  per_page: number;
  to: number | null;
  total: number;
}

export interface ApiPaginatedResponse<T> {
  success: boolean;
  message?: string;
  data: T;
  meta: PaginatedMeta;
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
}
