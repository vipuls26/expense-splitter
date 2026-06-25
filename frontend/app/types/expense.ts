import type { Id } from "./common";
import type { BaseUser } from "./user";

export interface ExpenseSplit {
  user: BaseUser;
  amount_owed: string;
}

export interface Expense {
  id: Id;
  group_id: Id;
  amount: string;
  description: string;
  is_settlement: boolean;
  date: string;
  paid_by: BaseUser;
  splits: ExpenseSplit[];
  created_at: string;
}

export interface CreateExpensePayload {
  amount: number;
  description: string;
  paid_by?: Id;
  date?: string;

  splits: {
    user_id: Id;
    amount_owed: number;
  }[];
}
