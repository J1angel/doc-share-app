<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentShareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => $this->relationLoaded('user') && $this->user
                ? (new UserSummaryResource($this->user))->resolve($request)
                : null,
            'permission' => $this->permission,
        ];
    }
}
