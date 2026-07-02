import type { Group } from "./group";

export interface GroupCreatedEvent {
    group: Group;
}

export interface GroupUpdatedEvent {
    groupId: number;
    group: Group;
}

export interface MemberRemovedEvent {
    groupId: number;
    userId: number;
    userName: string;
}