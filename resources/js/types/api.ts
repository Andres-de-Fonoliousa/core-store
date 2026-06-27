import { User, Product, Order, Category, Provider, DepositRequest } from '@/types/models';

export interface LoginRequest { email: string; password: string; }
export interface LoginResponse { token: string; user: User; }
export interface RegisterRequest { name: string; email: string; password: string; password_confirmation: string; }
export interface RegisterResponse { token: string; user: User; }

export interface ProductsListResponse {
  data: Product[];
  current_page: number;
  last_page: number;
  total: number;
}
export interface ProductDetailResponse extends Product {}

export interface CreateOrderRequest {
  product_id: number;
  quantity: number;
  playerId: string;
  params: Record<string, string>;
}
export interface CreateOrderResponse extends Order {}
export interface OrdersListResponse {
  data: Order[];
  current_page: number;
  last_page: number;
}

export interface CreateDepositRequest {
  amount: number;
  proof: File;
  note?: string;
}
export interface CreateDepositResponse {
  id: number;
  amount: number;
  type: 'deposit';
  status: 'pending';
  created_at: string;
}

export interface AdminDepositsResponse {
  data: DepositRequest[];
}
export interface ApproveDepositResponse { message: string; }
export interface ProviderTopUpRequest { amount: number; }
export interface ProviderTopUpResponse { balance: number; }
export interface ProfitMarginRequest { percent: number; }
export interface ProfitMarginResponse { percent: number; }
