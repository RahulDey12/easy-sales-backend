<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'client_title' => $this->title,
                'client_first_name' => $this->first_name,
                'client_last_name' => $this->last_name,
                'phone_number' => $this->phone_number,
                'requirement' => $this->whenNotNull($this->requirement),
                'status' => $this->status
            ],
            'relationships' => [
                'caller' => new CallerResource($this->whenLoaded('caller')),
                'call_meta' => new CallMetaResource($this->whenLoaded('meta')),
            ]
        ];
    }
}
