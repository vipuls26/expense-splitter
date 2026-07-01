import type { Id } from "~/types/common";
import type { CreateExpensePayload } from "~/types/expense";

export default function buildEqualSplits(totalAmount: number, membersToSplit: Id[],): CreateExpensePayload["splits"] {
    const baseAmount =
        Math.floor((totalAmount / membersToSplit.length) * 100) / 100;

    const remainder =
        Math.round((totalAmount - baseAmount * membersToSplit.length) * 100) / 100;

    return membersToSplit.map((userId, index) => ({
        user_id: userId,
        amount_owed: Number(
            (baseAmount + (index === 0 ? remainder : 0)).toFixed(2),
        ),
    }));
}