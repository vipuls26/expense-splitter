import type { Id } from "./common";
import type { ExpenseCategory } from "./expenseCategory";

export interface Budget {
  id: Id;
  group_id: Id;
  expense_category_id: Id;
  amount: number;
  expense_category?: ExpenseCategory;
  created_at: string;
  updated_at: string;
}

export interface CreateBudgetPayload {
  expense_category_id: Id;
  amount: number;
}

export interface UpdateBudgetPayload {
  amount: number;
}
