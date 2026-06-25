import type { Id } from "./common";

export interface BaseUser {
  id: Id;
  name: string;
  phone_no: string;
}

export interface User extends BaseUser {
  email: string;
}
