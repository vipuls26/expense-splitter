<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettlementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'from' => [
                'id' => $this->resource['from']['id'],
                'name' => $this->resource['from']['name'],
                'phone_no' => $this->resource['from']['phone_no'],
            ],
            'to' => [
                'id' => $this->resource['to']['id'],
                'name' => $this->resource['to']['name'],
                'phone_no' => $this->resource['to']['phone_no'],
            ],
            'amount' => $this->resource['amount'],
        ];
    }
}
