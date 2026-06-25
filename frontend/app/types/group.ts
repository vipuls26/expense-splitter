import type { Id } from "./common";
import type { BaseUser } from "./user";

export interface GroupMember extends BaseUser {
  role: "owner" | "member";
}

export interface Group {
  id: Id;
  name: string;
  description: string | null;
  created_by: Id;
  members_count: number;
  members: GroupMember[];
  created_at: string;
}

export interface CreateGroupPayload {
  name: string;
  description?: string;
}

export interface UpdateGroupPayload {
  name?: string;
  description?: string;
}
