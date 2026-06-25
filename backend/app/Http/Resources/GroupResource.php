<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'description' => $this->description,

            'created_by' => $this->created_by,

            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),

            'members_count' => $this->whenLoaded(
                'members',
                fn () => $this->members->count()
            ),

            'members' => $this->whenLoaded('members', function () {
                return $this->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'phone_no' => $member->phone_no,
                        'role' => $member->pivot->role,
                    ];
                });
            }),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
