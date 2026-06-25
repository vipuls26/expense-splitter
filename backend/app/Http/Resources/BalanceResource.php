<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->resource['user']['id'],
                'name' => $this->resource['user']['name'],
                'phone_no' => $this->resource['user']['phone_no'],
            ],
            'balance' => $this->resource['balance'],
        ];
    }
}
