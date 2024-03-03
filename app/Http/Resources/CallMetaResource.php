<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallMetaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'started' => $this->starts_at,
                'ended' => $this->ends_at,
                'transcription' => $this->transcription,
                'extraction' => $this->extraction,
            ]
        ];
    }
}
