import type { Id } from "./common";

export interface ExpenseCategory {
  id: Id;
  name: string;
  icon: string | null;
  color: string | null;
}
