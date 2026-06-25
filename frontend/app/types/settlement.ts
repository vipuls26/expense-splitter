import type { BaseUser } from "./user";

export interface Balance {
  user: BaseUser;
  balance: number;
}

export interface Settlement {
  from: BaseUser;
  to: BaseUser;
  amount: number;
}

export interface SettlementResponse {
  balances: Balance[];
  settlements: Settlement[];
}
