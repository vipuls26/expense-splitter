import type { Id } from "./common";
import type { BaseUser } from "./user";
import type { ExpenseCategory } from "./expenseCategory";

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
  expense_category?: ExpenseCategory;
  date: string;
  paid_by: BaseUser;
  splits: ExpenseSplit[];
  created_at: string;
}

export interface CreateExpensePayload {
  amount: number;
  description: string;
  expense_category_id: Id;
  paid_by?: Id;
  date?: string;

  splits: {
    user_id: Id;
    amount_owed: number;
  }[];
}
