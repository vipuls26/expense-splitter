export interface GroupMember {
  id: number
  name: string
  phone_no: string
  role: 'owner' | 'member'
}

export interface Group {
  id: number
  name: string
  description: string | null
  created_by: number
  members_count: number
  members: GroupMember[]
  created_at: string
}

export interface CreateGroupPayload {
  name: string
  description?: string
}

export interface UpdateGroupPayload {
  name?: string
  description?: string
}