<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    protected bool $includeContent = false;

    public function withContent(bool $include = true): static
    {
        $this->includeContent = $include;

        return $this;
    }

    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'relation' => $this->isOwnedBy($user) ? 'owned' : 'shared',
            'is_owner' => $this->isOwnedBy($user),
            'can_edit' => $this->canEdit($user),
            'owner' => $this->relationLoaded('owner') && $this->owner
                ? new UserSummaryResource($this->owner)
                : null,
            'shared_with' => $this->relationLoaded('sharedWith')
                ? $this->sharedWith->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'permission' => $u->pivot->permission,
                ])
                : [],
            'attachments' => $this->relationLoaded('attachments')
                ? DocumentAttachmentResource::collection($this->attachments)
                : [],
            'updated_at' => $this->updated_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'content' => $this->when($this->includeContent, $this->content),
        ];
    }
}
