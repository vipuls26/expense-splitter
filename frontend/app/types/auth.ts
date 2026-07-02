import type { User } from "./user";

export interface LoginPayload {
    email: string;
    password: string;
}

export interface RegisterPayload {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    phone_no: string;
}

export interface AuthResponse {
    user: User;
    token: string;
}