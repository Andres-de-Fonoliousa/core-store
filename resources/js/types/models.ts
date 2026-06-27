export interface User {
  id: number;
  name: string;
  email: string;
  balance: number;
  role: 'customer' | 'admin';
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  avatar?: string;
}

export interface Category {
  id: number;
  name: string;
  image: string | null;
  status: 'active' | 'inactive';
}

export interface Provider {
  id: number;
  name: string;
  base_url: string;
  token: string;
  sync_active: boolean;
  balance: number;
  status: 'active' | 'inactive';
}

export interface Product {
  id: number;
  name: string;
  price: number;
  cost_price: number;
  external_id: string;
  category: Category;
  provider: Provider;
  image: string;
  params: string[];
  qty_values: number[];
  status: 'active' | 'inactive';
  is_auto: boolean;
}

export interface Transaction {
  id: number;
  amount: number;
  status: 'pending' | 'completed' | 'rejected';
  type: 'deposit' | 'order';
  created_at: string;
}

export interface Order {
  id: number;
  status: 'paid' | 'unpaid';
  fulfillment_status: 'fulfilled' | 'pending_fulfillment' | 'failed';
  serial_code: string | null;
  price_at_time_of_order: number;
  quantity: number;
  transaction: Transaction;
  product?: Product;
  created_at: string;
}

export interface DepositRequest {
  id: number;
  amount: number;
  status: 'pending' | 'approved' | 'rejected';
  proof_url?: string;
  note?: string;
  user?: User;
  created_at: string;
}
