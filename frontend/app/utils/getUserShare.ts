import type { Id } from "~/types/common";
import type { Expense } from "~/types/expense";

export default function getUserShare(expense: Expense, userId?: Id): number {
  if (!userId) {
    return 0;
  }

  const split = expense.splits?.find(
    (split) => split.user.id === userId,
  );

  return Number(split?.amount_owed ?? 0);
}